-- Create additional test database for local development
CREATE DATABASE IF NOT EXISTS `flapabay_test` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Grant privileges to the user for both databases
GRANT ALL PRIVILEGES ON `flapabay_local`.* TO 'flapabay_user'@'%';
GRANT ALL PRIVILEGES ON `flapabay_test`.* TO 'flapabay_user'@'%';

-- Flush privileges
FLUSH PRIVILEGES;