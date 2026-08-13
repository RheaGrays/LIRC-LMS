-- ============================================================
-- LEMS (Library Entrance Monitoring System)
-- Complete MySQL/MariaDB Database Setup Script
-- For XAMPP / phpMyAdmin
-- ============================================================
-- Updated & Synchronized with all Laravel Migrations
-- ============================================================

CREATE DATABASE IF NOT EXISTS `lems_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `lems_db`;

-- 1. USERS TABLE
CREATE TABLE IF NOT EXISTS `users` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
    `password` VARCHAR(255) NOT NULL,
    `remember_token` VARCHAR(100) NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. PASSWORD RESET TOKENS
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
    `email` VARCHAR(255) NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. SESSIONS
CREATE TABLE IF NOT EXISTS `sessions` (
    `id` VARCHAR(255) NOT NULL,
    `user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `ip_address` VARCHAR(45) NULL DEFAULT NULL,
    `user_agent` TEXT NULL DEFAULT NULL,
    `payload` LONGTEXT NOT NULL,
    `last_activity` INT NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `sessions_user_id_index` (`user_id`),
    INDEX `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. CACHE
CREATE TABLE IF NOT EXISTS `cache` (
    `key` VARCHAR(255) NOT NULL,
    `value` MEDIUMTEXT NOT NULL,
    `expiration` INT NOT NULL,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cache_locks` (
    `key` VARCHAR(255) NOT NULL,
    `owner` VARCHAR(255) NOT NULL,
    `expiration` INT NOT NULL,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. JOBS
CREATE TABLE IF NOT EXISTS `jobs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `queue` VARCHAR(255) NOT NULL,
    `payload` LONGTEXT NOT NULL,
    `attempts` TINYINT UNSIGNED NOT NULL,
    `reserved_at` INT UNSIGNED NULL DEFAULT NULL,
    `available_at` INT UNSIGNED NOT NULL,
    `created_at` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `job_batches` (
    `id` VARCHAR(255) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `total_jobs` INT NOT NULL,
    `pending_jobs` INT NOT NULL,
    `failed_jobs` INT NOT NULL,
    `failed_job_ids` LONGTEXT NOT NULL,
    `options` MEDIUMTEXT NULL DEFAULT NULL,
    `cancelled_at` INT NULL DEFAULT NULL,
    `created_at` INT NOT NULL,
    `finished_at` INT NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `failed_jobs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `uuid` VARCHAR(255) NOT NULL,
    `connection` TEXT NOT NULL,
    `queue` TEXT NOT NULL,
    `payload` LONGTEXT NOT NULL,
    `exception` LONGTEXT NOT NULL,
    `failed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. ACADEMIC DEPARTMENTS
CREATE TABLE IF NOT EXISTS `academic_departments` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `level` VARCHAR(255) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. ACADEMIC PROGRAMS
CREATE TABLE IF NOT EXISTS `academic_programs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `department_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `code` VARCHAR(255) NULL DEFAULT NULL,
    `years` INT NOT NULL DEFAULT 4,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `academic_programs_department_id_foreign`
        FOREIGN KEY (`department_id`) REFERENCES `academic_departments` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. ACADEMIC TERMS
CREATE TABLE IF NOT EXISTS `academic_terms` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `academic_year` VARCHAR(255) NOT NULL,
    `semester` VARCHAR(255) NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `holidays` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `academic_terms_year_semester_unique` (`academic_year`, `semester`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. VIOLATION TYPES
CREATE TABLE IF NOT EXISTS `violation_types` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `violation_types_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. STUDENTS
CREATE TABLE IF NOT EXISTS `students` (
    `id` VARCHAR(255) NOT NULL,
    `last_name` VARCHAR(255) NOT NULL,
    `first_name` VARCHAR(255) NOT NULL,
    `middle_name` VARCHAR(255) NULL DEFAULT NULL,
    `department_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `program_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `year_level` VARCHAR(255) NULL DEFAULT NULL,
    `email` VARCHAR(255) NULL DEFAULT NULL,
    `patron_category` VARCHAR(255) NOT NULL DEFAULT 'Student',
    `contact` VARCHAR(255) NULL DEFAULT NULL,
    `photo_path` VARCHAR(255) NULL DEFAULT NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `students_status_index` (`status`),
    INDEX `students_department_id_index` (`department_id`),
    INDEX `students_department_id_status_index` (`department_id`, `status`),
    CONSTRAINT `students_department_id_foreign`
        FOREIGN KEY (`department_id`) REFERENCES `academic_departments` (`id`)
        ON DELETE SET NULL,
    CONSTRAINT `students_program_id_foreign`
        FOREIGN KEY (`program_id`) REFERENCES `academic_programs` (`id`)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. ATTENDANCE LOGS
CREATE TABLE IF NOT EXISTS `attendance_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `student_id` VARCHAR(255) NOT NULL,
    `action` ENUM('check_in', 'check_out') NOT NULL,
    `logged_at` TIMESTAMP NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `attendance_logs_student_id_logged_at_index` (`student_id`, `logged_at`),
    INDEX `attendance_logs_logged_at_index` (`logged_at`),
    INDEX `attendance_logs_action_index` (`action`),
    CONSTRAINT `attendance_logs_student_id_foreign`
        FOREIGN KEY (`student_id`) REFERENCES `students` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. VIOLATIONS
CREATE TABLE IF NOT EXISTS `violations` (
    `id` CHAR(36) NOT NULL,
    `student_id` VARCHAR(255) NOT NULL,
    `violation_type_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `notes` TEXT NULL DEFAULT NULL,
    `severity` ENUM('minor', 'moderate', 'severe') NOT NULL DEFAULT 'minor',
    `date` DATE NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `violations_student_id_index` (`student_id`),
    INDEX `violations_date_index` (`date`),
    INDEX `violations_severity_index` (`severity`),
    INDEX `violations_student_id_date_index` (`student_id`, `date`),
    CONSTRAINT `violations_student_id_foreign`
        FOREIGN KEY (`student_id`) REFERENCES `students` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. SECTION LOGS
CREATE TABLE IF NOT EXISTS `section_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `section_code` VARCHAR(255) NOT NULL,
    `section_name` VARCHAR(255) NOT NULL,
    `date` DATE NOT NULL,
    `hour` TINYINT NOT NULL,
    `occupied` INT NOT NULL DEFAULT 0,
    `reserved` INT NOT NULL DEFAULT 0,
    `available` INT NOT NULL DEFAULT 0,
    `total_capacity` INT NOT NULL DEFAULT 0,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `section_logs_code_date_hour_unique` (`section_code`, `date`, `hour`),
    INDEX `section_logs_code_date_index` (`section_code`, `date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. ADMINS
CREATE TABLE IF NOT EXISTS `admins` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(255) NOT NULL,
    `role` ENUM('Super Admin', 'Staff', 'Librarian') NOT NULL DEFAULT 'Staff',
    `avatar_initials` VARCHAR(2) NULL DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `remember_token` VARCHAR(100) NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `admins_email_unique` (`email`),
    INDEX `admins_is_active_index` (`is_active`),
    INDEX `admins_role_index` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. PENDING ADMIN APPROVALS
CREATE TABLE IF NOT EXISTS `pending_admin_approvals` (
    `id` CHAR(36) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(255) NOT NULL,
    `role` VARCHAR(255) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `pending_admin_approvals_email_unique` (`email`),
    INDEX `pending_admin_approvals_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. SYSTEM SETTINGS
CREATE TABLE IF NOT EXISTS `system_settings` (
    `key` VARCHAR(255) NOT NULL,
    `value` JSON NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. LIBRARY COLLECTIONS
CREATE TABLE IF NOT EXISTS `library_collections` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `badge` VARCHAR(255) NOT NULL,
    `badge_color` VARCHAR(255) NOT NULL DEFAULT '#c0392b',
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. LARAVEL MIGRATIONS TABLE
CREATE TABLE IF NOT EXISTS `migrations` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `migration` VARCHAR(255) NOT NULL,
    `batch` INT NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Record all migrations as already run (batch 1)
INSERT INTO `migrations` (`migration`, `batch`) VALUES
    ('0001_01_01_000000_create_users_table', 1),
    ('0001_01_01_000001_create_cache_table', 1),
    ('0001_01_01_000002_create_jobs_table', 1),
    ('0001_01_01_000003_create_sessions_table', 1),
    ('2026_01_01_000003_create_academic_departments_table', 1),
    ('2026_01_01_000004_create_academic_programs_table', 1),
    ('2026_01_01_000005_create_academic_terms_table', 1),
    ('2026_01_01_000006_create_violation_types_table', 1),
    ('2026_01_01_000010_create_students_table', 1),
    ('2026_01_01_000011_create_attendance_logs_table', 1),
    ('2026_01_01_000012_create_violations_table', 1),
    ('2026_01_01_000013_create_section_logs_table', 1),
    ('2026_01_01_000014_create_admins_table', 1),
    ('2026_01_01_000015_create_pending_admin_approvals_table', 1),
    ('2026_01_01_000016_create_system_settings_table', 1),
    ('2026_08_04_000001_add_missing_indexes', 1),
    ('2026_08_06_000001_create_library_collections_table', 1);

-- SEED DATA
-- ⚠️ SECURITY: Change the default admin password immediately after first login.
-- Default credentials: admin@corjesucollege.edu.ph / (ask your system administrator)
INSERT INTO `admins` (`email`, `password`, `full_name`, `role`, `avatar_initials`, `is_active`, `created_at`, `updated_at`)
VALUES (
    'admin@corjesucollege.edu.ph',
    '$2y$12$LhKdYOGFM.sROBqhUaEVFOxlZFNlOHJqe9SjZqU3fPdHVxkyn3Jie',
    'LEMS Administrator',
    'Super Admin',
    'LA',
    1,
    NOW(),
    NOW()
)
ON DUPLICATE KEY UPDATE `email` = `email`;

INSERT INTO `system_settings` (`key`, `value`, `created_at`, `updated_at`) VALUES
    ('active_term',           '"2025-2026-2"',  NOW(), NOW()),
    ('idle_timeout',          '60',             NOW(), NOW()),
    ('max_occupancy',         '200',            NOW(), NOW()),
    ('show_occupancy',        'true',           NOW(), NOW()),
    ('enable_webcam',         'false',          NOW(), NOW()),
    ('sound_on_checkin',      'false',          NOW(), NOW()),
    ('alert_capacity',        'true',           NOW(), NOW()),
    ('alert_daily_summary',   'false',          NOW(), NOW()),
    ('alert_repeated_denied', 'true',           NOW(), NOW())
ON DUPLICATE KEY UPDATE `key` = `key`;
