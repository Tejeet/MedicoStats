// app/index.js
const fastify = require('fastify')();

fastify.get('/', async (request, reply) => {
  return { message: 'Hello' };
});

fastify.listen({ port: 3110, host: '0.0.0.0' }, (err, address) => {
  if (err) {
    console.error(err);
    process.exit(1);
  }
  console.log(`Server running at ${address}`);
});
