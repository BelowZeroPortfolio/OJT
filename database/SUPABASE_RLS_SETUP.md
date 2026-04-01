# Supabase RLS Setup Guide

## Overview
This guide helps you enable Row Level Security (RLS) on your Supabase database for the attendance system.

## Important: Laravel Backend vs Direct Client Access

Since you're using Laravel for authentication (not Supabase Auth), you have two options:

### Option 1: Service Role Access (Recommended for Laravel)
Your Laravel backend uses the **service_role** key which bypasses RLS. This is the simplest approach.

**Pros:**
- No changes needed to your Laravel code
- Laravel handles all authorization via policies and middleware
- RLS acts as an additional security layer

**Cons:**
- RLS policies for students/admins won't apply to Laravel requests
- Security depends entirely on your Laravel implementation

### Option 2: Implement Supabase Auth
Switch to using Supabase authentication instead of Laravel's built-in auth.

**Pros:**
- RLS policies actively protect your data
- Client-side apps can directly query Supabase securely

**Cons:**
- Requires refactoring your entire authentication system
- More complex setup

## Steps to Enable RLS

### 1. Check Your Supabase Connection

Make sure you're using the correct key in your `.env`:

```env
# For Laravel backend (bypasses RLS)
SUPABASE_KEY=your_service_role_key_here

# For client-side apps (respects RLS)
SUPABASE_ANON_KEY=your_anon_key_here
```

### 2. Run the RLS Policies Script

1. Go to your Supabase Dashboard
2. Navigate to **SQL Editor**
3. Copy the contents of `database/supabase_policies.sql`
4. Paste and run the script

This will:
- Enable RLS on `users`, `attendance_records`, and `locations` tables
- Create policies that allow service_role to bypass RLS
- Create policies for Supabase Auth users (if you switch later)

### 3. Verify RLS is Enabled

Run this query in Supabase SQL Editor:

```sql
SELECT 
    schemaname,
    tablename,
    rowsecurity
FROM pg_tables
WHERE schemaname = 'public'
AND tablename IN ('users', 'attendance_records', 'locations');
```

All tables should show `rowsecurity = true`.

### 4. Check Active Policies

```sql
SELECT 
    schemaname,
    tablename,
    policyname,
    permissive,
    roles,
    cmd
FROM pg_policies
WHERE schemaname = 'public'
ORDER BY tablename, policyname;
```

You should see the "Service role bypass RLS" policy for each table.

## Testing RLS

### Test 1: Service Role Access (Your Laravel App)
Your Laravel app should work normally since it uses the service_role key.

### Test 2: Anon Key Access (Should be restricted)
Try querying with the anon key - it should be blocked unless you're authenticated:

```javascript
// This should fail or return empty results
const { data, error } = await supabase
  .from('attendance_records')
  .select('*');
```

## Troubleshooting

### Laravel queries failing after enabling RLS

**Check your .env file:**
```env
SUPABASE_KEY=eyJhbGc...  # Should be service_role key, not anon key
```

**Verify in code:**
```php
// In config/services.php or wherever you initialize Supabase
'supabase' => [
    'url' => env('SUPABASE_URL'),
    'key' => env('SUPABASE_KEY'), // Must be service_role key
],
```

### Need to temporarily disable RLS for debugging

```sql
-- Disable RLS on a table
ALTER TABLE attendance_records DISABLE ROW LEVEL SECURITY;

-- Re-enable when done
ALTER TABLE attendance_records ENABLE ROW LEVEL SECURITY;
```

## Additional Tables

If you add more tables that need RLS, use this template:

```sql
-- Enable RLS
ALTER TABLE your_table_name ENABLE ROW LEVEL SECURITY;

-- Allow service role to bypass
CREATE POLICY "Service role bypass RLS" ON your_table_name
    FOR ALL 
    USING (auth.role() = 'service_role')
    WITH CHECK (auth.role() = 'service_role');
```

## Security Best Practices

1. **Never expose service_role key to clients** - Only use it server-side
2. **Use anon key for client apps** - If you build a mobile/web app
3. **Keep Laravel authorization** - RLS is an additional layer, not a replacement
4. **Audit policies regularly** - Review who can access what
5. **Test with different roles** - Verify students can't access admin data

## Migration to Supabase Auth (Future)

If you want to fully leverage RLS in the future:

1. Replace Laravel Sanctum with Supabase Auth
2. Update your frontend to use Supabase client libraries
3. The existing RLS policies will automatically work
4. Remove or modify the service_role bypass policies

## Questions?

- RLS enabled but queries slow? Check indexes on user_id and role columns
- Need different policies? Modify `database/supabase_policies.sql`
- Want to add more tables? Follow the template above
