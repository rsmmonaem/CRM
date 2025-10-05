#!/bin/bash
# MySQL Setup and Migration Script

echo "=== Laravel CRM: SQLite to MySQL Migration ==="
echo ""

# Check if MySQL is installed
if ! command -v mysql &> /dev/null; then
    echo "❌ MySQL is not installed. Please install MySQL first."
    echo "   On macOS: brew install mysql"
    echo "   On Ubuntu: sudo apt-get install mysql-server"
    echo "   On Windows: Download from https://dev.mysql.com/downloads/"
    exit 1
fi

echo "✅ MySQL is installed"

# Check if MySQL service is running
if ! pgrep -x "mysqld" > /dev/null; then
    echo "⚠️  MySQL service is not running. Starting MySQL..."
    if command -v brew &> /dev/null; then
        brew services start mysql
    elif command -v systemctl &> /dev/null; then
        sudo systemctl start mysql
    else
        echo "Please start MySQL service manually"
        exit 1
    fi
fi

echo "✅ MySQL service is running"

# Get database credentials
echo ""
echo "Please provide MySQL root credentials:"
read -p "MySQL root password: " -s mysql_root_password
echo ""

# Create database and user
echo "Creating database and user..."

mysql -u root -p$mysql_root_password << EOF
CREATE DATABASE IF NOT EXISTS crm_database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'crm_user'@'localhost' IDENTIFIED BY 'crm_password_2024';
GRANT ALL PRIVILEGES ON crm_database.* TO 'crm_user'@'localhost';
FLUSH PRIVILEGES;
EOF

if [ $? -eq 0 ]; then
    echo "✅ Database and user created successfully"
else
    echo "❌ Failed to create database. Please check your MySQL root password."
    exit 1
fi

# Create .env file
echo ""
echo "Creating .env file with MySQL configuration..."

cat > .env << EOF
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:$(php artisan key:generate --show | cut -d: -f2)
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
APP_MAINTENANCE_STORE=database

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=crm_database
DB_USERNAME=crm_user
DB_PASSWORD=crm_password_2024

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database
CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="\${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="\${APP_NAME}"
EOF

echo "✅ .env file created"

# Run migrations
echo ""
echo "Running Laravel migrations..."
php artisan migrate:fresh

if [ $? -eq 0 ]; then
    echo "✅ Migrations completed successfully"
else
    echo "❌ Migrations failed"
    exit 1
fi

# Import data
echo ""
echo "Importing your data..."
mysql -u crm_user -pcrm_password_2024 crm_database < database_mysql_import.sql

if [ $? -eq 0 ]; then
    echo "✅ Data imported successfully"
else
    echo "❌ Data import failed"
    exit 1
fi

echo ""
echo "🎉 Migration completed successfully!"
echo ""
echo "📊 Summary:"
echo "   - Database: crm_database"
echo "   - User: crm_user"
echo "   - Password: crm_password_2024"
echo "   - Host: 127.0.0.1:3306"
echo ""
echo "🚀 Next steps:"
echo "   1. Test your application: php artisan serve"
echo "   2. Login with your existing credentials"
echo "   3. Verify all data is intact"
echo "   4. Remove migration files when satisfied"
echo ""
echo "🔧 To clean up migration files later:"
echo "   rm migrate_to_mysql.php export_sqlite_data.php database_mysql_import.sql MIGRATION_GUIDE.md"
