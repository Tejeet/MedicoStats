// db.js
const mysql = require('mysql2');

// Use environment variables or config file in production
const DB_CONFIG = {
  host: '94.136.185.134',
  port: 3306,
  user: 'root',
  password: 'myroot',
  database: 'echo.fleetsapi.com'
};

// Create a connection pool for better performance
const pool = mysql.createPool(DB_CONFIG);


module.exports = pool.promise(); // Use promise-based API
