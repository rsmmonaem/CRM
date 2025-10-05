<?php
/**
 * SQLite to MySQL Migration Script
 * This script will help you migrate your Laravel CRM from SQLite to MySQL
 * while preserving all your existing data.
 */

// Step 1: Export SQLite data
echo "=== SQLite to MySQL Migration Script ===\n\n";

echo "Step 1: Exporting SQLite data...\n";
echo "Run this command to export your SQLite data:\n";
echo "sqlite3 database/database.sqlite .dump > database_export.sql\n\n";

echo "Step 2: Create MySQL database\n";
echo "Connect to MySQL and run:\n";
echo "CREATE DATABASE crm_database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n";
echo "CREATE USER 'crm_user'@'localhost' IDENTIFIED BY 'your_password';\n";
echo "GRANT ALL PRIVILEGES ON crm_database.* TO 'crm_user'@'localhost';\n";
echo "FLUSH PRIVILEGES;\n\n";

echo "Step 3: Update your .env file\n";
echo "Change these values in your .env file:\n";
echo "DB_CONNECTION=mysql\n";
echo "DB_HOST=127.0.0.1\n";
echo "DB_PORT=3306\n";
echo "DB_DATABASE=crm_database\n";
echo "DB_USERNAME=crm_user\n";
echo "DB_PASSWORD=your_password\n\n";

echo "Step 4: Run Laravel migrations\n";
echo "php artisan migrate:fresh\n\n";

echo "Step 5: Import your data\n";
echo "Use the data import script provided below.\n\n";

echo "=== END OF INSTRUCTIONS ===\n";
