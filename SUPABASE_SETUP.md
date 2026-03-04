# Supabase Real-time Setup Guide

This guide will help you set up Supabase real-time functionality for the OJT Attendance System.

## Prerequisites

1. A Supabase project created at [supabase.com](https://supabase.com)
2. Your Supabase project credentials (URL, anon key, service key)
3. Laravel application with the attendance system installed

## Step 1: Configure Environment Variables

Update your `.env` file with your Supabase credentials:

```env
# Database Configuration
DB_CONNECTION=pgsql
DB_HOST=db.your-project-ref.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your-database-password

# Supabase Real-time Configuration
SUPABASE_URL=https://your-project-ref.supabase.co
SUPABASE_ANON_KEY=your-anon-key
SUPABASE_SERVICE_KEY=your-service-key

# Vite Environment Variables (for frontend)
VITE_SUPABASE_URL="${SUPABASE_URL}"
VITE_SUPABASE_ANON_KEY="${SUPABASE_ANON_KEY}"

# Set timezone to Philippine Time
APP_TIMEZONE=Asia/Manila
```

## Step 2: Test Database Connection

Run the setup command to test your connection:

```bash
php artisan supabase:setup-realtime --test
```

If successful, you should see:
```
✅ Detected Supabase connection: db.your-project-ref.supabase.co
✅ Database connection successful
✅ Connection test completed successfully
```

## Step 3: Run Laravel Migrations

Migrate your database schema to Supabase:

```bash
php artisan migrate
php artisan db:seed
```

## Step 4: Apply Supabase Real-time Policies

Run the setup command to get the SQL migration:

```bash
php artisan supabase:setup-realtime
```

This will display SQL that you need to run in your Supabase SQL Editor.

### Manual Steps in Supabase Dashboard:

1. Go to your [Supabase Dashboard](https://supabase.com/dashboard)
2. Select your project
3. Navigate to **SQL Editor** in the left sidebar
4. Click **New Query**
5. Copy and paste the SQL from the command output
6. Click **Run** to execute the migration

The SQL will:
- Enable Row Level Security (RLS) on all tables
- Create policies for students and admins
- Enable real-time subscriptions
- Set up proper indexes for performance

## Step 5: Build Frontend Assets

Build the frontend assets with Supabase integration:

```bash
npm install
npm run build
```

## Step 6: Test Real-time Functionality

1. Visit the test endpoint: `http://your-app.com/test-supabase`
2. You should see a JSON response indicating Supabase is connected
3. Log in as a student and go to the dashboard
4. Open browser developer tools and check the console for real-time connection logs

## Step 7: Verify Real-time Updates

1. Log in as a student
2. Scan an RFID card or manually enter attendance
3. You should see:
   - Real-time notifications in the UI
   - Console logs showing Supabase subscriptions
   - Automatic updates without page refresh

## Troubleshooting

### Connection Issues

If you get connection errors:

1. **Check your credentials** in `.env`
2. **Verify your Supabase project is active**
3. **Check firewall/network settings**
4. **Ensure your IP is allowed** in Supabase settings

### Real-time Not Working

If real-time updates aren't working:

1. **Check browser console** for JavaScript errors
2. **Verify RLS policies** were applied correctly
3. **Check Supabase logs** in the dashboard
4. **Ensure real-time is enabled** for your tables

### Performance Issues

For better performance:

1. **Use indexes** (included in migration)
2. **Limit real-time subscriptions** to necessary tables only
3. **Monitor Supabase usage** in the dashboard

## Environment Variables Reference

| Variable | Description | Example |
|----------|-------------|---------|
| `DB_CONNECTION` | Database driver | `pgsql` |
| `DB_HOST` | Supabase database host | `db.abc123.supabase.co` |
| `DB_DATABASE` | Database name | `postgres` |
| `DB_USERNAME` | Database username | `postgres` |
| `DB_PASSWORD` | Database password | Your project password |
| `SUPABASE_URL` | Supabase project URL | `https://abc123.supabase.co` |
| `SUPABASE_ANON_KEY` | Anonymous key for client | `eyJhbGciOiJIUzI1NiIs...` |
| `SUPABASE_SERVICE_KEY` | Service role key | `eyJhbGciOiJIUzI1NiIs...` |
| `VITE_SUPABASE_URL` | Frontend Supabase URL | `"${SUPABASE_URL}"` |
| `VITE_SUPABASE_ANON_KEY` | Frontend anon key | `"${SUPABASE_ANON_KEY}"` |

## Real-time Features

Once set up, the system provides:

### For Students:
- **Live attendance updates** without page refresh
- **Real-time status changes** (checked in/out)
- **Instant statistics updates** (hours, days present)
- **Live notifications** for successful scans

### For Admins:
- **Real-time attendance monitoring** across all students
- **Live dashboard updates** with new attendance records
- **Instant notifications** for system events

## Security Features

The setup includes:

- **Row Level Security (RLS)** on all tables
- **User-specific data access** (students see only their data)
- **Admin-level permissions** for management functions
- **Secure real-time subscriptions** with proper authentication

## Support

If you encounter issues:

1. Check the Laravel logs: `storage/logs/laravel.log`
2. Check browser console for JavaScript errors
3. Review Supabase logs in the dashboard
4. Test the connection: `php artisan supabase:setup-realtime --test`

## Next Steps

After successful setup:

1. **Test thoroughly** with multiple users
2. **Monitor performance** in Supabase dashboard
3. **Set up backups** for your Supabase project
4. **Configure alerts** for system monitoring