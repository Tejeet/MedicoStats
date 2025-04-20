// app/app.js
const fastify = require('fastify')();
const redis = require('redis');

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
