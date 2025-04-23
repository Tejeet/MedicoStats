// ========================================
// 📦 Imports
// ========================================
const fastify = require('fastify')({ logger: true });
const redis = require('redis');
const db = require('./db');
const aedes = require('aedes')();
const net = require('net');


const serverStartTime = Date.now(); 

// ========================================
// 🔗 Redis Connection
// ========================================
const redisClient = redis.createClient({ url: 'redis://localhost:6379' });

redisClient.connect()
  .then(() => console.log('✅ Connected to Redis'))
  .catch(err => {
    console.error('❌ Redis connection error:', err);
    process.exit(1);
  });

// ========================================
// 🚀 Fastify HTTP Routes
// ========================================
fastify.get('/', async (request, reply) => {
  try {
    const count = await redisClient.incr('visit_count');
    fastify.log.info("📩 Incoming Request " + count);

    logMessageToDB('visit_count', count);

    return { message: 'Hello from Node.js', visits: count };
  } catch (err) {
    console.error('❌ Redis error:', err);
    reply.code(500).send({ error: 'Redis failure' });
  }
});

fastify.get('/mqttdata', async (request, reply) => {
  const clients = Object.values(aedes.clients);
  const connectedClients = clients.length;

  const subscriptions = [];
  const topics = new Set();

  clients.forEach(client => {
    const clientId = client.id;
    const subs = Object.keys(client.subscriptions || {});
    subs.forEach(topic => {
      topics.add(topic);
      subscriptions.push({ clientId, topic });
    });
  });

  const retainedMessages = Object.keys(aedes.persistence._retained || {}).length;
  const uptime = `${Math.floor((Date.now() - serverStartTime) / 1000)}s`;

  return {
    mqttServer: {
      listening: mqttServer.listening,
      port: mqttServer.address().port,
      uptime
    },
    stats: {
      connectedClients,
      clientIds: clients.map(c => c.id),
      uniqueTopics: Array.from(topics),
      totalSubscriptions: subscriptions.length,
      subscriptions,
      retainedMessages
    }
  };
});

fastify.listen({ port: 3110, host: '0.0.0.0' }, (err, address) => {
  if (err) {
    console.error(err);
    process.exit(1);
  }
  console.log(`🚀 Fastify running at ${address}`);
});

// ========================================
// 📡 MQTT - Aedes Setup
// ========================================
const mqttServer = net.createServer(aedes.handle);
const MQTT_PORT = 1884;

mqttServer.listen(MQTT_PORT, '0.0.0.0', () => {
  console.log(`📡 Aedes MQTT server started on port ${MQTT_PORT}`);
});

// ========================================
// 🧠 Aedes Event Handlers
// ========================================
aedes.authenticate = (client, username, password, callback) => {
  const authorized = username === 'tejeet' && password.toString() === '1234';
  if (authorized) {
    console.log(`✅ MQTT Authenticated: ${client.id}`);
    callback(null, true);
  } else {
    console.log(`❌ MQTT Authentication Failed: ${client.id}`);
    callback(null, false);
  }
};

aedes.on('client', client => console.log(`🔌 MQTT Client Connected: ${client?.id}`));
aedes.on('clientDisconnect', client => console.log(`❌ MQTT Client Disconnected: ${client?.id}`));
aedes.on('subscribe', (subscriptions, client) => {
  subscriptions.forEach(sub => console.log(`📡 Client ${client?.id} subscribed to ${sub.topic}`));
});
aedes.on('unsubscribe', (subscriptions, client) => {
  subscriptions.forEach(topic => console.log(`📴 Client ${client?.id} unsubscribed from ${topic}`));
});
aedes.on('publish', (packet, client) => {
  console.log("new Publish msg");
  if (client) {
    const topic = packet.topic.toString();
    const message = packet.payload.toString();
    console.log(`📨 MQTT Message from ${client.id} on ${topic}: ${message}`);
    logMessageToDB(topic, message);
  }
});

// ========================================
// 🧾 Utility: Log to DB
// ========================================
function logMessageToDB(topic, message) {
  const logData = JSON.stringify({ topic, message });
  db.query('INSERT INTO logs (data) VALUES (?)', [logData], (err, result) => {
    if (err) console.error('❌ Failed to log request:', err);
    else console.log(`📥 Logged request with ID ${result.insertId}`);
  });
}

// ========================================
// 📤 Message Sender: Send to Specific Client
// ========================================
function sendMessageToClient(clientId, topic, message) {
  const client = aedes.clients[clientId];
  if (client) {
    aedes.publish({ topic, payload: message, qos: 0, retain: false }, (err) => {
      if (err) console.error(`❌ Error sending to ${clientId}:`, err);
      else console.log(`⏱️ Sent "${message}" to ${clientId} on topic "${topic}"`);
    });
  } else {
    console.log(`⚠️ Client "${clientId}" not connected.`);
  }
}

// ========================================
// ⏲️ Interval Messaging to Client
// ========================================
setInterval(() => {
  const now = new Date().toISOString();
  sendMessageToClient('1234', 'timestamp', now);
}, 10000);