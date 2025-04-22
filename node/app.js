// app/app.js
const fastify = require('fastify')({ logger: true });  // <-- enable logger
const redis = require('redis');
const db = require('./db'); // MySQL DB connection

// Connect to Redis running locally inside the container
const client = redis.createClient({
  url: 'redis://localhost:6379'
});

client.connect()
  .then(() => console.log('Connected to Redis'))
  .catch(err => {
    console.error('Redis connection error :', err);
    process.exit(1);
  });

fastify.get('/', async (request, reply) => {
  try {
    const count = await client.incr('visit_count');
    fastify.log.info("Incoming Request " + count);

      // Log full request body
      const logData = JSON.stringify(count);
      db.query(`INSERT INTO logs (data) VALUES (?)`, [logData], (err, result) => {
        if (err) {
          console.error('❌ Failed to log request:', err);
        } else {
          console.log(`📥 Logged request with ID ${result.insertId}`);
        }
      });

    return { message: 'Hello from Node.js', visits: count };
  } catch (err) {
    console.error('Redis error:', err);
    reply.code(500).send({ error: 'Redis failure' });
  }
});

fastify.listen({ port: 3110, host: '0.0.0.0' }, (err, address) => {
  if (err) {
    console.error(err);
    process.exit(1);
  }
  console.log(`Fastify running at ${address}`);
});
