<?php
if (!defined('ABSPATH')) {
    exit;
}

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo esc_html(get_bloginfo('name')); ?> — Student Portal</title>
    <?php wp_head(); ?>
    <style>
        /* Timetable specific styles */
        .timetable-grid {
            display: grid;
            grid-template-columns: 100px repeat(5, 1fr);
            gap: 1px;
            background-color: #e5e7eb;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            overflow: hidden;
        }
        
        .timetable-header {
            background-color: #f8fafc;
            font-weight: 600;
            padding: 1rem 0.5rem;
            text-align: center;
            color: #1e293b;
            border-bottom: 2px solid #cbd5e1;
        }
        
        .timetable-time-slot {
            background-color: #f1f5f9;
            font-weight: 500;
            padding: 1rem 0.5rem;
            text-align: center;
            color: #475569;
            border-right: 1px solid #e5e7eb;
        }
        
        .timetable-cell {
            background-color: white;
            min-height: 100px;
            padding: 0.5rem;
            position: relative;
        }
        
        .timetable-cell.has-class {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
        }
        
        .timetable-cell .module-code {
            font-weight: 700;
            font-size: 0.8rem;
            color: #1e40af;
            display: block;
            margin-bottom: 0.25rem;
        }
        
        .timetable-cell .module-name {
            font-size: 0.75rem;
            color: #334155;
            margin-bottom: 0.25rem;
            line-height: 1.3;
        }
        
        .timetable-cell .venue {
            font-size: 0.7rem;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .timetable-cell .venue i {
            font-size: 0.6rem;
        }
        
        .timetable-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 1rem;
            padding: 1rem;
            background-color: #f8fafc;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
        }
        
        .timetable-legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: #475569;
        }
        
        .timetable-legend-color {
            width: 20px;
            height: 20px;
            border-radius: 4px;
        }
        
        .timetable-legend-color.lecture {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
        }
        
        .timetable-legend-color.practical {
            background-color: #f0fdf4;
            border-left: 4px solid #22c55e;
        }
        
        .timetable-legend-color.tutorial {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
        }
        
        .timetable-legend-color.assessment {
            background-color: #fee2e2;
            border-left: 4px solid #ef4444;
        }
        
        .timetable-legend-color.exam {
            background-color: #fae8ff;
            border-left: 4px solid #a855f7;
        }
        
        .timetable-legend-color.holiday {
            background-color: #fee2e2;
            border-left: 4px solid #dc2626;
        }
        
        .timetable-legend-color.event {
            background-color: #fff7ed;
            border-left: 4px solid #f97316;
        }
        
        .current-week-badge {
            background-color: #3b82f6;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .week-navigation {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .week-nav-button {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 9999px;
            background-color: white;
            border: 1px solid #e5e7eb;
            color: #4b5563;
            transition: all 0.2s;
        }
        
        .week-nav-button:hover {
            background-color: #f3f4f6;
            border-color: #3b82f6;
            color: #3b82f6;
        }
        
        .week-nav-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        /* Calendar specific styles */
        .calendar-container {
            background: white;
            border-radius: 1rem;
            border: 1px solid #e5e7eb;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            background: linear-gradient(to right, #f8fafc, #ffffff);
            border-bottom: 1px solid #e5e7eb;
        }
        
        .calendar-title {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .calendar-month-year {
            display: flex;
            align-items: baseline;
            gap: 0.5rem;
        }
        
        .calendar-month {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1e293b;
            line-height: 1.2;
        }
        
        .calendar-year {
            font-size: 1.4rem;
            color: #64748b;
            font-weight: 500;
        }
        
        .calendar-day-name {
            font-size: 1rem;
            color: #3b82f6;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .calendar-nav {
            display: flex;
            gap: 0.5rem;
        }
        
        .calendar-nav-btn {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 9999px;
            background-color: white;
            border: 1px solid #e5e7eb;
            color: #4b5563;
            transition: all 0.2s;
            cursor: pointer;
        }
        
        .calendar-nav-btn:hover {
            background-color: #f3f4f6;
            border-color: #3b82f6;
            color: #3b82f6;
        }
        
        .calendar-weekdays {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            background-color: #f8fafc;
            padding: 1rem 0;
            text-align: center;
            font-weight: 600;
            color: #475569;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .calendar-weekday {
            font-size: 0.9rem;
        }
        
        .calendar-weekday .short {
            display: none;
        }
        
        @media (max-width: 640px) {
            .calendar-weekday .full {
                display: none;
            }
            .calendar-weekday .short {
                display: inline;
            }
        }
        
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1px;
            background-color: #e5e7eb;
            padding: 1px;
        }
        
        .calendar-day {
            background-color: white;
            min-height: 130px;
            padding: 0.75rem;
            position: relative;
            transition: background-color 0.2s;
        }
        
        .calendar-day:hover {
            background-color: #f8fafc;
        }
        
        .calendar-day.other-month {
            background-color: #f9fafb;
            color: #9ca3af;
        }
        
        .calendar-day.today {
            background-color: #eff6ff;
            border: 2px solid #3b82f6;
            position: relative;
            z-index: 1;
        }
        
        .calendar-day.has-events {
            cursor: pointer;
        }
        
        .day-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .day-number {
            font-weight: 600;
            font-size: 1rem;
            color: #1e293b;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 9999px;
        }
        
        .today .day-number {
            background-color: #3b82f6;
            color: white;
        }
        
        .day-weekday {
            font-size: 0.7rem;
            color: #64748b;
            background-color: #f1f5f9;
            padding: 0.2rem 0.5rem;
            border-radius: 9999px;
        }
        
        .calendar-events {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        
        .calendar-event {
            font-size: 0.7rem;
            padding: 0.2rem 0.4rem;
            border-radius: 0.25rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            cursor: pointer;
            transition: all 0.2s;
            border-left-width: 3px;
            border-left-style: solid;
        }
        
        .calendar-event:hover {
            transform: scale(1.02);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .calendar-event.lecture {
            background-color: #eff6ff;
            color: #1e40af;
            border-left-color: #3b82f6;
        }
        
        .calendar-event.practical {
            background-color: #f0fdf4;
            color: #166534;
            border-left-color: #22c55e;
        }
        
        .calendar-event.tutorial {
            background-color: #fef3c7;
            color: #92400e;
            border-left-color: #f59e0b;
        }
        
        .calendar-event.assessment {
            background-color: #fee2e2;
            color: #991b1b;
            border-left-color: #ef4444;
        }
        
        .calendar-event.exam {
            background-color: #fae8ff;
            color: #6b21a8;
            border-left-color: #a855f7;
        }
        
        .calendar-event.holiday {
            background-color: #fee2e2;
            color: #991b1b;
            border-left-color: #dc2626;
        }
        
        .calendar-event.event {
            background-color: #fff7ed;
            color: #9a3412;
            border-left-color: #f97316;
        }
        
        .event-time {
            font-weight: 600;
            margin-right: 0.25rem;
        }
        
        .event-more {
            font-size: 0.7rem;
            color: #64748b;
            font-weight: 600;
            margin-top: 0.25rem;
            text-align: center;
            background-color: #f1f5f9;
            padding: 0.2rem;
            border-radius: 0.25rem;
        }
        
        .calendar-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            padding: 1rem;
            background-color: #f8fafc;
            border-top: 1px solid #e5e7eb;
        }
        
        .view-toggle {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .view-toggle-btn {
            padding: 0.5rem 1.5rem;
            border-radius: 9999px;
            font-size: 0.9rem;
            font-weight: 500;
            background-color: white;
            border: 1px solid #e5e7eb;
            color: #4b5563;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .view-toggle-btn.active {
            background-color: #3b82f6;
            border-color: #3b82f6;
            color: white;
        }
        
        .view-toggle-btn:hover {
            border-color: #3b82f6;
            background-color: #f8fafc;
        }
        
        .view-toggle-btn.active:hover {
            background-color: #2563eb;
        }
        
        /* Event detail modal */
        .event-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 100000;
            align-items: center;
            justify-content: center;
        }
        
        .event-modal.active {
            display: flex;
        }
        
        .event-modal-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(4px);
        }
        
        .event-modal-content {
            position: relative;
            background-color: white;
            border-radius: 1.5rem;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            border: 2px solid #3b82f6;
            animation: modalSlideIn 0.3s ease-out;
        }
        
        @keyframes modalSlideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .event-modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            background: linear-gradient(to right, #f8fafc, #ffffff);
        }
        
        .event-modal-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
        }
        
        .event-modal-datetime {
            font-size: 0.9rem;
            color: #64748b;
            margin-top: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .event-modal-close {
            width: 32px;
            height: 32px;
            border-radius: 9999px;
            background-color: #f1f5f9;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #64748b;
            transition: all 0.2s;
        }
        
        .event-modal-close:hover {
            background-color: #fee2e2;
            color: #ef4444;
        }
        
        .event-modal-body {
            padding: 1.5rem;
        }
        
        .event-detail-item {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            padding: 1rem;
            background-color: #f8fafc;
            border-radius: 0.75rem;
            border: 1px solid #e5e7eb;
        }
        
        .event-detail-icon {
            width: 48px;
            height: 48px;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .event-detail-icon.lecture {
            background-color: #eff6ff;
            color: #3b82f6;
        }
        
        .event-detail-icon.practical {
            background-color: #f0fdf4;
            color: #22c55e;
        }
        
        .event-detail-icon.tutorial {
            background-color: #fef3c7;
            color: #f59e0b;
        }
        
        .event-detail-icon.assessment {
            background-color: #fee2e2;
            color: #ef4444;
        }
        
        .event-detail-icon.exam {
            background-color: #fae8ff;
            color: #a855f7;
        }
        
        .event-detail-icon.holiday {
            background-color: #fee2e2;
            color: #dc2626;
        }
        
        .event-detail-icon.event {
            background-color: #fff7ed;
            color: #f97316;
        }
        
        .event-detail-info {
            flex: 1;
        }
        
        .event-detail-label {
            font-size: 0.7rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.25rem;
        }
        
        .event-detail-value {
            font-size: 1rem;
            color: #1e293b;
            font-weight: 500;
        }
        
        .event-modal-footer {
            padding: 1.5rem;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }
        
        .event-modal-btn {
            padding: 0.5rem 1.5rem;
            border-radius: 9999px;
            background-color: #3b82f6;
            color: white;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .event-modal-btn:hover {
            background-color: #2563eb;
        }
        
        .event-modal-btn.secondary {
            background-color: #f1f5f9;
            color: #475569;
        }
        
        .event-modal-btn.secondary:hover {
            background-color: #e2e8f0;
        }
        
        /* Quick Stats Cards */
        .stat-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border: 1px solid #e5e7eb;
            transition: all 0.2s;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }
        
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
            line-height: 1.2;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #64748b;
            margin-top: 0.25rem;
        }
        
        /* Print styles */
        @media print {
            .no-print {
                display: none;
            }
            .calendar-container {
                border: none;
                box-shadow: none;
            }
        }
        
        /* Loading states */
        .loading-spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3b82f6;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @media (max-width: 768px) {
            .timetable-grid {
                grid-template-columns: 80px repeat(5, 1fr);
                font-size: 0.75rem;
            }
            
            .timetable-cell {
                min-height: 80px;
                padding: 0.25rem;
            }
            
            .timetable-cell .module-name {
                display: none;
            }
            
            .timetable-header {
                padding: 0.5rem 0.25rem;
                font-size: 0.7rem;
            }
            
            .calendar-grid {
                grid-template-columns: repeat(7, 1fr);
            }
            
            .calendar-day {
                min-height: 100px;
                padding: 0.5rem;
            }
            
            .calendar-event {
                font-size: 0.6rem;
                padding: 0.1rem 0.2rem;
            }
            
            .day-number {
                font-size: 0.8rem;
                width: 24px;
                height: 24px;
                line-height: 24px;
            }
            
            .calendar-month {
                font-size: 1.2rem;
            }
            
            .calendar-year {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body <?php body_class('nds-portal-body'); ?>>
<?php function_exists('wp_body_open') && wp_body_open(); ?>
<?php

global $wpdb;

// Resolve current learner from logged-in user
$student_id = (int) nds_portal_get_current_student_id();

// Allow administrators to override student_id via query parameter to view any student's portal
if (current_user_can('manage_options') && isset($_GET['student_id'])) {
    $student_id = intval($_GET['student_id']);
}

if ($student_id <= 0) {
    if (current_user_can('manage_options')) {
        // Admin viewing their own (empty) portal or just landing here
        $full_name = 'Administrator';
        $learner_data = [];
        $enrollments = [];
        $status = 'admin';
        $is_applicant = false;
        $has_no_enrollments = true;
    } else {
        echo '<div class="nds-tailwind-wrapper bg-gray-50 py-16"><div class="max-w-3xl mx-auto bg-white shadow-sm rounded-xl p-8 text-center text-gray-700">We could not find a learner profile linked to your account. Please contact the school.</div></div>';
        return;
    }
} else {
    $learner = nds_get_student($student_id);
    if (!$learner) {
        if (current_user_can('manage_options')) {
            echo '<div class="nds-tailwind-wrapper bg-gray-50 py-16"><div class="max-w-3xl mx-auto bg-white shadow-sm rounded-xl p-8 text-center text-gray-700">Learner with ID ' . $student_id . ' not found.</div></div>';
            return;
        }
        echo '<div class="nds-tailwind-wrapper bg-gray-50 py-16"><div class="max-w-3xl mx-auto bg-white shadow-sm rounded-xl p-8 text-center text-gray-700">Your learner profile could not be loaded. Please contact the school.</div></div>';
        return;
    }

    $learner_data = (array) $learner;
    $full_name    = trim(($learner_data['first_name'] ?? '') . ' ' . ($learner_data['last_name'] ?? ''));
}

// Get enrolled modules for the student
$enrolled_modules = $wpdb->get_results(
    $wpdb->prepare(
        "
        SELECT sm.*, m.name as module_name, m.code as module_code, m.type as module_type, m.duration_hours,
               c.name as course_name, c.code as course_code,
               p.name as program_name,
               ay.year_name, s.semester_name
        FROM {$wpdb->prefix}nds_student_modules sm
        LEFT JOIN {$wpdb->prefix}nds_modules m ON sm.module_id = m.id
        LEFT JOIN {$wpdb->prefix}nds_courses c ON m.course_id = c.id
        LEFT JOIN {$wpdb->prefix}nds_programs p ON c.program_id = p.id
        LEFT JOIN {$wpdb->prefix}nds_academic_years ay ON sm.academic_year_id = ay.id
        LEFT JOIN {$wpdb->prefix}nds_semesters s ON sm.semester_id = s.id
        WHERE sm.student_id = %d AND sm.status = 'enrolled'
        ORDER BY m.name ASC
        ",
        $student_id
    ),
    ARRAY_A
);

// Get enrolled courses (qualifications) for the student
$enrolled_courses = $wpdb->get_results(
    $wpdb->prepare(
        "
        SELECT e.*, c.name as course_name, c.code as course_code,
               p.id as program_id, p.name as program_name,
               ay.year_name, s.semester_name
        FROM {$wpdb->prefix}nds_student_enrollments e
        LEFT JOIN {$wpdb->prefix}nds_courses c ON e.course_id = c.id
        LEFT JOIN {$wpdb->prefix}nds_programs p ON c.program_id = p.id
        LEFT JOIN {$wpdb->prefix}nds_academic_years ay ON e.academic_year_id = ay.id
        LEFT JOIN {$wpdb->prefix}nds_semesters s ON e.semester_id = s.id
        WHERE e.student_id = %d
        ORDER BY e.created_at DESC
        ",
        $student_id
    ),
    ARRAY_A
);

// Get timetable data for the student
$timetable_data = [];
$calendar_events = [];
if (!empty($enrolled_modules)) {
    // Get module IDs from enrolled modules
    $module_ids = array_column($enrolled_modules, 'module_id');
    $module_ids = array_filter($module_ids); // Remove empty values
    
    if (!empty($module_ids)) {
        $placeholders = implode(',', array_fill(0, count($module_ids), '%d'));
        
        // Get timetable entries for enrolled modules
        $timetable_data = $wpdb->get_results(
            $wpdb->prepare(
                "
                SELECT t.*,
                       COALESCE(m.name, c.name) as display_name,
                       COALESCE(m.code, c.code) as display_code,
                       m.name as module_name, m.code as module_code, m.duration_hours,
                       c.name as course_name, c.code as course_code,
                       r.name as venue_name, r.code as room_code, r.location as room_location,
                       u.display_name as lecturer_name
                FROM {$wpdb->prefix}nds_course_schedules t
                LEFT JOIN {$wpdb->prefix}nds_modules m ON t.module_id = m.id
                LEFT JOIN {$wpdb->prefix}nds_courses c ON t.course_id = c.id
                LEFT JOIN {$wpdb->prefix}nds_rooms r ON t.room_id = r.id
                LEFT JOIN {$wpdb->prefix}nds_staff s ON t.lecturer_id = s.id
                LEFT JOIN {$wpdb->prefix}users u ON s.user_id = u.ID
                WHERE t.module_id IN ($placeholders)
                AND t.is_active = 1
                ORDER BY t.days, t.start_time
                ",
                $module_ids
            ),
            ARRAY_A
        );
        
        // Generate calendar events from timetable
        foreach ($timetable_data as $class) {
            // Handle multiple days (days field contains comma-separated values like "Mon,Wed,Fri")
            $days = explode(',', $class['days']);
            foreach ($days as $day) {
                $day = trim($day);
                if (empty($day)) continue;

                // Convert day name to number (1=Monday, 7=Sunday)
                $day_numbers = [
                    'Mon' => 1, 'Tue' => 2, 'Wed' => 3, 'Thu' => 4, 'Fri' => 5, 'Sat' => 6, 'Sun' => 7
                ];
                $day_of_week = $day_numbers[$day] ?? 1;

                $calendar_events[] = [
                    'id' => $class['id'] . '_' . $day . '_' . ($class['module_code'] ?? 'mod'),
                    'title' => ($class['display_code'] ?? $class['course_code']) . ' - ' . ucfirst($class['session_type'] ?? 'lecture'),
                    'description' => ($class['display_name'] ?? $class['course_name']),
                    'type' => $class['session_type'] ?? 'lecture',
                    'start_time' => $class['start_time'],
                    'end_time' => $class['end_time'],
                    'venue' => ($class['venue_name'] ?? '') . ' ' . ($class['room_code'] ?? ''),
                    'lecturer' => $class['lecturer_name'] ?? '',
                    'day_of_week' => $day_of_week,
                    'is_recurring' => true,
                    'module_code' => $class['display_code'] ?? $class['course_code'],
                    'module_name' => $class['display_name'] ?? $class['course_name']
                ];
            }
        }
    }
}

// Get academic calendar events
$academic_events = $wpdb->get_results(
    "
    SELECT * FROM {$wpdb->prefix}nds_academic_calendar 
    WHERE YEAR(event_date) >= " . (date('Y') - 1) . "
    ORDER BY event_date ASC
    ",
    ARRAY_A
);

// Merge with academic events
foreach ($academic_events as $event) {
    $calendar_events[] = [
        'id' => 'academic_' . $event['id'],
        'title' => $event['title'],
        'description' => $event['description'] ?? '',
        'type' => $event['event_type'] ?? 'event',
        'start_time' => $event['start_time'] ?? '00:00',
        'end_time' => $event['end_time'] ?? '23:59',
        'venue' => $event['location'] ?? '',
        'date' => $event['event_date'],
        'is_recurring' => false
    ];
}

// Recent enrollments (for Overview tab)
$recent_enrollments = array_slice($enrolled_courses, 0, 5);

// Faculty
$faculty = null;
if (!empty($learner_data['faculty_id'])) {
    $faculty = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}nds_faculties WHERE id = %d",
            $learner_data['faculty_id']
        ),
        ARRAY_A
    );
}

// Average grade
$avg_grade = $wpdb->get_var(
    $wpdb->prepare(
        "SELECT AVG(final_percentage) FROM {$wpdb->prefix}nds_student_enrollments 
         WHERE student_id = %d AND final_percentage IS NOT NULL",
        $student_id
    )
);

// Certificates count
$certificates_count = $wpdb->get_var(
    $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}nds_certificates 
         WHERE student_id = %d AND status = 'issued'",
        $student_id
    )
);

// Latest application linked to this user (if any)
$latest_application = null;
$status = $learner_data['status'] ?? 'prospect';
$is_applicant = in_array($status, ['prospect', 'applicant'], true);
$has_no_enrollments = empty($enrolled_modules);
if ($is_applicant || ($status === 'active' && $has_no_enrollments)) {
    $latest_application = function_exists('nds_portal_get_latest_application_for_current_user')
        ? nds_portal_get_latest_application_for_current_user()
        : null;
}

// Learner-facing programme name
$display_program_name = '';
if (!empty($enrolled_courses)) {
    foreach ($enrolled_courses as $row) {
        if (!empty($row['program_name'])) {
            $display_program_name = $row['program_name'];
            break;
        }
    }
}
if (!$display_program_name && !empty($latest_application)) {
    if (!empty($latest_application['course_name'])) {
        $display_program_name = $latest_application['course_name'];
        if (!empty($latest_application['level'])) {
            $display_program_name .= ' (NQF ' . $latest_application['level'] . ')';
        }
    }
}

// Counts for quick stats
$enrolled_modules_count = count($enrolled_modules);
$applied_modules_count  = 0;
if ($is_applicant && !empty($latest_application)) {
    $applied_modules_count = 1;
}

// Current tab and view
$current_tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'overview';
$current_view = isset($_GET['view']) ? sanitize_text_field(wp_unslash($_GET['view'])) : 'weekly';
$valid_tabs  = $is_applicant
    ? array('overview')
    : array('overview', 'courses', 'timetable', 'finances', 'results', 'graduation', 'certificates', 'documents', 'activity');
if (!in_array($current_tab, $valid_tabs, true)) {
    $current_tab = 'overview';
}

// Helper to build tab URLs
function nds_learner_portal_tab_url($tab, $view = null)
{
    $base = home_url('/portal/');
    if ($tab === 'overview') {
        return $base;
    }
    $url = add_query_arg('tab', $tab, $base);
    if ($view) {
        $url = add_query_arg('view', $view, $url);
    }
    return $url;
}

// Fetch unread notifications
$unread_notifications = function_exists('nds_get_unread_notifications') ? nds_get_unread_notifications($student_id) : [];
$unread_count = count($unread_notifications);

// Get current week for timetable
$current_week = isset($_GET['week']) ? intval($_GET['week']) : date('W');
$current_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Get current month for calendar
$current_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$current_calendar_year = isset($_GET['cal_year']) ? intval($_GET['cal_year']) : date('Y');

// Days of the week
$days_of_week = [
    1 => 'Monday',
    2 => 'Tuesday', 
    3 => 'Wednesday',
    4 => 'Thursday',
    5 => 'Friday',
    6 => 'Saturday',
    7 => 'Sunday'
];

$days_of_week_short = [
    1 => 'Mon',
    2 => 'Tue',
        3 => 'Wed',
    4 => 'Thu',
    5 => 'Fri',
    6 => 'Sat',
    7 => 'Sun'
];

// Time slots
$time_slots = [
    '08:00' => '08:00 - 09:00',
    '09:00' => '09:00 - 10:00',
    '10:00' => '10:00 - 11:00',
    '11:00' => '11:00 - 12:00',
    '12:00' => '12:00 - 13:00',
    '13:00' => '13:00 - 14:00',
    '14:00' => '14:00 - 15:00',
    '15:00' => '15:00 - 16:00',
    '16:00' => '16:00 - 17:00'
];

// Calendar helper functions
function get_month_name($month) {
    $months = [
        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
    ];
    return $months[$month];
}

function get_calendar_days($month, $year) {
    $first_day = mktime(0, 0, 0, $month, 1, $year);
    $days_in_month = date('t', $first_day);
    $day_of_week = date('N', $first_day); // 1 (Monday) to 7 (Sunday)
    
    $days = [];
    
    // Add previous month's days
    $prev_month_days = $day_of_week - 1;
    if ($prev_month_days > 0) {
        $prev_month = $month - 1;
        $prev_year = $year;
        if ($prev_month < 1) {
            $prev_month = 12;
            $prev_year = $year - 1;
        }
        $days_in_prev_month = date('t', mktime(0, 0, 0, $prev_month, 1, $prev_year));
        
        for ($i = $prev_month_days; $i > 0; $i--) {
            $day_num = $days_in_prev_month - $i + 1;
            $days[] = [
                'day' => $day_num,
                'month' => $prev_month,
                'year' => $prev_year,
                'current_month' => false
            ];
        }
    }
    
    // Add current month's days
    for ($i = 1; $i <= $days_in_month; $i++) {
        $days[] = [
            'day' => $i,
            'month' => $month,
            'year' => $year,
            'current_month' => true
        ];
    }
    
    // Add next month's days to complete the grid (42 days total for 6 weeks)
    $total_days = count($days);
    $remaining_days = 42 - $total_days;
    
    if ($remaining_days > 0) {
        $next_month = $month + 1;
        $next_year = $year;
        if ($next_month > 12) {
            $next_month = 1;
            $next_year = $year + 1;
        }
        
        for ($i = 1; $i <= $remaining_days; $i++) {
            $days[] = [
                'day' => $i,
                'month' => $next_month,
                'year' => $next_year,
                'current_month' => false
            ];
        }
    }
    
    return $days;
}

function get_events_for_date($events, $day, $month, $year) {
    $day_events = [];
    $date_string = sprintf('%04d-%02d-%02d', $year, $month, $day);
    $day_of_week = date('N', strtotime($date_string));
    
    foreach ($events as $event) {
        if (isset($event['is_recurring']) && $event['is_recurring']) {
            // For recurring events, check if this date matches the day of week
            if ($event['day_of_week'] == $day_of_week) {
                $event_copy = $event;
                $event_copy['date'] = $date_string;
                $day_events[] = $event_copy;
            }
        } else {
            // For single events, check exact date
            if (isset($event['date']) && $event['date'] == $date_string) {
                $day_events[] = $event;
            }
        }
    }
    
    // Sort events by time
    usort($day_events, function($a, $b) {
        return strcmp($a['start_time'] ?? '00:00', $b['start_time'] ?? '00:00');
    });
    
    return $day_events;
}

// Get upcoming events
$upcoming_events = [];
$today = date('Y-m-d');
foreach ($calendar_events as $event) {
    if (isset($event['is_recurring']) && $event['is_recurring']) {
        // For recurring events, add next occurrence
        $upcoming_events[] = $event;
    } else {
        if (isset($event['date']) && $event['date'] >= $today) {
            $upcoming_events[] = $event;
        }
    }
}

// Sort upcoming events
usort($upcoming_events, function($a, $b) {
    if (isset($a['is_recurring']) && $a['is_recurring']) return -1;
    if (isset($b['is_recurring']) && $b['is_recurring']) return 1;
    return strcmp($a['date'] ?? '', $b['date'] ?? '');
});

$upcoming_events = array_slice($upcoming_events, 0, 10);

?>

<div class="nds-tailwind-wrapper bg-gray-50 min-h-screen nds-portal-offset" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
    <?php
    // Show success modal if redirected from application form
    $show_success_modal = isset($_GET['application'], $_GET['id'])
        && $_GET['application'] === 'success'
        && !empty($latest_application)
        && intval($_GET['id']) === intval($latest_application['id'] ?? 0);
    ?>
    <?php if ($show_success_modal && !empty($latest_application)) : ?>
        <div
            id="nds-app-success-modal"
            class="fixed inset-0 z-40 flex items-center justify-center px-4"
            style="background-color: rgba(15, 23, 42, 0.35); backdrop-filter: blur(6px);"
        >
            <div
                class="bg-white rounded-2xl shadow-2xl p-6 sm:p-7 md:p-8 border-2 border-blue-400"
                style="max-width: 640px; width: 100%; margin: 1.5rem auto;"
            >
                <div class=" items-center justify-between mb-4">
                    <h2 class="text-lg sm:text-xl font-semibold text-emerald-800 flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-emerald-100 text-emerald-700">
                            ✓
                        </span>
                        Application submitted successfully
                    </h2>
                </div>
                <div class="space-y-4 text-sm text-gray-800">
                    <div class="bg-emerald-50 border border-emerald-100 rounded-lg p-4">
                        <div class="text-xs font-semibold tracking-wide text-emerald-700 uppercase mb-1">Application details</div>
                        <p><span class="font-medium">Application number:</span>
                            <span class="font-mono text-emerald-900">
                                <?php echo esc_html($latest_application['application_no'] ?? ''); ?>
                            </span>
                        </p>
                        <p><span class="font-medium">Course:</span>
                            <?php echo esc_html($latest_application['course_name'] ?? ''); ?>
                            <?php if (!empty($latest_application['level'])) : ?>
                                (NQF <?php echo esc_html($latest_application['level']); ?>)
                            <?php endif; ?>
                        </p>
                        <p><span class="font-medium">Status:</span>
                            <?php
                            $status_label = isset($latest_application['status'])
                                ? str_replace('_', ' ', $latest_application['status'])
                                : 'submitted';
                            echo esc_html(ucfirst($status_label));
                            ?>
                        </p>
                    </div>
                    <p class="text-gray-700">
                        Your application has been received and is being reviewed. You will be contacted via email with
                        updates on your application status.
                    </p>
                    <p class="text-xs text-gray-500">
                        Note: Please keep your application number
                        <span class="font-mono">
                            <?php echo esc_html($latest_application['application_no'] ?? ''); ?>
                        </span>
                        for your records.
                    </p>
                </div>
                <div class="mt-6 flex justify-end">
                    <button
                        id="nds-app-success-close"
                        type="button"
                        class="inline-flex items-center justify-center px-4 py-2 rounded-full text-sm font-semibold leading-snug transition-colors"
                        style="background-color:#2563eb;color:#ffffff;"
                    >
                        Go to dashboard
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Profile Update Messages -->
    <?php if (isset($_GET['profile_success']) && $_GET['profile_success'] === 'updated'): ?>
        <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 mx-4 mt-4 flex items-center">
            <span class="dashicons dashicons-yes-alt text-emerald-600 mr-3 text-xl"></span>
            <div>
                <h3 class="text-sm font-semibold text-emerald-800">Profile Updated</h3>
                <p class="text-sm text-emerald-700">Your profile information has been successfully updated.</p>
            </div>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['profile_error'])): ?>
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mx-4 mt-4 flex items-center">
            <span class="dashicons dashicons-warning text-red-600 mr-3 text-xl"></span>
            <div>
                <h3 class="text-sm font-semibold text-red-800">Update Failed</h3>
                <p class="text-sm text-red-700">
                    <?php
                    switch ($_GET['profile_error']) {
                        case 'required_fields':
                            echo 'Please fill in all required fields.';
                            break;
                        case 'invalid_email':
                            echo 'Please enter a valid email address.';
                            break;
                        case 'email_taken':
                            echo 'This email address is already in use by another student.';
                            break;
                        case 'update_failed':
                            echo 'Failed to update profile. Please try again.';
                            break;
                        default:
                            echo 'An error occurred while updating your profile.';
                    }
                    ?>
                </p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Header -->
    <?php if (current_user_can('manage_options')) : ?>
        <div class="bg-amber-50 border-b border-amber-200 py-2 px-4 shadow-sm relative z-50 no-print">
            <div class="max-w-7xl mx-auto flex items-center justify-between text-amber-800 text-sm font-medium">
                <div class="flex items-center">
                    <i class="fas fa-user-shield mr-2"></i>
                    <span>Viewing as Administrator</span>
                    <?php if ($student_id > 0 && !empty($learner_data)): ?>
                        <span class="mx-2">•</span>
                        <span>Viewing profile: <strong><?php echo esc_html($full_name); ?></strong> (ID: <?php echo $student_id; ?>)</span>
                    <?php endif; ?>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="<?php echo admin_url('admin.php?page=nds-all-learners'); ?>" class="hover:underline">
                        Return to Admin Dashboard
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="bg-white shadow-sm border-b border-gray-200 no-print">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-user text-white text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">
                            <?php echo esc_html($full_name ?: 'Learner'); ?>
                        </h1>
                        <p class="text-sm text-gray-600 mt-1">
                            <?php if (!empty($learner_data['student_number'])) : ?>
                                Student #<?php echo esc_html($learner_data['student_number']); ?>
                            <?php endif; ?>
                            <?php if ($display_program_name) : ?>
                                <?php echo !empty($learner_data['student_number']) ? ' • ' : ''; ?>Programme: <?php echo esc_html($display_program_name); ?>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <!-- Notification Bell -->
                    <div class="relative mr-2" id="nds-notification-wrapper">
                        <button id="nds-notification-bell" class="relative p-2.5 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all duration-300 group">
                            <i class="fas fa-bell text-xl"></i>
                            <?php if ($unread_count > 0) : ?>
                                <span id="nds-notification-badge" class="absolute top-0 right-0 flex h-4 w-4 translate-x-1/3 -translate-y-1/3">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                    <span class="relative inline-flex items-center justify-center rounded-full h-4 w-4 bg-red-600 text-[9px] font-bold text-white shadow-sm ring-1 ring-white">
                                        <?php echo $unread_count; ?>
                                    </span>
                                </span>
                            <?php endif; ?>
                        </button>

                        <!-- Notification Dropdown -->
                        <div id="nds-notification-dropdown" class="hidden absolute right-0 mt-4 w-96 bg-white rounded-3xl shadow-[0_20px_60px_-15px_rgba(0,0,0,0.25)] border-2 border-blue-400 z-[100000] transform origin-top-right transition-all duration-300 ring-1 ring-black/5">
                            <div class="p-6 border-b border-slate-50 flex justify-between items-center bg-slate-50/40 rounded-t-3xl">
                                <div class="space-y-0.5">
                                    <h3 class="text-xl font-bold text-slate-900">Notifications</h3>
                                    <?php if ($unread_count > 0) : ?>
                                        <p class="text-sm text-slate-500 font-medium">You have <?php echo $unread_count; ?> message<?php echo $unread_count > 1 ? 's' : ''; ?></p>
                                    <?php endif; ?>
                                </div>
                                <?php if ($unread_count > 0) : ?>
                                    <button id="nds-mark-all-read" class="text-xs text-blue-600 hover:text-blue-700 font-bold px-3 py-2 bg-white border border-blue-100 rounded-xl transition-all hover:shadow-sm">Mark all read</button>
                                <?php endif; ?>
                            </div>

                            <div class="max-h-[400px] overflow-y-auto" id="nds-notification-list">
                                <?php if ($unread_count > 0) : ?>
                                    <?php foreach ($unread_notifications as $notif) : 
                                        $type = $notif['type'] ?: 'info';
                                        $icon = 'fa-info-circle text-blue-500 bg-blue-50/50 border-blue-100';
                                        if ($type === 'timetable' || $type === 'calendar') {
                                            $icon = 'fa-calendar-alt text-indigo-500 bg-indigo-50/50 border-indigo-100';
                                        } elseif ($type === 'warning') {
                                            $icon = 'fa-exclamation-triangle text-amber-500 bg-amber-50/50 border-amber-100';
                                        } elseif ($type === 'success') {
                                            $icon = 'fa-check-circle text-emerald-500 bg-emerald-50/50 border-emerald-100';
                                        } elseif ($type === 'error') {
                                            $icon = 'fa-times-circle text-rose-500 bg-rose-50/50 border-rose-100';
                                        }
                                    ?>
                                        <div class="p-6 border-b border-slate-50 hover:bg-slate-50/80 transition-all relative group cursor-pointer" data-id="<?php echo $notif['id']; ?>" data-type="<?php echo $type; ?>">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0 border <?php echo $icon; ?> group-hover:scale-105 transition-transform duration-300 shadow-sm">
                                                    <i class="fas <?php echo explode(' ', $icon)[0]; ?> text-lg"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex justify-between items-start mb-1.5">
                                                        <p class="text-base font-bold text-slate-900 leading-none truncate pr-8"><?php echo esc_html($notif['title']); ?></p>
                                                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider whitespace-nowrap"><?php echo human_time_diff(strtotime($notif['created_at']), current_time('timestamp')); ?></span>
                                                    </div>
                                                    <p class="text-sm text-slate-500 leading-relaxed font-medium line-clamp-2"><?php echo wp_kses_post($notif['message']); ?></p>
                                                </div>
                                            </div>
                                            <button class="nds-mark-read absolute top-6 right-6 w-8 h-8 flex items-center justify-center rounded-xl text-slate-300 hover:text-blue-600 hover:bg-white hover:shadow-md opacity-0 group-hover:opacity-100 transition-all border border-transparent hover:border-slate-100" title="Mark as read">
                                                <i class="fas fa-check text-xs"></i>
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <div class="p-12 text-center">
                                        <div class="w-20 h-20 bg-gray-50 rounded-3xl flex items-center justify-center mx-auto mb-5 shadow-inner">
                                            <i class="fas fa-bell-slash text-gray-300 text-3xl"></i>
                                        </div>
                                        <h4 class="text-base font-bold text-gray-800">All caught up!</h4>
                                        <p class="text-sm text-gray-500 mt-2">No new notifications for you right now.</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="p-4 text-center border-t border-gray-50 bg-gray-50/30 rounded-b-2xl">
                                <a href="<?php echo esc_url(nds_learner_portal_tab_url('activity')); ?>" class="group inline-flex items-center text-xs font-bold text-gray-500 hover:text-blue-600 transition-colors">
                                    <span>View all portal activity</span>
                                    <i class="fas fa-chevron-right ml-1.5 transform group-hover:translate-x-1 transition-transform"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <a href="<?php echo esc_url(home_url('/')); ?>"
                       class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium shadow-sm transition-all duration-200">
                        <i class="fas fa-globe mr-2"></i>
                        Go to website
                    </a>
                    <a href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>"
                       class="inline-flex items-center px-4 py-2 rounded-lg bg-red-50 hover:bg-red-100 text-red-700 text-sm font-medium shadow-sm transition-all duration-200">
                        <i class="fas fa-sign-out-alt mr-2"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Quick Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6 no-print">
            <!-- First Stat Card -->
            <div class="stat-card">
                <div class="stat-icon bg-blue-50 text-blue-600">
                    <i class="fas fa-book text-2xl"></i>
                </div>
                <div class="stat-value"><?php echo $is_applicant ? $applied_modules_count : $enrolled_modules_count; ?></div>
                <div class="stat-label"><?php echo $is_applicant ? 'Applied Modules' : 'Enrolled Modules'; ?></div>
            </div>

            <!-- Second Stat Card -->
            <div class="stat-card">
                <div class="stat-icon bg-emerald-50 text-emerald-600">
                    <i class="fas fa-user-check text-2xl"></i>
                </div>
                <div class="stat-value">
                    <span class="px-3 py-1 rounded-full text-sm font-medium
                        <?php
                        echo $status === 'active'
                            ? 'bg-green-100 text-green-800'
                            : ($status === 'prospect'
                                ? 'bg-yellow-100 text-yellow-800'
                                : 'bg-gray-100 text-gray-800');
                        ?>">
                        <?php echo esc_html(ucfirst($status)); ?>
                    </span>
                </div>
                <div class="stat-label">Current Status</div>
            </div>

            <!-- Third Stat Card -->
            <div class="stat-card">
                <div class="stat-icon bg-purple-50 text-purple-600">
                    <i class="fas fa-chart-line text-2xl"></i>
                </div>
                <div class="stat-value"><?php echo $avg_grade ? number_format((float) $avg_grade, 1) . '%' : 'N/A'; ?></div>
                <div class="stat-label">Average Grade</div>
            </div>

            <!-- Fourth Stat Card -->
            <div class="stat-card">
                <div class="stat-icon bg-amber-50 text-amber-600">
                    <i class="fas fa-certificate text-2xl"></i>
                </div>
                <div class="stat-value"><?php echo $certificates_count; ?></div>
                <div class="stat-label">Certificates Earned</div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-100 mb-6">
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px overflow-x-auto" aria-label="Tabs">
                    <?php
                    $tabs = array(
                        'overview'     => array('icon' => 'fa-home', 'label' => 'Overview'),
                        'courses'      => array('icon' => 'fa-book', 'label' => 'Modules'),
                        'timetable'    => array('icon' => 'fa-calendar-alt', 'label' => 'Schedule'),
                        'finances'     => array('icon' => 'fa-dollar-sign', 'label' => 'Finances'),
                        'results'      => array('icon' => 'fa-chart-bar', 'label' => 'Results'),
                        'graduation'   => array('icon' => 'fa-graduation-cap', 'label' => 'Graduation'),
                        'certificates' => array('icon' => 'fa-certificate', 'label' => 'Certificates'),
                        'documents'    => array('icon' => 'fa-file', 'label' => 'Documents'),
                        'activity'     => array('icon' => 'fa-history', 'label' => 'Activity'),
                    );

                    // Applicants only see overview
                    if ($is_applicant) {
                        $tabs = array(
                            'overview' => $tabs['overview'],
                        );
                    }

                    foreach ($tabs as $tab_key => $tab_info) :
                        $is_active = ($current_tab === $tab_key);
                        $url       = nds_learner_portal_tab_url($tab_key);
                        ?>
                        <a href="<?php echo esc_url($url); ?>"
                           class="<?php echo $is_active
                               ? 'border-blue-500 text-blue-600'
                               : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>
                               whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm flex items-center space-x-2 transition-colors">
                            <i class="fas <?php echo esc_attr($tab_info['icon']); ?>"></i>
                            <span><?php echo esc_html($tab_info['label']); ?></span>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="p-6">
                <?php
                // Set learner_id for partials
                $_GET['id'] = $student_id;
                
                switch ($current_tab) {
                    case 'overview':
                        ?>
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                            <!-- Main Content Column -->
                            <div class="lg:col-span-2 space-y-6">
                                <!-- Personal Information -->
                                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <h2 class="text-xl font-semibold text-gray-900">Personal Information</h2>
                                        <button id="editProfileBtn" class="inline-flex items-center px-3 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition-colors">
                                            <i class="fas fa-edit mr-2"></i>
                                            Edit Details
                                        </button>
                                    </div>

                                    <!-- View Mode -->
                                    <div id="profileViewMode" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="text-sm font-medium text-gray-500">Full Name</label>
                                            <p class="mt-1 text-sm text-gray-900"><?php echo esc_html($full_name); ?></p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-500">Student Number</label>
                                            <p class="mt-1 text-sm text-gray-900"><?php echo esc_html($learner_data['student_number'] ?? 'N/A'); ?></p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-500">Email</label>
                                            <p class="mt-1 text-sm text-gray-900"><?php echo esc_html($learner_data['email'] ?? 'N/A'); ?></p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-500">Phone</label>
                                            <p class="mt-1 text-sm text-gray-900"><?php echo esc_html($learner_data['phone'] ?? 'N/A'); ?></p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-500">Date of Birth</label>
                                            <p class="mt-1 text-sm text-gray-900"><?php echo !empty($learner_data['date_of_birth']) ? esc_html(date('F j, Y', strtotime($learner_data['date_of_birth']))) : 'N/A'; ?></p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-500">Gender</label>
                                            <p class="mt-1 text-sm text-gray-900"><?php echo esc_html(ucfirst($learner_data['gender'] ?? 'N/A')); ?></p>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="text-sm font-medium text-gray-500">Address</label>
                                            <p class="mt-1 text-sm text-gray-900">
                                                <?php
                                                $address_parts = array_filter([
                                                    $learner_data['address'] ?? '',
                                                    $learner_data['city'] ?? '',
                                                    $learner_data['country'] ?? 'South Africa'
                                                ]);
                                                echo !empty($address_parts) ? esc_html(implode(', ', $address_parts)) : 'N/A';
                                                ?>
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Edit Mode -->
                                    <form id="profileEditForm" method="POST" action="<?php echo admin_url('admin-post.php'); ?>" class="hidden grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <?php wp_nonce_field('nds_update_student_profile', 'nds_profile_nonce'); ?>
                                        <input type="hidden" name="action" value="nds_update_student_profile">

                                        <div>
                                            <label for="first_name" class="text-sm font-medium text-gray-500">First Name *</label>
                                            <input type="text" id="first_name" name="first_name" value="<?php echo esc_attr($learner_data['first_name'] ?? ''); ?>" required
                                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        <div>
                                            <label for="last_name" class="text-sm font-medium text-gray-500">Last Name *</label>
                                            <input type="text" id="last_name" name="last_name" value="<?php echo esc_attr($learner_data['last_name'] ?? ''); ?>" required
                                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        <div>
                                            <label for="email" class="text-sm font-medium text-gray-500">Email *</label>
                                            <input type="email" id="email" name="email" value="<?php echo esc_attr($learner_data['email'] ?? ''); ?>" required
                                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        <div>
                                            <label for="phone" class="text-sm font-medium text-gray-500">Phone</label>
                                            <input type="tel" id="phone" name="phone" value="<?php echo esc_attr($learner_data['phone'] ?? ''); ?>"
                                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        <div>
                                            <label for="date_of_birth" class="text-sm font-medium text-gray-500">Date of Birth</label>
                                            <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo esc_attr($learner_data['date_of_birth'] ?? ''); ?>"
                                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        <div>
                                            <label for="gender" class="text-sm font-medium text-gray-500">Gender</label>
                                            <select id="gender" name="gender"
                                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                                <option value="">Select Gender</option>
                                                <option value="male" <?php selected($learner_data['gender'] ?? '', 'male'); ?>>Male</option>
                                                <option value="female" <?php selected($learner_data['gender'] ?? '', 'female'); ?>>Female</option>
                                                <option value="other" <?php selected($learner_data['gender'] ?? '', 'other'); ?>>Other</option>
                                            </select>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label for="address" class="text-sm font-medium text-gray-500">Address</label>
                                            <input type="text" id="address" name="address" value="<?php echo esc_attr($learner_data['address'] ?? ''); ?>" placeholder="Street address"
                                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        <div>
                                            <label for="city" class="text-sm font-medium text-gray-500">City</label>
                                            <input type="text" id="city" name="city" value="<?php echo esc_attr($learner_data['city'] ?? ''); ?>"
                                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        <div>
                                            <label for="country" class="text-sm font-medium text-gray-500">Country</label>
                                            <input type="text" id="country" name="country" value="<?php echo esc_attr($learner_data['country'] ?? 'South Africa'); ?>"
                                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        </div>

                                        <div class="md:col-span-2 flex justify-end space-x-3 pt-4">
                                            <button type="button" id="cancelEditBtn" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                                Cancel
                                            </button>
                                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">
                                                Save Changes
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Enrolled Qualifications -->
                                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Enrolled Qualifications</h2>
                                    <?php if (!empty($enrolled_courses)): ?>
                                        <div class="space-y-4">
                                            <?php foreach ($enrolled_courses as $enrollment): ?>
                                                <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                                                    <div class="flex items-center justify-between mb-2">
                                                        <h3 class="text-lg font-semibold text-gray-900">
                                                            <?php echo esc_html($enrollment['course_name'] ?? 'Course'); ?>
                                                        </h3>
                                                        <span class="px-2 py-1 text-xs font-medium rounded-full
                                                            <?php
                                                            $status = $enrollment['status'] ?? 'active';
                                                            echo $status === 'active' ? 'bg-green-100 text-green-800' :
                                                                 ($status === 'completed' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800');
                                                            ?>">
                                                            <?php echo esc_html(ucfirst($status)); ?>
                                                        </span>
                                                    </div>
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                                                        <div>
                                                            <span class="font-medium">Program:</span> <?php echo esc_html($enrollment['program_name'] ?? 'N/A'); ?>
                                                        </div>
                                                        <div>
                                                            <span class="font-medium">Course Code:</span> <?php echo esc_html($enrollment['course_code'] ?? 'N/A'); ?>
                                                        </div>
                                                        <div>
                                                            <span class="font-medium">Academic Year:</span> <?php echo esc_html($enrollment['year_name'] ?? 'N/A'); ?>
                                                        </div>
                                                        <div>
                                                            <span class="font-medium">Semester:</span> <?php echo esc_html($enrollment['semester_name'] ?? 'N/A'); ?>
                                                        </div>
                                                        <div>
                                                            <span class="font-medium">Enrolled:</span> <?php echo esc_html(date('M j, Y', strtotime($enrollment['created_at']))); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-gray-500 text-sm">No qualifications enrolled yet.</p>
                                    <?php endif; ?>
                                </div>

                                <!-- Upcoming Events Preview (Moved here) -->
                                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <h2 class="text-xl font-semibold text-gray-900">Upcoming Events</h2>
                                        <a href="<?php echo esc_url(nds_learner_portal_tab_url('timetable')); ?>"
                                           class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                                            View Full Schedule
                                        </a>
                                    </div>
                                    <?php if (!empty($upcoming_events)): ?>
                                        <div class="space-y-3">
                                            <?php foreach ($upcoming_events as $event): ?>
                                                <div class="flex items-center p-3 bg-gray-50 rounded-lg border border-gray-100 hover:border-blue-200 transition-all cursor-pointer" onclick="showEventDetails(<?php echo htmlspecialchars(json_encode($event)); ?>)">
                                                    <!-- ... event icon logic ... -->
                                                    <div class="w-12 h-12 rounded-lg flex items-center justify-center
                                                        <?php
                                                        $type = $event['type'] ?? 'event';
                                                        switch($type) {
                                                            case 'lecture': echo 'bg-blue-100 text-blue-600'; break;
                                                            case 'practical': echo 'bg-green-100 text-green-600'; break;
                                                            case 'tutorial': echo 'bg-amber-100 text-amber-600'; break;
                                                            case 'assessment': echo 'bg-red-100 text-red-600'; break;
                                                            case 'exam': echo 'bg-purple-100 text-purple-600'; break;
                                                            case 'holiday': echo 'bg-red-100 text-red-600'; break;
                                                            default: echo 'bg-gray-100 text-gray-600';
                                                        }
                                                        ?>">
                                                        <i class="fas 
                                                            <?php
                                                            switch($type) {
                                                                case 'lecture': echo 'fa-chalkboard-teacher'; break;
                                                                case 'practical': echo 'fa-flask'; break;
                                                                case 'tutorial': echo 'fa-users'; break;
                                                                case 'assessment': echo 'fa-pencil-alt'; break;
                                                                case 'exam': echo 'fa-graduation-cap'; break;
                                                                case 'holiday': echo 'fa-umbrella-beach'; break;
                                                                default: echo 'fa-calendar-alt';
                                                            }
                                                            ?>"></i>
                                                    </div>
                                                    <div class="flex-1 ml-3">
                                                        <h4 class="font-medium text-gray-900"><?php echo esc_html($event['title']); ?></h4>
                                                        <p class="text-xs text-gray-500">
                                                            <?php
                                                            if (isset($event['is_recurring']) && $event['is_recurring']) {
                                                                echo 'Every ' . $days_of_week[$event['day_of_week']] . ' at ' . substr($event['start_time'], 0, 5);
                                                            } else {
                                                                echo date('F j, Y', strtotime($event['date'])) . ' at ' . substr($event['start_time'], 0, 5);
                                                            }
                                                            ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-gray-500 text-sm">No upcoming events scheduled.</p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Sidebar Column -->
                            <div class="lg:col-span-1 space-y-6">
                                <?php if (!empty($latest_application)) : ?>
                                    <!-- Application Status Card -->
                                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                        <div class="flex items-center justify-between mb-4">
                                            <h2 class="text-lg font-semibold text-gray-900">Application</h2>
                                            <span class="px-2 py-1 bg-emerald-100 text-emerald-800 text-xs font-medium rounded-full">
                                                Active
                                            </span>
                                        </div>
                                        <div class="bg-emerald-50 rounded-lg p-4">
                                           <!-- ... app details ... -->
                                            <div class="text-xs font-semibold tracking-wide text-emerald-700 uppercase">Status</div>
                                            <div class="mt-1 text-lg font-semibold text-emerald-900">
                                                <?php echo esc_html(ucfirst(str_replace('_', ' ', $latest_application['status'] ?? 'submitted'))); ?>
                                            </div>
                                            <div class="mt-2 text-sm text-emerald-800">
                                                <?php echo esc_html($latest_application['course_name'] ?? ''); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Enrolled Modules (Moved to Sidebar) -->
                                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <h2 class="text-lg font-semibold text-gray-900">My Modules</h2>
                                        <a href="<?php echo esc_url(nds_learner_portal_tab_url('courses')); ?>" class="text-sm text-blue-600 hover:text-blue-700 font-medium">View All</a>
                                    </div>
                                    <?php if (!empty($enrolled_modules)): ?>
                                        <div class="space-y-4">
                                            <?php
                                            $recent_modules = array_slice($enrolled_modules, 0, 5);
                                            foreach ($recent_modules as $module):
                                            ?>
                                                <div class="p-3 bg-gray-50 rounded-lg border border-gray-100 hover:border-blue-200 transition-colors group">
                                                    <div class="flex justify-between items-start mb-2">
                                                        <div class="font-medium text-gray-900 text-sm leading-tight">
                                                            <?php echo esc_html($module['module_name'] ?? 'Module'); ?>
                                                        </div>
                                                        <span class="ml-2 px-1.5 py-0.5 text-[10px] font-medium rounded-full bg-blue-100 text-blue-800">
                                                            <?php echo esc_html($module['module_code'] ?? ''); ?>
                                                        </span>
                                                    </div>
                                                    <div class="text-xs text-gray-500 mb-2">
                                                        <?php echo esc_html($module['course_name'] ?? ''); ?>
                                                    </div>
                                                    
                                                    <?php if (!empty($module['final_percentage'])): ?>
                                                        <div class="mt-2">
                                                            <div class="flex justify-between text-xs text-gray-500 mb-1">
                                                                <span>Progress</span>
                                                                <span class="font-medium text-gray-700"><?php echo number_format($module['final_percentage'], 1); ?>%</span>
                                                            </div>
                                                            <div class="w-full bg-gray-200 rounded-full h-1.5 overflow-hidden">
                                                                <div class="bg-blue-600 h-1.5 rounded-full transition-all duration-500" style="width: <?php echo $module['final_percentage']; ?>%"></div>
                                                            </div>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="mt-2 flex items-center text-xs text-gray-500">
                                                            <i class="fas fa-clock mr-1"></i> In Progress
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-gray-500 text-sm">No modules enrolled yet.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        
                        </div>
                        <?php
                        break;

                    case 'courses':
                        // Group modules by program
                        $modules_by_program = [];
                        foreach ($enrolled_modules as $row) {
                            $pid   = $row['program_id'] ?? 0;
                            $pname = $row['program_name'] ?? __('Unassigned Program', 'nds-school');
                            if (!isset($modules_by_program[$pid])) {
                                $modules_by_program[$pid] = [
                                    'name'    => $pname,
                                    'rows'    => [],
                                ];
                            }
                            $modules_by_program[$pid]['rows'][] = $row;
                        }
                        ?>
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Programme Modules</h2>
                        <?php if (empty($modules_by_program)) : ?>
                            <p class="text-sm text-gray-600">You are not enrolled in any modules yet.</p>
                        <?php else : ?>
                            <div class="space-y-6">
                                <?php foreach ($modules_by_program as $program): ?>
                                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                        <h3 class="text-md font-semibold text-gray-900 mb-3">
                                            <?php echo esc_html($program['name']); ?>
                                        </h3>
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                                <thead class="bg-gray-100">
                                                <tr>
                                                    <th class="px-3 py-2 text-left font-medium text-gray-700">Module</th>
                                                    <th class="px-3 py-2 text-left font-medium text-gray-700">Code</th>
                                                    <th class="px-3 py-2 text-left font-medium text-gray-700">Type</th>
                                                    <th class="px-3 py-2 text-left font-medium text-gray-700">Year / Semester</th>
                                                    <th class="px-3 py-2 text-left font-medium text-gray-700">Status</th>
                                                    <th class="px-3 py-2 text-left font-medium text-gray-700">Progress</th>
                                                </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-100">
                                                <?php foreach ($program['rows'] as $row): ?>
                                                    <tr>
                                                        <td class="px-3 py-2 text-gray-800">
                                                            <?php echo esc_html($row['module_name'] ?? 'Module'); ?>
                                                        </td>
                                                        <td class="px-3 py-2 text-gray-700">
                                                            <?php echo esc_html($row['module_code'] ?? ''); ?>
                                                        </td>
                                                        <td class="px-3 py-2 text-gray-700">
                                                            <?php echo esc_html(ucfirst($row['module_type'] ?? 'theory')); ?>
                                                        </td>
                                                        <td class="px-3 py-2 text-gray-700">
                                                            <?php
                                                            $year  = $row['year_name'] ?? '';
                                                            $sem   = $row['semester_name'] ?? '';
                                                            $label = trim($year . ' ' . $sem);
                                                            echo esc_html($label ?: '—');
                                                            ?>
                                                        </td>
                                                        <td class="px-3 py-2">
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                                <?php
                                                                $status = $row['status'] ?? 'enrolled';
                                                                echo $status === 'enrolled' ? 'bg-green-100 text-green-800' : 
                                                                     ($status === 'completed' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800');
                                                                ?>">
                                                                <?php echo esc_html(ucfirst($status)); ?>
                                                            </span>
                                                        </td>
                                                        <td class="px-3 py-2">
                                                            <?php
                                                            if (!empty($row['final_percentage'])) {
                                                                $percentage = floatval($row['final_percentage']);
                                                                ?>
                                                                <div class="flex items-center gap-2">
                                                                    <span class="text-gray-800"><?php echo number_format($percentage, 1); ?>%</span>
                                                                    <div class="w-16 bg-gray-200 rounded-full h-1.5">
                                                                        <div class="bg-blue-600 h-1.5 rounded-full" style="width: <?php echo $percentage; ?>%"></div>
                                                                    </div>
                                                                </div>
                                                            <?php
                                                            } else {
                                                                echo '—';
                                                            }
                                                            ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php
                        endif;
                        break;

                    case 'timetable':
                        ?>
                        <!-- View Toggle -->
                        <div class="view-toggle mb-4 no-print">
                            <a href="<?php echo esc_url(nds_learner_portal_tab_url('timetable', 'weekly')); ?>" 
                               class="view-toggle-btn <?php echo $current_view == 'weekly' ? 'active' : ''; ?>">
                                <i class="fas fa-calendar-week mr-2"></i>
                                Weekly View
                            </a>
                            <a href="<?php echo esc_url(nds_learner_portal_tab_url('timetable', 'monthly')); ?>" 
                               class="view-toggle-btn <?php echo $current_view == 'monthly' ? 'active' : ''; ?>">
                                <i class="fas fa-calendar-alt mr-2"></i>
                                Monthly Calendar
                            </a>
                            <button onclick="window.print()" class="view-toggle-btn ml-auto">
                                <i class="fas fa-print mr-2"></i>
                                Print
                            </button>
                        </div>

                        <?php if ($current_view == 'weekly'): ?>
                            <!-- Weekly Timetable View -->
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                <!-- Timetable -->
                                <div class="lg:col-span-2">
                                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                        <!-- Header with week navigation -->
                                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4 no-print">
                                            <div>
                                                <h2 class="text-xl font-semibold text-gray-900 flex items-center gap-2">
                                                    <i class="fas fa-calendar-alt text-blue-600"></i>
                                                    Weekly Timetable
                                                </h2>
                                                <p class="text-sm text-gray-500 mt-1">
                                                    Week <?php echo $current_week; ?> (<?php echo date('d M Y', strtotime($current_year . 'W' . str_pad($current_week, 2, '0', STR_PAD_LEFT))); ?>)
                                                </p>
                                            </div>
                                            
                                            <div class="week-navigation">
                                                <a href="<?php echo add_query_arg(['week' => max(1, $current_week - 1), 'year' => $current_week > 1 ? $current_year : $current_year - 1, 'view' => 'weekly']); ?>" 
                                                   class="week-nav-button <?php echo $current_week <= 1 ? 'opacity-50 pointer-events-none' : ''; ?>">
                                                    <i class="fas fa-chevron-left"></i>
                                                </a>
                                                
                                                <span class="current-week-badge">
                                                    <i class="fas fa-clock"></i>
                                                    Current Week
                                                </span>
                                                
                                                <a href="<?php echo add_query_arg(['week' => min(52, $current_week + 1), 'year' => $current_week < 52 ? $current_year : $current_year + 1, 'view' => 'weekly']); ?>" 
                                                   class="week-nav-button <?php echo $current_week >= 52 ? 'opacity-50 pointer-events-none' : ''; ?>">
                                                    <i class="fas fa-chevron-right"></i>
                                                </a>
                                            </div>
                                        </div>

                                        <?php if (!empty($timetable_data)): ?>
                                            <!-- Timetable Grid -->
                                            <div class="timetable-grid mb-4">
                                                <!-- Empty corner cell -->
                                                <div class="timetable-header bg-gray-100"></div>
                                                
                                                <!-- Day headers -->
                                                <?php foreach ($days_of_week as $day_num => $day_name): if ($day_num <= 5): ?>
                                                    <div class="timetable-header">
                                                        <div class="font-bold"><?php echo esc_html($day_name); ?></div>
                                                        <span class="text-xs font-normal text-gray-500">
                                                            <?php 
                                                            $day_date = date('d M', strtotime($current_year . 'W' . str_pad($current_week, 2, '0', STR_PAD_LEFT) . $day_num));
                                                            echo $day_date;
                                                            ?>
                                                        </span>
                                                    </div>
                                                <?php endif; endforeach; ?>

                                                <!-- Time slots -->
                                                <?php foreach ($time_slots as $time => $time_label): ?>
                                                    <!-- Time column -->
                                                    <div class="timetable-time-slot">
                                                        <?php echo esc_html($time_label); ?>
                                                    </div>
                                                    
                                                    <!-- Day columns -->
                                                    <?php foreach ($days_of_week as $day_num => $day_name): if ($day_num <= 5): ?>
                                                        <div class="timetable-cell">
                                                            <?php
                                                            $classes_at_time = array_filter($timetable_data, function($class) use ($day_num, $time) {
                                                                // Check if this class occurs on the current day
                                                                $class_days = explode(',', $class['days']);
                                                                $day_names = ['Mon' => 1, 'Tue' => 2, 'Wed' => 3, 'Thu' => 4, 'Fri' => 5, 'Sat' => 6, 'Sun' => 7];
                                                                $current_day_name = array_search($day_num, $day_names);
                                                                
                                                                $class_occurs_today = false;
                                                                foreach ($class_days as $day) {
                                                                    $day = trim($day);
                                                                    if (isset($day_names[$day]) && $day_names[$day] == $day_num) {
                                                                        $class_occurs_today = true;
                                                                        break;
                                                                    }
                                                                }
                                                                
                                                                return $class_occurs_today && substr($class['start_time'], 0, 5) == $time;
                                                            });
                                                            
                                                            foreach ($classes_at_time as $class):
                                                                $class_type = $class['session_type'] ?? 'lecture';
                                                                $type_class = '';
                                                                $type_icon = '';
                                                                
                                                                switch($class_type) {
                                                                    case 'practical':
                                                                        $type_class = 'has-class practical';
                                                                        $type_icon = 'fa-flask';
                                                                        break;
                                                                    case 'tutorial':
                                                                        $type_class = 'has-class tutorial';
                                                                        $type_icon = 'fa-users';
                                                                        break;
                                                                    case 'assessment':
                                                                        $type_class = 'has-class assessment';
                                                                        $type_icon = 'fa-pencil-alt';
                                                                        break;
                                                                    default:
                                                                        $type_class = 'has-class lecture';
                                                                        $type_icon = 'fa-chalkboard-teacher';
                                                                }
                                                            ?>
                                                                <div class="<?php echo $type_class; ?>" onclick="showEventDetails(<?php echo htmlspecialchars(json_encode($class)); ?>)">
                                                                    <span class="module-code">
                                                                        <?php echo esc_html($class['display_code'] ?? $class['course_code']); ?>
                                                                    </span>
                                                                    <span class="module-name">
                                                                        <?php echo esc_html($class['display_name'] ?? $class['course_name']); ?>
                                                                    </span>
                                                                    <span class="venue">
                                                                        <i class="fas <?php echo $type_icon; ?>"></i>
                                                                        <?php 
                                                                        $venue = '';
                                                                        if (!empty($class['venue_name'])) {
                                                                            $venue .= $class['venue_name'];
                                                                        }
                                                                        if (!empty($class['room_code'])) {
                                                                            $venue .= ' (' . $class['room_code'] . ')';
                                                                        }
                                                                        echo esc_html($venue ?: 'TBC');
                                                                        ?>
                                                                    </span>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php endif; endforeach; ?>
                                                <?php endforeach; ?>
                                            </div>

                                            <!-- Legend -->
                                            <div class="timetable-legend no-print">
                                                <div class="timetable-legend-item">
                                                    <div class="timetable-legend-color lecture"></div>
                                                    <span>Lecture</span>
                                                </div>
                                                <div class="timetable-legend-item">
                                                    <div class="timetable-legend-color practical"></div>
                                                    <span>Practical</span>
                                                </div>
                                                <div class="timetable-legend-item">
                                                    <div class="timetable-legend-color tutorial"></div>
                                                    <span>Tutorial</span>
                                                </div>
                                                <div class="timetable-legend-item">
                                                    <div class="timetable-legend-color assessment"></div>
                                                    <span>Assessment</span>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-center py-12">
                                                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                                    <i class="fas fa-calendar-times text-gray-400 text-3xl"></i>
                                                </div>
                                                <h3 class="text-lg font-medium text-gray-900 mb-2">No Timetable Available</h3>
                                                <p class="text-gray-500 max-w-md mx-auto">
                                                    Your timetable is currently being prepared. Please check back later.
                                                </p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- Enrolled Modules Sidebar -->
                                <div class="lg:col-span-1">
                                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 sticky top-6">
                                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                            <i class="fas fa-book-open text-blue-600 mr-2"></i>
                                            My Modules
                                        </h3>
                                        
                                        <?php if (!empty($enrolled_modules)): ?>
                                            <div class="space-y-3 max-h-[600px] overflow-y-auto pr-2">
                                                <?php foreach ($enrolled_modules as $module): ?>
                                                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-100 hover:border-blue-200 transition-colors">
                                                        <div class="font-medium text-gray-900 text-sm">
                                                            <?php echo esc_html($module['module_name'] ?? 'Module'); ?>
                                                        </div>
                                                        <div class="text-xs text-gray-500 mt-1">
                                                            <?php echo esc_html($module['module_code'] ?? ''); ?>
                                                        </div>
                                                        <?php if (!empty($module['final_percentage'])): ?>
                                                            <div class="mt-2">
                                                                <div class="flex justify-between text-xs mb-1">
                                                                    <span>Progress</span>
                                                                    <span><?php echo number_format($module['final_percentage'], 1); ?>%</span>
                                                                </div>
                                                                <div class="w-full bg-gray-200 rounded-full h-1.5">
                                                                    <div class="bg-blue-600 h-1.5 rounded-full" style="width: <?php echo $module['final_percentage']; ?>%"></div>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <p class="text-sm text-gray-500">No modules enrolled yet.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                        <?php else: ?>
                            <!-- Monthly Calendar View -->
                            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                                <div class="lg:col-span-3">
                                    <div class="calendar-container">
                                        <!-- Calendar Header -->
                                        <div class="calendar-header no-print">
                                            <div class="calendar-title">
                                                <div class="calendar-month-year">
                                                    <span class="calendar-month"><?php echo get_month_name($current_month); ?></span>
                                                    <span class="calendar-year"><?php echo $current_calendar_year; ?></span>
                                                </div>
                                                <div class="calendar-day-name">
                                                    <?php echo date('l'); ?>, <?php echo date('F j, Y'); ?>
                                                </div>
                                            </div>
                                            <div class="calendar-nav">
                                                <a href="<?php echo add_query_arg([
                                                    'view' => 'monthly',
                                                    'month' => $current_month > 1 ? $current_month - 1 : 12,
                                                    'cal_year' => $current_month > 1 ? $current_calendar_year : $current_calendar_year - 1
                                                ]); ?>" class="calendar-nav-btn">
                                                    <i class="fas fa-chevron-left"></i>
                                                </a>
                                                <a href="<?php echo add_query_arg([
                                                    'view' => 'monthly',
                                                    'month' => date('n'),
                                                    'cal_year' => date('Y')
                                                ]); ?>" class="calendar-nav-btn">
                                                    <i class="fas fa-calendar-alt"></i>
                                                </a>
                                                <a href="<?php echo add_query_arg([
                                                    'view' => 'monthly',
                                                    'month' => $current_month < 12 ? $current_month + 1 : 1,
                                                    'cal_year' => $current_month < 12 ? $current_calendar_year : $current_calendar_year + 1
                                                ]); ?>" class="calendar-nav-btn">
                                                    <i class="fas fa-chevron-right"></i>
                                                </a>
                                            </div>
                                        </div>

                                        <!-- Weekdays Header -->
                                        <div class="calendar-weekdays">
                                            <?php foreach ($days_of_week as $day_num => $day_name): ?>
                                                <div class="calendar-weekday">
                                                    <span class="full"><?php echo esc_html($day_name); ?></span>
                                                    <span class="short"><?php echo esc_html(substr($day_name, 0, 3)); ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>

                                        <!-- Calendar Grid -->
                                        <div class="calendar-grid">
                                            <?php
                                            $calendar_days = get_calendar_days($current_month, $current_calendar_year);
                                            $today = date('Y-m-d');
                                            
                                            foreach ($calendar_days as $day_info):
                                                $day = $day_info['day'];
                                                $month = $day_info['month'];
                                                $year = $day_info['year'];
                                                $is_current_month = $day_info['current_month'];
                                                
                                                $date_string = sprintf('%04d-%02d-%02d', $year, $month, $day);
                                                $is_today = ($date_string == $today);
                                                $day_of_week_num = date('N', strtotime($date_string));
                                                $day_name = $days_of_week[$day_of_week_num];
                                                
                                                // Get events for this day
                                                $day_events = get_events_for_date($calendar_events, $day, $month, $year);
                                                
                                                $day_class = 'calendar-day';
                                                if (!$is_current_month) $day_class .= ' other-month';
                                                if ($is_today) $day_class .= ' today';
                                                if (!empty($day_events)) $day_class .= ' has-events';
                                            ?>
                                                <div class="<?php echo $day_class; ?>" data-date="<?php echo $date_string; ?>">
                                                    <div class="day-header">
                                                        <span class="day-number"><?php echo $day; ?></span>
                                                        <?php if ($is_current_month): ?>
                                                            <span class="day-weekday"><?php echo esc_html(substr($day_name, 0, 3)); ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                    
                                                    <?php if (!empty($day_events)): ?>
                                                        <div class="calendar-events">
                                                            <?php
                                                            $display_events = array_slice($day_events, 0, 3);
                                                            foreach ($display_events as $event):
                                                                $event_type = $event['type'] ?? 'event';
                                                            ?>
                                                                <div class="calendar-event <?php echo $event_type; ?>" 
                                                                     onclick="showEventDetails(<?php echo htmlspecialchars(json_encode($event)); ?>)">
                                                                    <span class="event-time"><?php echo substr($event['start_time'] ?? '00:00', 0, 5); ?></span>
                                                                    <?php echo esc_html(strlen($event['title']) > 15 ? substr($event['title'], 0, 13) . '...' : $event['title']); ?>
                                                                </div>
                                                            <?php endforeach; ?>
                                                            
                                                            <?php if (count($day_events) > 3): ?>
                                                                <div class="event-more">
                                                                    +<?php echo count($day_events) - 3; ?> more
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>

                                        <!-- Calendar Legend -->
                                        <div class="calendar-legend no-print">
                                            <div class="timetable-legend-item">
                                                <div class="timetable-legend-color lecture"></div>
                                                <span>Lecture</span>
                                            </div>
                                            <div class="timetable-legend-item">
                                                <div class="timetable-legend-color practical"></div>
                                                <span>Practical</span>
                                            </div>
                                            <div class="timetable-legend-item">
                                                <div class="timetable-legend-color tutorial"></div>
                                                <span>Tutorial</span>
                                            </div>
                                            <div class="timetable-legend-item">
                                                <div class="timetable-legend-color assessment"></div>
                                                <span>Assessment</span>
                                            </div>
                                            <div class="timetable-legend-item">
                                                <div class="timetable-legend-color exam"></div>
                                                <span>Exam</span>
                                            </div>
                                            <div class="timetable-legend-item">
                                                <div class="timetable-legend-color holiday"></div>
                                                <span>Holiday</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Calendar Sidebar -->
                                <div class="lg:col-span-1">
                                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 sticky top-6">
                                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                            <i class="fas fa-calendar-check text-blue-600 mr-2"></i>
                                            Month Summary
                                        </h3>
                                        
                                        <?php
                                        // Count events by type
                                        $month_events = [
                                            'lecture' => 0,
                                            'practical' => 0,
                                            'tutorial' => 0,
                                            'assessment' => 0,
                                            'exam' => 0,
                                            'holiday' => 0
                                        ];
                                        
                                        foreach ($calendar_days as $day_info) {
                                            if ($day_info['current_month']) {
                                                $day_events = get_events_for_date($calendar_events, $day_info['day'], $day_info['month'], $day_info['year']);
                                                foreach ($day_events as $event) {
                                                    $type = $event['type'] ?? 'event';
                                                    if (isset($month_events[$type])) {
                                                        $month_events[$type]++;
                                                    }
                                                }
                                            }
                                        }
                                        ?>
                                        
                                        <div class="space-y-2 mb-6">
                                            <div class="flex justify-between items-center p-2 bg-blue-50 rounded-lg">
                                                <span class="text-sm font-medium text-blue-700">Total Events</span>
                                                <span class="font-bold text-blue-900"><?php echo array_sum($month_events); ?></span>
                                            </div>
                                            <div class="flex justify-between items-center p-2">
                                                <span class="text-sm text-gray-600">Lectures</span>
                                                <span class="font-medium"><?php echo $month_events['lecture']; ?></span>
                                            </div>
                                            <div class="flex justify-between items-center p-2">
                                                <span class="text-sm text-gray-600">Practicals</span>
                                                <span class="font-medium"><?php echo $month_events['practical']; ?></span>
                                            </div>
                                            <div class="flex justify-between items-center p-2">
                                                <span class="text-sm text-gray-600">Tutorials</span>
                                                <span class="font-medium"><?php echo $month_events['tutorial']; ?></span>
                                            </div>
                                            <div class="flex justify-between items-center p-2">
                                                <span class="text-sm text-gray-600">Assessments</span>
                                                <span class="font-medium"><?php echo $month_events['assessment']; ?></span>
                                            </div>
                                            <div class="flex justify-between items-center p-2">
                                                <span class="text-sm text-gray-600">Exams</span>
                                                <span class="font-medium"><?php echo $month_events['exam']; ?></span>
                                            </div>
                                        </div>
                                        
                                        <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                                            <i class="fas fa-star text-yellow-500 mr-2"></i>
                                            This Week
                                        </h3>
                                        
                                        <?php
                                        // Get this week's events
                                        $this_week_events = array_filter($calendar_events, function($event) use ($current_week, $current_year) {
                                            if (isset($event['is_recurring']) && $event['is_recurring']) {
                                                return true;
                                            }
                                            if (isset($event['date'])) {
                                                $week = date('W', strtotime($event['date']));
                                                $year = date('Y', strtotime($event['date']));
                                                return $week == $current_week && $year == $current_year;
                                            }
                                            return false;
                                        });
                                        ?>
                                        
                                        <div class="space-y-2">
                                            <?php if (!empty($this_week_events)): ?>
                                                <?php foreach (array_slice($this_week_events, 0, 5) as $event): ?>
                                                    <div class="text-sm p-2 bg-gray-50 rounded-lg">
                                                        <div class="font-medium"><?php echo esc_html($event['title']); ?></div>
                                                        <div class="text-xs text-gray-500">
                                                            <?php
                                                            if (isset($event['is_recurring'])) {
                                                                echo $days_of_week[$event['day_of_week']];
                                                            } else {
                                                                echo date('D', strtotime($event['date']));
                                                            }
                                                            ?> at <?php echo substr($event['start_time'] ?? '00:00', 0, 5); ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <p class="text-sm text-gray-500">No events this week</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php
                        break;

                    case 'finances':
                        // Include finances partial
                        if (file_exists(plugin_dir_path(__FILE__) . '../includes/partials/learner-dashboard-finances.php')) {
                            include plugin_dir_path(__FILE__) . '../includes/partials/learner-dashboard-finances.php';
                        } else {
                            echo '<p class="text-gray-500">Finances section coming soon.</p>';
                        }
                        break;

                    case 'results':
                        if (file_exists(plugin_dir_path(__FILE__) . '../includes/partials/learner-dashboard-results.php')) {
                            include plugin_dir_path(__FILE__) . '../includes/partials/learner-dashboard-results.php';
                        } else {
                            echo '<p class="text-gray-500">Results section coming soon.</p>';
                        }
                        break;

                    case 'graduation':
                        if (file_exists(plugin_dir_path(__FILE__) . '../includes/partials/learner-dashboard-graduation.php')) {
                            include plugin_dir_path(__FILE__) . '../includes/partials/learner-dashboard-graduation.php';
                        } else {
                            echo '<p class="text-gray-500">Graduation section coming soon.</p>';
                        }
                        break;

                    case 'certificates':
                        if (file_exists(plugin_dir_path(__FILE__) . '../includes/partials/learner-dashboard-certificates.php')) {
                            include plugin_dir_path(__FILE__) . '../includes/partials/learner-dashboard-certificates.php';
                        } else {
                            echo '<p class="text-gray-500">Certificates section coming soon.</p>';
                        }
                        break;

                    case 'documents':
                        if (file_exists(plugin_dir_path(__FILE__) . '../includes/partials/learner-dashboard-documents.php')) {
                            include plugin_dir_path(__FILE__) . '../includes/partials/learner-dashboard-documents.php';
                        } else {
                            echo '<p class="text-gray-500">Documents section coming soon.</p>';
                        }
                        break;

                    case 'activity':
                        if (file_exists(plugin_dir_path(__FILE__) . '../includes/partials/learner-dashboard-activity.php')) {
                            include plugin_dir_path(__FILE__) . '../includes/partials/learner-dashboard-activity.php';
                        } else {
                            echo '<p class="text-gray-500">Activity section coming soon.</p>';
                        }
                        break;
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Event Detail Modal -->
<div id="event-detail-modal" class="event-modal">
    <div class="event-modal-overlay" onclick="closeEventModal()"></div>
    <div class="event-modal-content">
        <div class="event-modal-header">
            <div>
                <h3 id="event-modal-title" class="event-modal-title">Event Details</h3>
                <div id="event-modal-datetime" class="event-modal-datetime"></div>
            </div>
            <button class="event-modal-close" onclick="closeEventModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="event-modal-body" id="event-modal-body">
            <!-- Event details will be inserted here -->
        </div>
        <div class="event-modal-footer">
            <button class="event-modal-btn secondary" onclick="closeEventModal()">Close</button>
            <button class="event-modal-btn" onclick="addToCalendar()">Add to Calendar</button>
        </div>
    </div>
</div>

<!-- Notification Detail Modal -->
<div id="nds-notification-detail-modal" class="fixed inset-0 z-[100000] hidden" style="display: none;">
    <div class="fixed inset-0 bg-slate-900/50 transition-opacity"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform border-2 border-blue-400 overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl">
                <div class="bg-white px-6 py-5 border-b border-slate-200 flex justify-between items-center">
                    <div>
                        <h3 id="nds-modal-title" class="text-xl font-bold text-slate-900">Notification</h3>
                        <p id="nds-modal-type-label" class="text-slate-500 text-xs mt-0.5">Notification Message</p>
                    </div>
                    <button type="button" class="text-slate-400 hover:text-slate-600 transition-colors p-2" onclick="ndsCloseNotificationModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="p-6 sm:p-8">
                    <div class="flex flex-col sm:flex-row gap-6 items-start">
                        <div id="nds-modal-icon-container" class="w-16 h-16 flex-shrink-0 flex items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                            <i id="nds-modal-icon" class="fas fa-bell text-2xl"></i>
                        </div>
                        <div class="flex-1 space-y-4">
                            <p id="nds-modal-message" class="text-lg text-slate-800 leading-relaxed whitespace-pre-wrap font-medium"></p>
                            <div class="pt-6 border-t border-slate-100 flex items-center justify-end">
                                <button type="button" onclick="ndsCloseNotificationModal()" class="px-6 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold transition-colors">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($show_success_modal) && $show_success_modal) : ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var modal = document.getElementById('nds-app-success-modal');
        var closeBtn = document.getElementById('nds-app-success-close');
        if (modal && closeBtn) {
            closeBtn.addEventListener('click', function () {
                modal.classList.add('hidden');
                try {
                    var url = new URL(window.location.href);
                    url.searchParams.delete('application');
                    url.searchParams.delete('id');
                    var newUrl = url.pathname + (url.search ? url.search : '');
                    window.history.replaceState({}, '', newUrl);
                } catch (e) {}
            });
        }
    });
    </script>
<?php endif; ?>

<script>
// Global variables
let currentEvent = null;

// Event Modal Functions
function showEventDetails(event) {
    const modal = document.getElementById('event-detail-modal');
    const titleEl = document.getElementById('event-modal-title');
    const datetimeEl = document.getElementById('event-modal-datetime');
    const bodyEl = document.getElementById('event-modal-body');
    
    currentEvent = event;
    titleEl.textContent = event.title || 'Event Details';
    
    // Format date and time
    let datetimeText = '';
    if (event.is_recurring) {
        const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        datetimeText = 'Every ' + days[event.day_of_week - 1];
        if (event.start_time) {
            datetimeText += ' at ' + event.start_time.substr(0,5);
            if (event.end_time) {
                datetimeText += ' - ' + event.end_time.substr(0,5);
            }
        }
    } else {
        const eventDate = new Date(event.date + 'T' + (event.start_time || '00:00'));
        datetimeText = eventDate.toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        if (event.start_time) {
            datetimeText += ' at ' + event.start_time.substr(0,5);
            if (event.end_time) {
                datetimeText += ' - ' + event.end_time.substr(0,5);
            }
        }
    }
    datetimeEl.textContent = datetimeText;
    
    // Build event details
    const typeColors = {
        'lecture': { bg: 'bg-blue-100', text: 'text-blue-600', icon: 'fa-chalkboard-teacher' },
        'practical': { bg: 'bg-green-100', text: 'text-green-600', icon: 'fa-flask' },
        'tutorial': { bg: 'bg-amber-100', text: 'text-amber-600', icon: 'fa-users' },
        'assessment': { bg: 'bg-red-100', text: 'text-red-600', icon: 'fa-pencil-alt' },
        'exam': { bg: 'bg-purple-100', text: 'text-purple-600', icon: 'fa-graduation-cap' },
        'holiday': { bg: 'bg-red-100', text: 'text-red-600', icon: 'fa-umbrella-beach' },
        'event': { bg: 'bg-gray-100', text: 'text-gray-600', icon: 'fa-calendar-alt' }
    };
    
    const type = event.type || 'event';
    const colors = typeColors[type] || typeColors.event;
    
    let detailsHtml = `
        <div class="event-detail-item">
            <div class="event-detail-icon ${type} ${colors.bg} ${colors.text}">
                <i class="fas ${colors.icon}"></i>
            </div>
            <div class="event-detail-info">
                <div class="event-detail-label">Event Type</div>
                <div class="event-detail-value">${type.charAt(0).toUpperCase() + type.slice(1)}</div>
            </div>
        </div>
    `;
    
    if (event.description) {
        detailsHtml += `
            <div class="event-detail-item">
                <div class="event-detail-icon ${type} bg-gray-100 text-gray-600">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="event-detail-info">
                    <div class="event-detail-label">Description</div>
                    <div class="event-detail-value">${event.description}</div>
                </div>
            </div>
        `;
    }
    
    if (event.course_code) {
        detailsHtml += `
            <div class="event-detail-item">
                <div class="event-detail-icon ${type} bg-blue-100 text-blue-600">
                    <i class="fas fa-book"></i>
                </div>
                <div class="event-detail-info">
                    <div class="event-detail-label">Course</div>
                    <div class="event-detail-value">${event.course_code} - ${event.course_name || ''}</div>
                </div>
            </div>
        `;
    }
    
    if (event.venue) {
        detailsHtml += `
            <div class="event-detail-item">
                <div class="event-detail-icon ${type} bg-orange-100 text-orange-600">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="event-detail-info">
                    <div class="event-detail-label">Venue</div>
                    <div class="event-detail-value">${event.venue}</div>
                </div>
            </div>
        `;
    }
    
    if (event.lecturer) {
        detailsHtml += `
            <div class="event-detail-item">
                <div class="event-detail-icon ${type} bg-purple-100 text-purple-600">
                    <i class="fas fa-user"></i>
                </div>
                <div class="event-detail-info">
                    <div class="event-detail-label">Lecturer</div>
                    <div class="event-detail-value">${event.lecturer}</div>
                </div>
            </div>
        `;
    }
    
    bodyEl.innerHTML = detailsHtml;
    
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeEventModal() {
    const modal = document.getElementById('event-detail-modal');
    modal.classList.remove('active');
    document.body.style.overflow = '';
    currentEvent = null;
}

function addToCalendar() {
    if (!currentEvent) return;
    
    // Create calendar event URL (Google Calendar format)
    let eventUrl = 'https://www.google.com/calendar/render?action=TEMPLATE';
    eventUrl += '&text=' + encodeURIComponent(currentEvent.title);
    
    if (currentEvent.description) {
        eventUrl += '&details=' + encodeURIComponent(currentEvent.description);
    }
    
    if (currentEvent.venue) {
        eventUrl += '&location=' + encodeURIComponent(currentEvent.venue);
    }
    
    if (currentEvent.is_recurring) {
        // For recurring events, we'll just open the current week's date
        const today = new Date();
        const daysUntilNext = (currentEvent.day_of_week - today.getDay() + 7) % 7;
        const nextDate = new Date(today);
        nextDate.setDate(today.getDate() + daysUntilNext);
        
        const startStr = nextDate.toISOString().replace(/-|:|\.\d+/g, '');
        eventUrl += '&dates=' + startStr + '/' + startStr;
    } else {
        const startStr = currentEvent.date.replace(/-/g, '') + 'T' + (currentEvent.start_time || '000000').replace(/:/g, '');
        const endStr = currentEvent.date.replace(/-/g, '') + 'T' + (currentEvent.end_time || '000000').replace(/:/g, '');
        eventUrl += '&dates=' + startStr + '/' + endStr;
    }
    
    window.open(eventUrl, '_blank');
}

// Notification functions
function ndsOpenNotificationModal(title, message, type) {
    const modal = document.getElementById('nds-notification-detail-modal');
    const titleEl = document.getElementById('nds-modal-title');
    const messageEl = document.getElementById('nds-modal-message');
    const iconEl = document.getElementById('nds-modal-icon');
    const iconContainer = document.getElementById('nds-modal-icon-container');
    const typeLabel = document.getElementById('nds-modal-type-label');
    const dropdown = document.getElementById('nds-notification-dropdown');

    if (!modal) return;
    
    if (dropdown) dropdown.classList.add('hidden');

    titleEl.textContent = title;
    // message may contain HTML markup
    messageEl.innerHTML = message;

    const types = {
        'info': { icon: 'fa-info-circle', color: 'blue', label: 'Information' },
        'success': { icon: 'fa-check-circle', color: 'emerald', label: 'Success' },
        'warning': { icon: 'fa-exclamation-triangle', color: 'amber', label: 'Warning' },
        'error': { icon: 'fa-times-circle', color: 'rose', label: 'Error' },
        'calendar': { icon: 'fa-calendar-alt', color: 'indigo', label: 'Calendar Update' }
    };

    const config = types[type] || types['info'];
    typeLabel.textContent = config.label;
    
    const textColorMap = {
        'blue': 'text-blue-600',
        'emerald': 'text-emerald-600',
        'amber': 'text-amber-600',
        'rose': 'text-rose-600',
        'indigo': 'text-indigo-600'
    };

    const bgColorMap = {
        'blue': 'bg-blue-50',
        'emerald': 'bg-emerald-50',
        'amber': 'bg-amber-50',
        'rose': 'bg-rose-50',
        'indigo': 'bg-indigo-50'
    };

    iconEl.className = 'fas ' + config.icon + ' text-3xl ' + textColorMap[config.color];
    iconContainer.className = 'w-20 h-20 flex-shrink-0 flex items-center justify-center rounded-2xl ' + bgColorMap[config.color] + ' shadow-sm';
    
    modal.classList.remove('hidden');
    modal.style.display = 'block';
    document.body.classList.add('overflow-hidden');
}

function ndsCloseNotificationModal() {
    const modal = document.getElementById('nds-notification-detail-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.style.display = 'none';
        document.body.classList.remove('overflow-hidden');
    }
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeEventModal();
        ndsCloseNotificationModal();
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Notification bell
    const bell = document.getElementById('nds-notification-bell');
    const dropdown = document.getElementById('nds-notification-dropdown');
    const markAllBtn = document.getElementById('nds-mark-all-read');
    
    if (bell && dropdown) {
        bell.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdown.classList.toggle('hidden');
        });

        document.addEventListener('click', function(e) {
            if (!dropdown.contains(e.target) && !bell.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
    }

    // Handle notification clicks
    document.querySelectorAll('#nds-notification-list > div[data-id]').forEach(item => {
        item.style.cursor = 'pointer';
        item.addEventListener('click', function(e) {
            if (e.target.closest('.nds-mark-read')) return;

            const title = this.querySelector('p.text-base').textContent.trim();
            const msgEl = this.querySelector('p.text-sm');
            const message = msgEl ? msgEl.innerHTML.trim() : '';
            const type = this.dataset.type || 'info';

            ndsOpenNotificationModal(title, message, type);

            const markBtn = this.querySelector('.nds-mark-read');
            if (markBtn) markBtn.click();
        });
    });

    // Mark all as read
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function() {
            const list = document.getElementById('nds-notification-list');
            if (list) {
                list.innerHTML = `
                    <div class="p-12 text-center">
                        <div class="w-20 h-20 bg-gray-50 rounded-3xl flex items-center justify-center mx-auto mb-5 shadow-inner">
                            <i class="fas fa-bell-slash text-gray-300 text-3xl"></i>
                        </div>
                        <h4 class="text-base font-bold text-gray-800">All caught up!</h4>
                        <p class="text-sm text-gray-500 mt-2">No new notifications.</p>
                    </div>
                `;
            }
            updateBadge(true);
            markAllBtn.remove();
        });
    }

    function updateBadge(clear = false) {
        const badge = document.getElementById('nds-notification-badge');
        if (!badge) return;
        if (clear) {
            badge.remove();
            return;
        }
        const current = parseInt(badge.textContent.trim());
        if (current > 1) {
            badge.textContent = current - 1;
        } else {
            badge.remove();
        }
    }
});

// Profile editing functionality
document.addEventListener('DOMContentLoaded', function() {
    const editBtn = document.getElementById('editProfileBtn');
    const cancelBtn = document.getElementById('cancelEditBtn');
    const viewMode = document.getElementById('profileViewMode');
    const editForm = document.getElementById('profileEditForm');

    if (editBtn && cancelBtn && viewMode && editForm) {
        editBtn.addEventListener('click', function() {
            viewMode.classList.add('hidden');
            editForm.classList.remove('hidden');
            editBtn.classList.add('hidden');
        });

        cancelBtn.addEventListener('click', function() {
            editForm.classList.add('hidden');
            viewMode.classList.remove('hidden');
            editBtn.classList.remove('hidden');
        });
    }
});
</script>

<?php wp_footer(); ?>
</body>
</html>