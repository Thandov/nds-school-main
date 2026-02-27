<?php

/**
 * nds-school.php
 * Plugin Name: NDS School
 * Plugin URI: https://kayiseit.co.za
 * Description: A modern school management system for WordPress.
 * Version: 1.0
 * Author: Thando Hlophe
 * Author URI: https://kayiseit.co.za
 * License: GPL2
 */

if (!defined('ABSPATH')) {
    exit; // Prevent direct access
}

// Define plugin directory constant
if (!defined('NDS_SCHOOL_PLUGIN_DIR')) {
    define('NDS_SCHOOL_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

// Include calendar functions for AJAX handlers
require_once NDS_SCHOOL_PLUGIN_DIR . 'includes/calendar-functions.php';
require_once NDS_SCHOOL_PLUGIN_DIR . 'includes/notification-functions.php';
require_once NDS_SCHOOL_PLUGIN_DIR . 'includes/program-functions.php';

/**
 * Restrict WordPress admin area for subscribers when this plugin is active.
 * - Blocks /wp-admin/ for users with only the 'subscriber' role
 * - Allows AJAX, cron, and login/logout to function normally
 * - Can be toggled on/off via Settings page
 */
add_action('init', function () {
    // Check if blocking is enabled (default: enabled)
    $block_subscribers = get_option('nds_block_subscribers_backend', '1');
    if ($block_subscribers !== '1') {
        return; // Feature is disabled
    }

    // Only care about admin-side requests
    if (!is_admin()) {
        return;
    }

    // Allow AJAX and CRON requests to pass through
    if ((defined('DOING_AJAX') && DOING_AJAX) || (defined('DOING_CRON') && DOING_CRON)) {
        return;
    }

    // Let WordPress handle non-logged-in users (login screen, etc.)
    if (!is_user_logged_in()) {
        return;
    }

    $user = wp_get_current_user();

    // If the user is a pure subscriber, keep them out of wp-admin
    if (in_array('subscriber', (array) $user->roles, true)) {
        // Option: send learners to the /portal/ dashboard instead of homepage
        $redirect_url = home_url('/portal/');
        wp_safe_redirect($redirect_url);
        exit;
    }
});

// Hide the admin bar on the front-end for subscribers (controlled by setting)
add_filter('show_admin_bar', function ($show) {
    $block_subscribers = get_option('nds_block_subscribers_backend', '1');
    $hide_admin_bar = get_option('nds_hide_subscriber_admin_bar', '0');
    
    if ($block_subscribers === '1' && $hide_admin_bar === '1') {
        if (is_user_logged_in() && current_user_can('subscriber')) {
            return false;
        }
    }
    return $show;
});

/**
 * Ensure learners (subscribers) are redirected to the site homepage on logout,
 * instead of being sent to the default wp-login.php screen.
 */
add_filter('logout_redirect', function ($redirect_to, $requested_redirect_to, $user) {
    // Normalize $user to a WP_User instance when possible
    if ($user && !($user instanceof WP_User)) {
        $user = get_user_by('id', (int) $user);
    }

    if ($user instanceof WP_User && in_array('subscriber', (array) $user->roles, true)) {
        return home_url('/');
    }

    return $redirect_to;
}, 10, 3);

function enqueue_custom_scripts() {
    // Only load on admin pages
    if (!is_admin()) {
        return;
    }
    
    $current_page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
    
    // Only load scripts on NDS plugin pages
    if (strpos($current_page, 'nds-') !== 0) {
        return;
    }
    
    // Media upload script - only load on pages that need it
    $media_pages = array('nds-add-student', 'nds-edit-student', 'nds-add-learner', 'nds-edit-learner', 'nds-hero-carousel', 'nds-add-recipe', 'nds-recipe-details', 'nds-content-management', 'nds-recipes', 'nds-content', 'nds-staff', 'nds-staff-management', 'nds-edit-staff', 'nds-add-staff');
    if (in_array($current_page, $media_pages)) {
        wp_enqueue_media();
        wp_enqueue_script('mediaqq-js', plugin_dir_url(__FILE__) . 'assets/js/media-upload.js', array('jquery'), null, true);
    }
    
    // Main JS - only load on pages that need interactive features
    $interactive_pages = array('nds-academy', 'nds-students', 'nds-courses', 'nds-programs', 'nds-faculties', 'nds-staff');
    if (in_array($current_page, $interactive_pages) || strpos($current_page, 'nds-edit-') === 0 || strpos($current_page, 'nds-add-') === 0) {
        wp_enqueue_script('ndsJSschool-js', plugin_dir_url(__FILE__) . 'assets/js/ndsJSschool.js', array('jquery'), null, true);
    }
    
    // SweetAlert2 - only load on pages that show alerts/confirmations
    $alert_pages = array('nds-students', 'nds-courses', 'nds-programs', 'nds-staff', 'nds-applications', 'nds-learner-management');
    if (in_array($current_page, $alert_pages) || strpos($current_page, 'nds-edit-') === 0 || strpos($current_page, 'nds-add-') === 0) {
        wp_enqueue_style('sweetalert2-css', 'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css', array(), '11.0.0');
        wp_enqueue_script('sweetalert2-js', 'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js', array(), '11.0.0', true);
        wp_enqueue_script('nds-sweetalert', plugin_dir_url(__FILE__) . 'assets/js/nds-sweetalert.js', array('sweetalert2-js'), filemtime(plugin_dir_path(__FILE__) . 'assets/js/nds-sweetalert.js'), true);
    }
    
    // Toasts - only load on pages that show toast notifications
    $toast_pages = array('nds-students', 'nds-courses', 'nds-programs', 'nds-learner-management');
    if (in_array($current_page, $toast_pages)) {
        wp_enqueue_style('toastify-css', 'https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css', array(), null, 'all');
        wp_enqueue_script('toastify-js', 'https://cdn.jsdelivr.net/npm/toastify-js', array(), null, true);
        wp_enqueue_script('nds-toasts', plugin_dir_url(__FILE__) . 'assets/js/nds-toasts.js', array('toastify-js'), filemtime(plugin_dir_path(__FILE__) . 'assets/js/nds-toasts.js'), true);
    }
}
add_action('admin_enqueue_scripts', 'enqueue_custom_scripts');

function nds_admin_enqueue_styles()
{
    if (!is_admin()) return;

    // Detect any NDS plugin page globally (robust against menu slug/screen ID changes)
    $current_page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
    if (strpos($current_page, 'nds-') !== 0) {
        return; // not one of our plugin pages
    }

    wp_enqueue_style('nds-icons', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css', array(), null, 'all');

    // Load Tailwind CSS with high priority
    $tailwind_file = plugin_dir_path(__FILE__) . 'assets/css/frontend.css';
    if (file_exists($tailwind_file)) {
        wp_enqueue_style('nds-tailwindcss', plugin_dir_url(__FILE__) . 'assets/css/frontend.css', array(), filemtime($tailwind_file), 'all');
    }

    // Load additional styles if they exist
    $styles_file = plugin_dir_path(__FILE__) . 'assets/css/styles.css';
    if (file_exists($styles_file)) {
        wp_enqueue_style('nds-stylescss', plugin_dir_url(__FILE__) . 'assets/css/styles.css', array('nds-tailwindcss'), filemtime($styles_file), 'all');
    }
}
add_action('admin_enqueue_scripts', 'nds_admin_enqueue_styles');

// Lightweight schema guard to auto-heal missing tables/columns from third-party plugins
add_action('init', function () {
    // Run at most once every 10 minutes to avoid overhead
    if (get_transient('nds_schema_last_check')) {
        return;
    }
    set_transient('nds_schema_last_check', 1, 10 * MINUTE_IN_SECONDS);

    global $wpdb;
    $needs_migration = false;

    // Check Action Scheduler tables (often required by other plugins like Fluent Forms)
    $as_actions = $wpdb->prefix . 'actionscheduler_actions';
    $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $as_actions));
    if (empty($table_exists)) {
        $needs_migration = true;
    }

    // Check critical students table columns
    $students_table = $wpdb->prefix . 'nds_students';
    $students_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $students_table));
    if (!empty($students_exists)) {
        $cols = $wpdb->get_col("DESC {$students_table}", 0);
        if (!$cols || !in_array('id', $cols, true) || !in_array('email', $cols, true) || !in_array('student_number', $cols, true)) {
            $needs_migration = true;
        }
    } else {
        $needs_migration = true;
    }

    // Check applications core tables (tracking + detailed form)
    $applications_table = $wpdb->prefix . 'nds_applications';
    $applications_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $applications_table));
    if (empty($applications_exists)) {
        $needs_migration = true;
    }

    $application_forms_table = $wpdb->prefix . 'nds_application_forms';
    $application_forms_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $application_forms_table));
    if (empty($application_forms_exists)) {
        $needs_migration = true;
    }

    $activity_log_table = $wpdb->prefix . 'nds_student_activity_log';
    $activity_log_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $activity_log_table));
    if (empty($activity_log_exists)) {
        $needs_migration = true;
    }

    $notifications_table = $wpdb->prefix . 'nds_notifications';
    $notifications_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $notifications_table));
    if (empty($notifications_exists)) {
        $needs_migration = true;
    }

    if ($needs_migration) {
        require_once plugin_dir_path(__FILE__) . 'includes/database.php';
        if (function_exists('nds_school_create_tables')) {
            nds_school_create_tables();
        }
    }
}, 1);

function my_custom_plugin_enqueue_styles() {
    // Only load CSS in frontend or admin if needed
    wp_enqueue_style(
        'my-custom-plugin-style', // handle
        plugin_dir_url(__FILE__) . 'assets/css/custom-style.css', // path
        array(), // dependencies
        '1.0.0', // version
        'all' // media
    );
}
add_action('wp_enqueue_scripts', 'my_custom_plugin_enqueue_styles'); // For frontend
// add_action('admin_enqueue_scripts', 'my_custom_plugin_enqueue_styles'); // For admin panel



// Include necessary files
include_once plugin_dir_path(__FILE__) . 'includes/admin-menu.php';
include_once plugin_dir_path(__FILE__) . 'includes/admin-pages.php';
include_once plugin_dir_path(__FILE__) . 'includes/rooms-management.php';
include_once plugin_dir_path(__FILE__) . 'includes/seed.php';
include_once plugin_dir_path(__FILE__) . 'includes/migrate-to-university-schema.php';
include_once plugin_dir_path(__FILE__) . 'includes/hero-carousel.php';
include_once plugin_dir_path(__FILE__) . 'includes/hero-carousel-admin.php';
include_once plugin_dir_path(__FILE__) . 'includes/application-functions.php';
include_once plugin_dir_path(__FILE__) . 'includes/staff-roles.php';
include_once plugin_dir_path(__FILE__) . 'public/class-shortcodes.php';

// Initialize shortcodes
new NDS_Shortcodes();


// Activation Hook
function nds_school_activate() {
    require_once plugin_dir_path(__FILE__) . 'includes/database.php';
    nds_school_create_tables();
    
    // Run database migration
    require_once plugin_dir_path(__FILE__) . 'includes/database-migration.php';
    $migration = new NDS_Database_Migration();
    $migration->force_migration();
    
    // Add rewrite rules for recipes
    add_rewrite_rule('^recipe/([0-9]+)/?$', 'index.php?nds_recipe_id=$matches[1]', 'top');
    // Add rewrite rule for student portal
    add_rewrite_rule('^portal/?$', 'index.php?nds_portal=1', 'top');
    // Add rewrite rule for calendar
    add_rewrite_rule('^calendar/?$', 'index.php?nds_calendar=1', 'top');
    
    // Flush rewrite rules
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'nds_school_activate');

// Manual migration runner (one-time use): /wp-admin/admin-post.php?action=nds_run_migrations
add_action('admin_post_nds_run_migrations', 'nds_run_migrations_action');
function nds_run_migrations_action() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }
    
    // Rate limiting
    if (!nds_check_rate_limit('migration', 1, 3600)) {
        wp_die('Migration can only be run once per hour. Please wait.');
    }
    
    require_once plugin_dir_path(__FILE__) . 'includes/database-migration.php';
    $migration = new NDS_Database_Migration();
    $migration->force_migration();
    
    wp_redirect(admin_url('admin.php?page=nds-academy&migration=success'));
    exit;
}

// Rate limiting function to prevent abuse
function nds_check_rate_limit($action, $limit = 10, $window = 60) {
    $user_id = get_current_user_id();
    if (!$user_id) return true; // Allow for non-logged in users (though they shouldn't reach here)

    $transient_key = 'nds_rate_limit_' . $user_id . '_' . $action;
    $attempts = get_transient($transient_key);

    if ($attempts === false) {
        $attempts = 0;
    }

    if ($attempts >= $limit) {
        nds_log_error('Rate limit exceeded', array(
            'user_id' => $user_id,
            'action' => $action,
            'attempts' => $attempts
        ), 'warning');
        return false;
    }

    set_transient($transient_key, $attempts + 1, $window);
    return true;
}

// Comprehensive error logging function
function nds_log_error($message, $context = array(), $level = 'error') {
    $log_entry = array(
        'timestamp' => current_time('Y-m-d H:i:s'),
        'level' => $level,
        'message' => $message,
        'context' => $context,
        'user_id' => get_current_user_id(),
        'ip_address' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown',
        'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'unknown'
    );

    // Log to WordPress error log
    error_log('NDS Plugin [' . $level . ']: ' . $message . ' | Context: ' . json_encode($context));

    // Also log to database for better tracking (optional - comment out if not needed)
    global $wpdb;
    $wpdb->insert(
        $wpdb->prefix . 'nds_student_activity_log',
        array(
            'student_id' => 0, // Use 0 for system events
            'actor_id' => get_current_user_id(),
            'action' => 'system_error',
            'action_type' => 'create',
            'old_values' => null,
            'new_values' => json_encode($log_entry),
            'ip_address' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null,
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null
        ),
        array('%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s')
    );
}

function nds_school_run_migrations_action() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }

    require_once plugin_dir_path(__FILE__) . 'includes/database.php';
    nds_school_create_tables();

    wp_redirect(admin_url('admin.php?page=nds-learner-management&success=migrations_ran'));
    exit;
}
add_action('admin_post_nds_run_migrations', 'nds_school_run_migrations_action');

// Function to clean up expired rate limit transients (run daily)
function nds_cleanup_rate_limits() {
    global $wpdb;

    // Clean up expired transients from options table
    $wpdb->query($wpdb->prepare(
        "DELETE FROM {$wpdb->options}
         WHERE option_name LIKE %s
         AND option_value = '0'",
        $wpdb->esc_like('_transient_nds_rate_limit_') . '%'
    ));

    // Log cleanup
    nds_log_error('Rate limit cleanup completed', array(), 'info');
}

// Schedule daily cleanup of rate limits
if (!wp_next_scheduled('nds_daily_cleanup')) {
    wp_schedule_event(time(), 'daily', 'nds_daily_cleanup');
}
add_action('nds_daily_cleanup', 'nds_cleanup_rate_limits');

/**
 * Helper: get latest application for current student / user
 */
function nds_portal_get_latest_application_for_current_user() {
    if (!is_user_logged_in()) {
        return null;
    }

    global $wpdb;
    $wp_user_id = get_current_user_id();
    $student_id = nds_portal_get_current_student_id();

    $apps_table  = $wpdb->prefix . 'nds_applications';
    $forms_table = $wpdb->prefix . 'nds_application_forms';

    $where_clauses = array();
    $params        = array();

    if ($student_id) {
        $where_clauses[] = 'a.student_id = %d';
        $params[]        = $student_id;
    }

    $where_clauses[] = 'a.wp_user_id = %d';
    $params[]        = $wp_user_id;

    $where_sql = implode(' OR ', $where_clauses);

    $sql = "
        SELECT a.id, a.application_no, a.status, a.submitted_at,
               af.course_name, af.level
        FROM {$apps_table} a
        LEFT JOIN {$forms_table} af ON af.application_id = a.id
        WHERE {$where_sql}
        ORDER BY a.submitted_at DESC
        LIMIT 1
    ";

    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    $row = $wpdb->get_row($wpdb->prepare($sql, $params), ARRAY_A);

    return $row ?: null;
}

// One-off: add FK students.faculty_id -> faculties.id
function nds_add_faculty_fk_action() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }
    global $wpdb;
    $students = $wpdb->prefix . 'nds_students';
    $paths = $wpdb->prefix . 'nds_faculties';
    // Attempt to add FK; ignore errors if it already exists
    $wpdb->query("ALTER TABLE {$students} ADD CONSTRAINT fk_students_faculty FOREIGN KEY (faculty_id) REFERENCES {$paths}(id) ON DELETE SET NULL");
    $notice = $wpdb->last_error ? 'error=' . rawurlencode($wpdb->last_error) : 'success=faculty_fk_added';
    wp_redirect(admin_url('admin.php?page=nds-all-learners&' . $notice));
    exit;
}
add_action('admin_post_nds_add_faculty_fk', 'nds_add_faculty_fk_action');

// AJAX handler for getting courses by faculty
function nds_get_programs_by_faculty() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'nds_get_courses_nonce')) {
        wp_send_json_error('Security check failed');
    }

    $faculty_id = isset($_POST['faculty_id']) ? intval($_POST['faculty_id']) : 0;
    if ($faculty_id <= 0) {
        wp_send_json_error('Invalid faculty ID');
    }

    global $wpdb;

    // Fetch all programs for a given faculty
    $programs = $wpdb->get_results(
        $wpdb->prepare(
            "
            SELECT id, name
            FROM {$wpdb->prefix}nds_programs
            WHERE faculty_id = %d
            AND status = 'active'
            ORDER BY name ASC
            ",
            $faculty_id
        )
    );

    if ($programs === null) {
        wp_send_json_error('Failed to load programs: ' . $wpdb->last_error);
    }

    wp_send_json_success($programs);
}
add_action('wp_ajax_nds_get_programs_by_faculty', 'nds_get_programs_by_faculty');

function nds_get_courses_by_faculty() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'nds_get_courses_nonce')) {
        wp_send_json_error('Security check failed');
    }

    $faculty_id = isset($_POST['faculty_id']) ? intval($_POST['faculty_id']) : 0;
    if ($faculty_id <= 0) {
        wp_send_json_error('Invalid faculty ID');
    }

    global $wpdb;

    // Courses are linked to programs; programs belong to a faculty.
    // Join through programs to get all courses for a given faculty.
    $courses = $wpdb->get_results(
        $wpdb->prepare(
            "
            SELECT c.id, c.name
            FROM {$wpdb->prefix}nds_courses c
            INNER JOIN {$wpdb->prefix}nds_programs p ON c.program_id = p.id
            WHERE p.faculty_id = %d
            ORDER BY c.name ASC
            ",
            $faculty_id
        )
    );

    if ($courses === null) {
        wp_send_json_error('Failed to load courses: ' . $wpdb->last_error);
    }

    wp_send_json_success($courses);
}
add_action('wp_ajax_nds_get_courses_by_faculty', 'nds_get_courses_by_faculty');

// AJAX: Add staff role
function nds_add_staff_role_ajax() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'nds_manage_roles')) {
        wp_send_json_error('Security check failed');
    }
    
    $role = isset($_POST['role']) ? sanitize_text_field($_POST['role']) : '';
    if (empty($role)) {
        wp_send_json_error('Role name is required');
    }
    
    $result = nds_add_staff_role($role);
    
    if ($result['success']) {
        wp_send_json_success($result['message']);
    } else {
        wp_send_json_error($result['message']);
    }
}
add_action('wp_ajax_nds_add_staff_role', 'nds_add_staff_role_ajax');

// AJAX: Delete staff role
function nds_delete_staff_role_ajax() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'nds_manage_roles')) {
        wp_send_json_error('Security check failed');
    }
    
    $role = isset($_POST['role']) ? sanitize_text_field($_POST['role']) : '';
    if (empty($role)) {
        wp_send_json_error('Role name is required');
    }
    
    $result = nds_delete_staff_role($role);
    
    if ($result['success']) {
        wp_send_json_success($result['message']);
    } else {
        wp_send_json_error($result['message']);
    }
}
add_action('wp_ajax_nds_delete_staff_role', 'nds_delete_staff_role_ajax');

// AJAX: Restore roles from backup
function nds_restore_roles_backup_ajax() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'nds_manage_roles')) {
        wp_send_json_error('Security check failed');
    }
    
    $result = nds_restore_roles_from_backup();
    
    if ($result['success']) {
        wp_send_json_success($result['message']);
    } else {
        wp_send_json_error($result['message']);
    }
}
add_action('wp_ajax_nds_restore_roles_backup', 'nds_restore_roles_backup_ajax');

// AJAX: enroll student to a course (create or update enrollment)
function nds_enroll_student_ajax() {
    // Enhanced security checks
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized access');
    }
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'nds_enroll_student_nonce')) {
        wp_send_json_error('Security check failed');
    }

    // Rate limiting to prevent abuse
    if (!nds_check_rate_limit('enroll_student', 20, 60)) { // 20 enrollments per minute
        wp_send_json_error('Too many enrollment attempts. Please wait before trying again.');
    }

    // Sanitize and validate inputs
    $student_id = isset($_POST['student_id']) ? intval($_POST['student_id']) : 0;
    $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;

    // Validate input ranges and types
    if ($student_id <= 0 || $course_id <= 0) {
        wp_send_json_error('Invalid student or course ID');
    }

    // Additional validation - ensure student and course exist
    global $wpdb;
    $student_exists = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}nds_students WHERE id = %d",
        $student_id
    ));
    $course_exists = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}nds_courses WHERE id = %d",
        $course_id
    ));

    if (!$student_exists) {
        wp_send_json_error('Student not found');
    }
    if (!$course_exists) {
        wp_send_json_error('Course not found');
    }
    $enrollments_table = $wpdb->prefix . 'nds_student_enrollments';
    $academic_years_table = $wpdb->prefix . 'nds_academic_years';
    $semesters_table = $wpdb->prefix . 'nds_semesters';

    // Ensure table exists
    $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $enrollments_table));
    if (!$exists) {
        wp_send_json_error('Enrollments table missing');
    }

    // Determine active academic year and semester
    $active_year_id = (int) $wpdb->get_var("SELECT id FROM {$academic_years_table} WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
    $active_semester_id = (int) $wpdb->get_var($wpdb->prepare("SELECT id FROM {$semesters_table} WHERE academic_year_id = %d AND is_active = 1 ORDER BY id DESC LIMIT 1", $active_year_id));
    if (!$active_year_id || !$active_semester_id) {
        wp_send_json_error('Active academic year/semester not set');
    }

    // Check for existing enrollment in this course for active term
    $existing = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$enrollments_table} WHERE student_id = %d AND course_id = %d AND academic_year_id = %d AND semester_id = %d",
        $student_id, $course_id, $active_year_id, $active_semester_id
    ));

    if ($existing) {
        // Update existing enrollment
        $ok = $wpdb->update(
            $enrollments_table,
            ['status' => 'enrolled', 'updated_at' => current_time('mysql')],
            ['id' => $existing],
            ['%s','%s'],
            ['%d']
        );
            if ($ok === false) {
            nds_log_error('Failed to update student enrollment', array(
                'student_id' => $student_id,
                'course_id' => $course_id,
                'error' => $wpdb->last_error
            ));
            wp_send_json_error($wpdb->last_error ?: 'Update failed');
        }
    } else {
        // Check if student is already enrolled in any course (one qualification per student - business rule)
        $existing_enrollment = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$enrollments_table} WHERE student_id = %d AND status IN ('applied','enrolled','waitlisted')",
            $student_id
        ));

        if ($existing_enrollment) {
            nds_log_error('Attempted to enroll student already enrolled in a qualification', array(
                'student_id' => $student_id,
                'course_id' => $course_id,
                'existing_enrollment_id' => $existing_enrollment
            ), 'warning');
            wp_send_json_error('Student is already enrolled in a qualification. Only one qualification per student is allowed.');
        }

        // Create new enrollment
        $ok = $wpdb->insert(
            $enrollments_table,
            [
                'student_id' => $student_id,
                'course_id' => $course_id,
                'academic_year_id' => $active_year_id,
                'semester_id' => $active_semester_id,
                'enrollment_date' => current_time('mysql'),
                'status' => 'enrolled',
            ],
            ['%d','%d','%d','%d','%s','%s']
        );
        if ($ok === false) {
            nds_log_error('Failed to create student enrollment', array(
                'student_id' => $student_id,
                'course_id' => $course_id,
                'error' => $wpdb->last_error
            ));
            wp_send_json_error($wpdb->last_error ?: 'Insert failed');
        }
    }

    // Log successful enrollment
    nds_log_error('Student successfully enrolled', array(
        'student_id' => $student_id,
        'course_id' => $course_id,
        'academic_year_id' => $active_year_id,
        'semester_id' => $active_semester_id
    ), 'info');

    wp_send_json_success(true);
}
add_action('wp_ajax_nds_enroll_student', 'nds_enroll_student_ajax');

// AJAX handler to get enrolled students for a course
function nds_get_enrolled_students_ajax() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'nds_get_enrolled_students_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }

    if (!isset($_POST['course_id'])) {
        wp_send_json_error('Course ID is required');
        return;
    }

    $course_id = intval($_POST['course_id']);

    global $wpdb;

    // Limit to active term
    $active_year_id = (int) $wpdb->get_var("SELECT id FROM {$wpdb->prefix}nds_academic_years WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
    $active_semester_id = (int) $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}nds_semesters WHERE academic_year_id = %d AND is_active = 1 ORDER BY id DESC LIMIT 1", $active_year_id));

    $enrolled_students = $wpdb->get_results($wpdb->prepare(
        "SELECT s.id, s.first_name, s.last_name, s.student_number\n        FROM {$wpdb->prefix}nds_students s\n        JOIN {$wpdb->prefix}nds_student_enrollments e ON s.id = e.student_id\n        WHERE e.course_id = %d\n          AND e.academic_year_id = %d\n          AND e.semester_id = %d\n          AND e.status IN ('applied','enrolled','waitlisted')\n        ORDER BY s.first_name, s.last_name",
        $course_id, $active_year_id, $active_semester_id
    ));

    wp_send_json_success($enrolled_students);
}
add_action('wp_ajax_nds_get_enrolled_students', 'nds_get_enrolled_students_ajax');

// AJAX handler to get available students for a course
function nds_get_available_students_ajax() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'nds_get_available_students_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }

    if (!isset($_POST['course_id'])) {
        wp_send_json_error('Course ID is required');
        return;
    }

    $course_id = intval($_POST['course_id']);

    global $wpdb;

    $active_year_id = (int) $wpdb->get_var("SELECT id FROM {$wpdb->prefix}nds_academic_years WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
    $active_semester_id = (int) $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}nds_semesters WHERE academic_year_id = %d AND is_active = 1 ORDER BY id DESC LIMIT 1", $active_year_id));

    // Frontend rule: a student can belong to only ONE course in the active term.
    // Optimized query using LEFT JOIN instead of subquery for better performance
    $available_students = $wpdb->get_results($wpdb->prepare(
        "SELECT s.id, s.first_name, s.last_name, s.student_number
        FROM {$wpdb->prefix}nds_students s
        LEFT JOIN {$wpdb->prefix}nds_student_enrollments e ON (
            s.id = e.student_id
            AND e.academic_year_id = %d
            AND e.semester_id = %d
            AND e.status IN ('applied','enrolled','waitlisted')
        )
        WHERE e.student_id IS NULL
        AND s.status IN ('active', 'prospect')
        ORDER BY s.first_name, s.last_name",
        $active_year_id, $active_semester_id
    ));

    wp_send_json_success($available_students);
}
add_action('wp_ajax_nds_get_available_students', 'nds_get_available_students_ajax');

// AJAX handler to unenroll a student from a course
function nds_unenroll_student_ajax() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized access');
    }
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'nds_unenroll_student_nonce')) {
        wp_send_json_error('Security check failed');
    }

    // Rate limiting to prevent abuse
    if (!nds_check_rate_limit('unenroll_student', 30, 60)) { // 30 unenrollments per minute
        wp_send_json_error('Too many unenrollment attempts. Please wait before trying again.');
    }

    // Sanitize and validate inputs
    $student_id = isset($_POST['student_id']) ? intval($_POST['student_id']) : 0;
    $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;

    // Validate input ranges and types
    if ($student_id <= 0 || $course_id <= 0) {
        wp_send_json_error('Invalid student or course ID');
    }

    // Additional validation - ensure student and course exist
    global $wpdb;
    $student_exists = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}nds_students WHERE id = %d",
        $student_id
    ));
    $course_exists = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}nds_courses WHERE id = %d",
        $course_id
    ));

    if (!$student_exists) {
        wp_send_json_error('Student not found');
    }
    if (!$course_exists) {
        wp_send_json_error('Course not found');
    }
    $enrollments_table = $wpdb->prefix . 'nds_student_enrollments';
    $academic_years_table = $wpdb->prefix . 'nds_academic_years';
    $semesters_table = $wpdb->prefix . 'nds_semesters';

    // Get active academic year and semester
    $active_year_id = (int) $wpdb->get_var("SELECT id FROM {$academic_years_table} WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
    $active_semester_id = (int) $wpdb->get_var($wpdb->prepare("SELECT id FROM {$semesters_table} WHERE academic_year_id = %d AND is_active = 1 ORDER BY id DESC LIMIT 1", $active_year_id));

    if (!$active_year_id || !$active_semester_id) {
        wp_send_json_error('Active academic year/semester not set');
    }

    // Only delete enrollment for the ACTIVE term (prevents accidental deletion of historical records)
    $deleted = $wpdb->delete(
        $enrollments_table,
        array(
            'student_id' => $student_id,
            'course_id' => $course_id,
            'academic_year_id' => $active_year_id,
            'semester_id' => $active_semester_id
        ),
        array('%d', '%d', '%d', '%d')
    );

    if ($deleted === false) {
        nds_log_error('Failed to unenroll student', array(
            'student_id' => $student_id,
            'course_id' => $course_id,
            'academic_year_id' => $active_year_id,
            'semester_id' => $active_semester_id,
            'error' => $wpdb->last_error
        ));
        wp_send_json_error('Failed to unenroll student: ' . $wpdb->last_error);
    } elseif ($deleted === 0) {
        nds_log_error('Attempted to unenroll student not enrolled in course', array(
            'student_id' => $student_id,
            'course_id' => $course_id,
            'academic_year_id' => $active_year_id,
            'semester_id' => $active_semester_id
        ), 'warning');
        wp_send_json_error('Student was not enrolled in this course for the active term');
    } else {
        // Log successful unenrollment
        nds_log_error('Student successfully unenrolled', array(
            'student_id' => $student_id,
            'course_id' => $course_id,
            'academic_year_id' => $active_year_id,
            'semester_id' => $active_semester_id
        ), 'info');
        wp_send_json_success(true);
    }
}
add_action('wp_ajax_nds_unenroll_student', 'nds_unenroll_student_ajax');

// AJAX: approve student application (set status to active)
function nds_approve_student_application_ajax() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'nds_approve_application_nonce')) {
        wp_send_json_error('Bad nonce');
    }
    $student_id = isset($_POST['student_id']) ? intval($_POST['student_id']) : 0;
    if ($student_id <= 0) {
        wp_send_json_error('Invalid student');
    }
    global $wpdb;
    $ok = $wpdb->update(
        $wpdb->prefix . 'nds_students',
        ['status' => 'active'],
        ['id' => $student_id],
        ['%s'],
        ['%d']
    );
    if ($ok === false) {
        wp_send_json_error($wpdb->last_error ?: 'Update failed');
    }
    wp_send_json_success(true);
}
add_action('wp_ajax_nds_approve_student_application', 'nds_approve_student_application_ajax');

// AJAX: get enrolled count for a course (active term)
function nds_get_course_enrolled_count_ajax() {
    if (!isset($_POST['course_id'])) {
        wp_send_json_error('Missing course_id');
    }
    global $wpdb;
    $course_id = intval($_POST['course_id']);
    $active_year_id = (int) $wpdb->get_var("SELECT id FROM {$wpdb->prefix}nds_academic_years WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
    $active_semester_id = (int) $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}nds_semesters WHERE academic_year_id = %d AND is_active = 1 ORDER BY id DESC LIMIT 1", $active_year_id));
    $count = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}nds_student_enrollments e WHERE e.course_id=%d AND e.academic_year_id=%d AND e.semester_id=%d AND e.status IN ('applied','enrolled','waitlisted')",
        $course_id, $active_year_id, $active_semester_id
    ));
    wp_send_json_success(['count' => $count]);
}
add_action('wp_ajax_nds_get_course_enrolled_count', 'nds_get_course_enrolled_count_ajax');

// AJAX: get overall enrollment quick stats
function nds_get_enrollment_quick_stats_ajax() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }
    global $wpdb;
    $total_students = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}nds_students");
    $enrolled_students = (int) $wpdb->get_var("SELECT COUNT(DISTINCT student_id) FROM {$wpdb->prefix}nds_student_enrollments");
    $courses = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}nds_courses");
    $unassigned = $total_students - $enrolled_students;
    wp_send_json_success([
        'total' => $total_students,
        'enrolled' => $enrolled_students,
        'unassigned' => max(0,$unassigned),
        'courses' => $courses,
    ]);
}
add_action('wp_ajax_nds_get_enrollment_quick_stats', 'nds_get_enrollment_quick_stats_ajax');

function nds_school_deactivate()
{
    global $wpdb;

    // Temporarily disable foreign key checks
    $wpdb->query("SET FOREIGN_KEY_CHECKS = 0;");

    // All NDS tables to drop (in order to handle dependencies)
    $tables = [
        // Application related (drop first due to foreign keys)
        $wpdb->prefix . "nds_application_documents",
        $wpdb->prefix . "nds_application_reviews",
        $wpdb->prefix . "nds_application_payments",
        $wpdb->prefix . "nds_applications",
        $wpdb->prefix . "nds_application_forms",
        
        // Enrollment and student related
        $wpdb->prefix . "nds_student_enrollments",
        $wpdb->prefix . "nds_student_events",
        $wpdb->prefix . "nds_student_progression",
        $wpdb->prefix . "nds_students",
        
        // Course related
        $wpdb->prefix . "nds_course_accreditations",
        $wpdb->prefix . "nds_course_prerequisites",
        $wpdb->prefix . "nds_course_lecturers",
        $wpdb->prefix . "nds_course_schedules",
        $wpdb->prefix . "nds_courses",
        
        // Program related
        $wpdb->prefix . "nds_program_accreditations",
        $wpdb->prefix . "nds_program_levels",
        $wpdb->prefix . "nds_programs",
        
        // Academic calendar
        $wpdb->prefix . "nds_semesters",
        $wpdb->prefix . "nds_academic_years",
        
        // Lookup and reference tables
        $wpdb->prefix . "nds_course_categories",
        $wpdb->prefix . "nds_program_types_lookup",
        $wpdb->prefix . "nds_program_types",
        $wpdb->prefix . "nds_accreditation_bodies",
        $wpdb->prefix . "nds_faculties",
        
        // Staff and other
        $wpdb->prefix . "nds_staff",
        $wpdb->prefix . "nds_recipes",
        $wpdb->prefix . "nds_hero_carousel",
        $wpdb->prefix . "nds_trade_tests",
        
        // Legacy tables (if they still exist)
        $wpdb->prefix . "nds_education_paths",
        $wpdb->prefix . "nds_possible_employment",
        $wpdb->prefix . "nds_duration_breakdown",
    ];

    // Drop all tables
    foreach ($tables as $table) {
        $result = $wpdb->query("DROP TABLE IF EXISTS {$table}");

        if ($result === false) {
            error_log("NDS Plugin Deactivation: Failed to drop table: {$table} - Error: " . $wpdb->last_error);
        } else {
            error_log("NDS Plugin Deactivation: Successfully dropped table: {$table}");
        }
    }

    // Re-enable foreign key checks
    $wpdb->query("SET FOREIGN_KEY_CHECKS = 1;");
    
    // Clear any cached data
    delete_option('nds_portal_rules_flushed');
    flush_rewrite_rules();
}

function nds_add_rewrite_rules() {
    add_rewrite_rule('^academy/([^/]+)-([0-9]+)/?', 'index.php?nds_education_path_id=$matches[2]', 'top');
    add_rewrite_rule('^recipe/([0-9]+)/?$', 'index.php?nds_recipe_id=$matches[1]', 'top');
}
add_action('init', 'nds_add_rewrite_rules');

function nds_add_query_vars($vars) {
    $vars[] = 'nds_education_path_id';
    $vars[] = 'nds_recipe_id';
    return $vars;
}
add_filter('query_vars', 'nds_add_query_vars');

function nds_template_redirect() {
    if (get_query_var('nds_education_path_id')) {
        include plugin_dir_path(__FILE__) . 'templates/education-path-single.php';
        exit;
    }
    
    if (get_query_var('nds_recipe_id')) {
        include plugin_dir_path(__FILE__) . 'templates/recipe-single.php';
        exit;
    }
}
add_action('template_redirect', 'nds_template_redirect');

function nds_page_template_filter($template) {
    if (is_page()) {
        $page_template = get_post_meta(get_the_ID(), '_wp_page_template', true);
        if ($page_template === 'education-path-single.php') {
            $template = plugin_dir_path(__FILE__) . 'templates/education-path-single.php';
        } elseif ($page_template === 'program-single.php') {
            $template = plugin_dir_path(__FILE__) . 'templates/program-single.php';
        }
    }
    return $template;
}
add_filter('page_template', 'nds_page_template_filter');

// Add /programs/slug-id URL
add_action('init', 'nds_add_program_rewrite_rule');
function nds_add_program_rewrite_rule() {
    add_rewrite_rule('^programs/([^/]+)-([0-9]+)/?', 'index.php?nds_program_id=$matches[2]', 'top');
    // Add rewrite rule for calendar
    add_rewrite_rule('^calendar/?$', 'index.php?nds_calendar=1', 'top');
}

// Register query var
add_filter('query_vars', function ($vars) {
    $vars[] = 'nds_program_id';
    // Portal query var for /portal/
    $vars[] = 'nds_portal';
    // Staff portal query var for /staff-portal/
    $vars[] = 'nds_staff_portal';
    // Calendar query var for /calendar/
    $vars[] = 'nds_calendar';
    return $vars;
});

// Redirect to custom template
add_action('template_redirect', 'nds_program_template_redirect');
function nds_program_template_redirect() {
    $program_id = get_query_var('nds_program_id');
    if ($program_id) {
        include plugin_dir_path(__FILE__) . 'templates/program-single.php';
        exit;
    }
    
    // Handle calendar page
    $calendar = get_query_var('nds_calendar');
    if ($calendar == '1') {
        // Enqueue calendar assets
        // FullCalendar CSS
        wp_enqueue_style(
            'fullcalendar-css',
            'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css',
            array(),
            '6.1.10'
        );
        
        // FullCalendar JS
        wp_enqueue_script(
            'fullcalendar-js',
            'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js',
            array('jquery'),
            '6.1.10',
            true
        );
        
        // Unified calendar component
        $calendar_js_path = plugin_dir_path(__FILE__) . 'assets/js/admin-calendar.js';
        if (file_exists($calendar_js_path)) {
            wp_enqueue_script(
                'nds-frontend-calendar',
                plugin_dir_url(__FILE__) . 'assets/js/admin-calendar.js',
                array('jquery', 'fullcalendar-js'),
                filemtime($calendar_js_path),
                true
            );
            
            // Localize script for AJAX (use BOTH names for compatibility during migration)
            $calendar_data = array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('nds_public_calendar_nonce')
            );
            wp_localize_script('nds-frontend-calendar', 'ndsFrontendCalendar', $calendar_data);
            wp_localize_script('nds-frontend-calendar', 'ndsCalendar', $calendar_data);
        }
        
        // Enqueue Tailwind CSS if available
        $css_file = plugin_dir_path(__FILE__) . 'assets/css/frontend.css';
        if (file_exists($css_file)) {
            wp_enqueue_style(
                'nds-tailwindcss-calendar',
                plugin_dir_url(__FILE__) . 'assets/css/frontend.css',
                array(),
                filemtime($css_file),
                'all'
            );
        }
        
        // Font Awesome icons
        wp_enqueue_style('nds-icons', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css', array(), null, 'all');
        
        // Create a simple page that displays the calendar shortcode
        get_header();
        echo do_shortcode('[nds_calendar]');
        get_footer();
        exit;
    }
}


register_deactivation_hook(__FILE__, 'nds_school_deactivate');

// -----------------------------
// Learner Frontend Dashboard: /portal/
// -----------------------------
add_action('init', function () {
    // Keep the existing /portal/ rewrite, but route it to a lean, learner-only dashboard
    add_rewrite_rule('^portal/?$', 'index.php?nds_portal=1', 'top');
    // Staff portal route
    add_rewrite_rule('^staff-portal/?$', 'index.php?nds_staff_portal=1', 'top');
});

add_action('template_redirect', function () {
    $is_portal = (int) get_query_var('nds_portal');
    if ($is_portal !== 1) {
        return;
    }

    // Require login – learners will use their normal WP account
    if (!is_user_logged_in()) {
        wp_safe_redirect(wp_login_url(home_url('/portal/')));
        exit;
    }

    // Map current WP user to an NDS student/learner record
    if (!function_exists('nds_portal_get_current_student_id')) {
        // Safety guard – if helper is missing, fail gracefully
        wp_die(__('Student portal is not available right now.', 'nds-school'));
    }

    $student_id = (int) nds_portal_get_current_student_id();
    if ($student_id <= 0) {
        // Allow admins to view the portal template (which handles missing student ID gracefully)
        if (current_user_can('manage_options')) {
            // Do nothing, let it fall through to include the template
        } else {
            // No learner profile for regular user – send them to the online application form
            $application_url = home_url('/online-application/');
            wp_safe_redirect($application_url);
            exit;
        }
    }

    // Render a standalone full-screen learner dashboard (no theme header/nav)
    include plugin_dir_path(__FILE__) . 'templates/learner-portal.php';
    exit;
});

// Staff Portal Route Handler
add_action('template_redirect', function () {
    $is_staff_portal = (int) get_query_var('nds_staff_portal');
    if ($is_staff_portal !== 1) {
        return;
    }

    // Require login – staff will use their normal WP account
    if (!is_user_logged_in()) {
        wp_safe_redirect(wp_login_url(home_url('/staff-portal/')));
        exit;
    }

    // Map current WP user to an NDS staff record
    if (!function_exists('nds_portal_get_current_staff_id')) {
        wp_die(__('Staff portal is not available right now.', 'nds-school'));
    }

    $staff_id = (int) nds_portal_get_current_staff_id();
    if ($staff_id <= 0) {
        // No staff profile yet for this WP account
        wp_die(__('No staff profile found for your account. Please contact the administrator.', 'nds-school'));
    }

    // Render a standalone full-screen staff dashboard (no theme header/nav)
    include plugin_dir_path(__FILE__) . 'templates/staff-portal.php';
    exit;
});

// Enqueue learner dashboard assets only on /portal/
add_action('wp_enqueue_scripts', function () {
    $is_portal = (int) get_query_var('nds_portal');
    if ($is_portal !== 1) {
        return;
    }

    // Tailwind-style utility CSS used across the plugin
    $frontend_css = plugin_dir_path(__FILE__) . 'assets/css/frontend.css';
    if (file_exists($frontend_css)) {
        wp_enqueue_style(
            'nds-learner-portal-frontend',
            plugin_dir_url(__FILE__) . 'assets/css/frontend.css',
            array(),
            filemtime($frontend_css),
            'all'
        );
    }

    // Additional component styles (cards, layouts, etc.)
    $styles_css = plugin_dir_path(__FILE__) . 'assets/css/styles.css';
    if (file_exists($styles_css)) {
        wp_enqueue_style(
            'nds-learner-portal-styles',
            plugin_dir_url(__FILE__) . 'assets/css/styles.css',
            array('nds-learner-portal-frontend'),
            filemtime($styles_css),
            'all'
        );
    }

    // Student Portal Layout CSS (overrides theme headers/footers)
    $layout_css = plugin_dir_path(__FILE__) . 'assets/css/student-portal-layout.css';
    if (file_exists($layout_css)) {
        wp_enqueue_style(
            'nds-student-portal-layout',
            plugin_dir_url(__FILE__) . 'assets/css/student-portal-layout.css',
            array('nds-learner-portal-frontend', 'nds-learner-portal-styles'),
            filemtime($layout_css),
            'all'
        );
    }

    // Icons for the dashboard
    wp_enqueue_style(
        'nds-learner-portal-icons',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css',
        array(),
        null,
        'all'
    );
    
    // Calendar scripts for timetable tab
    // FullCalendar CSS
    wp_enqueue_style(
        'fullcalendar-css',
        'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css',
        array(),
        '6.1.10'
    );
    
    // FullCalendar JS
    wp_enqueue_script(
        'fullcalendar-js',
        'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js',
        array('jquery'),
        '6.1.10',
        true
    );
    
    // Unified calendar component
    $calendar_js_path = plugin_dir_path(__FILE__) . 'assets/js/admin-calendar.js';
    if (file_exists($calendar_js_path)) {
        wp_enqueue_script(
            'nds-frontend-calendar',
            plugin_dir_url(__FILE__) . 'assets/js/admin-calendar.js',
            array('jquery', 'fullcalendar-js'),
            filemtime($calendar_js_path),
            true
        );
        
        // Localize script for AJAX (use BOTH names for compatibility)
        $calendar_data = array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('nds_public_calendar_nonce')
        );
        wp_localize_script('nds-frontend-calendar', 'ndsFrontendCalendar', $calendar_data);
        wp_localize_script('nds-frontend-calendar', 'ndsCalendar', $calendar_data);
    }
});

// Enqueue staff portal assets only on /staff-portal/
add_action('wp_enqueue_scripts', function () {
    $is_staff_portal = (int) get_query_var('nds_staff_portal');
    if ($is_staff_portal !== 1) {
        return;
    }

    // Tailwind-style utility CSS used across the plugin
    $frontend_css = plugin_dir_path(__FILE__) . 'assets/css/frontend.css';
    if (file_exists($frontend_css)) {
        wp_enqueue_style(
            'nds-staff-portal-frontend',
            plugin_dir_url(__FILE__) . 'assets/css/frontend.css',
            array(),
            filemtime($frontend_css),
            'all'
        );
    }

    // Additional component styles (cards, layouts, etc.)
    $styles_css = plugin_dir_path(__FILE__) . 'assets/css/styles.css';
    if (file_exists($styles_css)) {
        wp_enqueue_style(
            'nds-staff-portal-styles',
            plugin_dir_url(__FILE__) . 'assets/css/styles.css',
            array('nds-staff-portal-frontend'),
            filemtime($styles_css),
            'all'
        );
    }

    // Icons for the dashboard
    wp_enqueue_style(
        'nds-staff-portal-icons',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css',
        array(),
        null,
        'all'
    );
    
    // Calendar scripts for timetable tab
    // FullCalendar CSS
    wp_enqueue_style(
        'fullcalendar-css',
        'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css',
        array(),
        '6.1.10'
    );
    
    // FullCalendar JS
    wp_enqueue_script(
        'fullcalendar-js',
        'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js',
        array('jquery'),
        '6.1.10',
        true
    );
    
    // Unified calendar component (works for both admin and frontend)
    $calendar_js_path = plugin_dir_path(__FILE__) . 'assets/js/admin-calendar.js';
    if (file_exists($calendar_js_path)) {
        wp_enqueue_script(
            'nds-staff-calendar',
            plugin_dir_url(__FILE__) . 'assets/js/admin-calendar.js',
            array('jquery', 'fullcalendar-js'),
            filemtime($calendar_js_path),
            true
        );
        
        // Localize script for AJAX
        wp_localize_script('nds-staff-calendar', 'ndsStaffCalendar', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('nds_staff_calendar_nonce')
        ));
    }
});

// One-time rewrite flush to activate /portal/ and /staff-portal/ without manual permalinks save
add_action('init', function () {
    if (!get_option('nds_portal_rules_flushed')) {
        flush_rewrite_rules(false);
        update_option('nds_portal_rules_flushed', 1);
    }
}, 99);

// Force flush rewrite rules on next page load (one-time for staff portal)
add_action('init', function () {
    if (!get_option('nds_staff_portal_rules_flushed')) {
        flush_rewrite_rules(false);
        update_option('nds_staff_portal_rules_flushed', 1);
    }
}, 100);

// -----------------------------
// Student Portal helpers & AJAX
// -----------------------------
function nds_portal_get_current_student_id() {
    if (!is_user_logged_in()) {
        return 0;
    }
    $wp_user_id = get_current_user_id();
    $user = get_userdata($wp_user_id);
    if (!$user) return 0;

    global $wpdb;
    // Prefer explicit mapping via wp_user_id
    $student_id = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}nds_students WHERE wp_user_id = %d",
        $wp_user_id
    ));
    if ($student_id) return $student_id;

    // Fallback by email match (in case mapping not set yet)
    $student_id = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}nds_students WHERE email = %s",
        $user->user_email
    ));
    return $student_id ?: 0;
}

// -----------------------------
// Staff Portal helpers
// -----------------------------
function nds_portal_get_current_staff_id() {
    if (!is_user_logged_in()) {
        return 0;
    }
    $wp_user_id = get_current_user_id();
    $user = get_userdata($wp_user_id);
    if (!$user) return 0;

    global $wpdb;
    // Get staff ID via user_id mapping (staff table uses 'user_id' field)
    $staff_id = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}nds_staff WHERE user_id = %d",
        $wp_user_id
    ));
    
    // Fallback: try email match if no direct user_id mapping
    if (!$staff_id && $user->user_email) {
        $staff_id = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}nds_staff WHERE email = %s",
            $user->user_email
        ));
    }
    
    return $staff_id ?: 0;
}

/**
 * Public AJAX: register a basic WordPress user for applicants
 * Used by the multi-step online application form before submission.
 */
function nds_register_applicant_user() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field($_POST['nonce']), 'nds_applicant_reg')) {
        wp_send_json_error('Security check failed.');
    }

    $first_name = isset($_POST['first_name']) ? sanitize_text_field(wp_unslash($_POST['first_name'])) : '';
    $last_name  = isset($_POST['last_name']) ? sanitize_text_field(wp_unslash($_POST['last_name'])) : '';
    $email      = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    $password   = isset($_POST['password']) ? (string) wp_unslash($_POST['password']) : '';

    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        wp_send_json_error('All fields are required.');
    }
    if (!is_email($email)) {
        wp_send_json_error('Please enter a valid email address.');
    }
    if (email_exists($email)) {
        wp_send_json_error('An account with this email already exists. Please log in instead.');
    }

    error_log("NDS Registration Attempt: Email=$email, FirstName=$first_name, LastName=$last_name");

    $username = sanitize_user($email, true);
    if (username_exists($username)) {
        // Fallback: append random suffix
        $username = $username . '_' . wp_generate_password(4, false);
    }

    $user_id = wp_create_user($username, $password, $email);
    if (is_wp_error($user_id)) {
        error_log('NDS Registration Error: ' . $user_id->get_error_message());
        wp_send_json_error($user_id->get_error_message());
    }

    // Update basic profile
    wp_update_user(array(
        'ID'         => $user_id,
        'first_name' => $first_name,
        'last_name'  => $last_name,
    ));

    // Log the user in immediately
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id, true);

    error_log('NDS Registration Success: User ID ' . $user_id);
    wp_send_json_success(array(
        'user_id' => $user_id,
    ));
}
add_action('wp_ajax_nopriv_nds_register_applicant_user', 'nds_register_applicant_user');
add_action('wp_ajax_nds_register_applicant_user', 'nds_register_applicant_user');

add_action('wp_ajax_nds_portal_overview', function () {
    if (!is_user_logged_in()) {
        wp_send_json_error('Unauthorized', 401);
    }
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field($_POST['nonce']), 'nds_portal_nonce')) {
        wp_send_json_error('Bad nonce', 403);
    }

    global $wpdb;
    $student_id = nds_portal_get_current_student_id();
    if (!$student_id) {
        wp_send_json_error('Student not found for current user');
    }

    // Active term
    $active_year_id = (int) $wpdb->get_var("SELECT id FROM {$wpdb->prefix}nds_academic_years WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
    $active_semester_id = $active_year_id ? (int) $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}nds_semesters WHERE academic_year_id = %d AND is_active = 1 ORDER BY id DESC LIMIT 1",
        $active_year_id
    )) : 0;

    // KPIs
    $enrolled_count = 0;
    if ($active_year_id && $active_semester_id) {
        $enrolled_count = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}nds_student_enrollments
             WHERE student_id = %d AND academic_year_id = %d AND semester_id = %d AND status IN ('applied','enrolled','waitlisted')",
            $student_id, $active_year_id, $active_semester_id
        ));
    }

    $avg_percentage = (float) $wpdb->get_var($wpdb->prepare(
        "SELECT AVG(final_percentage) FROM {$wpdb->prefix}nds_student_enrollments
         WHERE student_id = %d AND final_percentage IS NOT NULL",
        $student_id
    ));
    $avg_percentage = $avg_percentage ? round($avg_percentage, 1) : 0.0;

    // Notifications (simple recent activity count last 30 days)
    $notifications = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}nds_student_activity_log WHERE student_id = %d AND timestamp > (NOW() - INTERVAL 30 DAY)",
        $student_id
    ));

    // Latest application (if any)
    $latest_app = nds_portal_get_latest_application_for_current_user();

    $mode = 'learner';
    if ($latest_app && $enrolled_count === 0 && in_array($latest_app['status'], array('submitted','under_review','waitlisted','draft','conditional_offer'), true)) {
        $mode = 'applicant';
    }

    wp_send_json_success(array(
        'enrolledCount' => $enrolled_count,
        'average'       => $avg_percentage,
        'notifications' => $notifications,
        'mode'          => $mode,
        'application'   => $latest_app,
    ));
});

add_action('wp_ajax_nds_portal_courses', function () {
    if (!is_user_logged_in()) {
        wp_send_json_error('Unauthorized', 401);
    }
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field($_POST['nonce']), 'nds_portal_nonce')) {
        wp_send_json_error('Bad nonce', 403);
    }

    global $wpdb;
    $student_id = nds_portal_get_current_student_id();
    if (!$student_id) {
        wp_send_json_error('Student not found for current user');
    }

    // Prefer active term courses first
    $active_year_id = (int) $wpdb->get_var("SELECT id FROM {$wpdb->prefix}nds_academic_years WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
    $active_semester_id = $active_year_id ? (int) $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}nds_semesters WHERE academic_year_id = %d AND is_active = 1 ORDER BY id DESC LIMIT 1",
        $active_year_id
    )) : 0;

    $courses = array();
    if ($active_year_id && $active_semester_id) {
        $courses = $wpdb->get_results($wpdb->prepare(
            "SELECT e.id as enrollment_id, c.id as course_id, c.name as course_name, e.status, e.enrollment_date,
                    e.final_percentage, e.final_grade
             FROM {$wpdb->prefix}nds_student_enrollments e
             JOIN {$wpdb->prefix}nds_courses c ON c.id = e.course_id
             WHERE e.student_id = %d AND e.academic_year_id = %d AND e.semester_id = %d
             ORDER BY c.name ASC",
            $student_id, $active_year_id, $active_semester_id
        ), ARRAY_A);
    }

    // Fallback to all-time if none in active term
    if (!$courses) {
        $courses = $wpdb->get_results($wpdb->prepare(
            "SELECT e.id as enrollment_id, c.id as course_id, c.name as course_name, e.status, e.enrollment_date,
                    e.final_percentage, e.final_grade
             FROM {$wpdb->prefix}nds_student_enrollments e
             JOIN {$wpdb->prefix}nds_courses c ON c.id = e.course_id
             WHERE e.student_id = %d
             ORDER BY e.updated_at DESC",
            $student_id
        ), ARRAY_A);
    }

    wp_send_json_success($courses ?: array());
});

add_action('wp_ajax_nds_portal_marks', function () {
    if (!is_user_logged_in()) {
        wp_send_json_error('Unauthorized', 401);
    }
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field($_POST['nonce']), 'nds_portal_nonce')) {
        wp_send_json_error('Bad nonce', 403);
    }

    global $wpdb;
    $student_id = nds_portal_get_current_student_id();
    if (!$student_id) {
        wp_send_json_error('Student not found for current user');
    }

    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT c.name as course_name, e.final_percentage, e.final_grade, e.updated_at
         FROM {$wpdb->prefix}nds_student_enrollments e
         JOIN {$wpdb->prefix}nds_courses c ON c.id = e.course_id
         WHERE e.student_id = %d AND (e.final_percentage IS NOT NULL OR e.final_grade IS NOT NULL)
         ORDER BY e.updated_at DESC",
        $student_id
    ), ARRAY_A);

    wp_send_json_success($rows ?: array());
});

// AJAX handler for uploading learner documents
add_action('wp_ajax_nds_upload_learner_document', 'nds_upload_learner_document_ajax');
function nds_upload_learner_document_ajax() {
    if (!is_user_logged_in()) {
        wp_send_json_error('Unauthorized', 401);
    }
    
    if (!isset($_POST['nds_upload_document_nonce']) || !wp_verify_nonce($_POST['nds_upload_document_nonce'], 'nds_upload_learner_document')) {
        wp_send_json_error('Security check failed', 403);
    }
    
    $learner_id = isset($_POST['learner_id']) ? intval($_POST['learner_id']) : 0;
    if ($learner_id <= 0) {
        wp_send_json_error('Invalid learner ID');
    }
    
    // Verify the logged-in user is linked to this learner (for frontend portal)
    $current_student_id = nds_portal_get_current_student_id();
    if ($current_student_id > 0 && $current_student_id !== $learner_id) {
        // If user is a learner, they can only upload for themselves
        wp_send_json_error('Unauthorized - you can only upload documents for your own account');
    }
    
    // For admin users, allow upload for any learner
    if ($current_student_id <= 0 && !current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }
    
    if (empty($_FILES['document_file']) || $_FILES['document_file']['error'] !== UPLOAD_ERR_OK) {
        wp_send_json_error('No file uploaded or upload error');
    }
    
    $file = $_FILES['document_file'];
    $allowed_extensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_ext, $allowed_extensions)) {
        wp_send_json_error('Invalid file type. Allowed: PDF, DOC, DOCX, JPG, PNG');
    }
    
    if ($file['size'] > 10 * 1024 * 1024) { // 10MB
        wp_send_json_error('File size exceeds 10MB limit');
    }
    
    global $wpdb;
    $learner = nds_get_student($learner_id);
    if (!$learner) {
        wp_send_json_error('Learner not found');
    }
    
    $learner_data = (array) $learner;
    $learner_name = trim(($learner_data['first_name'] ?? '') . ' ' . ($learner_data['last_name'] ?? ''));
    $current_year = date('Y');
    
    // Create student folder structure: /public/Students/{Year}/{student_id}_{name}/
    $plugin_dir = plugin_dir_path(__FILE__);
    $student_folder_name = $learner_id . '_' . sanitize_file_name(str_replace(' ', '-', strtolower($learner_name)));
    $student_base_dir = $plugin_dir . 'public/Students/' . $current_year . '/';
    $student_upload_dir = $student_base_dir . $student_folder_name . '/';
    
    if (!file_exists($student_upload_dir)) {
        wp_mkdir_p($student_upload_dir);
    }
    
    // Generate unique filename: [student_number]_[initials]_[document_name].[ext]
    $student_info = nds_get_student($learner_id);
    $student_number = $student_info->student_number ?? 'STU' . $learner_id;
    $initials = '';
    if (!empty($student_info->first_name)) {
        $initials .= substr($student_info->first_name, 0, 1);
    }
    if (!empty($student_info->last_name)) {
        $initials .= substr($student_info->last_name, 0, 1);
    }
    $initials = strtoupper($initials);
    
    $document_name_raw = isset($_POST['document_name']) ? sanitize_text_field($_POST['document_name']) : 'document';
    $document_name_slug = sanitize_file_name($document_name_raw);
    
    $unique_filename = $student_number . '_' . $initials . '_' . $document_name_slug . '_' . time() . '.' . $file_ext;
    $dest_path = $student_upload_dir . $unique_filename;
    
    if (!move_uploaded_file($file['tmp_name'], $dest_path)) {
        wp_send_json_error('Failed to save file');
    }
    
    // Store document info in database
    $relative_path = 'Students/' . $current_year . '/' . $student_folder_name . '/' . $unique_filename;
    $category = isset($_POST['document_category']) ? sanitize_text_field($_POST['document_category']) : 'other';
    
    // Map categories to document_type ENUM
    $doc_type_map = [
        'application' => 'other', // application_documents table exists, but we use this for general uploads
        'academic'    => 'academic_record',
        'financial'   => 'other',
        'other'       => 'other'
    ];
    $doc_type = $doc_type_map[$category] ?? 'other';

    // We need an application_id if we want to use nds_application_documents. 
    // Usually students have an application record.
    $application = $wpdb->get_row($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}nds_applications WHERE student_id = %d ORDER BY created_at DESC LIMIT 1",
        $learner_id
    ));

    if ($application) {
        $wpdb->insert(
            $wpdb->prefix . 'nds_application_documents',
            [
                'application_id' => $application->id,
                'document_type'  => $doc_type,
                'file_name'      => $document_name_raw,
                'file_path'      => $relative_path,
                'file_size'      => $file['size'],
                'mime_type'      => $file['type'],
                'uploaded_by'    => get_current_user_id(),
                'uploaded_at'    => current_time('mysql')
            ],
            ['%d', '%s', '%s', '%s', '%d', '%s', '%d', '%s']
        );
    }
    
    wp_send_json_success([
        'message' => 'Document uploaded successfully',
        'path' => $relative_path,
        'filename' => $unique_filename
    ]);
}

// AJAX handler for removing learner documents
add_action('wp_ajax_nds_remove_learner_document', 'nds_remove_learner_document_ajax');
function nds_remove_learner_document_ajax() {
    if (!is_user_logged_in()) {
        wp_send_json_error('Unauthorized', 401);
    }
    
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'nds_remove_learner_document')) {
        wp_send_json_error('Security check failed', 403);
    }
    
    $learner_id = isset($_POST['learner_id']) ? intval($_POST['learner_id']) : 0;
    $doc_label = isset($_POST['document_name']) ? sanitize_text_field($_POST['document_name']) : '';
    
    if ($learner_id <= 0 || empty($doc_label)) {
        wp_send_json_error('Invalid parameters');
    }
    
    // Authorization check
    $current_student_id = nds_portal_get_current_student_id();
    if ($current_student_id > 0 && $current_student_id !== $learner_id) {
        wp_send_json_error('Unauthorized');
    }
    if ($current_student_id <= 0 && !current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }
    
    global $wpdb;
    
    // Mapping for application form fields
    $required_docs_mapping = [
        'ID/Passport (Applicant)' => 'id_passport_applicant',
        'ID/Passport (Responsible Person)' => 'id_passport_responsible',
        'SAQA Certificate' => 'saqa_certificate',
        'Study Permit' => 'study_permit',
        'Parent/Spouse ID' => 'parent_spouse_id',
        'Latest Results' => 'latest_results',
        'Proof of Residence' => 'proof_residence',
        'Highest Grade Certificate' => 'highest_grade_cert',
        'Proof of Medical Aid' => 'proof_medical_aid'
    ];
    
    $removed = false;
    
    // 1. Check if it's a field in application_forms
    if (isset($required_docs_mapping[$doc_label])) {
        $field_name = $required_docs_mapping[$doc_label];
        $student = nds_get_student($learner_id);
        if ($student && !empty($student->email)) {
            $wpdb->update(
                $wpdb->prefix . 'nds_application_forms',
                [$field_name => ''],
                ['email' => $student->email],
                ['%s'],
                ['%s']
            );
            $removed = true;
        }
    }
    
    // 2. Also remove from application_documents table if it exists there
    $app_ids = $wpdb->get_col($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}nds_applications WHERE student_id = %d",
        $learner_id
    ));
    
    if (!empty($app_ids)) {
        foreach ($app_ids as $app_id) {
            $wpdb->delete(
                $wpdb->prefix . 'nds_application_documents',
                ['application_id' => $app_id, 'file_name' => $doc_label]
            );
        }
        $removed = true;
    }
    
    if ($removed) {
        wp_send_json_success('Document removed successfully');
    } else {
        wp_send_json_error('Document not found or could not be removed');
    }
}