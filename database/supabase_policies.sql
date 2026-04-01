-- Enable Row Level Security (RLS) on attendance_records table
ALTER TABLE attendance_records ENABLE ROW LEVEL SECURITY;

-- Drop existing policies if they exist
DROP POLICY IF EXISTS "Students can view own attendance records" ON attendance_records;
DROP POLICY IF EXISTS "Admins can view all attendance records" ON attendance_records;
DROP POLICY IF EXISTS "Students can insert own attendance records" ON attendance_records;
DROP POLICY IF EXISTS "Students can update own attendance records" ON attendance_records;
DROP POLICY IF EXISTS "Admins can manage all attendance records" ON attendance_records;
DROP POLICY IF EXISTS "Service role bypass RLS" ON attendance_records;

-- Policy for service role (Laravel backend) to bypass RLS
-- This allows your Laravel app to manage all records
CREATE POLICY "Service role bypass RLS" ON attendance_records
    FOR ALL 
    USING (auth.role() = 'service_role')
    WITH CHECK (auth.role() = 'service_role');

-- Policy for students to view only their own attendance records (if using Supabase Auth)
CREATE POLICY "Students can view own attendance records" ON attendance_records
    FOR SELECT USING (
        auth.uid()::text = (
            SELECT users.id::text 
            FROM users 
            WHERE users.id = attendance_records.user_id 
            AND users.role = 'student'
        )
    );

-- Policy for admins to view all attendance records (if using Supabase Auth)
CREATE POLICY "Admins can view all attendance records" ON attendance_records
    FOR SELECT USING (
        EXISTS (
            SELECT 1 FROM users 
            WHERE users.id::text = auth.uid()::text 
            AND users.role = 'admin'
        )
    );

-- Policy for students to insert their own attendance records (if using Supabase Auth)
CREATE POLICY "Students can insert own attendance records" ON attendance_records
    FOR INSERT WITH CHECK (
        auth.uid()::text = (
            SELECT users.id::text 
            FROM users 
            WHERE users.id = attendance_records.user_id 
            AND users.role = 'student'
        )
    );

-- Policy for students to update their own attendance records (if using Supabase Auth)
CREATE POLICY "Students can update own attendance records" ON attendance_records
    FOR UPDATE USING (
        auth.uid()::text = (
            SELECT users.id::text 
            FROM users 
            WHERE users.id = attendance_records.user_id 
            AND users.role = 'student'
        )
    );

-- Policy for admins to insert/update any attendance records (if using Supabase Auth)
CREATE POLICY "Admins can manage all attendance records" ON attendance_records
    FOR ALL USING (
        EXISTS (
            SELECT 1 FROM users 
            WHERE users.id::text = auth.uid()::text 
            AND users.role = 'admin'
        )
    );

-- Enable real-time for attendance_records table
ALTER PUBLICATION supabase_realtime ADD TABLE attendance_records;

-- Enable RLS on users table
ALTER TABLE users ENABLE ROW LEVEL SECURITY;

-- Drop existing policies if they exist
DROP POLICY IF EXISTS "Users can view own profile" ON users;
DROP POLICY IF EXISTS "Admins can view all users" ON users;
DROP POLICY IF EXISTS "Admins can manage users" ON users;
DROP POLICY IF EXISTS "Service role bypass RLS" ON users;

-- Policy for service role (Laravel backend) to bypass RLS
CREATE POLICY "Service role bypass RLS" ON users
    FOR ALL 
    USING (auth.role() = 'service_role')
    WITH CHECK (auth.role() = 'service_role');

-- Policy for users to view their own profile (if using Supabase Auth)
CREATE POLICY "Users can view own profile" ON users
    FOR SELECT USING (auth.uid()::text = id::text);

-- Policy for admins to view all users (if using Supabase Auth)
CREATE POLICY "Admins can view all users" ON users
    FOR SELECT USING (
        EXISTS (
            SELECT 1 FROM users u 
            WHERE u.id::text = auth.uid()::text 
            AND u.role = 'admin'
        )
    );

-- Policy for admins to manage users (if using Supabase Auth)
CREATE POLICY "Admins can manage users" ON users
    FOR ALL USING (
        EXISTS (
            SELECT 1 FROM users u 
            WHERE u.id::text = auth.uid()::text 
            AND u.role = 'admin'
        )
    );

-- Enable real-time for users table (for profile updates)
ALTER PUBLICATION supabase_realtime ADD TABLE users;

-- Enable RLS on locations table
ALTER TABLE locations ENABLE ROW LEVEL SECURITY;

-- Drop existing policies if they exist
DROP POLICY IF EXISTS "Authenticated users can view locations" ON locations;
DROP POLICY IF EXISTS "Admins can manage locations" ON locations;
DROP POLICY IF EXISTS "Service role bypass RLS" ON locations;

-- Policy for service role (Laravel backend) to bypass RLS
CREATE POLICY "Service role bypass RLS" ON locations
    FOR ALL 
    USING (auth.role() = 'service_role')
    WITH CHECK (auth.role() = 'service_role');

-- Policy for all authenticated users to view locations (if using Supabase Auth)
CREATE POLICY "Authenticated users can view locations" ON locations
    FOR SELECT USING (auth.role() = 'authenticated');

-- Policy for admins to manage locations (if using Supabase Auth)
CREATE POLICY "Admins can manage locations" ON locations
    FOR ALL USING (
        EXISTS (
            SELECT 1 FROM users 
            WHERE users.id::text = auth.uid()::text 
            AND users.role = 'admin'
        )
    );

-- Enable real-time for locations table
ALTER PUBLICATION supabase_realtime ADD TABLE locations;