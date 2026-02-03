<?php
// Prevent direct access - this file should only be included by WordPress
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Ensure WordPress functions are available
if (!function_exists('current_user_can')) {
    return;
}

function nds_program_card($program, $option = 1)
{
    if ($option == 1) {
?>

        <div class="program-card bg-white border border-gray-200 rounded-lg p-4">
            <!-- Header Section -->
            <div class="flex items-start justify-between mb-3">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-8 h-8 bg-gray-100 rounded flex items-center justify-center">
                            <i class="fas fa-graduation-cap text-gray-600 text-sm"></i>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900">
                                <?php echo esc_html($program['name']); ?>
                            </h4>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-700">
                                    Active
                                </span>
                                <?php if (!empty($program['path_name'])): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-700">
                                        <?php echo esc_html($program['path_name']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <?php if (!empty($program['description'])): ?>
                <p class="text-gray-600 text-sm mb-3"><?php echo esc_html(substr($program['description'], 0, 100)) . (strlen($program['description']) > 100 ? '...' : ''); ?></p>
            <?php endif; ?>

            <!-- Stats Section -->
            <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                <div class="flex items-center gap-4">
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-book mr-1"></i>
                        <span class="font-medium"><?php echo intval($program['course_count']); ?></span>
                        <span class="text-gray-500 ml-1">Courses</span>
                    </div>

                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-clock mr-1"></i>
                        <span class="font-medium"><?php echo esc_html(isset($program['duration_months']) ? $program['duration_months'] : '12'); ?></span>
                        <span class="text-gray-500 ml-1">Months</span>
                    </div>

                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-certificate mr-1"></i>
                        <span class="font-medium"><?php echo esc_html(ucfirst(isset($program['program_type']) ? $program['program_type'] : 'diploma')); ?></span>
                    </div>
                </div>

                <!-- Action Button -->
                <div class="flex items-center gap-2">
                    <a href="<?php echo admin_url('admin.php?page=nds-courses&edit_program=' . $program['id']); ?>"
                        class="inline-flex items-center px-3 py-1 text-xs font-medium text-white bg-gray-600 hover:bg-gray-700 rounded">
                        <i class="fas fa-book mr-1"></i>Courses
                    </a>
                </div>
            </div>
        </div>
    <?php
    }
    if ($option == 2) {
    ?>
        <div class="programCard bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow duration-200 flex flex-col min-h-0">
            <!-- Header Section -->
            <div class="flex items-start justify-between mb-3 flex-shrink-0">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-2 flex-wrap">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-50 text-gray-700 whitespace-nowrap">
                    Active
                </span>
                    <?php if (!empty($program['path_name'])): ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-50 text-gray-600 truncate">
                            <?php echo esc_html($program['path_name']); ?>
                        </span>
                    <?php endif; ?>
                </div>
                    <h6 class="text-lg font-semibold text-gray-900 mb-2 truncate"><?php echo esc_html($program['name']); ?></h6>
                        <?php if (!empty($program['description'])): ?>
                            <p class="text-gray-600 text-sm mb-3 line-clamp-2"><?php echo esc_html($program['description']); ?></p>
                        <?php endif; ?>
                    </div>
                    </div>

            <!-- Stats Section -->
            <div class="grid grid-cols-3 gap-2 mb-4 pb-4 border-b border-gray-100 flex-shrink-0">
                    <div class="text-center">
                        <div class="flex items-center justify-center text-sm text-gray-600 mb-1">
                            <span class="dashicons dashicons-book text-purple-600 text-base mr-1"></span>
                            <span class="font-semibold text-gray-900"><?php echo intval($program['course_count']); ?></span>
                        </div>
                        <p class="text-xs text-gray-500">Courses</p>
                    </div>

                    <div class="text-center">
                        <div class="flex items-center justify-center text-sm text-gray-600 mb-1">
                            <span class="dashicons dashicons-clock text-blue-600 text-base mr-1"></span>
                            <span class="font-semibold text-gray-900"><?php echo esc_html(isset($program['duration_months']) ? $program['duration_months'] : '12'); ?></span>
                    </div>
                        <p class="text-xs text-gray-500">Months</p>
                </div>

                    <div class="text-center">
                        <div class="flex items-center justify-center text-sm text-gray-600 mb-1">
                            <span class="dashicons dashicons-awards text-emerald-600 text-base mr-1"></span>
                        <span class="font-semibold text-gray-900 truncate"><?php echo esc_html(ucfirst(isset($program['program_type']) ? $program['program_type'] : 'diploma')); ?></span>
                    </div>
                        <p class="text-xs text-gray-500">Type</p>
                </div>
                </div>
                
                <!-- Action Buttons -->
            <div class="flex gap-2 mt-auto pt-2 flex-shrink-0">
                    <button type="button" 
                            onclick="openAddCourseModal(<?php echo $program['id']; ?>, '<?php echo esc_js($program['name']); ?>')"
                        class="flex-1 inline-flex items-center justify-center px-2 py-2 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors min-w-0">
                    <span class="dashicons dashicons-plus-alt2 mr-1 text-sm flex-shrink-0"></span>
                    <span class="truncate">Add Course</span>
                    </button>
                    <a href="<?php echo admin_url('admin.php?page=nds-courses&program_id=' . $program['id']); ?>"
                   class="flex-1 inline-flex items-center justify-center px-2 py-2 text-xs font-medium text-white bg-gray-600 hover:bg-gray-700 rounded-lg transition-colors min-w-0">
                    <span class="dashicons dashicons-book mr-1 text-sm flex-shrink-0"></span>
                    <span class="truncate">Manage</span>
                    </a>
            </div>
        </div>
    <?php
    }
}

// Modern Programs Management with Tailwind CSS
function nds_programs_page_tailwind()
{
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }
    global $wpdb;

    $table_programs = $wpdb->prefix . 'nds_programs';
    $table_courses = $wpdb->prefix . 'nds_courses';
    $table_paths = $wpdb->prefix . 'nds_faculties';

    // Get faculty_id from URL for filtering
    $filter_faculty_id = isset($_GET['faculty_id']) ? intval($_GET['faculty_id']) : 0;

    // Get programs with course counts
    $programs = array();
    if ($filter_faculty_id > 0) {
        $programs = $wpdb->get_results($wpdb->prepare("
        SELECT p.*,
               COUNT(c.id) as course_count,
               ep.name as path_name
        FROM {$table_programs} p
        LEFT JOIN {$table_courses} c ON p.id = c.program_id
        LEFT JOIN {$table_paths} ep ON p.faculty_id = ep.id
            WHERE p.faculty_id = %d
        GROUP BY p.id
        ORDER BY p.name
        ", $filter_faculty_id), ARRAY_A);
    } else {
        // Load all programs if no faculty filter
        $programs = $wpdb->get_results("
        SELECT p.*,
               COUNT(c.id) as course_count,
               ep.name as path_name
        FROM {$table_programs} p
        LEFT JOIN {$table_courses} c ON p.id = c.program_id
        LEFT JOIN {$table_paths} ep ON p.faculty_id = ep.id
        GROUP BY p.id
        ORDER BY p.name
        ", ARRAY_A);
    }

    // Get the current faculty info if filtering by faculty_id
    $current_path = null;
    if ($filter_faculty_id) {
        $current_path = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_paths} WHERE id = %d", $filter_faculty_id), ARRAY_A);
    }

    // Get all faculties for filter dropdown
    $all_faculties = $wpdb->get_results("SELECT id, name FROM {$table_paths} ORDER BY name", ARRAY_A);

    // Get recent programs
    $recent_programs = $wpdb->get_results("
        SELECT * FROM {$table_programs}
        ORDER BY created_at DESC
        LIMIT 5
    ", ARRAY_A);

    // Statistics
    $total_programs = count($programs);
    $active_programs = $total_programs; // All programs are considered active for now
    $total_courses = array_sum(array_column($programs, 'course_count'));

    // Get faculty_id from URL for auto-selection
    $selected_faculty_id = isset($_GET['faculty_id']) ? intval($_GET['faculty_id']) : 0;

    ?>
    <div class="nds-tailwind-wrapper bg-gray-50 min-h-screen" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
        <!-- Header -->
        <div class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-purple-600 rounded-xl flex items-center justify-center">
                            <span class="dashicons dashicons-welcome-learn-more text-white text-2xl"></span>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">
                                <?php echo $current_path ? esc_html($current_path['name']) : 'Programs Management'; ?>
                            </h1>
                            <p class="text-gray-600"><?php echo $current_path ? 'Programs under this faculty' : 'Manage academic programs and their associated courses'; ?></p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                <?php if ($current_path): ?>
                            <a href="<?php echo admin_url('admin.php?page=nds-programs'); ?>"
                                class="inline-flex items-center px-4 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium shadow-sm">
                                <span class="dashicons dashicons-arrow-left-alt2 mr-1 text-base"></span>
                                Back to All Programs
                            </a>
                        <?php endif; ?>
                        <?php
                        // Check if faculties exist
                        global $wpdb;
                        $paths_table = $wpdb->prefix . 'nds_faculties';
                        $paths_count = $wpdb->get_var("SELECT COUNT(*) FROM $paths_table");
                        if ($paths_count == 0): ?>
                            <a href="<?php echo admin_url('admin.php?page=nds-faculties'); ?>"
                                class="inline-flex items-center px-4 py-2 rounded-lg bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium shadow-sm">
                                <span class="dashicons dashicons-networking mr-1 text-base"></span>
                                Create Faculty First
                            </a>
                <?php else: ?>
                            <a href="#addProgramModal" class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium shadow-sm">
                                <span class="dashicons dashicons-plus-alt2 mr-1 text-base"></span>
                                Add Program
                            </a>
                <?php endif; ?>
        </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
            <!-- Success/Error Messages -->
            <?php if (isset($_GET['success']) && $_GET['success'] === 'program_created'): ?>
                <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 flex items-center">
                    <span class="dashicons dashicons-yes-alt text-emerald-600 mr-3 text-xl"></span>
                    <div>
                        <h3 class="text-sm font-semibold text-emerald-800">Success</h3>
                        <p class="text-sm text-emerald-700">Program created successfully!</p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 flex items-center">
                    <span class="dashicons dashicons-warning text-red-600 mr-3 text-xl"></span>
                    <div>
                        <h3 class="text-sm font-semibold text-red-800">Error</h3>
                        <p class="text-sm text-red-700">
                    <?php
                    switch ($_GET['error']) {
                        case 'missing_fields':
                            echo 'Please fill in all required fields.';
                            break;
                        case 'program_exists':
                            echo 'A program with this name already exists in the selected faculty.';
                            break;
                        case 'db_error':
                            echo 'Database error occurred. Please try again.';
                            break;
                        default:
                            echo 'An error occurred. Please try again.';
                    }
                    ?>
                        </p>
                </div>
                </div>
                    <?php endif; ?>


            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Programs</p>
                            <p class="mt-2 text-2xl font-semibold text-gray-900">
                                <?php echo number_format_i18n($total_programs); ?>
                            </p>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                            <span class="dashicons dashicons-welcome-learn-more text-blue-600 text-xl"></span>
                    </div>
                </div>
                        </div>

                <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Active Programs</p>
                            <p class="mt-2 text-2xl font-semibold text-gray-900">
                                <?php echo number_format_i18n($active_programs); ?>
                            </p>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center">
                            <span class="dashicons dashicons-yes-alt text-emerald-600 text-xl"></span>
                    </div>
                </div>
                        </div>

                <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Courses</p>
                            <p class="mt-2 text-2xl font-semibold text-gray-900">
                                <?php echo number_format_i18n($total_courses); ?>
                            </p>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center">
                            <span class="dashicons dashicons-book text-purple-600 text-xl"></span>
                    </div>
                </div>
                        </div>

                <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Faculties</p>
                            <p class="mt-2 text-2xl font-semibold text-gray-900">
                                <?php
                                                                        $unique_paths = array_unique(array_column($programs, 'path_name'));
                                echo number_format_i18n(count(array_filter($unique_paths)));
                                ?>
                            </p>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-orange-50 flex items-center justify-center">
                            <span class="dashicons dashicons-networking text-orange-600 text-xl"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Faculty Filter -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Filter by Faculty</h3>
                        <p class="text-sm text-gray-500">Select a faculty to view only its programs</p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <form method="GET" action="" class="flex items-center space-x-3">
                            <input type="hidden" name="page" value="nds-programs">
                            <select name="faculty_id" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                <option value="">All Faculties</option>
                                <?php foreach ($all_faculties as $faculty): ?>
                                    <option value="<?php echo esc_attr($faculty['id']); ?>" <?php selected($filter_faculty_id, $faculty['id']); ?>>
                                        <?php echo esc_html($faculty['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Programs List -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                            <div>
                                <h2 class="text-sm font-semibold text-gray-900"><?php echo $current_path ? 'Programs' : 'All Programs'; ?></h2>
                                <p class="text-xs text-gray-500">Manage and organize your programs</p>
                            </div>
                        </div>

                        <div class="p-6" id="programsContainer">
                            <?php if (empty($programs)): ?>
                                <div class="text-center py-12" id="emptyState">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <span class="dashicons dashicons-welcome-learn-more text-gray-400 text-3xl"></span>
                                    </div>
                                    <h4 class="text-lg font-semibold text-gray-900 mb-2">No Programs Found</h4>
                                    <p class="text-sm text-gray-500 mb-6">Get started by creating your first academic program.</p>
                                    <div class="flex flex-col sm:flex-row gap-3 justify-center items-center">
                                        <?php if (empty($all_faculties)): ?>
                                            <a href="<?php echo admin_url('admin.php?page=nds-faculties'); ?>"
                                                class="inline-flex items-center px-4 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium transition-colors">
                                                <span class="dashicons dashicons-networking mr-2 text-base"></span>Create Faculty First
                                            </a>
                                        <?php else: ?>
                                            <a href="#addProgramModal" class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition-colors">
                                                <span class="dashicons dashicons-plus-alt2 mr-2 text-base"></span>Create Program
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="overflow-x-auto">
                                    <table id="programsTable" class="min-w-full divide-y divide-gray-200 text-sm">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-700">Programme</th>
                                                <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-700">Faculty</th>
                                                <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-700">Courses</th>
                                                <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-700">Duration (months)</th>
                                                <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-700">Type</th>
                                                <th scope="col" class="px-4 py-3 text-right font-semibold text-gray-700">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-100">
                                            <?php foreach ($programs as $program): ?>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 align-top">
                                                        <div class="font-medium text-gray-900">
                                                            <?php echo esc_html($program['name']); ?>
                                                        </div>
                                                        <?php if (!empty($program['description'])): ?>
                                                            <div class="mt-1 text-xs text-gray-500 line-clamp-2">
                                                                <?php echo esc_html($program['description']); ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="px-4 py-3 align-top text-gray-700">
                                                        <?php echo esc_html($program['path_name'] ?? '—'); ?>
                                                    </td>
                                                    <td class="px-4 py-3 align-top text-gray-700">
                                                        <?php echo intval($program['course_count']); ?>
                                                    </td>
                                                    <td class="px-4 py-3 align-top text-gray-700">
                                                        <?php echo esc_html(isset($program['duration_months']) ? $program['duration_months'] : '12'); ?>
                                                    </td>
                                                    <td class="px-4 py-3 align-top text-gray-700">
                                                        <?php echo esc_html(ucfirst(isset($program['program_type']) ? $program['program_type'] : 'diploma')); ?>
                                                    </td>
                                                    <td class="px-4 py-3 align-top text-right">
                                                        <div class="inline-flex items-center gap-2">
                                                            <button type="button"
                                                                onclick="openAddCourseModal(<?php echo $program['id']; ?>, '<?php echo esc_js($program['name']); ?>')"
                                                                class="inline-flex items-center px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium">
                                                                <span class="dashicons dashicons-plus-alt2 mr-1 text-sm"></span>
                                                                Add Course
                                                            </button>
                                                            <a href="<?php echo admin_url('admin.php?page=nds-courses&program_id=' . $program['id']); ?>"
                                                               class="inline-flex items-center px-3 py-1.5 rounded-lg bg-gray-600 hover:bg-gray-700 text-white text-xs font-medium">
                                                                <span class="dashicons dashicons-book mr-1 text-sm"></span>
                                                                Manage
                                                            </a>
                                                            <button type="button"
                                                                onclick="confirmDelete(<?php echo $program['id']; ?>, '<?php echo esc_js($program['name']); ?>')"
                                                                class="inline-flex items-center px-2 py-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-700 text-xs font-medium">
                                                                <span class="dashicons dashicons-trash text-sm"></span>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">

                        <!-- Recent Programs -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="px-5 py-4 border-b border-gray-100">
                                <h2 class="text-sm font-semibold text-gray-900">Recent Programs</h2>
                                <p class="text-xs text-gray-500">Latest programs created</p>
                            </div>
                            <div class="p-4">
                                <?php if (empty($recent_programs)): ?>
                                    <p class="text-gray-500 text-sm">No recent programs</p>
                                <?php else: ?>
                                    <div class="space-y-3">
                                        <?php foreach ($recent_programs as $program): ?>
                                            <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                                <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                                    <span class="dashicons dashicons-welcome-learn-more text-emerald-600 text-base"></span>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <h5 class="text-sm font-medium text-gray-900 truncate"><?php echo esc_html($program['name']); ?></h5>
                                                    <p class="text-xs text-gray-500"><?php echo date('M j, Y', strtotime($program['created_at'])); ?></p>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="px-5 py-4 border-b border-gray-100">
                                <h2 class="text-sm font-semibold text-gray-900">Quick Actions</h2>
                                <p class="text-xs text-gray-500">Navigate to related sections</p>
                            </div>
                            <div class="p-4 space-y-3">
                                <a href="<?php echo admin_url('admin.php?page=nds-courses'); ?>"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                                    <span class="dashicons dashicons-book mr-2 text-base"></span>Go to Course Management
                                </a>
                                <a href="<?php echo admin_url('admin.php?page=nds-faculties'); ?>"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors text-sm font-medium">
                                    <span class="dashicons dashicons-networking mr-2 text-base"></span>Manage Faculties
                                </a>
                                <button type="button" onclick="exportPrograms()"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors text-sm font-medium">
                                    <span class="dashicons dashicons-download mr-2 text-base"></span>Export Data
                                </button>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

            <!-- Add Program Modal -->
            <div id="addProgramModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999;" onclick="if(event.target === this) closeModal();">
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation();">
                        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                            <div class="flex items-center">
                                <span class="dashicons dashicons-plus-alt2 text-blue-600 mr-3 text-xl"></span>
                                <h2 class="text-xl font-semibold text-gray-900">Add New Program</h2>
                            </div>
                            <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors p-2 rounded-lg hover:bg-gray-100">
                                <span class="dashicons dashicons-no-alt text-xl"></span>
                                </button>
                            </div>
                        <div class="p-6">

                            <?php
                            // Use the same form template as the edit program page
                            echo program_form('add', null, null);
                            ?>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Course Modal -->
            <div id="addCourseModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden" style="display: none;" onclick="if(event.target === this) closeAddCourseModal();">
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation();">
                        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                            <div class="flex items-center min-w-0 flex-1">
                                <span class="dashicons dashicons-plus-alt2 text-blue-600 mr-3 text-xl flex-shrink-0"></span>
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-xl font-semibold text-gray-900">Add Course</h3>
                                    <p class="text-sm text-gray-500 mt-0.5 truncate">to <span id="modal-program-name" class="font-medium text-gray-700"></span></p>
                                </div>
                            </div>
                            <button type="button" onclick="closeAddCourseModal()" class="text-gray-400 hover:text-gray-600 transition-colors p-2 rounded-lg hover:bg-gray-100 flex-shrink-0 ml-3">
                                <span class="dashicons dashicons-no-alt text-xl"></span>
                            </button>
                        </div>
                        <div class="p-6">
                            <form method="POST" action="javascript:void(0);" onsubmit="event.preventDefault(); submitCourseForm(this);">
                                <?php wp_nonce_field('nds_course_nonce', 'nds_course_nonce'); ?>
                                <input type="hidden" name="action" value="nds_create_course_ajax">
                                <?php
                                // Get program ID from the modal trigger - will be set by JavaScript
                                echo course_form('add', null, null, null, true);
                                ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Hidden Delete Form -->
            <form id="deleteProgramForm" method="POST" action="<?php echo admin_url('admin-post.php'); ?>" style="display: none;">
                <input type="hidden" name="action" value="nds_delete_program">
                <input type="hidden" name="program_id" id="delete_program_id">
                <?php wp_nonce_field('nds_delete_program_nonce', 'nds_delete_program_nonce'); ?>
            </form>

        </div>

        <!-- Custom Styles -->
        <style>
            .line-clamp-2 {
                display: -webkit-box;
                -webkit-line-clamp: 2;
                line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }

            .group:hover .group-hover\:opacity-100 {
                opacity: 1;
            }

            .program-card {
                transition: all 0.2s ease-in-out;
            }

            .program-card:hover {
                transform: translateY(-1px);
            }
        </style>

        <!-- Include Auto-Select Helper -->
        <script src="<?php echo plugin_dir_url(__FILE__); ?>modal-auto-select.js"></script>

        <script>
            function openProgramModal() {
                const modal = document.getElementById('addProgramModal');
                if (modal) {
                    modal.style.display = 'block';
                    document.body.style.overflow = 'hidden';
                }
            }

            function closeModal() {
                const modal = document.getElementById('addProgramModal');
                if (modal) {
                    modal.style.display = 'none';
                    document.body.style.overflow = '';
                }
            }

            function confirmDelete(programId, programName) {
                if (confirm(`Are you sure you want to delete "${programName}"? This will also remove all associated courses.`)) {
                    document.getElementById('delete_program_id').value = programId;
                    document.getElementById('deleteProgramForm').submit();
                }
            }

            function exportPrograms() {
                alert('Export functionality will be implemented soon.');
            }

            // Modal trigger
            document.addEventListener('DOMContentLoaded', function() {
                // Handle links with href="#addProgramModal"
                const modalLinks = document.querySelectorAll('a[href="#addProgramModal"]');
                modalLinks.forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        openProgramModal();
                    });
                });

                // Handle button with id="addProgramButton"
                const addProgramButton = document.getElementById('addProgramButton');
                if (addProgramButton) {
                    addProgramButton.addEventListener('click', function(e) {
                        e.preventDefault();
                        openProgramModal();
                    });
                }

                // AJAX form submission
                const addProgramForm = document.getElementById('addProgramForm');
                if (addProgramForm) {
                    addProgramForm.addEventListener('submit', function(e) {
                        e.preventDefault();
                        
                        const submitBtn = document.getElementById('saveProgramBtn');
                        const saveText = submitBtn.querySelector('.save-text');
                        const loadingText = submitBtn.querySelector('.loading-text');
                        
                        // Show loading state
                        saveText.classList.add('hidden');
                        loadingText.classList.remove('hidden');
                        submitBtn.disabled = true;
                        
                        // Get form data
                        const formData = new FormData(addProgramForm);
                        formData.append('action', 'nds_add_program_ajax');
                        
                        // Submit via AJAX
                        const ajaxUrl = typeof ajaxurl !== 'undefined' ? ajaxurl : '<?php echo admin_url('admin-ajax.php'); ?>';
                        fetch(ajaxUrl, {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Close modal
                                closeModal();
                                
                                // Show success message
                                if (typeof NDSNotification !== 'undefined') {
                                    NDSNotification.success('Program created successfully!');
                                } else {
                                    alert('Program created successfully!');
                                }
                                
                                // Reload programs
                                reloadPrograms(data.data.faculty_id);
                            } else {
                                // Show error message
                                const errorMsg = data.data.message || 'Error creating program';
                                if (typeof NDSNotification !== 'undefined') {
                                    NDSNotification.error(errorMsg);
                                } else {
                                    alert(errorMsg);
                                }
                                
                                // Reset button
                                saveText.classList.remove('hidden');
                                loadingText.classList.add('hidden');
                                submitBtn.disabled = false;
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            if (typeof NDSNotification !== 'undefined') {
                                NDSNotification.error('An error occurred. Please try again.');
                            } else {
                                alert('An error occurred. Please try again.');
                            }
                            
                            // Reset button
                            saveText.classList.remove('hidden');
                            loadingText.classList.add('hidden');
                            submitBtn.disabled = false;
                        });
                    });
                }
            });

            // Function to reload programs via AJAX
            function reloadPrograms(facultyId) {
                const programsContainer = document.getElementById('programsContainer');
                const programsGrid = document.getElementById('programsGrid');
                const programsList = document.getElementById('programsList');
                const emptyState = document.getElementById('emptyState');
                
                if (!programsContainer) {
                    // If container not found, reload the page
                    window.location.reload();
                    return;
                }
                
                // Show loading state
                programsContainer.innerHTML = '<div class="text-center py-12"><i class="fas fa-spinner fa-spin text-4xl text-gray-400"></i><p class="mt-4 text-gray-600">Loading programs...</p></div>';
                
                // Build URL with faculty_id if provided
                const url = new URL(window.location.href);
                if (facultyId) {
                    url.searchParams.set('faculty_id', facultyId);
                } else {
                    url.searchParams.delete('faculty_id');
                }
                
                // Fetch updated page
                fetch(url.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    // Create a temporary container to parse the HTML
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = html;
                    
                    // Find the programs container in the new HTML
                    const newContainer = tempDiv.querySelector('#programsContainer');
                    
                    if (newContainer) {
                        // Replace with new content
                        programsContainer.outerHTML = newContainer.outerHTML;
                        
                        // Re-initialize modal triggers for new content
                        const modalLinks = document.querySelectorAll('a[href="#addProgramModal"]');
                        modalLinks.forEach(link => {
                            link.addEventListener('click', function(e) {
                                e.preventDefault();
                                openProgramModal();
                            });
                        });

                        // Re-initialize button trigger
                        const addProgramButton = document.getElementById('addProgramButton');
                        if (addProgramButton) {
                            addProgramButton.addEventListener('click', function(e) {
                                e.preventDefault();
                                openProgramModal();
                            });
                        }
                    } else {
                        // Fallback: reload page
                        window.location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error reloading programs:', error);
                    // Fallback: reload page
                    window.location.reload();
                });
            }

            // Function to show notifications (using SweetAlert2 if available)
            function showNotification(message, type) {
                if (typeof NDSNotification !== 'undefined') {
                    // Use SweetAlert2
                    switch(type) {
                        case 'success':
                            NDSNotification.success(message);
                            break;
                        case 'error':
                            NDSNotification.error(message);
                            break;
                        case 'warning':
                            NDSNotification.warning(message);
                            break;
                        default:
                            NDSNotification.info(message);
                    }
                } else {
                    // Fallback to simple alert
                    alert(message);
                }
            }
        </script>

        <script>
            // Add Course Modal Functions
            function openAddCourseModal(programId, programName) {
                const modal = document.getElementById('addCourseModal');
                if (modal) {
                    // Ensure modal is attached directly to <body> so it centers over full viewport
                    if (modal.parentElement !== document.body) {
                        document.body.appendChild(modal);
                    }

                    // Set program name in modal header
                    const programNameSpan = modal.querySelector('#modal-program-name');
                    if (programNameSpan) {
                        programNameSpan.textContent = programName;
                    }

                    // Set program ID in the form
                    const form = modal.querySelector('form');
                    if (form) {
                        // Remove any existing program_id input
                        const existingInput = form.querySelector('input[name="program_id"]');
                        if (existingInput) {
                            existingInput.remove();
                        }
                        
                        // Hide program selection dropdown if it exists
                        const programSelect = form.querySelector('select[name="program_id"]');
                        if (programSelect) {
                            const selectContainer = programSelect.closest('.flex.flex-col');
                            if (selectContainer) {
                                selectContainer.style.display = 'none';
                            }
                        }
                        
                        // Add hidden input with program_id
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'program_id';
                        hiddenInput.value = programId;
                        form.appendChild(hiddenInput);
                    }

                    modal.classList.remove('hidden');
                    modal.classList.add('flex', 'items-center', 'justify-center');
                    modal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                }
            }

            function closeAddCourseModal() {
                const modal = document.getElementById('addCourseModal');
                if (modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex', 'items-center', 'justify-center');
                    modal.style.display = 'none';
                    document.body.style.overflow = '';
                    
                    // Reset form
                    const form = modal.querySelector('form');
                    if (form) {
                        form.reset();
                        
                        // Remove the hidden program_id input we added
                        const hiddenInput = form.querySelector('input[name="program_id"]');
                        if (hiddenInput) {
                            hiddenInput.remove();
                        }
                        
                        // Show program selection dropdown again
                        const programSelect = form.querySelector('select[name="program_id"]');
                        if (programSelect) {
                            const selectContainer = programSelect.closest('.flex.flex-col');
                            if (selectContainer) {
                                selectContainer.style.display = '';
                            }
                        }
                    }
                    
                    // Clear program name
                    const programNameSpan = modal.querySelector('#modal-program-name');
                    if (programNameSpan) {
                        programNameSpan.textContent = '';
                    }
                }
            }

            // Handle Add Course Form Submission
            document.addEventListener('DOMContentLoaded', function() {
                // The form now uses onsubmit handler directly
            });
        </script>

        <script>
            // Course Form Submission
            function submitCourseForm(form) {
                const formData = new FormData(form);
                const submitBtn = form.querySelector('input[type="submit"]');
                const originalText = submitBtn.value;
                
                // Show loading state
                submitBtn.value = 'Creating...';
                 submitBtn.disabled = true;
                
                // Submit via AJAX
                const ajaxUrl = typeof ajaxurl !== 'undefined' ? ajaxurl : '<?php echo admin_url('admin-ajax.php'); ?>';
                
                fetch(ajaxUrl, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Close modal
                        closeAddCourseModal();
                        
                        // Show success message
                        if (typeof NDSNotification !== 'undefined') {
                            NDSNotification.success('Course created successfully!');
                        } else {
                            alert('Course created successfully!');
                        }
                        
                        // Reload page to show new course
                        setTimeout(() => {
                            window.location.reload();
                        }, 500);
                    } else {
                        // Show error message
                        const errorMsg = data.data && data.data.message ? data.data.message : (data.data || 'Error creating course');
                        if (typeof NDSNotification !== 'undefined') {
                            NDSNotification.error(errorMsg);
                        } else {
                            alert('Error: ' + errorMsg);
                        }
                        
                        // Reset button
                        submitBtn.value = originalText;
                        submitBtn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (typeof NDSNotification !== 'undefined') {
                        NDSNotification.error('An error occurred. Please try again.');
                    } else {
                        alert('An error occurred. Please try again.');
                    }
                    
                    // Reset button
                    submitBtn.value = originalText;
                    submitBtn.disabled = false;
                });
            }
        </script>

        <script>
            // Program Form Submission
            function submitProgramForm(form) {
                const formData = new FormData(form);
                const submitBtn = form.querySelector('input[type="submit"]');
                const originalText = submitBtn.value;
                
                // Show loading state
                submitBtn.value = 'Saving...';
                submitBtn.disabled = true;
                
                // Submit via AJAX
                const ajaxUrl = typeof ajaxurl !== 'undefined' ? ajaxurl : '<?php echo admin_url('admin-ajax.php'); ?>';
                
                fetch(ajaxUrl, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Close modal
                        closeModal();
                        
                        // Show success message
                        if (typeof NDSNotification !== 'undefined') {
                            NDSNotification.success('Program created successfully!');
                        } else {
                            alert('Program created successfully!');
                        }
                        
                        // Reload page to show new program
                        setTimeout(() => {
                            window.location.reload();
                        }, 500);
                    } else {
                        // Show error message
                        const errorMsg = data.data && data.data.message ? data.data.message : (data.data || 'Error creating program');
                        if (typeof NDSNotification !== 'undefined') {
                            NDSNotification.error(errorMsg);
                        } else {
                            alert('Error: ' + errorMsg);
                        }
                        
                        // Reset button
                        submitBtn.value = originalText;
                        submitBtn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (typeof NDSNotification !== 'undefined') {
                        NDSNotification.error('An error occurred. Please try again.');
                    } else {
                        alert('An error occurred. Please try again.');
                    }
                    
                    // Reset button
                    submitBtn.value = originalText;
                    submitBtn.disabled = false;
                });
            }
        </script>
    <?php
} ?>