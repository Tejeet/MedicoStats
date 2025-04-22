const fastify = require('fastify')({ logger: true });
const redis = require('redis');
const db = require('./db');
const aedes = require('aedes')();
const net = require('net');

// Redis connection
const client = redis.createClient({ url: 'redis://localhost:6379' });

client.connect()
  .then(() => console.log('✅ Connected to Redis'))
  .catch(err => {
    console.error('Redis connection error :', err);
    process.exit(1);
  });

// Fastify HTTP Route
fastify.get('/', async (request, reply) => {
  try {
    const count = await client.incr('visit_count');
    fastify.log.info("📩 Incoming Request " + count);

    const logData = JSON.stringify(count);
    db.query(`INSERT INTO logs (data) VALUES (?)`, [logData], (err, result) => {
      if (err) console.error('❌ Failed to log request:', err);
      else console.log(`📥 Logged request with ID ${result.insertId}`);
    });

    return { message: 'Hello from Node.js', visits: count };
  } catch (err) {
    console.error('Redis error:', err);
    reply.code(500).send({ error: 'Redis failure' });
  }
});

// Start Fastify
fastify.listen({ port: 3110, host: '0.0.0.0' }, (err, address) => {
  if (err) {
    console.error(err);
    process.exit(1);
  }
  console.log(`🚀 Fastify running at ${address}`);
});

fastify.get('/mqttdata', async (request, reply) => {
  // Helper function to remove circular references from the object
  function removeCircularReferences(obj, seen = new Set()) {
    if (typeof obj !== 'object' || obj === null) {
      return obj; // Base case: non-object or null
    }

    if (seen.has(obj)) {
      return; // Circular reference found, return undefined
    }

    seen.add(obj);

    // Recursively copy the object, handling circular references
    const result = Array.isArray(obj) ? [] : {};

    for (const [key, value] of Object.entries(obj)) {
      result[key] = removeCircularReferences(value, seen);
    }

    return result;
  }

  // Safe stringified version of the aedes object without circular references
  const aedesData = removeCircularReferences(aedes);

  return {
    mqttServer: {
      listening: mqttServer.listening,
      port: mqttServer.address().port
    },
    aedes: aedesData
  };
});



// ======================
// 🔌 MQTT - Aedes Setup
// ======================
const mqttServer = net.createServer(aedes.handle);
const MQTT_PORT = 1884;


// Start MQTT TCP server on 0.0.0.0
mqttServer.listen(MQTT_PORT, '0.0.0.0', () => {
  console.log(`📡 Aedes MQTT server started on port ${MQTT_PORT}`);
});
// ======================
// 🧠 Aedes Event Handlers
// ======================

// Optional: Authentication
aedes.authenticate = (client, username, password, callback) => {
  const authorized =
    username === 'tejeet' && password.toString() === '1234';

  if (authorized) {
    console.log(`✅ MQTT Authenticated: ${client.id}`);
    callback(null, true);
  } else {
    console.log(`❌ MQTT Authentication Failed: ${client.id}`);
    callback(null, false);
  }
};

// On client connect
aedes.on('client', (client) => {
  console.log(`🔌 MQTT Client Connected: ${client?.id}`);
});

// On client disconnect
aedes.on('clientDisconnect', (client) => {
  console.log(`❌ MQTT Client Disconnected: ${client?.id}`);
});

// On client subscribe
aedes.on('subscribe', (subscriptions, client) => {
  subscriptions.forEach(sub => {
    console.log(`📡 Client ${client?.id} subscribed to ${sub.topic}`);
  });
});

// On client unsubscribe
aedes.on('unsubscribe', (subscriptions, client) => {
  subscriptions.forEach(topic => {
    console.log(`📴 Client ${client?.id} unsubscribed from ${topic}`);
  });
});

aedes.on('publish', async (packet, client) => {
  if (client) {
    console.log(`📨 MQTT Message from ${client.id} on ${packet.topic}: ${packet.payload.toString()}`);
    logMessageToDB(packet.topic.toString(), packet.payload.toString());
  }
});


// Database Queries

// Function to log messages to database
function logMessageToDB(topic, message) {
  const logData = JSON.stringify({ topic, message });
  db.query('INSERT INTO logs (data) VALUES (?)', [logData], (err, result) => {
    if (err) console.error('❌ Failed to log request:', err);
    else console.log(`📥 Logged request with ID ${result.insertId}`);
  });
}

// Function to send a message to a specific client
function sendMessageToClient(clientId, topic, message) {
  const client = aedes.clients[clientId];
  if (client) {
    aedes.publish({
      topic: topic,
      payload: message,
      qos: 0,
      retain: false
    }, (err) => {
      if (err) {
        console.error(`❌ Error sending to ${clientId}:`, err);
      } else {
        console.log(`⏱️ Sent "${message}" to ${clientId} on topic "${topic}"`);
      }
    });
  } else {
    console.log(`⚠️ Client "${clientId}" not connected.`);
  }
}

// Send timestamp every 10 seconds to client "1234"
setInterval(() => {
  const now = new Date().toISOString();
  sendMessageToClient('1234', 'timestamp', now);
}, 10000);