# MySQL Setup Instructions

## Prerequisites
Make sure you have MySQL installed and running:
- XAMPP, WAMP, or standalone MySQL
- phpMyAdmin (optional but recommended)

## Step 1: Create Database

### Option A: Using phpMyAdmin
1. Open phpMyAdmin (usually http://localhost/phpmyadmin)
2. Click "New" to create a new database
3. Database name: `Library_ms`
4. Collation: `utf8mb4_unicode_ci`
5. Click "Create"

### Option B: Using MySQL Command Line
```sql
mysql -u root -p
CREATE DATABASE Library_ms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit
```

### Option C: Using the SQL File
```bash
mysql -u root -p < setup-mysql.sql
```

## Step 2: Update Laravel Configuration
✅ Already done - .env file updated with MySQL settings

## Step 3: Run Laravel Migrations
```bash
php artisan config:clear
php artisan migrate:fresh
```

## Step 4: Create Demo Data
```bash
# Option 1: Web interface
# Visit: http://127.0.0.1:8000/api/auto-setup

# Option 2: Command line
php artisan tinker
# Then run the demo data commands
```

## Database Configuration
- **Host:** 127.0.0.1
- **Port:** 3306
- **Database:** Library_ms
- **Username:** root
- **Password:** (empty - change if needed)

## Troubleshooting
- Make sure MySQL service is running
- Check if port 3306 is available
- Verify root user has no password (or update .env with correct password)
- Ensure MySQL user has CREATE DATABASE privileges