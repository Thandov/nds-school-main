<?php
// Prevent direct access - this file should only be included by WordPress
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Ensure WordPress functions are available
if (!function_exists('current_user_can')) {
    return;
}
function nds_course_overview($course, $option = 1)
{
    if ($option == 1) {
?>
        <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center space-x-3 mb-3">
                        <h4 class="text-lg font-semibold text-gray-900">
                            <a href="<?php echo admin_url('admin.php?page=nds-course-overview&course_id=' . $course['id']); ?>"
                                class="hover:text-blue-600 transition-colors">
                                <?php echo esc_html($course['name']); ?>
                            </a>
                        </h4>
                        <span class="px-2 py-1 rounded-full text-xs font-medium
                                                            <?php echo strtolower($course['status']) === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                            <?php echo ucfirst($course['status']); ?>
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-graduation-cap mr-2"></i>
                            <?php echo esc_html($course['program_name'] ?: 'No Program'); ?>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-clock mr-2"></i>
                            <?php echo esc_html(isset($course['duration']) && $course['duration'] ? $course['duration'] : 'N/A'); ?> <?php echo isset($course['duration']) && $course['duration'] ? 'weeks' : ''; ?>
                        </div>
                    </div>

                    <?php if (!empty($course['description'])): ?>
                        <p class="text-gray-600 mb-4 text-sm"><?php echo esc_html(substr($course['description'], 0, 150)) . (strlen($course['description']) > 150 ? '...' : ''); ?></p>
                    <?php endif; ?>

                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div class="flex items-center text-gray-500">
                            <i class="fas fa-chalkboard-teacher mr-2 text-gray-700"></i>
                            <?php echo intval($course['lecturer_count']); ?> Chef Instructors
                        </div>
                        <div class="flex items-center text-gray-500">
                            <i class="fas fa-users mr-2 text-gray-700"></i>
                            <?php echo intval($course['student_count']); ?> Culinary Students
                        </div>
                    </div>
                </div>

                <div class="flex items-center space-x-2 ml-4">
                    <a href="<?php echo admin_url('admin.php?page=nds-edit-course&edit_course=' . $course['id']); ?>"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                        <i class="fas fa-edit mr-1"></i>Edit
                    </a>
                    <button type="button" onclick="confirmDelete(<?php echo $course['id']; ?>, '<?php echo esc_js($course['name']); ?>')"
                        class="inline-flex items-center px-3 py-2 border border-red-300 rounded-lg text-sm font-medium text-red-700 bg-white hover:bg-red-50 transition-colors">
                        <i class="fas fa-trash mr-1"></i>Delete
                    </button>
                </div>
            </div>
        </div>
    <?php
    }
    if ($option == 2) {
    ?>
        <div class="course-card-wrapper courseCard">
            <a href="<?php echo admin_url('admin.php?page=nds-course-overview&course_id=' . $course['id']); ?>"
                class="block courseCard">
                <!-- Program and Duration Info -->
                <div class="mb-3">
                    <div class="flex items-center text-sm text-gray-600 mb-1">
                        <i class="fas fa-graduation-cap mr-2 text-gray-700"></i>
                        <span class="truncate"><?php echo esc_html($course['program_name'] ?: 'No Program'); ?></span>
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-clock mr-2 text-gray-700"></i>
                        <span><?php echo esc_html(isset($course['duration']) && $course['duration'] ? $course['duration'] : 'N/A'); ?> <?php echo isset($course['duration']) && $course['duration'] ? 'weeks' : ''; ?></span>
                    </div>
                </div>

                <!-- Description -->
                <?php if (!empty($course['description'])): ?>
                    <p class="text-gray-600 text-sm mb-3 line-clamp-2"><?php echo esc_html(substr($course['description'], 0, 100)) . (strlen($course['description']) > 100 ? '...' : ''); ?></p>
                <?php endif; ?>

                <!-- Stats -->
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div class="flex items-center text-gray-600">
                        <div class="w-6 h-6 bg-purple-100 rounded flex items-center justify-center mr-2">
                            <i class="fas fa-chalkboard-teacher text-purple-600 text-xs"></i>
                        </div>
                        <div>
                            <div class="font-medium"><?php echo intval($course['lecturer_count']); ?></div>
                            <div class="text-xs text-gray-500">Instructors</div>
                        </div>
                    </div>
                    <div class="flex items-center text-gray-600">
                        <div class="w-6 h-6 bg-orange-100 rounded flex items-center justify-center mr-2">
                            <i class="fas fa-users text-orange-600 text-xs"></i>
                        </div>
                        <div>
                            <div class="font-medium"><?php echo intval($course['student_count']); ?></div>
                            <div class="text-xs text-gray-500">Students</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    <?php
    }
}
// Modern Courses Management with Tailwind CSS
function nds_courses_page_tailwind()
{
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }
    global $wpdb;

    $table_courses = $wpdb->prefix . 'nds_courses';
    $table_programs = $wpdb->prefix . 'nds_programs';
    $table_staff = $wpdb->prefix . 'nds_staff';
    $table_assignments = $wpdb->prefix . 'nds_course_lecturers';

    // Get program_id from URL for filtering
    $filter_program_id = isset($_GET['program_id']) ? intval($_GET['program_id']) : 0;
    
    // Debug: Check if we have a program_id filter
    if ($filter_program_id > 0) {
        error_log("Filtering courses by program_id: " . $filter_program_id);
    }
    
    // Debug: Check total courses in database
    $total_courses_in_db = $wpdb->get_var("SELECT COUNT(*) FROM {$table_courses}");
    error_log("Total courses in database: " . $total_courses_in_db);
    
    // Debug: Check courses per program
    $courses_per_program = $wpdb->get_results("
        SELECT p.name as program_name, COUNT(c.id) as course_count 
        FROM {$table_programs} p 
        LEFT JOIN {$table_courses} c ON p.id = c.program_id 
        GROUP BY p.id, p.name
    ", ARRAY_A);
    
    foreach ($courses_per_program as $program) {
        error_log("Program '{$program['program_name']}' has {$program['course_count']} courses");
    }


    // Get courses with related data (filtered by program_id if provided)
    $where_clause = $filter_program_id ? "WHERE c.program_id = {$filter_program_id}" : "";
    $courses = $wpdb->get_results("
        SELECT c.*,
               c.duration_weeks as duration,
               p.name as program_name,
               COUNT(DISTINCT cl.lecturer_id) as lecturer_count,
               COUNT(DISTINCT s.id) as student_count
        FROM {$table_courses} c
        LEFT JOIN {$table_programs} p ON c.program_id = p.id
        LEFT JOIN {$table_assignments} cl ON c.id = cl.course_id
        LEFT JOIN {$table_staff} st ON cl.lecturer_id = st.id
        LEFT JOIN {$wpdb->prefix}nds_student_enrollments se ON c.id = se.course_id
        LEFT JOIN {$wpdb->prefix}nds_students s ON se.student_id = s.id
        {$where_clause}
        GROUP BY c.id
        ORDER BY c.name ASC
    ", ARRAY_A);


    // Get the current program info if filtering by program_id
    $current_program = null;
    if ($filter_program_id) {
        $current_program = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_programs} WHERE id = %d", $filter_program_id), ARRAY_A);
    }

    // Get programs for dropdown
    $programs = $wpdb->get_results("SELECT id, name as program_name FROM {$table_programs} ORDER BY name", ARRAY_A);

    // Get staff for lecturer assignment
    $staff = $wpdb->get_results("SELECT id, first_name, last_name, role FROM {$table_staff} ORDER BY first_name, last_name", ARRAY_A);

    // Statistics
    $total_courses = count($courses);
    $active_courses = count(array_filter($courses, function ($c) {
        return strtolower($c['status']) === 'active';
    }));
    $total_lecturers = array_sum(array_column($courses, 'lecturer_count'));
    $total_students = array_sum(array_column($courses, 'student_count'));

    // Get program_id from URL for auto-selection
    $selected_program_id = isset($_GET['program_id']) ? intval($_GET['program_id']) : 0;

    // Force-load Tailwind CSS for this screen to avoid WP admin CSS conflicts
    $plugin_dir = plugin_dir_path(dirname(__FILE__));
    $css_file   = $plugin_dir . 'assets/css/frontend.css';
    if (file_exists($css_file)) {
        wp_enqueue_style(
            'nds-tailwindcss-courses',
            plugin_dir_url(dirname(__FILE__)) . 'assets/css/frontend.css',
            array(),
            filemtime($css_file),
            'all'
        );
        wp_add_inline_style('nds-tailwindcss-courses', '
            .nds-tailwind-wrapper { all: initial !important; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important; }
            .nds-tailwind-wrapper * { box-sizing: border-box !important; }
            .nds-tailwind-wrapper .bg-white { background-color: #ffffff !important; }
            .nds-tailwind-wrapper .bg-gray-50 { background-color: #f9fafb !important; }
            .nds-tailwind-wrapper .text-gray-900 { color: #111827 !important; }
            .nds-tailwind-wrapper .text-gray-600 { color: #4b5563 !important; }
            .nds-tailwind-wrapper .rounded-xl { border-radius: 0.75rem !important; }
            .nds-tailwind-wrapper .shadow-sm { box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05) !important; }
            .nds-tailwind-wrapper .border { border-width: 1px !important; }
            .nds-tailwind-wrapper .border-gray-200 { border-color: #e5e7eb !important; }
        ');
    }
    wp_enqueue_style('nds-icons', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css', array(), null, 'all');

    ?>
    <div class="nds-tailwind-wrapper bg-gray-50 min-h-screen" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
        <!-- Header -->
        <div class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                            <span class="dashicons dashicons-welcome-learn-more text-white text-2xl"></span>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">
                                <?php echo $current_program ? esc_html($current_program['name']) : 'Culinary Courses Management'; ?>
                            </h1>
                            <p class="text-sm text-gray-600 mt-1">Manage culinary courses, assign chef instructors, and track student enrollment</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <?php if ($current_program): ?>
                            <?php
                            // When viewing courses for a specific program, send the user back
                            // to the Programs screen filtered by that program's faculty.
                            $back_faculty_id = isset($current_program['faculty_id']) ? intval($current_program['faculty_id']) : 0;
                            $back_url = $back_faculty_id
                                ? admin_url('admin.php?page=nds-programs&faculty_id=' . $back_faculty_id)
                                : admin_url('admin.php?page=nds-programs');
                            ?>
                            <a href="<?php echo esc_url($back_url); ?>"
                                class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium shadow-sm transition-all duration-200">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Back to Programs
                            </a>
                        <?php endif; ?>
                        <a href="#addCourseModal" class="inline-flex items-center px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium shadow-md hover:shadow-lg transition-all duration-200">
                            <i class="fas fa-plus mr-2"></i>
                            Add Course
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Breadcrumb Navigation -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-4">
            <nav class="flex items-center space-x-2 text-sm text-gray-600">
                <a href="<?php echo admin_url('admin.php?page=nds-academy'); ?>" class="hover:text-blue-600 transition-colors flex items-center">
                    <i class="fas fa-home mr-1"></i>NDS Academy
                </a>
                <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
                <?php if ($current_program): ?>
                    <a href="<?php echo admin_url('admin.php?page=nds-programs'); ?>" class="hover:text-blue-600 transition-colors">
                        Programs
                    </a>
                    <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
                <?php endif; ?>
                <span class="text-gray-900 font-medium">Courses</span>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

            <!-- Success Message -->
            <?php if (isset($_GET['course_created']) && $_GET['course_created'] === 'success'): ?>
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-4">
                    <i class="fas fa-check-circle mr-2"></i>Course created successfully!
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['schedule_warnings'])): ?>
                <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg mb-4">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Schedule Warning:</strong> <?php echo esc_html(urldecode($_GET['schedule_warnings'])); ?>
                </div>
            <?php endif; ?>
            
            <?php 
            // Check for transient warnings (from update)
            if (isset($_GET['edit_course'])) {
                $course_id = intval($_GET['edit_course']);
                $warning = get_transient('nds_schedule_overlap_warning_' . $course_id);
                if ($warning) {
                    delete_transient('nds_schedule_overlap_warning_' . $course_id);
                    ?>
                    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg mb-4">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Schedule Warning:</strong> <?php echo esc_html($warning); ?>
                    </div>
                    <?php
                }
            }
            ?>
            
            <?php if (isset($_GET['course_deleted']) && $_GET['course_deleted'] === 'success'): ?>
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                    <i class="fas fa-check-circle mr-2"></i>Course deleted successfully!
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error']) && $_GET['error'] === 'delete_failed'): ?>
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                    <i class="fas fa-exclamation-circle mr-2"></i>Failed to delete course. Please check the error logs.
                </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100 flex flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500"><?php echo $current_program ? 'Program Courses' : 'Culinary Courses'; ?></p>
                                <p class="mt-2 text-2xl font-semibold text-gray-900">
                                    <?php echo number_format_i18n($total_courses); ?>
                                </p>
                            </div>
                            <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center">
                                <span class="dashicons dashicons-book text-green-600 text-xl"></span>
                            </div>
                        </div>
                        <p class="mt-3 text-xs text-gray-500">
                            <?php echo $current_program ? 'Courses in this program.' : 'Total courses available.'; ?>
                        </p>
                    </div>

                    <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100 flex flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500"><?php echo $current_program ? 'Active in Program' : 'Active Courses'; ?></p>
                                <p class="mt-2 text-2xl font-semibold text-gray-900">
                                    <?php echo number_format_i18n($active_courses); ?>
                                </p>
                            </div>
                            <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                                <span class="dashicons dashicons-yes-alt text-blue-600 text-xl"></span>
                            </div>
                        </div>
                        <p class="mt-3 text-xs text-gray-500">
                            <?php echo $active_courses > 0 ? round(($active_courses / max(1, $total_courses)) * 100) : 0; ?>% of courses are active.
                        </p>
                    </div>

                    <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100 flex flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500"><?php echo $current_program ? 'Program Instructors' : 'Chef Instructors'; ?></p>
                                <p class="mt-2 text-2xl font-semibold text-gray-900">
                                    <?php echo number_format_i18n($total_lecturers); ?>
                                </p>
                            </div>
                            <div class="w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center">
                                <span class="dashicons dashicons-businessperson text-purple-600 text-xl"></span>
                            </div>
                        </div>
                        <p class="mt-3 text-xs text-gray-500">
                            Instructors assigned to courses.
                        </p>
                    </div>

                    <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100 flex flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500"><?php echo $current_program ? 'Program Students' : 'Culinary Students'; ?></p>
                                <p class="mt-2 text-2xl font-semibold text-gray-900">
                                    <?php echo number_format_i18n($total_students); ?>
                                </p>
                            </div>
                            <div class="w-10 h-10 rounded-lg bg-orange-50 flex items-center justify-center">
                                <span class="dashicons dashicons-groups text-orange-600 text-xl"></span>
                            </div>
                        </div>
                        <p class="mt-3 text-xs text-gray-500">
                            Students enrolled in courses.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- Courses List -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                                <div>
                                    <h2 class="text-sm font-semibold text-gray-900"><?php echo $current_program && isset($current_program['name']) ? esc_html($current_program['name']) . ' Courses' : 'All Courses'; ?></h2>
                                    <p class="text-xs text-gray-500">Manage and organize your courses</p>
                                </div>
                            </div>

                            <div class="p-6">
                                <?php if (empty($courses)): ?>
                                    <div class="text-center py-12">
                                        <i class="fas fa-book text-4xl text-gray-300 mb-4"></i>
                                        <h4 class="text-lg font-medium text-gray-900 mb-2">No Culinary Courses Found</h4>
                                        <p class="text-gray-500 mb-4">Get started by creating your first culinary course.</p>
                                        <a href="#addCourseModal" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition-all duration-200 shadow-md hover:shadow-lg">
                                            <i class="fas fa-plus mr-2"></i>Create Course
                                        </a>
                                    </div>
                                <?php else:
                                    $option = 1;
                                ?>
                                    <?php if ($option == 2): ?>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                                        <?php else: ?>
                                            <div class="space-y-4">
                                            <?php endif; ?>
                                            <?php foreach ($courses as $course):
                                                echo nds_course_overview($course, $option);
                                            endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">

                            <!-- Quick Filters -->
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                                <div class="px-5 py-4 border-b border-gray-100">
                                    <h2 class="text-sm font-semibold text-gray-900">Quick Filters</h2>
                                    <p class="text-xs text-gray-500">Filter courses by program or status</p>
                                </div>
                                <div class="p-4 space-y-3">
                                    <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="">All Programs</option>
                                        <?php foreach ($programs as $program): ?>
                                            <option value="<?php echo $program['id']; ?>"><?php echo esc_html($program['program_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>

                                    <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="">All Status</option>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="draft">Draft</option>
                                    </select>

                                    <button type="button" onclick="applyFilters()"
                                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-all duration-200 shadow-md hover:shadow-lg">
                                        Apply Filters
                                    </button>
                                </div>
                            </div>

                            <!-- Lecturer Assignment -->
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                                <div class="px-5 py-4 border-b border-gray-100">
                                    <h2 class="text-sm font-semibold text-gray-900">Assign Chef Instructor</h2>
                                    <p class="text-xs text-gray-500">Assign instructors to courses</p>
                                </div>
                                <div class="p-4">
                                    <form id="assignLecturerForm" class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Course</label>
                                            <select id="assign_course_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                                <option value="">Select Course</option>
                                                <?php foreach ($courses as $course): ?>
                                                    <option value="<?php echo $course['id']; ?>"><?php echo esc_html($course['name']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Chef Instructor</label>
                                            <select id="assign_lecturer_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                                <option value="">Select Chef Instructor</option>
                                                <?php foreach ($staff as $member): ?>
                                                    <option value="<?php echo $member['id']; ?>">
                                                        <?php echo esc_html($member['first_name'] . ' ' . $member['last_name'] . ' (' . $member['role'] . ')'); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <button type="button" onclick="assignLecturer()"
                                            class="w-full bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 px-4 rounded-lg transition-all duration-200 shadow-md hover:shadow-lg">
                                            <i class="fas fa-plus mr-2"></i>Assign Chef Instructor
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Quick Actions -->
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                                <div class="px-5 py-4 border-b border-gray-100">
                                    <h2 class="text-sm font-semibold text-gray-900">Quick Actions</h2>
                                    <p class="text-xs text-gray-500">Navigate to related sections</p>
                                </div>
                                <div class="p-4 space-y-3">
                                    <a href="<?php echo admin_url('admin.php?page=nds-programs'); ?>"
                                        class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all duration-200 shadow-md hover:shadow-lg">
                                        <i class="fas fa-graduation-cap mr-2"></i>Manage Programs
                                    </a>
                                    <a href="<?php echo admin_url('admin.php?page=nds-education-paths'); ?>"
                                        class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-all duration-200 shadow-md hover:shadow-lg">
                                        <i class="fas fa-route mr-2"></i>Manage Paths
                                    </a>
                                    <button type="button" onclick="exportCourses()"
                                        class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-all duration-200 shadow-md hover:shadow-lg">
                                        <i class="fas fa-download mr-2"></i>Export Data
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
            </div>

            <!-- Add Course Modal -->
                <div id="addCourseModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
                    <div class="flex items-center justify-center min-h-screen p-4">
                        <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-screen overflow-y-auto">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-6">
                                    <h2 class="text-2xl font-bold text-gray-900">Add New Course</h2>
                                    <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                                        <i class="fas fa-times text-xl"></i>
                                    </button>
                                </div>

                                <form method="POST" action="<?php echo admin_url('admin-post.php'); ?>">
                                    <?php wp_nonce_field('nds_add_course_nonce', 'nds_add_course_nonce'); ?>
                                    <input type="hidden" name="action" value="nds_add_course">

                                    <div class="space-y-6">
                                        <div>
                                            <label for="course_name" class="block text-sm font-semibold text-gray-900 mb-2">
                                                Course Name <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" id="course_name" name="course_name"
                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                                placeholder="e.g., French Cuisine Fundamentals" required>
                                        </div>

                                        <div>
                                            <label for="program_id" class="block text-sm font-semibold text-gray-900 mb-2">
                                                Program <span class="text-red-500">*</span>
                                            </label>
                                            <select id="program_id" name="program_id" data-auto-select="program_id"
                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" required>
                                                <option value="">Select Program</option>
                                                <?php foreach ($programs as $program): ?>
                                                    <?php $selected = ($selected_program_id && $program['id'] == $selected_program_id) ? ' selected' : ''; ?>
                                                    <option value="<?php echo $program['id']; ?>" <?php echo $selected; ?>><?php echo esc_html($program['program_name']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div>
                                                <label for="course_code" class="block text-sm font-semibold text-gray-900 mb-2">
                                                    Course Code
                                                </label>
                                                <input type="text" id="course_code" name="course_code"
                                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                                    placeholder="e.g., CUL101">
                                            </div>

                                            <div>
                                                <label for="duration" class="block text-sm font-semibold text-gray-900 mb-2">
                                                    Duration (hours)
                                                </label>
                                                <input type="number" id="duration" name="duration"
                                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                                    placeholder="45" min="1">
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div>
                                                <label for="start_date" class="block text-sm font-semibold text-gray-900 mb-2">
                                                    Start Date
                                                </label>
                                                <input type="date" id="start_date" name="start_date"
                                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                            </div>

                                            <div>
                                                <label for="end_date" class="block text-sm font-semibold text-gray-900 mb-2">
                                                    End Date
                                                </label>
                                                <input type="date" id="end_date" name="end_date"
                                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                            </div>
                                        </div>

                                        <div>
                                            <label for="description" class="block text-sm font-semibold text-gray-900 mb-2">
                                                Description
                                            </label>
                                            <textarea id="description" name="description" rows="4"
                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                                placeholder="Describe the culinary techniques, ingredients, and skills students will learn..."></textarea>
                                        </div>

                                        <!-- Schedule / Timetable (reusable component) -->
                                        <?php
                                        require_once plugin_dir_path(__FILE__) . 'partials/schedule-fields.php';
                                        if (function_exists('nds_render_schedule_fields')) {
                                            nds_render_schedule_fields(array(
                                                'lecturers' => $staff ?? array(),
                                                'prefix' => 'schedule'
                                            ));
                                        }
                                        ?>

                                        <div>
                                            <label for="status" class="block text-sm font-semibold text-gray-900 mb-2">
                                                Status
                                            </label>
                                            <select id="status" name="status"
                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                                <option value="active">Active</option>
                                                <option value="inactive">Inactive</option>
                                                <option value="draft">Draft</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
                                        <button type="button" onclick="closeModal()"
                                            class="px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                            Cancel
                                        </button>
                                        <button type="submit"
                                            class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-all duration-200 font-medium shadow-md hover:shadow-lg">
                                            Create Course
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hidden Delete Form -->
                <form id="deleteCourseForm" method="POST" action="<?php echo admin_url('admin-post.php'); ?>" style="display: none;">
                    <input type="hidden" name="action" value="nds_delete_course">
                    <input type="hidden" name="course_id" id="delete_course_id">
                    <?php if (isset($filter_program_id) && $filter_program_id > 0): ?>
                        <input type="hidden" name="program_id" value="<?php echo esc_attr($filter_program_id); ?>">
                    <?php endif; ?>
                    <?php wp_nonce_field('nds_delete_course_nonce', 'nds_delete_course_nonce'); ?>
                </form>

            </div>
        </div>

        <!-- Include Auto-Select Helper -->
        <script src="<?php echo plugin_dir_url(__FILE__); ?>modal-auto-select.js"></script>

        <script>
            function closeModal() {
                document.getElementById('addCourseModal').classList.add('hidden');
                document.body.style.overflow = '';

                // Use helper to reset modal
                if (window.ModalAutoSelect) {
                    window.ModalAutoSelect.resetModal('addCourseModal');
                }
            }

            function confirmDelete(courseId, courseName) {
                if (confirm(`Are you sure you want to delete "${courseName}"? This will also remove all lecturer assignments and student enrollments.`)) {
                    document.getElementById('delete_course_id').value = courseId;
                    document.getElementById('deleteCourseForm').submit();
                }
            }

            function applyFilters() {
                alert('Filter functionality will be implemented soon.');
            }

            function assignLecturer() {
                const courseId = document.getElementById('assign_course_id').value;
                const lecturerId = document.getElementById('assign_lecturer_id').value;

                if (!courseId || !lecturerId) {
                    alert('Please select both a course and lecturer.');
                    return;
                }

                // AJAX call to assign lecturer
                alert('Lecturer assignment functionality will be implemented soon.');
            }

            function exportCourses() {
                alert('Export functionality will be implemented soon.');
            }

            // Modal trigger
            document.addEventListener('DOMContentLoaded', function() {
                const modalLinks = document.querySelectorAll('a[href="#addCourseModal"]');
                modalLinks.forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        document.getElementById('addCourseModal').classList.remove('hidden');
                        document.body.style.overflow = 'hidden';
                    });
                });
            });
        </script>
    <?php
}
    ?>