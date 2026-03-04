-- Enable Row Level Security and Real-time for OJT Attendance System

-- Enable Row Level Security (RLS) on attendance_records table
ALTER TABLE attendance_records ENABLE ROW LEVEL SECURITY;

-- Policy for students to view only their own attendance records
CREATE POLICY "Students can view own attendance records" ON attendance_records
    FOR SELECT USING (
        auth.uid()::text = user_id::text
        OR EXISTS (
            SELECT 1 FROM users 
            WHERE users.id::text = auth.uid()::text 
            AND users.role = 'admin'
        )
    );

-- Policy for students to insert their own attendance records
CREATE POLICY "Students can insert own attendance records" ON attendance_records
    FOR INSERT WITH CHECK (
        auth.uid()::text = user_id::text
        OR EXISTS (
            SELECT 1 FROM users 
            WHERE users.id::text = auth.uid()::text 
            AND users.role = 'admin'
        )
    );

-- Policy for students to update their own attendance records
CREATE POLICY "Students can update own attendance records" ON attendance_records
    FOR UPDATE USING (
        auth.uid()::text = user_id::text
        OR EXISTS (
            SELECT 1 FROM users 
            WHERE users.id::text = auth.uid()::text 
            AND users.role = 'admin'
        )
    );

-- Enable RLS on users table
ALTER TABLE users ENABLE ROW LEVEL SECURITY;

-- Policy for users to view their own profile and admins to view all
CREATE POLICY "Users can view profiles" ON users
    FOR SELECT USING (
        auth.uid()::text = id::text
        OR EXISTS (
            SELECT 1 FROM users u 
            WHERE u.id::text = auth.uid()::text 
            AND u.role = 'admin'
        )
    );

-- Policy for admins to manage users
CREATE POLICY "Admins can manage users" ON users
    FOR ALL USING (
        EXISTS (
            SELECT 1 FROM users u 
            WHERE u.id::text = auth.uid()::text 
            AND u.role = 'admin'
        )
    );

-- Enable RLS on locations table
ALTER TABLE locations ENABLE ROW LEVEL SECURITY;

-- Policy for all authenticated users to view locations
CREATE POLICY "Authenticated users can view locations" ON locations
    FOR SELECT USING (auth.role() = 'authenticated');

-- Policy for admins to manage locations
CREATE POLICY "Admins can manage locations" ON locations
    FOR ALL USING (
        EXISTS (
            SELECT 1 FROM users 
            WHERE users.id::text = auth.uid()::text 
            AND users.role = 'admin'
        )
    );

-- Enable RLS on activity_logs table
ALTER TABLE activity_logs ENABLE ROW LEVEL SECURITY;

-- Policy for users to view their own activity logs and admins to view all
CREATE POLICY "Users can view own activity logs" ON activity_logs
    FOR SELECT USING (
        auth.uid()::text = user_id::text
        OR EXISTS (
            SELECT 1 FROM users 
            WHERE users.id::text = auth.uid()::text 
            AND users.role = 'admin'
        )
    );

-- Policy for admins to manage activity logs
CREATE POLICY "Admins can manage activity logs" ON activity_logs
    FOR ALL USING (
        EXISTS (
            SELECT 1 FROM users 
            WHERE users.id::text = auth.uid()::text 
            AND users.role = 'admin'
        )
    );

-- Enable real-time for key tables
ALTER PUBLICATION supabase_realtime ADD TABLE attendance_records;
ALTER PUBLICATION supabase_realtime ADD TABLE users;
ALTER PUBLICATION supabase_realtime ADD TABLE locations;

-- Create a function to handle real-time notifications
CREATE OR REPLACE FUNCTION notify_attendance_change()
RETURNS TRIGGER AS $$
BEGIN
    -- Notify about attendance record changes
    PERFORM pg_notify(
        'attendance_change',
        json_build_object(
            'operation', TG_OP,
            'record', row_to_json(NEW),
            'old_record', row_to_json(OLD),
            'timestamp', extract(epoch from now())
        )::text
    );
    
    RETURN COALESCE(NEW, OLD);
END;
$$ LANGUAGE plpgsql;

-- Create trigger for attendance_records table
DROP TRIGGER IF EXISTS attendance_change_trigger ON attendance_records;
CREATE TRIGGER attendance_change_trigger
    AFTER INSERT OR UPDATE OR DELETE ON attendance_records
    FOR EACH ROW EXECUTE FUNCTION notify_attendance_change();

-- Grant necessary permissions
GRANT USAGE ON SCHEMA public TO anon, authenticated;
GRANT SELECT ON ALL TABLES IN SCHEMA public TO anon, authenticated;
GRANT INSERT, UPDATE ON attendance_records TO authenticated;
GRANT INSERT, UPDATE ON users TO authenticated;
GRANT INSERT ON activity_logs TO authenticated;