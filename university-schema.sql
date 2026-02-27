-- ============================================================================
-- UNIVERSITY DATABASE SCHEMA
-- Clean, Normalized Structure for Faculties, Programs, and Courses
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ============================================================================
-- 1. FACULTIES (Schools/Colleges)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `faculties` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(20) UNIQUE NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `short_name` VARCHAR(100),
    `description` TEXT,
    `dean_name` VARCHAR(255),
    `dean_email` VARCHAR(255),
    `contact_phone` VARCHAR(20),
    `contact_email` VARCHAR(255),
    `website_url` VARCHAR(255),
    `status` ENUM('active', 'inactive', 'archived') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_code` (`code`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 2. PROGRAM TYPES (Lookup Table)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `program_types` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(20) UNIQUE NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT,
    `typical_duration_years` INT,
    `level` ENUM('undergraduate', 'postgraduate', 'professional') DEFAULT 'undergraduate',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed Program Types
INSERT INTO `program_types` (`code`, `name`, `typical_duration_years`, `level`) VALUES
('diploma', 'Diploma', 1, 'undergraduate'),
('bachelor', 'Bachelor\'s Degree', 3, 'undergraduate'),
('honours', 'Honours Degree', 1, 'undergraduate'),
('masters', 'Master\'s Degree', 2, 'postgraduate'),
('phd', 'Courses of Philosophy', 3, 'postgraduate'),
('certificate', 'Certificate', 0, 'professional'),
('short_course', 'Short Course', 0, 'professional')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- ============================================================================
-- 3. ACCREDITATION BODIES
-- ============================================================================
CREATE TABLE IF NOT EXISTS `accreditation_bodies` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(20) UNIQUE NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `short_name` VARCHAR(100),
    `description` TEXT,
    `logo_url` VARCHAR(255),
    `website_url` VARCHAR(255),
    `contact_email` VARCHAR(255),
    `contact_phone` VARCHAR(20),
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_code` (`code`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 4. PROGRAMS (Degrees/Diplomas)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `programs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `faculty_id` INT NOT NULL,
    `program_type_id` INT NOT NULL,
    
    `code` VARCHAR(50) UNIQUE NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `short_name` VARCHAR(100),
    `description` TEXT,
    
    -- Academic Details
    `nqf_level` INT,
    `total_credits` INT,
    `duration_years` DECIMAL(3,1),
    `duration_months` INT,
    
    -- Accreditation
    `accreditation_body_id` INT NULL,
    `accreditation_number` VARCHAR(100),
    `accreditation_expiry` DATE,
    
    -- Requirements
    `entry_requirements` TEXT,
    `prerequisites` TEXT,
    
    -- Status
    `status` ENUM('active', 'inactive', 'archived', 'draft') DEFAULT 'active',
    `intake_periods` JSON,
    
    -- Metadata
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`faculty_id`) REFERENCES `faculties`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`program_type_id`) REFERENCES `program_types`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`accreditation_body_id`) REFERENCES `accreditation_bodies`(`id`) ON DELETE SET NULL,
    
    INDEX `idx_faculty` (`faculty_id`),
    INDEX `idx_program_type` (`program_type_id`),
    INDEX `idx_code` (`code`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 5. PROGRAM LEVELS (Year 1, Year 2, etc.)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `program_levels` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `program_id` INT NOT NULL,
    
    `level_number` INT NOT NULL,
    `name` VARCHAR(100),
    `description` TEXT,
    `required_credits` INT,
    
    -- Progression
    `min_gpa` DECIMAL(3,2),
    `prerequisites_level_id` INT NULL,
    
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`program_id`) REFERENCES `programs`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`prerequisites_level_id`) REFERENCES `program_levels`(`id`) ON DELETE SET NULL,
    
    UNIQUE KEY `unique_program_level` (`program_id`, `level_number`),
    INDEX `idx_program` (`program_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 6. COURSE CATEGORIES (Lookup)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `course_categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(20) UNIQUE NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed Course Categories
INSERT INTO `course_categories` (`code`, `name`) VALUES
('core', 'Core Course'),
('elective', 'Elective Course'),
('foundation', 'Foundation Course'),
('capstone', 'Capstone Project'),
('internship', 'Internship/Practicum')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- ============================================================================
-- 7. COURSES (Subjects/Modules)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `courses` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `program_id` INT NOT NULL,
    `level_id` INT NULL,
    
    -- Identification
    `code` VARCHAR(50) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `short_name` VARCHAR(100),
    `description` TEXT,
    
    -- Academic Details
    `nqf_level` INT,
    `credits` INT NOT NULL DEFAULT 0,
    `contact_hours` INT,
    `self_study_hours` INT,
    `duration_weeks` INT,
    
    -- Categorization
    `category_id` INT NULL,
    `is_required` BOOLEAN DEFAULT TRUE,
    
    -- Assessment
    `assessment_method` TEXT,
    `pass_percentage` DECIMAL(5,2) DEFAULT 50.00,
    
    -- Pricing (if applicable)
    `price` DECIMAL(10,2) DEFAULT 0.00,
    `currency` VARCHAR(3) DEFAULT 'ZAR',
    
    -- Status
    `status` ENUM('active', 'inactive', 'archived', 'draft') DEFAULT 'active',
    
    -- Metadata
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`program_id`) REFERENCES `programs`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`level_id`) REFERENCES `program_levels`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`category_id`) REFERENCES `course_categories`(`id`) ON DELETE SET NULL,
    
    UNIQUE KEY `unique_course_code` (`code`),
    INDEX `idx_program` (`program_id`),
    INDEX `idx_level` (`level_id`),
    INDEX `idx_category` (`category_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 8. COURSE PREREQUISITES (M2M)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `course_prerequisites` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT NOT NULL,
    `prerequisite_course_id` INT NOT NULL,
    `is_mandatory` BOOLEAN DEFAULT TRUE,
    `min_grade` VARCHAR(10) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`prerequisite_course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
    
    UNIQUE KEY `unique_prerequisite` (`course_id`, `prerequisite_course_id`),
    INDEX `idx_course` (`course_id`),
    INDEX `idx_prerequisite` (`prerequisite_course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 9. PROGRAM ACCREDITATIONS (M2M)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `program_accreditations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `program_id` INT NOT NULL,
    `accreditation_body_id` INT NOT NULL,
    `accreditation_number` VARCHAR(100),
    `accreditation_date` DATE,
    `expiry_date` DATE,
    `status` ENUM('active', 'expired', 'pending') DEFAULT 'active',
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`program_id`) REFERENCES `programs`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`accreditation_body_id`) REFERENCES `accreditation_bodies`(`id`) ON DELETE CASCADE,
    
    UNIQUE KEY `unique_program_accreditation` (`program_id`, `accreditation_body_id`),
    INDEX `idx_program` (`program_id`),
    INDEX `idx_accreditation_body` (`accreditation_body_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 10. COURSE ACCREDITATIONS (M2M)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `course_accreditations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT NOT NULL,
    `accreditation_body_id` INT NOT NULL,
    `accreditation_number` VARCHAR(100),
    `accreditation_date` DATE,
    `expiry_date` DATE,
    `status` ENUM('active', 'expired', 'pending') DEFAULT 'active',
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`accreditation_body_id`) REFERENCES `accreditation_bodies`(`id`) ON DELETE CASCADE,
    
    UNIQUE KEY `unique_course_accreditation` (`course_id`, `accreditation_body_id`),
    INDEX `idx_course` (`course_id`),
    INDEX `idx_accreditation_body` (`accreditation_body_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- EXAMPLE QUERIES
-- ============================================================================

-- Get all courses for a program
-- SELECT c.*, p.name as program_name, f.name as faculty_name
-- FROM courses c
-- JOIN programs p ON c.program_id = p.id
-- JOIN faculties f ON p.faculty_id = f.id
-- WHERE p.id = 1;

-- Get course with prerequisites
-- SELECT c.*, 
--        GROUP_CONCAT(cp.prerequisite_course_id) as prerequisite_ids
-- FROM courses c
-- LEFT JOIN course_prerequisites cp ON c.id = cp.course_id
-- WHERE c.id = 1
-- GROUP BY c.id;

-- Get program with all accreditations
-- SELECT p.*,
--        GROUP_CONCAT(ab.name) as accreditation_bodies
-- FROM programs p
-- LEFT JOIN program_accreditations pa ON p.id = pa.program_id
-- LEFT JOIN accreditation_bodies ab ON pa.accreditation_body_id = ab.id
-- WHERE p.id = 1
-- GROUP BY p.id;



