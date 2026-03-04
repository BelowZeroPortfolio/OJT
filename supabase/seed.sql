-- Seed data for OJT Attendance System
-- This will populate the local Supabase with sample data

-- Insert locations first (needed for foreign key references)
INSERT INTO locations (id, location_code, name, address, latitude, longitude, radius, is_active, created_at, updated_at) VALUES
(1, 'MAIN001', 'Main Office', '123 Business District, Makati City', 14.5547, 121.0244, 100, TRUE, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(2, 'BRANCH002', 'Branch Office', '456 Tech Hub, Taguig City', 14.5176, 121.0509, 150, TRUE, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(3, 'REMOTE003', 'Remote Location', '789 Innovation Center, Quezon City', 14.6760, 121.0437, 200, TRUE, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
ON CONFLICT (id) DO UPDATE SET
    location_code = EXCLUDED.location_code,
    name = EXCLUDED.name,
    address = EXCLUDED.address,
    latitude = EXCLUDED.latitude,
    longitude = EXCLUDED.longitude,
    radius = EXCLUDED.radius,
    is_active = EXCLUDED.is_active,
    updated_at = CURRENT_TIMESTAMP;

-- Insert admin user
INSERT INTO users (id, name, email, password, role, created_at, updated_at) 
VALUES (
    1,
    'Admin User', 
    'admin@ojt.com', 
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password
    'admin', 
    CURRENT_TIMESTAMP, 
    CURRENT_TIMESTAMP
) ON CONFLICT (id) DO UPDATE SET
    name = EXCLUDED.name,
    email = EXCLUDED.email,
    role = EXCLUDED.role,
    updated_at = CURRENT_TIMESTAMP;

-- Insert sample students
INSERT INTO users (id, name, email, password, role, student_id, course, assigned_location_id, rfid_number, created_at, updated_at) VALUES
(2, 'John Doe', 'john.doe@student.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'STU001', 'Computer Science', 1, '1234567890', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(3, 'Jane Smith', 'jane.smith@student.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'STU002', 'Information Technology', 1, '0987654321', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(4, 'Mike Johnson', 'mike.johnson@student.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'STU003', 'Computer Engineering', 2, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(5, 'Sarah Wilson', 'sarah.wilson@student.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'STU004', 'Software Engineering', 1, '1122334455', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(6, 'Alex Chen', 'alex.chen@student.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'STU005', 'Computer Science', 2, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
ON CONFLICT (id) DO UPDATE SET
    name = EXCLUDED.name,
    email = EXCLUDED.email,
    student_id = EXCLUDED.student_id,
    course = EXCLUDED.course,
    assigned_location_id = EXCLUDED.assigned_location_id,
    rfid_number = EXCLUDED.rfid_number,
    updated_at = CURRENT_TIMESTAMP;

-- Insert locations
INSERT INTO locations (id, location_code, name, address, latitude, longitude, radius, is_active, created_at, updated_at) VALUES
(1, 'MAIN001', 'Main Office', '123 Business District, Makati City', 14.5547, 121.0244, 100, TRUE, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(2, 'BRANCH002', 'Branch Office', '456 Tech Hub, Taguig City', 14.5176, 121.0509, 150, TRUE, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(3, 'REMOTE003', 'Remote Location', '789 Innovation Center, Quezon City', 14.6760, 121.0437, 200, TRUE, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
ON CONFLICT (id) DO UPDATE SET
    location_code = EXCLUDED.location_code,
    name = EXCLUDED.name,
    address = EXCLUDED.address,
    latitude = EXCLUDED.latitude,
    longitude = EXCLUDED.longitude,
    radius = EXCLUDED.radius,
    is_active = EXCLUDED.is_active,
    updated_at = CURRENT_TIMESTAMP;

-- Insert sample attendance records
INSERT INTO attendance_records (user_id, location_id, date, time_in, time_out, hours_worked, status, created_at, updated_at) VALUES
(2, 1, CURRENT_DATE - INTERVAL '5 days', '08:00:00', '17:00:00', 8.00, 'present', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(2, 1, CURRENT_DATE - INTERVAL '4 days', '08:15:00', '17:30:00', 8.25, 'present', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(2, 1, CURRENT_DATE - INTERVAL '3 days', '08:00:00', '17:00:00', 8.00, 'present', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(3, 1, CURRENT_DATE - INTERVAL '5 days', '08:30:00', '17:15:00', 7.75, 'present', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(3, 1, CURRENT_DATE - INTERVAL '4 days', '08:00:00', '17:00:00', 8.00, 'present', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(4, 2, CURRENT_DATE - INTERVAL '5 days', '09:00:00', '18:00:00', 8.00, 'present', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(5, 1, CURRENT_DATE - INTERVAL '5 days', '08:00:00', '16:30:00', 7.50, 'present', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
ON CONFLICT (user_id, date) DO UPDATE SET
    time_in = EXCLUDED.time_in,
    time_out = EXCLUDED.time_out,
    hours_worked = EXCLUDED.hours_worked,
    status = EXCLUDED.status,
    updated_at = CURRENT_TIMESTAMP;

-- Reset sequences to avoid conflicts
SELECT setval('users_id_seq', (SELECT MAX(id) FROM users));
SELECT setval('locations_id_seq', (SELECT MAX(id) FROM locations));
SELECT setval('attendance_records_id_seq', (SELECT MAX(id) FROM attendance_records));