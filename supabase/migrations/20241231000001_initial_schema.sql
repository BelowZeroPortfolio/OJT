-- Initial schema migration for OJT Attendance System
-- This creates all the tables from Laravel migrations

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id BIGSERIAL PRIMARY KEY,
    student_id VARCHAR(50) NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(255) NOT NULL DEFAULT 'student',
    course VARCHAR(100) NULL,
    assigned_location_id BIGINT NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    rfid_number VARCHAR(255) NULL UNIQUE
);

-- Create password_reset_tokens table
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL
);

-- Create failed_jobs table
CREATE TABLE IF NOT EXISTS failed_jobs (
    id BIGSERIAL PRIMARY KEY,
    uuid VARCHAR(255) UNIQUE NOT NULL,
    connection TEXT NOT NULL,
    queue TEXT NOT NULL,
    payload TEXT NOT NULL,
    exception TEXT NOT NULL,
    failed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Create personal_access_tokens table
CREATE TABLE IF NOT EXISTS personal_access_tokens (
    id BIGSERIAL PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(64) UNIQUE NOT NULL,
    abilities TEXT NULL,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Create locations table
CREATE TABLE IF NOT EXISTS locations (
    id BIGSERIAL PRIMARY KEY,
    location_code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    address TEXT NULL,
    latitude DECIMAL(10, 8) NULL,
    longitude DECIMAL(11, 8) NULL,
    radius INTEGER NOT NULL DEFAULT 100,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL
);

-- Create attendance_records table
CREATE TABLE IF NOT EXISTS attendance_records (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    location_id BIGINT NULL,
    date DATE NOT NULL,
    time_in TIME NULL,
    time_out TIME NULL,
    hours_worked DECIMAL(5, 2) NULL DEFAULT 0.00,
    status VARCHAR(255) NOT NULL DEFAULT 'present',
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE SET NULL,
    UNIQUE(user_id, date)
);

-- Create reports table
CREATE TABLE IF NOT EXISTS reports (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NULL,
    title VARCHAR(255) NOT NULL,
    type VARCHAR(255) NOT NULL,
    parameters JSON NULL,
    file_path VARCHAR(255) NULL,
    status VARCHAR(255) NOT NULL DEFAULT 'pending',
    generated_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Create activity_logs table
CREATE TABLE IF NOT EXISTS activity_logs (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NULL,
    action VARCHAR(255) NOT NULL,
    description TEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Create cache table
CREATE TABLE IF NOT EXISTS cache (
    key VARCHAR(255) PRIMARY KEY,
    value TEXT NOT NULL,
    expiration INTEGER NOT NULL
);

-- Create cache_locks table
CREATE TABLE IF NOT EXISTS cache_locks (
    key VARCHAR(255) PRIMARY KEY,
    owner VARCHAR(255) NOT NULL,
    expiration INTEGER NOT NULL
);

-- Create sessions table
CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload TEXT NOT NULL,
    last_activity INTEGER NOT NULL
);

-- Create indexes for sessions table
CREATE INDEX IF NOT EXISTS sessions_user_id_index ON sessions(user_id);
CREATE INDEX IF NOT EXISTS sessions_last_activity_index ON sessions(last_activity);

-- Create jobs table
CREATE TABLE IF NOT EXISTS jobs (
    id BIGSERIAL PRIMARY KEY,
    queue VARCHAR(255) NOT NULL,
    payload TEXT NOT NULL,
    attempts INTEGER NOT NULL,
    reserved_at INTEGER NULL,
    available_at INTEGER NOT NULL,
    created_at INTEGER NOT NULL
);

-- Create index for jobs table
CREATE INDEX IF NOT EXISTS jobs_queue_index ON jobs(queue);

-- Create job_batches table
CREATE TABLE IF NOT EXISTS job_batches (
    id VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    total_jobs INTEGER NOT NULL,
    pending_jobs INTEGER NOT NULL,
    failed_jobs INTEGER NOT NULL,
    failed_job_ids TEXT NOT NULL,
    options TEXT NULL,
    cancelled_at INTEGER NULL,
    created_at INTEGER NOT NULL,
    finished_at INTEGER NULL
);

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_attendance_records_user_id ON attendance_records(user_id);
CREATE INDEX IF NOT EXISTS idx_attendance_records_date ON attendance_records(date);
CREATE INDEX IF NOT EXISTS idx_attendance_records_created_at ON attendance_records(created_at);
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_users_student_id ON users(student_id);
CREATE INDEX IF NOT EXISTS idx_users_role ON users(role);
CREATE INDEX IF NOT EXISTS idx_users_assigned_location_id ON users(assigned_location_id);
CREATE INDEX IF NOT EXISTS idx_users_rfid_number ON users(rfid_number);
CREATE INDEX IF NOT EXISTS idx_activity_logs_user_id ON activity_logs(user_id);
CREATE INDEX IF NOT EXISTS idx_activity_logs_created_at ON activity_logs(created_at);
CREATE INDEX IF NOT EXISTS idx_locations_location_code ON locations(location_code);
CREATE INDEX IF NOT EXISTS idx_locations_is_active ON locations(is_active);

-- Add foreign key constraints
ALTER TABLE users ADD CONSTRAINT fk_users_assigned_location_id 
    FOREIGN KEY (assigned_location_id) REFERENCES locations(id) ON DELETE SET NULL;

-- Insert default admin user
INSERT INTO users (name, email, password, role, created_at, updated_at) 
VALUES (
    'Admin User', 
    'admin@ojt.com', 
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password
    'admin', 
    CURRENT_TIMESTAMP, 
    CURRENT_TIMESTAMP
) ON CONFLICT (email) DO NOTHING;

-- Insert default location
INSERT INTO locations (location_code, name, address, radius, is_active, created_at, updated_at) 
VALUES (
    'MAIN001',
    'Main Office', 
    '123 Business District, City', 
    100, 
    TRUE, 
    CURRENT_TIMESTAMP, 
    CURRENT_TIMESTAMP
);