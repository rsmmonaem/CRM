# SQLite to MySQL Migration Guide

This guide will help you migrate your Laravel CRM from SQLite to MySQL while preserving all your existing data.

## Prerequisites

- MySQL server installed and running
- Access to MySQL command line or phpMyAdmin
- Backup of your current SQLite database

## Step 1: Export SQLite Data

Run the data export script:

```bash
php export_sqlite_data.php
```

This will create a `database_mysql_import.sql` file with all your data.

## Step 2: Create MySQL Database

Connect to MySQL and create a new database:

```sql
CREATE DATABASE crm_database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'crm_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON crm_database.* TO 'crm_user'@'localhost';
FLUSH PRIVILEGES;
```

## Step 3: Update Environment Configuration

Create or update your `.env` file with MySQL settings:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=crm_database
DB_USERNAME=crm_user
DB_PASSWORD=your_secure_password
```

## Step 4: Run Laravel Migrations

Create the database structure in MySQL:

```bash
php artisan migrate:fresh
```

## Step 5: Import Your Data

Import the exported data into MySQL:

```bash
mysql -u crm_user -p crm_database < database_mysql_import.sql
```

## Step 6: Test Your Application

1. Start your Laravel development server:
   ```bash
   php artisan serve
   ```

2. Test all functionality:
   - Login/logout
   - Create/edit leads
   - View dashboard
   - Call logging
   - All CRUD operations

## Step 7: Clean Up

Once everything is working:

1. Remove the SQLite database file:
   ```bash
   rm database/database.sqlite
   ```

2. Remove migration scripts:
   ```bash
   rm migrate_to_mysql.php
   rm export_sqlite_data.php
   rm database_mysql_import.sql
   ```

## Troubleshooting

### Common Issues:

1. **Character Encoding**: Ensure MySQL uses utf8mb4 for proper Unicode support
2. **Foreign Key Constraints**: Laravel migrations handle these automatically
3. **Data Types**: Some SQLite types may need adjustment for MySQL
4. **Permissions**: Ensure the MySQL user has proper privileges

### If Something Goes Wrong:

1. Restore your SQLite database from backup
2. Check MySQL error logs
3. Verify database credentials in `.env`
4. Ensure all required PHP extensions are installed (pdo_mysql)

## Benefits of MySQL:

- Better performance for larger datasets
- More robust data integrity
- Better concurrent access handling
- More advanced querying capabilities
- Better backup and recovery options
- Production-ready scalability

## Security Notes:

- Use strong passwords for MySQL users
- Consider using SSL connections in production
- Regularly backup your MySQL database
- Keep MySQL server updated
