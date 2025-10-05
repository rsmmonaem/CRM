# Modern CRM System

A comprehensive Customer Relationship Management (CRM) system built with Laravel 12, Vue.js 3, and Inertia.js. This application provides lead management, call tracking, user management, and role-based permissions for efficient business operations.

## 🚀 Features

### Core Functionality
- **Lead Management**: Create, edit, and track customer leads with detailed information
- **Call Tracking**: Log and manage follow-up calls with scheduling capabilities
- **Service Management**: Organize and categorize business services
- **Status Tracking**: Monitor lead progression through customizable statuses
- **User Management**: Comprehensive user administration with role-based access
- **Permission System**: Granular permissions for different modules and actions
- **Dashboard Analytics**: Overview of leads, calls, and user performance
- **Search Functionality**: Quick search across leads and other entities

### Technical Features
- **Modern Stack**: Laravel 12 + Vue.js 3 + Inertia.js + Tailwind CSS
- **Real-time Updates**: Server-side rendering with Vue.js components
- **Responsive Design**: Mobile-friendly interface with Tailwind CSS
- **API Endpoints**: RESTful API for lead details and call history
- **Database Support**: SQLite (default) and MySQL compatibility
- **Authentication**: Laravel Breeze with Sanctum for API authentication

## 📋 Prerequisites

Before you begin, ensure you have the following installed:

- **PHP**: 8.2 or higher
- **Composer**: Latest version
- **Node.js**: 18.x or higher
- **NPM**: Latest version
- **Database**: SQLite (default) or MySQL 8.0+

## 🛠️ Installation

### 1. Clone the Repository

```bash
git clone https://github.com/rsmmonaem/CRM.git
cd CRM
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node.js Dependencies

```bash
npm install
```

### 4. Environment Configuration

```bash
# Copy the environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 5. Database Setup

#### Option A: SQLite (Default)
```bash
# Create SQLite database file
touch database/database.sqlite
```

#### Option B: MySQL
1. Create a MySQL database
2. Update `.env` file with your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=crm
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 6. Run Database Migrations

```bash
php artisan migrate
```

### 7. Seed Initial Data

```bash
php artisan db:seed
```

### 8. Build Frontend Assets

```bash
# For development
npm run dev

# For production
npm run build
```

### 9. Start the Application

```bash
# Start Laravel development server
php artisan serve

# In another terminal, start Vite for frontend assets
npm run dev
```

The application will be available at `http://localhost:8000`

## 🔧 Development Setup

### Using Composer Scripts

The project includes convenient development scripts:

```bash
# Start all development services (server, queue, logs, vite)
composer run dev

# Run tests
composer run test
```

This will start:
- Laravel development server
- Queue worker
- Log monitoring (Pail)
- Vite development server

### Database Migrations

The application includes the following key migrations:

- `create_users_table` - User authentication and management
- `create_services_table` - Business services catalog
- `create_statuses_table` - Lead status definitions
- `create_leads_table` - Main lead management
- `create_lead_details_table` - Call tracking and follow-ups
- `create_permissions_table` - Permission system
- `create_user_permissions_table` - User permission assignments

## 📊 Database Schema

### Core Models

#### Leads
- **name**: Lead contact name
- **company_name**: Company information
- **location**: Geographic location
- **phone**: Contact phone number
- **email**: Contact email address
- **service_id**: Associated service
- **status_id**: Current lead status
- **assigned_user_id**: Assigned team member
- **created_by**: Lead creator

#### Lead Details
- **lead_id**: Associated lead
- **call_followup_date**: Scheduled call date
- **call_followup_summary**: Call notes and summary
- **next_call_date**: Next scheduled follow-up
- **created_by**: Call log creator

#### Users
- **name**: User full name
- **email**: User email (login)
- **password**: Encrypted password
- **role**: User role (admin/user)

#### Permissions
- **module**: Permission module (leads, services, users, etc.)
- **action**: Permission action (view, create, edit, delete)

## 🔐 User Roles & Permissions

### Admin Role
- Full access to all modules and actions
- User management capabilities
- System configuration access

### User Role
- Access based on assigned permissions
- Module-specific permissions (leads, services, statuses, users)
- Action-specific permissions (view, create, edit, delete)

### Permission System
The application uses a granular permission system where:
- Permissions are organized by modules (leads, services, statuses, users)
- Each module has specific actions (view, create, edit, delete)
- Users can be assigned specific permission combinations
- Admins automatically have all permissions

## 🚀 Usage

### 1. First Login
1. Navigate to `http://localhost:8000`
2. Register a new account or use seeded admin credentials
3. Complete your profile setup

### 2. Lead Management
1. **Create Leads**: Add new customer leads with contact information
2. **Assign Leads**: Assign leads to team members
3. **Update Status**: Track lead progression through sales pipeline
4. **Add Services**: Associate leads with relevant business services

### 3. Call Tracking
1. **Schedule Calls**: Set follow-up dates for leads
2. **Log Calls**: Record call summaries and outcomes
3. **Track History**: View complete call history for each lead
4. **Manage Follow-ups**: Schedule and track next call dates

### 4. User Management
1. **Create Users**: Add new team members
2. **Assign Permissions**: Configure user access levels
3. **Manage Roles**: Set user roles and capabilities

### 5. Dashboard
- View lead statistics and performance metrics
- Monitor pending calls and follow-ups
- Track user activity and assignments

## 🔌 API Endpoints

### Public API
- `GET /api/leads/{lead}/call-history-public` - Get lead call history (public)

### Authenticated API
- `GET /api/search` - Search across entities
- `GET /api/quick-search` - Quick search functionality
- `GET /api/leads/{lead}/details` - Get detailed lead information
- `GET /api/leads/{lead}/call-history` - Get authenticated lead call history

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit
```

## 📁 Project Structure

```
app/
├── Http/
│   ├── Controllers/     # API and web controllers
│   ├── Middleware/      # Custom middleware
│   └── Requests/        # Form request validation
├── Models/              # Eloquent models
└── Providers/           # Service providers

resources/
├── js/
│   ├── Components/      # Vue.js components
│   ├── Layouts/         # Application layouts
│   ├── Pages/           # Inertia.js pages
│   └── composables/     # Vue composables
└── css/                 # Stylesheets

database/
├── migrations/          # Database migrations
├── seeders/            # Database seeders
└── factories/          # Model factories

routes/
├── web.php             # Web routes
├── auth.php            # Authentication routes
└── console.php         # Artisan commands
```

## 🔧 Configuration

### Environment Variables

Key environment variables in `.env`:

```env
APP_NAME="CRM"
APP_ENV=local
APP_KEY=base64:your-app-key
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database.sqlite

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
```

## 🚀 Deployment

### Production Build

```bash
# Install production dependencies
composer install --optimize-autoloader --no-dev

# Build frontend assets
npm run build

# Run migrations
php artisan migrate --force

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Server Requirements

- PHP 8.2+
- Web server (Apache/Nginx)
- Database (MySQL/PostgreSQL recommended for production)
- SSL certificate (recommended)

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

For support and questions:
- Create an issue in the GitHub repository
- Contact the development team
- Check the documentation wiki

## 🔄 Changelog

### Version 1.0.0
- Initial release
- Lead management system
- Call tracking functionality
- User management with permissions
- Dashboard analytics
- API endpoints for integration

---

**Built with ❤️ using Laravel, Vue.js, and Inertia.js**
