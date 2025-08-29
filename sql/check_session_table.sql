-- Check session table structure and data
-- Run this to diagnose session issues

-- Check if the session table exists
SHOW TABLES LIKE 'access_sessions';

-- Check table structure
DESCRIBE access_sessions;

-- Check current sessions
SELECT 
    id,
    session_id,
    ip_address,
    user_agent,
    timestamp,
    last_activity,
    data
FROM access_sessions 
ORDER BY timestamp DESC 
LIMIT 10;

-- Check session count
SELECT COUNT(*) as total_sessions FROM access_sessions;

-- Check for expired sessions (older than 6 hours)
SELECT 
    COUNT(*) as expired_sessions,
    MIN(timestamp) as oldest_session,
    MAX(timestamp) as newest_session
FROM access_sessions 
WHERE timestamp < (UNIX_TIMESTAMP() - 21600);

-- Check for sessions without user data
SELECT 
    COUNT(*) as sessions_without_user_data
FROM access_sessions 
WHERE data IS NULL OR data = '';

-- Check PHP session configuration
SHOW VARIABLES LIKE 'session%';
