# OJT Attendance System - Setup Guide

## Prerequisites

Before setting up this project on a new laptop, ensure you have the following installed:

1. **PHP** (version 8.1 or higher)
   - Download from: https://www.php.net/downloads
   - Verify: `php -v`
   - **Required PHP Extensions** (enable in php.ini):
     ```
     extension=openssl
     extension=pdo_pgsql
     extension=pgsql
     extension=mbstring
     extension=tokenizer
     extension=xml
     extension=ctype
     extension=json
     extension=bcmath
     extension=fileinfo
     extension=gd
     extension=curl
     extension=zip
     ```
   - **How to enable extensions:**
     1. Locate your `php.ini` file (run `php --ini` to find it)
     2. Open `php.ini` in a text editor
     3. Find the extensions listed above and remove the `;` at the beginning of each line
     4. If an extension is missing, add it to the file
     5. Save and restart your web server/terminal

2. **Composer** (PHP dependency manager)
   - Download from: https://getcomposer.org/download/
   - Verify: `composer -v`

3. **Node.js & npm** (version 16 or higher)
   - Download from: https://nodejs.org/
   - Verify: `node -v` and `npm -v`

4. **PostgreSQL** (or Supabase account)
   - This project uses Supabase PostgreSQL
   - Create account at: https://supabase.com

## Installation Steps

### 1. Clone the Repository
```bash
git clone <repository-url>
cd <project-folder>
```

### 2. Install PHP Dependencies
```bash
composer install
```

### 3. Install Node Dependencies
```bash
npm install
```

### 4. Environment Configuration

Copy the example environment file:
```bash
copy .env.example .env
```

Edit `.env` file and configure the following:

#### Application Settings
```
APP_NAME="OJT Attendance System"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000
```

#### Database Configuration (Supabase)
```
DB_CONNECTION=pgsql
DB_HOST=your-supabase-host.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your-supabase-password
```

**To get Supabase credentials:**
- Go to your Supabase project dashboard
- Navigate to Settings > Database
- Copy the connection details

#### Session & Cache
```
CACHE_DRIVER=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database
```

#### Mail Configuration (Optional)
```
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 5. Generate Application Key
```bash
php artisan key:generate
```

### 6. Run Database Migrations
```bash
php artisan migrate
```

If you need to seed initial data:
```bash
php artisan db:seed
```

### 7. Create Storage Link
```bash
php artisan storage:link
```

### 8. Build Frontend Assets
For development:
```bash
npm run dev
```

For production:
```bash
npm run build
```

### 9. Start the Development Server
```bash
php artisan serve
```



- Clear cache: `php artisan cache:clear`
- Clear config: `php artisan config:clear`
- Clear views: `php artisan view:clear`
- Run tests: `php artisan test`


