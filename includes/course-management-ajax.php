<?php
// Prevent direct access - this file should only be included by WordPress
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Ensure schedule component is available for AJAX calls
if (!function_exists('nds_render_schedule_fields')) {
    require_once NDS_SCHOOL_PLUGIN_DIR . 'includes/partials/schedule-fields.php';
}

// AJAX handler for updating student enrollments
add_action('wp_ajax_nds_update_student_enrollments', 'nds_update_student_enrollments_ajax');
add_action('wp_ajax_nds_get_course_students', 'nds_get_course_students_ajax');
add_action('wp_ajax_nds_get_modal_student_lists', 'nds_get_modal_student_lists_ajax');
add_action('wp_ajax_nds_assign_lecturer', 'nds_assign_lecturer_ajax');

// AJAX handler for updating student enrollments
function nds_update_student_enrollments_ajax() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'nds_student_enrollment_nonce')) {
        wp_die('Security check failed');
    }

    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }

    global $wpdb;

    $course_id = intval($_POST['course_id']);
    $enrolled_student_ids = json_decode(stripslashes($_POST['enrolled_student_ids']), true);

    if (!$course_id || !is_array($enrolled_student_ids)) {
        wp_send_json_error('Invalid data provided');
    }

    $table_enrollments = $wpdb->prefix . 'nds_student_enrollments';

    try {
        // Begin transaction
        $wpdb->query('START TRANSACTION');

        // Get current enrollments for this course
        $current_enrollments = $wpdb->get_results($wpdb->prepare("
            SELECT student_id FROM {$table_enrollments} WHERE course_id = %d
        ", $course_id), ARRAY_A);
        
        $current_student_ids = array_column($current_enrollments, 'student_id');

        // Remove students who are no longer enrolled
        $students_to_remove = array_diff($current_student_ids, $enrolled_student_ids);
        if (!empty($students_to_remove)) {
            $placeholders = implode(',', array_fill(0, count($students_to_remove), '%d'));
            $wpdb->query($wpdb->prepare("
                DELETE FROM {$table_enrollments} 
                WHERE course_id = %d AND student_id IN ({$placeholders})
            ", array_merge([$course_id], $students_to_remove)));
        }

        // Get active academic year and semester
        $active_year_id = (int) $wpdb->get_var("SELECT id FROM {$wpdb->prefix}nds_academic_years WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
        $active_semester_id = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}nds_semesters WHERE academic_year_id = %d AND is_active = 1 ORDER BY id DESC LIMIT 1",
            $active_year_id
        ));
        
        // Fallback to defaults if not set
        if (!$active_year_id) $active_year_id = 1;
        if (!$active_semester_id) $active_semester_id = 1;
        
        // Add new students
        $students_to_add = array_diff($enrolled_student_ids, $current_student_ids);
        foreach ($students_to_add as $student_id) {
            // Check for duplicate enrollment before inserting
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$table_enrollments} 
                 WHERE student_id = %d AND course_id = %d AND academic_year_id = %d AND semester_id = %d",
                $student_id, $course_id, $active_year_id, $active_semester_id
            ));
            
            if ($existing) {
                // Update existing enrollment status
                $wpdb->update(
                    $table_enrollments,
                    ['status' => 'enrolled', 'updated_at' => current_time('mysql')],
                    ['id' => $existing],
                    ['%s', '%s'],
                    ['%d']
                );
                continue;
            }
            
            $result = $wpdb->insert(
                $table_enrollments,
                [
                    'student_id' => $student_id,
                    'course_id' => $course_id,
                    'academic_year_id' => $active_year_id,
                    'semester_id' => $active_semester_id,
                    'enrollment_date' => current_time('Y-m-d'),
                    'status' => 'enrolled',
                    'created_at' => current_time('mysql')
                ],
                ['%d', '%d', '%d', '%d', '%s', '%s', '%s']
            );
            
            // Handle duplicate key errors
            if (!$result && $wpdb->last_error) {
                if (strpos($wpdb->last_error, 'Duplicate') !== false || strpos($wpdb->last_error, 'UNIQUE') !== false) {
                    // Duplicate prevented by database constraint
                    continue;
                }
            }
        }

        // Commit transaction
        $wpdb->query('COMMIT');

        wp_send_json_success([
            'message' => 'Student enrollments updated successfully',
            'added_count' => count($students_to_add),
            'removed_count' => count($students_to_remove)
        ]);

    } catch (Exception $e) {
        // Rollback transaction
        $wpdb->query('ROLLBACK');
        wp_send_json_error('Database error: ' . $e->getMessage());
    }
}

// AJAX handler for getting course students table HTML
function nds_get_course_students_ajax() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'nds_get_students_nonce')) {
        wp_die('Security check failed');
    }

    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }

    global $wpdb;

    $course_id = intval($_POST['course_id']);

    if (!$course_id) {
        wp_send_json_error('Invalid course ID');
    }

    $table_students = $wpdb->prefix . 'nds_students';
    $table_enrollments = $wpdb->prefix . 'nds_student_enrollments';

    // Get enrolled students
    $enrolled_students = $wpdb->get_results($wpdb->prepare("
        SELECT s.*, se.enrollment_date, se.status as enrollment_status
        FROM {$table_students} s
        INNER JOIN {$table_enrollments} se ON s.id = se.student_id
        WHERE se.course_id = %d
        ORDER BY s.last_name, s.first_name
    ", $course_id), ARRAY_A);

    $html = '';
    $count = 0;

    if (!empty($enrolled_students)) {
        foreach ($enrolled_students as $student) {
            $count++;
            $status_classes = [
                'active' => 'bg-green-100 text-green-800',
                'inactive' => 'bg-yellow-100 text-yellow-800',
                'suspended' => 'bg-red-100 text-red-800'
            ];
            $status_class = $status_classes[$student['status']] ?? 'bg-gray-100 text-gray-800';

            $html .= '<tr class="student-item">';
            $html .= '<td class="px-4 py-2 flex items-center space-x-2">';
            $html .= '<span class="w-8 h-8 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center">';
            $html .= '<i class="fas fa-user"></i>';
            $html .= '</span>';
            $html .= '<span>' . esc_html(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) . '</span>';
            $html .= '</td>';
            $html .= '<td class="px-4 py-2 text-gray-700">' . esc_html($student['student_number'] ?? 'N/A') . '</td>';
            $html .= '<td class="px-4 py-2">';
            if (!empty($student['status'])) {
                $html .= '<span class="inline-block px-2 py-1 rounded-full text-xs font-semibold ' . $status_class . '">';
                $html .= ucfirst(esc_html($student['status']));
                $html .= '</span>';
            } else {
                $html .= '<span class="text-gray-400">-</span>';
            }
            $html .= '</td>';
            $html .= '<td class="px-4 py-2 text-gray-700">';
            if (!empty($student['enrollment_date'])) {
                $html .= esc_html(date('Y-m-d', strtotime($student['enrollment_date'])));
            } else {
                $html .= '<span class="text-gray-400">-</span>';
            }
            $html .= '</td>';
            $html .= '</tr>';
        }
    }

    wp_send_json_success([
        'data' => $html,
        'count' => $count
    ]);
}

// AJAX handler for getting modal student lists (enrolled and available)
function nds_get_modal_student_lists_ajax() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'nds_get_students_nonce')) {
        wp_die('Security check failed');
    }

    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }

    global $wpdb;

    $course_id = intval($_POST['course_id']);

    if (!$course_id) {
        wp_send_json_error('Invalid course ID');
    }

    $table_students = $wpdb->prefix . 'nds_students';
    $table_enrollments = $wpdb->prefix . 'nds_student_enrollments';

    // Get enrolled students
    $enrolled_students = $wpdb->get_results($wpdb->prepare("
        SELECT s.*, se.enrollment_date, se.status as enrollment_status
        FROM {$table_students} s
        INNER JOIN {$table_enrollments} se ON s.id = se.student_id
        WHERE se.course_id = %d
        ORDER BY s.last_name, s.first_name
    ", $course_id), ARRAY_A);

    // Get all active students
    $all_students = $wpdb->get_results("
        SELECT s.* FROM {$table_students} s
        ORDER BY s.last_name, s.first_name
    ", ARRAY_A);

    $enrolled_student_ids = array_column($enrolled_students, 'id');

    // Generate enrolled students HTML
    $enrolled_html = '';
    if (!empty($enrolled_students)) {
        foreach ($enrolled_students as $student) {
            $enrolled_html .= '<div class="student-item bg-white border border-gray-200 rounded-lg p-3 cursor-move hover:shadow-md transition-shadow" 
                                 draggable="true" data-student-id="' . $student['id'] . '">
                                 <div class="flex items-center space-x-3">
                                     <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center text-white text-sm font-medium">
                                         ' . strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1)) . '
                                     </div>
                                     <div class="flex-1">
                                         <p class="font-medium text-gray-900">' . esc_html($student['first_name'] . ' ' . $student['last_name']) . '</p>
                                         <p class="text-sm text-gray-500">' . esc_html($student['student_number']) . '</p>
                                     </div>
                                 </div>
                             </div>';
        }
    } else {
        $enrolled_html = '<div class="flex flex-col items-center justify-center h-80 text-center">
                              <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                                  <i class="fas fa-users text-green-500 text-2xl"></i>
                              </div>
                              <h4 class="text-lg font-medium text-gray-900 mb-2">Drop Students Here</h4>
                              <p class="text-gray-600 text-sm max-w-xs">Drag students from the left column to enroll them in this course</p>
                          </div>';
    }

    // Generate available students HTML
    $available_html = '';
    $available_count = 0;
    foreach ($all_students as $student) {
        if (!in_array($student['id'], $enrolled_student_ids)) {
            $available_count++;
            $available_html .= '<div class="student-item bg-white border border-gray-200 rounded-lg p-3 cursor-move hover:shadow-md transition-shadow" 
                                   draggable="true" data-student-id="' . $student['id'] . '">
                                   <div class="flex items-center space-x-3">
                                       <div class="w-8 h-8 bg-gray-500 rounded-full flex items-center justify-center text-white text-sm font-medium">
                                           ' . strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1)) . '
                                       </div>
                                       <div class="flex-1">
                                           <p class="font-medium text-gray-900">' . esc_html($student['first_name'] . ' ' . $student['last_name']) . '</p>
                                           <p class="text-sm text-gray-500">' . esc_html($student['student_number']) . '</p>
                                       </div>
                                   </div>
                               </div>';
        }
    }

    if ($available_count === 0) {
        $available_html = '<div class="flex flex-col items-center justify-center h-80 text-center">
                               <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                   <i class="fas fa-user-plus text-gray-500 text-2xl"></i>
                               </div>
                               <h4 class="text-lg font-medium text-gray-900 mb-2">All Students Enrolled</h4>
                               <p class="text-gray-600 text-sm max-w-xs">All available students are already enrolled in this course</p>
                           </div>';
    }

    wp_send_json_success([
        'enrolled_html' => $enrolled_html,
        'available_html' => $available_html,
        'enrolled_count' => count($enrolled_students),
        'available_count' => $available_count
    ]);
}

// AJAX handler for assigning lecturer to course
function nds_assign_lecturer_ajax() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'nds_lecturer_assignment_nonce')) {
        wp_send_json_error('Security check failed');
    }

    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }

    global $wpdb;

    $course_id = intval($_POST['course_id']);
    $lecturer_id = intval($_POST['lecturer_id']);

    if (!$course_id || !$lecturer_id) {
        wp_send_json_error('Invalid data provided');
    }

    $table_lecturers = $wpdb->prefix . 'nds_course_lecturers';

    try {
        // Check if lecturer is already assigned to this course
        $existing_assignment = $wpdb->get_row($wpdb->prepare("
            SELECT id FROM {$table_lecturers} 
            WHERE course_id = %d AND lecturer_id = %d
        ", $course_id, $lecturer_id), ARRAY_A);

        if ($existing_assignment) {
            wp_send_json_error('This lecturer is already assigned to this course');
        }

        // Insert new assignment
        $result = $wpdb->insert(
            $table_lecturers,
            [
                'course_id' => $course_id,
                'lecturer_id' => $lecturer_id,
                'assigned_date' => current_time('mysql')
            ],
            ['%d', '%d', '%s']
        );

        if ($result === false) {
            wp_send_json_error('Failed to assign lecturer');
        }

        wp_send_json_success([
            'message' => 'Lecturer assigned successfully',
            'assignment_id' => $wpdb->insert_id
        ]);

    } catch (Exception $e) {
        wp_send_json_error('Database error: ' . $e->getMessage());
    }
}

/**
 * AJAX: Get module schedule editor (renders schedule-fields component)
 */
add_action('wp_ajax_nds_get_module_schedule_editor', 'nds_get_module_schedule_editor_ajax');
function nds_get_module_schedule_editor_ajax() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'nds_module_schedules_nonce')) {
        wp_send_json_error('Security check failed');
    }

    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }

    global $wpdb;
    $module_id = intval($_POST['module_id'] ?? 0);
    $course_id = intval($_POST['course_id'] ?? 0);

    if (!$module_id || !$course_id) {
        wp_send_json_error('Invalid module or course ID');
    }

    // Get existing schedules for this module
    $schedules_table = $wpdb->prefix . 'nds_course_schedules';
    $existing_schedules = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$schedules_table} 
         WHERE module_id = %d 
         ORDER BY days, start_time",
        $module_id
    ), ARRAY_A);

    // Get lecturers for course
    $staff_table = $wpdb->prefix . 'nds_staff';
    $course_lecturers_table = $wpdb->prefix . 'nds_course_lecturers';
    $lecturers = $wpdb->get_results($wpdb->prepare(
        "SELECT s.* FROM {$staff_table} s
         INNER JOIN {$course_lecturers_table} cl ON s.id = cl.lecturer_id
         WHERE cl.course_id = %d
         ORDER BY s.first_name, s.last_name",
        $course_id
    ), ARRAY_A);

    // Get modules for course (needed by schedule component)
    $modules_table = $wpdb->prefix . 'nds_modules';
    $modules = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$modules_table} WHERE course_id = %d AND status = 'active' ORDER BY name",
        $course_id
    ), ARRAY_A);

    ob_start();
    
    // Render schedule fields component
    if (function_exists('nds_render_schedule_fields')) {
        nds_render_schedule_fields([
            'lecturers' => $lecturers,
            'prefix' => 'schedule',
            'existing_schedules' => $existing_schedules,
            'course_id' => $course_id,
            'modules' => $modules,
        ]);
    } else {
        echo '<p class="text-red-500">Schedule component not found</p>';
    }
    
    $html = ob_get_clean();
    wp_send_json_success($html);
}

/**
 * Handle form submission: Update module schedules
 */
add_action('admin_post_nds_update_module_schedules', 'nds_update_module_schedules_handler');
function nds_update_module_schedules_handler() {
    // Check nonce and permissions
    if (!isset($_POST['nds_edit_module_schedules_nonce']) || !wp_verify_nonce($_POST['nds_edit_module_schedules_nonce'], 'nds_edit_module_schedules')) {
        wp_die('Security check failed');
    }

    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }

    global $wpdb;
    $module_id = intval($_POST['module_id'] ?? 0);
    $course_id = intval($_POST['course_id'] ?? 0);

    if (!$module_id || !$course_id) {
        wp_redirect(add_query_arg('error', 'invalid_data', wp_get_referer()));
        exit;
    }

    // Get module and course info
    $module = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}nds_modules WHERE id = %d",
        $module_id
    ), ARRAY_A);

    if (!$module) {
        wp_redirect(add_query_arg('error', 'module_not_found', wp_get_referer()));
        exit;
    }

    // Get old schedules for change detection
    $old_schedules = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}nds_course_schedules 
         WHERE module_id = %d 
         ORDER BY id",
        $module_id
    ), ARRAY_A);

    $old_schedules_by_id = [];
    foreach ($old_schedules as $sched) {
        $old_schedules_by_id[$sched['id']] = $sched;
    }

    // Delete old schedules and insert new ones
    $wpdb->delete(
        $wpdb->prefix . 'nds_course_schedules',
        ['module_id' => $module_id],
        ['%d']
    );

    // Process new schedules from form
    $changes = [];
    $new_schedules_added = [];

    if (isset($_POST['schedule']) && is_array($_POST['schedule'])) {
        foreach ($_POST['schedule'] as $index => $schedule_data) {
            // Handle days array to string conversion
            $days = '';
            if (isset($schedule_data['days'])) {
                if (is_array($schedule_data['days'])) {
                    $days = implode(', ', array_map('sanitize_text_field', $schedule_data['days']));
                } else {
                    $days = sanitize_text_field($schedule_data['days']);
                }
            }

            // Skip empty schedules
            if (empty($days) || empty($schedule_data['start_time'])) {
                continue;
            }

            $start_time = sanitize_text_field($schedule_data['start_time']);
            $end_time = !empty($schedule_data['end_time']) ? sanitize_text_field($schedule_data['end_time']) : $start_time;
            $location = sanitize_text_field($schedule_data['location'] ?? '');
            $session_type = sanitize_text_field($schedule_data['session_type'] ?? '');
            $lecturer_id = !empty($schedule_data['lecturer_id']) ? intval($schedule_data['lecturer_id']) : null;

            // Calculate hours
            $start = strtotime($start_time);
            $end = strtotime($end_time);
            $day_hours = ($end - $start) / 3600;

            $schedule_insert = [
                'module_id' => $module_id,
                'course_id' => $course_id,
                'days' => $days,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'location' => $location,
                'session_type' => $session_type,
                'lecturer_id' => $lecturer_id,
                'day_hours' => $day_hours,
                'is_active' => 1
            ];

            $result = $wpdb->insert(
                $wpdb->prefix . 'nds_course_schedules',
                $schedule_insert,
                ['%d', '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%f', '%d']
            );

            if ($result) {
                $new_id = $wpdb->insert_id;
                $new_schedules_added[] = $new_id;

                // Track changes for old schedule (if updating)
                if (isset($old_schedules_by_id[$new_id])) {
                    $old = $old_schedules_by_id[$new_id];
                    
                    if ($old['days'] !== $days) {
                        $changes[] = "Days: {$old['days']} → $days";
                    }
                    if ($old['start_time'] !== $start_time) {
                        $changes[] = "Start time: " . date('H:i', strtotime($old['start_time'])) . " → " . date('H:i', strtotime($start_time));
                    }
                    if ($old['end_time'] !== $end_time) {
                        $changes[] = "End time: " . date('H:i', strtotime($old['end_time'])) . " → " . date('H:i', strtotime($end_time));
                    }
                    if ($old['location'] !== $location) {
                        $changes[] = "Venue: " . ($old['location'] ?: 'Not set') . " → " . ($location ?: 'Not set');
                    }
                } else {
                    // New schedule
                    $changes[] = "New schedule added: $days, $start_time-$end_time" . ($location ? ", Room: $location" : "");
                }
            }
        }
    }

    // Send notifications to students with details about what changed
    if (!empty($changes) && function_exists('nds_create_notification')) {
        $course_name = $wpdb->get_var($wpdb->prepare(
            "SELECT name FROM {$wpdb->prefix}nds_courses WHERE id = %d",
            $course_id
        ));

        // Get students enrolled in this course
        $students = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT se.student_id 
             FROM {$wpdb->prefix}nds_student_enrollments se 
             WHERE se.course_id = %d AND se.status IN ('active', 'enrolled')",
            $course_id
        ));

        if (!empty($students)) {
            $title = "Schedule Updated: " . $module['name'];
            
            // Build detailed message with what changed
            // build an HTML list of changes with tailwind spacing classes
            $changes_html = '<ul class="ml-4 mt-2 mb-2 list-disc">'; 
            foreach ($changes as $change) {
                // Add emoji indicators
                $icon = '📍'; // default
                if (strpos($change, 'Days') === 0) $icon = '📅';
                if (strpos($change, 'Start') === 0 || strpos($change, 'End') === 0) $icon = '🕐';
                if (strpos($change, 'Venue') === 0) $icon = '🏢';
                if (strpos($change, 'New') === 0) $icon = '✅';

                $changes_html .= '<li>' . esc_html($icon . ' ' . $change) . '</li>';
            }
            $changes_html .= '</ul>';

            // store the message with HTML markup so it can be rendered on the portal
            $message = 'The schedule for ' . esc_html($module['name']) . ' has been updated:';
            $message .= '<br><br>' . $changes_html;
            $message .= '<br><br>Please check your timetable.';

            foreach ($students as $student_id) {
                nds_create_notification(
                    $student_id,
                    $title,
                    $message,
                    'timetable',
                    '/portal/timetable/'
                );
            }
        }
    }

    wp_redirect(add_query_arg('success', 'schedules_updated', wp_get_referer()));
    exit;
}
?>
