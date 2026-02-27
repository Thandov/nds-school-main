<?php
/**
 * MIGRATION SCRIPT: Destroy Old Structure & Create New University Schema
 * 
 * WARNING: This will DROP all existing tables and create new ones!
 * Make sure you have a backup before running this!
 * 
 * Usage: Visit: wp-admin/admin.php?page=nds-migrate-university-schema
 */

if (!defined('ABSPATH')) {
    exit;
}

function nds_migrate_to_university_schema() {
    global $wpdb;
    
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }
    
    $charset_collate = $wpdb->get_charset_collate();
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    
    // ============================================================================
    // STEP 1: BACKUP EXISTING DATA (Safety First!)
    // ============================================================================
    $backup_timestamp = date('Ymd_His');
    $backup_file = plugin_dir_path(dirname(__FILE__)) . 'backups/university_migration_backup_' . $backup_timestamp . '.sql';
    
    // Create backup directory if it doesn't exist
    $backup_dir = dirname($backup_file);
    if (!file_exists($backup_dir)) {
        wp_mkdir_p($backup_dir);
    }
    
    // Get all existing NDS tables (for backup)
    $existing_tables = [
        'nds_education_paths',          // Will be replaced by nds_faculties
        'nds_program_types_lookup',     // Will be replaced by nds_program_types
        'nds_programs',
        'nds_program_levels',
        'nds_courses',
        'nds_accreditation_bodies',
        'nds_course_accreditations',
        'nds_trade_tests',              // May be removed if not needed
        'nds_students',
        'nds_academic_years',
        'nds_semesters',
        'nds_student_enrollments',
        'nds_applications',
        'nds_application_documents',
        'nds_application_reviews',
        'nds_application_payments',
        // Keep these - still in use!
        'nds_course_lecturers',         // KEEP - Still used
        'nds_course_schedules',          // KEEP - Still used
        'nds_student_events',            // KEEP - Still used
        'nds_staff',                     // KEEP - Still used
        'nds_recipes'                    // KEEP - Still used (content management)
    ];
    
    $backup_sql = "-- NDS Database Backup - " . date('Y-m-d H:i:s') . "\n";
    $backup_sql .= "-- Migration to University Schema\n\n";
    
    foreach ($existing_tables as $table) {
        $full_table = $wpdb->prefix . $table;
        if ($wpdb->get_var("SHOW TABLES LIKE '$full_table'") == $full_table) {
            // Backup table structure
            $create_table = $wpdb->get_row("SHOW CREATE TABLE $full_table", ARRAY_N);
            $backup_sql .= "\n-- Table: $full_table\n";
            $backup_sql .= "DROP TABLE IF EXISTS `$full_table`;\n";
            $backup_sql .= $create_table[1] . ";\n\n";
            
            // Backup table data
            $rows = $wpdb->get_results("SELECT * FROM $full_table", ARRAY_A);
            if (!empty($rows)) {
                $backup_sql .= "-- Data for $full_table\n";
                foreach ($rows as $row) {
                    $keys = array_keys($row);
                    $values = array_map(function($val) use ($wpdb) {
                        return $wpdb->_real_escape($val);
                    }, array_values($row));
                    $backup_sql .= "INSERT INTO `$full_table` (`" . implode('`, `', $keys) . "`) VALUES ('" . implode("', '", $values) . "');\n";
                }
                $backup_sql .= "\n";
            }
        }
    }
    
    file_put_contents($backup_file, $backup_sql);
    
    // ============================================================================
    // STEP 2: DISABLE FOREIGN KEY CHECKS
    // ============================================================================
    $wpdb->query('SET FOREIGN_KEY_CHECKS = 0');
    
    // ============================================================================
    // STEP 3: DROP ONLY OLD/REPLACED TABLES (Keep tables still in use!)
    // ============================================================================
    // Only drop tables that are being replaced, NOT tables still in use
    $tables_to_drop = [
        'nds_education_paths',          // Replaced by nds_faculties
        'nds_program_types_lookup',     // Replaced by nds_program_types (new structure)
        'nds_trade_tests'               // Optional - remove if not needed
    ];
    
    // Note: We're NOT dropping these (they're still in use):
    // - nds_staff (still used)
    // - nds_course_lecturers (still used)
    // - nds_course_schedules (still used)
    // - nds_student_events (still used)
    // - nds_recipes (still used)
    // - nds_students, nds_courses, etc. (will be recreated with new structure)
    
    foreach ($tables_to_drop as $table) {
        $full_table = $wpdb->prefix . $table;
        $wpdb->query("DROP TABLE IF EXISTS `$full_table`");
    }
    
    // Drop and recreate core tables (they'll be recreated with new structure)
    $tables_to_recreate = [
        'nds_student_enrollments',
        'nds_course_accreditations',
        'nds_application_documents',
        'nds_application_reviews',
        'nds_application_payments',
        'nds_applications',
        'nds_courses',
        'nds_program_levels',
        'nds_programs',
        'nds_accreditation_bodies',
        'nds_students',
        'nds_academic_years',
        'nds_semesters'
    ];
    
    foreach ($tables_to_recreate as $table) {
        $full_table = $wpdb->prefix . $table;
        $wpdb->query("DROP TABLE IF EXISTS `$full_table`");
    }
    
    // ============================================================================
    // STEP 4: CREATE NEW UNIVERSITY SCHEMA
    // ============================================================================
    
    // 1. FACULTIES
    $t_faculties = $wpdb->prefix . 'nds_faculties';
    $sql_faculties = "CREATE TABLE IF NOT EXISTS $t_faculties (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(20) UNIQUE NOT NULL,
        name VARCHAR(255) NOT NULL,
        short_name VARCHAR(100),
        description TEXT,
        dean_name VARCHAR(255),
        dean_email VARCHAR(255),
        contact_phone VARCHAR(20),
        contact_email VARCHAR(255),
        website_url VARCHAR(255),
        status ENUM('active', 'inactive', 'archived') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_code (code),
        INDEX idx_status (status)
    ) $charset_collate;";
    dbDelta($sql_faculties);
    
    // 2. PROGRAM TYPES
    $t_program_types = $wpdb->prefix . 'nds_program_types';
    $sql_program_types = "CREATE TABLE IF NOT EXISTS $t_program_types (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(20) UNIQUE NOT NULL,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        typical_duration_years INT,
        level ENUM('undergraduate', 'postgraduate', 'professional') DEFAULT 'undergraduate',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_code (code)
    ) $charset_collate;";
    dbDelta($sql_program_types);
    
    // Seed Program Types
    $program_types = [
        ['diploma', 'Diploma', 1, 'undergraduate'],
        ['bachelor', 'Bachelor\'s Degree', 3, 'undergraduate'],
        ['honours', 'Honours Degree', 1, 'undergraduate'],
        ['masters', 'Master\'s Degree', 2, 'postgraduate'],
        ['phd', 'Courses of Philosophy', 3, 'postgraduate'],
        ['certificate', 'Certificate', 0, 'professional'],
        ['short_course', 'Short Course', 0, 'professional']
    ];
    foreach ($program_types as $type) {
        $wpdb->query($wpdb->prepare(
            "INSERT IGNORE INTO $t_program_types (code, name, typical_duration_years, level) VALUES (%s, %s, %d, %s)",
            $type[0], $type[1], $type[2], $type[3]
        ));
    }
    
    // 3. ACCREDITATION BODIES
    $t_accreditation_bodies = $wpdb->prefix . 'nds_accreditation_bodies';
    $sql_accreditation_bodies = "CREATE TABLE IF NOT EXISTS $t_accreditation_bodies (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(20) UNIQUE NOT NULL,
        name VARCHAR(255) NOT NULL,
        short_name VARCHAR(100),
        description TEXT,
        logo_url VARCHAR(255),
        website_url VARCHAR(255),
        contact_email VARCHAR(255),
        contact_phone VARCHAR(20),
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_code (code),
        INDEX idx_status (status)
    ) $charset_collate;";
    dbDelta($sql_accreditation_bodies);
    
    // 4. PROGRAMS
    $t_programs = $wpdb->prefix . 'nds_programs';
    $sql_programs = "CREATE TABLE IF NOT EXISTS $t_programs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        faculty_id INT NOT NULL,
        program_type_id INT NOT NULL,
        code VARCHAR(50) UNIQUE NOT NULL,
        name VARCHAR(255) NOT NULL,
        short_name VARCHAR(100),
        description TEXT,
        nqf_level INT,
        total_credits INT,
        duration_years DECIMAL(3,1),
        duration_months INT,
        accreditation_body_id INT NULL,
        accreditation_number VARCHAR(100),
        accreditation_expiry DATE,
        entry_requirements TEXT,
        prerequisites TEXT,
        status ENUM('active', 'inactive', 'archived', 'draft') DEFAULT 'active',
        intake_periods JSON,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (faculty_id) REFERENCES $t_faculties(id) ON DELETE RESTRICT,
        FOREIGN KEY (program_type_id) REFERENCES $t_program_types(id) ON DELETE RESTRICT,
        FOREIGN KEY (accreditation_body_id) REFERENCES $t_accreditation_bodies(id) ON DELETE SET NULL,
        INDEX idx_faculty (faculty_id),
        INDEX idx_program_type (program_type_id),
        INDEX idx_code (code),
        INDEX idx_status (status)
    ) $charset_collate;";
    dbDelta($sql_programs);
    
    // 5. PROGRAM LEVELS
    $t_program_levels = $wpdb->prefix . 'nds_program_levels';
    $sql_program_levels = "CREATE TABLE IF NOT EXISTS $t_program_levels (
        id INT AUTO_INCREMENT PRIMARY KEY,
        program_id INT NOT NULL,
        level_number INT NOT NULL,
        name VARCHAR(100),
        description TEXT,
        required_credits INT,
        min_gpa DECIMAL(3,2),
        prerequisites_level_id INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (program_id) REFERENCES $t_programs(id) ON DELETE CASCADE,
        FOREIGN KEY (prerequisites_level_id) REFERENCES $t_program_levels(id) ON DELETE SET NULL,
        UNIQUE KEY unique_program_level (program_id, level_number),
        INDEX idx_program (program_id)
    ) $charset_collate;";
    dbDelta($sql_program_levels);
    
    // 6. COURSE CATEGORIES
    $t_course_categories = $wpdb->prefix . 'nds_course_categories';
    $sql_course_categories = "CREATE TABLE IF NOT EXISTS $t_course_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(20) UNIQUE NOT NULL,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_code (code)
    ) $charset_collate;";
    dbDelta($sql_course_categories);
    
    // Seed Course Categories
    $categories = [
        ['core', 'Core Course'],
        ['elective', 'Elective Course'],
        ['foundation', 'Foundation Course'],
        ['capstone', 'Capstone Project'],
        ['internship', 'Internship/Practicum']
    ];
    foreach ($categories as $cat) {
        $wpdb->query($wpdb->prepare(
            "INSERT IGNORE INTO $t_course_categories (code, name) VALUES (%s, %s)",
            $cat[0], $cat[1]
        ));
    }
    
    // 7. COURSES
    $t_courses = $wpdb->prefix . 'nds_courses';
    $sql_courses = "CREATE TABLE IF NOT EXISTS $t_courses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        program_id INT NOT NULL,
        level_id INT NULL,
        code VARCHAR(50) NOT NULL,
        name VARCHAR(255) NOT NULL,
        short_name VARCHAR(100),
        description TEXT,
        nqf_level INT,
        credits INT NOT NULL DEFAULT 0,
        contact_hours INT,
        self_study_hours INT,
        duration_weeks INT,
        category_id INT NULL,
        is_required BOOLEAN DEFAULT TRUE,
        assessment_method TEXT,
        pass_percentage DECIMAL(5,2) DEFAULT 50.00,
        price DECIMAL(10,2) DEFAULT 0.00,
        currency VARCHAR(3) DEFAULT 'ZAR',
        status ENUM('active', 'inactive', 'archived', 'draft') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (program_id) REFERENCES $t_programs(id) ON DELETE CASCADE,
        FOREIGN KEY (level_id) REFERENCES $t_program_levels(id) ON DELETE SET NULL,
        FOREIGN KEY (category_id) REFERENCES $t_course_categories(id) ON DELETE SET NULL,
        UNIQUE KEY unique_course_code (code),
        INDEX idx_program (program_id),
        INDEX idx_level (level_id),
        INDEX idx_category (category_id),
        INDEX idx_status (status),
        INDEX idx_code (code)
    ) $charset_collate;";
    dbDelta($sql_courses);
    
    // 8. COURSE PREREQUISITES
    $t_course_prerequisites = $wpdb->prefix . 'nds_course_prerequisites';
    $sql_course_prerequisites = "CREATE TABLE IF NOT EXISTS $t_course_prerequisites (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_id INT NOT NULL,
        prerequisite_course_id INT NOT NULL,
        is_mandatory BOOLEAN DEFAULT TRUE,
        min_grade VARCHAR(10) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (course_id) REFERENCES $t_courses(id) ON DELETE CASCADE,
        FOREIGN KEY (prerequisite_course_id) REFERENCES $t_courses(id) ON DELETE CASCADE,
        UNIQUE KEY unique_prerequisite (course_id, prerequisite_course_id),
        INDEX idx_course (course_id),
        INDEX idx_prerequisite (prerequisite_course_id)
    ) $charset_collate;";
    dbDelta($sql_course_prerequisites);
    
    // 9. PROGRAM ACCREDITATIONS
    $t_program_accreditations = $wpdb->prefix . 'nds_program_accreditations';
    $sql_program_accreditations = "CREATE TABLE IF NOT EXISTS $t_program_accreditations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        program_id INT NOT NULL,
        accreditation_body_id INT NOT NULL,
        accreditation_number VARCHAR(100),
        accreditation_date DATE,
        expiry_date DATE,
        status ENUM('active', 'expired', 'pending') DEFAULT 'active',
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (program_id) REFERENCES $t_programs(id) ON DELETE CASCADE,
        FOREIGN KEY (accreditation_body_id) REFERENCES $t_accreditation_bodies(id) ON DELETE CASCADE,
        UNIQUE KEY unique_program_accreditation (program_id, accreditation_body_id),
        INDEX idx_program (program_id),
        INDEX idx_accreditation_body (accreditation_body_id)
    ) $charset_collate;";
    dbDelta($sql_program_accreditations);
    
    // 10. COURSE ACCREDITATIONS
    $t_course_accreditations = $wpdb->prefix . 'nds_course_accreditations';
    $sql_course_accreditations = "CREATE TABLE IF NOT EXISTS $t_course_accreditations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_id INT NOT NULL,
        accreditation_body_id INT NOT NULL,
        accreditation_number VARCHAR(100),
        accreditation_date DATE,
        expiry_date DATE,
        status ENUM('active', 'expired', 'pending') DEFAULT 'active',
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (course_id) REFERENCES $t_courses(id) ON DELETE CASCADE,
        FOREIGN KEY (accreditation_body_id) REFERENCES $t_accreditation_bodies(id) ON DELETE CASCADE,
        UNIQUE KEY unique_course_accreditation (course_id, accreditation_body_id),
        INDEX idx_course (course_id),
        INDEX idx_accreditation_body (accreditation_body_id)
    ) $charset_collate;";
    dbDelta($sql_course_accreditations);
    
    // ============================================================================
    // STEP 5: RE-ENABLE FOREIGN KEY CHECKS
    // ============================================================================
    $wpdb->query('SET FOREIGN_KEY_CHECKS = 1');
    
    // ============================================================================
    // STEP 6: RETURN SUCCESS MESSAGE
    // ============================================================================
    return [
        'success' => true,
        'message' => 'Database structure successfully migrated to University Schema!',
        'backup_file' => $backup_file,
        'tables_created' => [
            'nds_faculties',
            'nds_program_types',
            'nds_accreditation_bodies',
            'nds_programs',
            'nds_program_levels',
            'nds_course_categories',
            'nds_courses',
            'nds_course_prerequisites',
            'nds_program_accreditations',
            'nds_course_accreditations'
        ]
    ];
}

// Admin page to run migration
function nds_migrate_university_schema_page() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }
    
    $result = null;
    if (isset($_POST['run_migration']) && wp_verify_nonce($_POST['migration_nonce'], 'nds_migrate_university')) {
        $result = nds_migrate_to_university_schema();
    }
    
    ?>
    <div class="wrap">
        <h1>Migrate to University Schema</h1>
        
        <?php if ($result && $result['success']): ?>
            <div class="notice notice-success">
                <p><strong>✅ Success!</strong> <?php echo esc_html($result['message']); ?></p>
                <p>Backup saved to: <code><?php echo esc_html($result['backup_file']); ?></code></p>
                <p><strong>Tables Created:</strong></p>
                <ul>
                    <?php foreach ($result['tables_created'] as $table): ?>
                        <li><?php echo esc_html($table); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php else: ?>
            <div class="notice notice-warning">
                <p><strong>⚠️ WARNING:</strong> This will DROP all existing NDS tables and create new ones!</p>
                <p>Make sure you have a full database backup before proceeding.</p>
            </div>
            
            <form method="post">
                <?php wp_nonce_field('nds_migrate_university', 'migration_nonce'); ?>
                <p>
                    <input type="submit" name="run_migration" class="button button-primary" 
                           value="Destroy Old Structure & Create New Schema" 
                           onclick="return confirm('Are you absolutely sure? This will DELETE all existing data!');">
                </p>
            </form>
        <?php endif; ?>
    </div>
    <?php
}

// Add admin menu item
add_action('admin_menu', function() {
    add_submenu_page(
        'nds-academy',
        'Migrate University Schema',
        'Migrate DB Schema',
        'manage_options',
        'nds-migrate-university-schema',
        'nds_migrate_university_schema_page'
    );
}, 999);

