# Database Setup Instructions

## Option 1: Using Supabase (Recommended)

1. **Connect to Supabase**:
   - Click the "Connect to Supabase" button in the top right
   - The migration file will automatically create all tables
   - Connection details will be added to your `.env` file

2. **Default Admin Login**:
   - Username: `admin`
   - Password: `admin123`

## Option 2: Using Local MySQL

### Step 1: Create Database
1. Open your MySQL client (phpMyAdmin, MySQL Workbench, or command line)
2. Run the SQL script from `database_setup.sql`

### Step 2: Update Configuration
Update `includes/config.php` with your local MySQL details:

```php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'your_mysql_username');
define('DB_PASS', 'your_mysql_password');
define('DB_NAME', 'spa_booking');
```

### Step 3: Create Uploads Directory
```bash
mkdir uploads
mkdir uploads/therapists
chmod 755 uploads
chmod 755 uploads/therapists
```

## Default Login Credentials

### Admin Panel Access
- URL: `http://localhost:3000/admin/login.php`
- Username: `admin`
- Password: `admin123`

## Database Tables Created

1. **admins** - Admin user authentication
2. **services** - Available spa services/therapies
3. **therapists** - Therapist profiles and details
4. **therapist_images** - Multiple images per therapist
5. **therapist_services** - Many-to-many relationship between therapists and services
6. **bookings** - Customer booking records

## Sample Data Included

- 1 Admin user (admin/admin123)
- 6 Sample services (Swedish Massage, Deep Tissue, etc.)
- 3 Sample therapists with different specializations
- Service assignments for each therapist

## Next Steps

1. Set up the database using one of the options above
2. Access the admin panel to add real therapist data
3. Upload therapist images through the admin interface
4. Test the booking system on the frontend

## File Upload Requirements

Make sure your web server has write permissions to the `uploads/` directory for image uploads to work properly.