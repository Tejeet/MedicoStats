// db.js
const mysql = require('mysql2');

// Use environment variables or config file in production
const DB_CONFIG = {
  host: 'sql.freedb.tech',
  port: 3306,
  user: 'freedb_tejeet',
  password: 'uq5RdT?3Vk!?xKT',
  database: 'freedb_containernode'
};

// Create a connection pool for better performance
const pool = mysql.createPool(DB_CONFIG);


module.exports = pool.promise(); // Use promise-based API
