    -- Enable Row Level Security (RLS) on attendance_records table
    ALTER TABLE attendance_records ENABLE ROW LEVEL SECURITY;

    -- Drop existing policies if they exist
    DROP POLICY IF EXISTS "Students can view own attendance records" ON attendance_records;
    DROP POLICY IF EXISTS "Admins can view all attendance records" ON attendance_records;
    DROP POLICY IF EXISTS "Supervisors can view location attendance records" ON attendance_records;
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

    -- Policy for supervisors to view attendance records for their location (if using Supabase Auth)
    CREATE POLICY "Supervisors can view location attendance records" ON attendance_records
        FOR SELECT USING (
            EXISTS (
                SELECT 1 FROM users u
                INNER JOIN locations l ON l.supervisor_id = u.id
                WHERE u.id::text = auth.uid()::text 
                AND u.role = 'supervisor'
                AND attendance_records.location_id = l.id
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
    DO $$ 
    BEGIN
        IF EXISTS (
            SELECT 1 FROM pg_publication_tables 
            WHERE pubname = 'supabase_realtime' 
            AND tablename = 'attendance_records'
        ) THEN
            ALTER PUBLICATION supabase_realtime DROP TABLE attendance_records;
        END IF;
        ALTER PUBLICATION supabase_realtime ADD TABLE attendance_records;
    END $$;

    -- Enable RLS on users table
    ALTER TABLE users ENABLE ROW LEVEL SECURITY;

    -- Drop existing policies if they exist
    DROP POLICY IF EXISTS "Users can view own profile" ON users;
    DROP POLICY IF EXISTS "Admins can view all users" ON users;
    DROP POLICY IF EXISTS "Supervisors can view location students" ON users;
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

    -- Policy for supervisors to view students at their location (if using Supabase Auth)
    CREATE POLICY "Supervisors can view location students" ON users
        FOR SELECT USING (
            EXISTS (
                SELECT 1 FROM users supervisor
                INNER JOIN locations l ON l.supervisor_id = supervisor.id
                WHERE supervisor.id::text = auth.uid()::text 
                AND supervisor.role = 'supervisor'
                AND users.assigned_location_id = l.id
                AND users.role = 'student'
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
    DO $$ 
    BEGIN
        IF EXISTS (
            SELECT 1 FROM pg_publication_tables 
            WHERE pubname = 'supabase_realtime' 
            AND tablename = 'users'
        ) THEN
            ALTER PUBLICATION supabase_realtime DROP TABLE users;
        END IF;
        ALTER PUBLICATION supabase_realtime ADD TABLE users;
    END $$;

    -- Enable RLS on locations table
    ALTER TABLE locations ENABLE ROW LEVEL SECURITY;

    -- Drop existing policies if they exist
    DROP POLICY IF EXISTS "Authenticated users can view locations" ON locations;
    DROP POLICY IF EXISTS "Supervisors can view own location" ON locations;
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

    -- Policy for supervisors to view their own location (if using Supabase Auth)
    CREATE POLICY "Supervisors can view own location" ON locations
        FOR SELECT USING (
            EXISTS (
                SELECT 1 FROM users 
                WHERE users.id::text = auth.uid()::text 
                AND users.role = 'supervisor'
                AND locations.supervisor_id = users.id
            )
        );

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
    DO $$ 
    BEGIN
        IF EXISTS (
            SELECT 1 FROM pg_publication_tables 
            WHERE pubname = 'supabase_realtime' 
            AND tablename = 'locations'
        ) THEN
            ALTER PUBLICATION supabase_realtime DROP TABLE locations;
        END IF;
        ALTER PUBLICATION supabase_realtime ADD TABLE locations;
    END $$;

    -- Enable RLS on reports table
    ALTER TABLE reports ENABLE ROW LEVEL SECURITY;

    -- Drop existing policies if they exist
    DROP POLICY IF EXISTS "Students can view own reports" ON reports;
    DROP POLICY IF EXISTS "Admins can view all reports" ON reports;
    DROP POLICY IF EXISTS "Supervisors can view own reports" ON reports;
    DROP POLICY IF EXISTS "Admins can manage reports" ON reports;
    DROP POLICY IF EXISTS "Service role bypass RLS" ON reports;

    -- Policy for service role (Laravel backend) to bypass RLS
    CREATE POLICY "Service role bypass RLS" ON reports
        FOR ALL 
        USING (auth.role() = 'service_role')
        WITH CHECK (auth.role() = 'service_role');

    -- Policy for users to view only their own reports (if using Supabase Auth)
    CREATE POLICY "Students can view own reports" ON reports
        FOR SELECT USING (
            auth.uid()::text = (
                SELECT users.id::text 
                FROM users 
                WHERE users.id = reports.generated_by
            )
        );

    -- Policy for admins to view all reports (if using Supabase Auth)
    CREATE POLICY "Admins can view all reports" ON reports
        FOR SELECT USING (
            EXISTS (
                SELECT 1 FROM users 
                WHERE users.id::text = auth.uid()::text 
                AND users.role = 'admin'
            )
        );

    -- Policy for supervisors to view their own reports (if using Supabase Auth)
    CREATE POLICY "Supervisors can view own reports" ON reports
        FOR SELECT USING (
            EXISTS (
                SELECT 1 FROM users 
                WHERE users.id::text = auth.uid()::text 
                AND users.role = 'supervisor'
                AND reports.generated_by = users.id
            )
        );

    -- Policy for admins to manage reports (if using Supabase Auth)
    CREATE POLICY "Admins can manage reports" ON reports
        FOR ALL USING (
            EXISTS (
                SELECT 1 FROM users 
                WHERE users.id::text = auth.uid()::text 
                AND users.role = 'admin'
            )
        );

    -- Enable real-time for reports table
    DO $$ 
    BEGIN
        IF EXISTS (
            SELECT 1 FROM pg_publication_tables 
            WHERE pubname = 'supabase_realtime' 
            AND tablename = 'reports'
        ) THEN
            ALTER PUBLICATION supabase_realtime DROP TABLE reports;
        END IF;
        ALTER PUBLICATION supabase_realtime ADD TABLE reports;
    END $$;

    -- Enable RLS on activity_logs table
    ALTER TABLE activity_logs ENABLE ROW LEVEL SECURITY;

    -- Drop existing policies if they exist
    DROP POLICY IF EXISTS "Admins can view all activity logs" ON activity_logs;
    DROP POLICY IF EXISTS "Users can view own activity logs" ON activity_logs;
    DROP POLICY IF EXISTS "Service role bypass RLS" ON activity_logs;

    -- Policy for service role (Laravel backend) to bypass RLS
    CREATE POLICY "Service role bypass RLS" ON activity_logs
        FOR ALL 
        USING (auth.role() = 'service_role')
        WITH CHECK (auth.role() = 'service_role');

    -- Policy for admins to view all activity logs (if using Supabase Auth)
    CREATE POLICY "Admins can view all activity logs" ON activity_logs
        FOR SELECT USING (
            EXISTS (
                SELECT 1 FROM users 
                WHERE users.id::text = auth.uid()::text 
                AND users.role = 'admin'
            )
        );

    -- Policy for users to view their own activity logs (if using Supabase Auth)
    CREATE POLICY "Users can view own activity logs" ON activity_logs
        FOR SELECT USING (
            auth.uid()::text = (
                SELECT users.id::text 
                FROM users 
                WHERE users.id = activity_logs.user_id
            )
        );

    -- Enable real-time for activity_logs table
    DO $$ 
    BEGIN
        IF EXISTS (
            SELECT 1 FROM pg_publication_tables 
            WHERE pubname = 'supabase_realtime' 
            AND tablename = 'activity_logs'
        ) THEN
            ALTER PUBLICATION supabase_realtime DROP TABLE activity_logs;
        END IF;
        ALTER PUBLICATION supabase_realtime ADD TABLE activity_logs;
    END $$;

    -- Enable RLS on qr_attendance_tokens table
    ALTER TABLE qr_attendance_tokens ENABLE ROW LEVEL SECURITY;

    -- Drop existing policies if they exist
    DROP POLICY IF EXISTS "Admins can manage QR tokens" ON qr_attendance_tokens;
    DROP POLICY IF EXISTS "Supervisors can manage location QR tokens" ON qr_attendance_tokens;
    DROP POLICY IF EXISTS "Authenticated users can view active tokens" ON qr_attendance_tokens;
    DROP POLICY IF EXISTS "Service role bypass RLS" ON qr_attendance_tokens;

    -- Policy for service role (Laravel backend) to bypass RLS
    CREATE POLICY "Service role bypass RLS" ON qr_attendance_tokens
        FOR ALL 
        USING (auth.role() = 'service_role')
        WITH CHECK (auth.role() = 'service_role');

    -- Policy for authenticated users to view active tokens (if using Supabase Auth)
    CREATE POLICY "Authenticated users can view active tokens" ON qr_attendance_tokens
        FOR SELECT USING (
            auth.role() = 'authenticated' 
            AND expires_at > NOW()
        );

    -- Policy for admins to manage QR tokens (if using Supabase Auth)
    CREATE POLICY "Admins can manage QR tokens" ON qr_attendance_tokens
        FOR ALL USING (
            EXISTS (
                SELECT 1 FROM users 
                WHERE users.id::text = auth.uid()::text 
                AND users.role = 'admin'
            )
        );

    -- Policy for supervisors to manage QR tokens for their location (if using Supabase Auth)
    CREATE POLICY "Supervisors can manage location QR tokens" ON qr_attendance_tokens
        FOR ALL USING (
            EXISTS (
                SELECT 1 FROM users supervisor
                INNER JOIN locations l ON l.supervisor_id = supervisor.id
                WHERE supervisor.id::text = auth.uid()::text 
                AND supervisor.role = 'supervisor'
                AND qr_attendance_tokens.location_id = l.id
            )
        );

    -- Enable real-time for qr_attendance_tokens table
    DO $$ 
    BEGIN
        IF EXISTS (
            SELECT 1 FROM pg_publication_tables 
            WHERE pubname = 'supabase_realtime' 
            AND tablename = 'qr_attendance_tokens'
        ) THEN
            ALTER PUBLICATION supabase_realtime DROP TABLE qr_attendance_tokens;
        END IF;
        ALTER PUBLICATION supabase_realtime ADD TABLE qr_attendance_tokens;
    END $$;