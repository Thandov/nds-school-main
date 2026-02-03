<?php
// Prevent direct access - this file should only be included by WordPress
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Ensure WordPress functions are available
if (!function_exists('current_user_can')) {
    return;
}

// Modern Learner Management with Tailwind CSS
function nds_learner_management_page() {
    if (!current_user_can('manage_options')) { wp_die('Unauthorized'); }

    global $wpdb;
    $students_table = $wpdb->prefix . 'nds_students';
    $enrollments_table  = $wpdb->prefix . 'nds_student_enrollments';
    $courses_table      = $wpdb->prefix . 'nds_courses';
    $academic_years_tbl = $wpdb->prefix . 'nds_academic_years';
    $semesters_tbl      = $wpdb->prefix . 'nds_semesters';

    // Handle delete action
    if (isset($_GET['delete']) && !empty($_GET['delete'])) {
        $delete_id = intval($_GET['delete']);
        // Add delete functionality if needed
    }

    // Get filter parameters
    $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
    $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
    $intake_year = isset($_GET['intake_year']) ? intval($_GET['intake_year']) : '';
    $intake_semester = isset($_GET['intake_semester']) ? sanitize_text_field($_GET['intake_semester']) : '';

    // Build query
    $where_clause = "1=1";
    $params = array();

    if ($status_filter) {
        $where_clause .= " AND status = %s";
        $params[] = $status_filter;
    }

    if ($search) {
        $where_clause .= " AND (first_name LIKE %s OR last_name LIKE %s OR email LIKE %s OR student_number LIKE %s)";
        $search_term = '%' . $search . '%';
        $params = array_merge($params, [$search_term, $search_term, $search_term, $search_term]);
    }

    if ($intake_year) {
        $where_clause .= " AND intake_year = %d";
        $params[] = $intake_year;
    }

    if ($intake_semester) {
        $where_clause .= " AND intake_semester = %s";
        $params[] = $intake_semester;
    }

    // Get learners with optional primary intake snapshot (year/semester) still on students
    $query = "SELECT * FROM {$students_table} WHERE {$where_clause} ORDER BY created_at DESC";
    $learners = $params ? $wpdb->get_results($wpdb->prepare($query, $params), ARRAY_A) : $wpdb->get_results($query, ARRAY_A);

    // Get statistics
    $total_learners = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$students_table}");
    $active_learners = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$students_table} WHERE status = 'active'");
    $prospect_learners = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$students_table} WHERE status = 'prospect'");
    $inactive_learners = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$students_table} WHERE status = 'inactive'");

    // Get unique intake years for filter
    $intake_years = $wpdb->get_col("SELECT DISTINCT intake_year FROM {$students_table} WHERE intake_year IS NOT NULL ORDER BY intake_year DESC");

    // Handle success/error messages
    $message = '';
    $message_type = '';
    
    if (isset($_GET['success'])) {
        $message_type = 'success';
        switch ($_GET['success']) {
            case 'bulk_delete':
                $count = isset($_GET['count']) ? intval($_GET['count']) : 0;
                $message = "Successfully deleted {$count} learner(s)";
                break;
            case 'bulk_status':
                $count = isset($_GET['count']) ? intval($_GET['count']) : 0;
                $status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
                $message = "Successfully changed status of {$count} learner(s) to {$status}";
                break;
            case 'deleted':
                $message = "Learner successfully deleted";
                break;
        }
    } elseif (isset($_GET['error'])) {
        $message_type = 'error';
        switch ($_GET['error']) {
            case 'no_ids':
            case 'invalid_ids':
                $message = "Invalid learner IDs provided";
                break;
            case 'no_id':
            case 'invalid_id':
                $message = "Invalid learner ID";
                break;
            case 'delete_failed':
                $message = "Failed to delete learner";
                break;
            case 'missing_params':
                $message = "Missing required parameters";
                break;
            case 'invalid_status':
                $message = "Invalid status provided";
                break;
            case 'security_check_failed':
                $message = "Security verification failed. Please try again.";
                break;
            default:
                $message = "An error occurred";
        }
    }

    ?>
    <div class="nds-tailwind-wrapper bg-gray-50 min-h-screen" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
        <!-- Header -->
        <div class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-green-600 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-users text-white text-2xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Learner Management</h1>
                            <p class="text-sm text-gray-600 mt-1">Manage learners, track progress, and monitor enrollment</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <a href="<?php echo admin_url('admin-post.php?action=nds_seed_students'); ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-all duration-200 shadow-sm hover:shadow-md">
                            <i class="fas fa-seedling mr-2"></i>Seed Dataz
                        </a>
                        <a href="<?php echo admin_url('admin.php?page=nds-add-learner'); ?>" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 px-6 rounded-lg flex items-center gap-2 transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                            <i class="fas fa-user-plus"></i>
                            Add Learner
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Breadcrumb Navigation -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-4">
            <nav class="flex items-center space-x-2 text-sm text-gray-600">
                <a href="<?php echo admin_url('admin.php?page=nds-academy'); ?>" class="hover:text-green-600 transition-colors flex items-center">
                    <i class="fas fa-home mr-1"></i>NDS Academy
                </a>
                <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
                <span class="text-gray-900 font-medium">Learner Management</span>
            </nav>
        </div>

        <!-- Success/Error Messages -->
        <?php if ($message): ?>
            <div id="messageAlert" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-6 p-4 rounded-lg shadow-md <?php echo $message_type === 'success' ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'; ?> flex justify-between items-center">
                <div class="flex items-center">
                    <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?> mr-3 text-xl"></i>
                    <span class="font-medium"><?php echo esc_html($message); ?></span>
                </div>
                <button onclick="document.getElementById('messageAlert').remove()" class="text-<?php echo $message_type === 'success' ? 'green' : 'red'; ?>-700 hover:text-<?php echo $message_type === 'success' ? 'green' : 'red'; ?>-900">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <script>
                setTimeout(function() {
                    const alert = document.getElementById('messageAlert');
                    if (alert) {
                        alert.style.transition = 'opacity 0.5s';
                        alert.style.opacity = '0';
                        setTimeout(() => alert.remove(), 500);
                    }
                }, 5000);
            </script>
        <?php endif; ?>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Learners</p>
                            <p class="mt-2 text-2xl font-semibold text-gray-900">
                                <?php echo number_format($total_learners); ?>
                            </p>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center">
                            <i class="fas fa-users text-green-600 text-xl"></i>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-gray-500">
                        Active: <span class="font-medium text-gray-800"><?php echo number_format($active_learners); ?></span>
                        (<?php echo $total_learners > 0 ? round(($active_learners / $total_learners) * 100) : 0; ?>% of learners)
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Active</p>
                            <p class="mt-2 text-2xl font-semibold text-gray-900">
                                <?php echo number_format($active_learners); ?>
                            </p>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                            <i class="fas fa-user-check text-blue-600 text-xl"></i>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-gray-500">
                        <?php echo $total_learners > 0 ? round(($active_learners / $total_learners) * 100) : 0; ?>% of all learners are active.
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Prospects</p>
                            <p class="mt-2 text-2xl font-semibold text-gray-900">
                                <?php echo number_format($prospect_learners); ?>
                            </p>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-yellow-50 flex items-center justify-center">
                            <i class="fas fa-clock text-yellow-600 text-xl"></i>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-gray-500">
                        Learners awaiting enrollment confirmation.
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Inactive</p>
                            <p class="mt-2 text-2xl font-semibold text-gray-900">
                                <?php echo number_format($inactive_learners); ?>
                            </p>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center">
                            <i class="fas fa-user-times text-red-600 text-xl"></i>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-gray-500">
                        Learners currently not active in the system.
                    </p>
                </div>
            </div>
            <!-- Filters -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 items-end">
                    <input type="hidden" name="page" value="nds-all-learners">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input type="text" name="search" value="<?php echo esc_attr($search); ?>" placeholder="Name, email, or student number..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            <option value="">All Status</option>
                            <option value="active" <?php selected($status_filter, 'active'); ?>>Active</option>
                            <option value="prospect" <?php selected($status_filter, 'prospect'); ?>>Prospect</option>
                            <option value="inactive" <?php selected($status_filter, 'inactive'); ?>>Inactive</option>
                            <option value="graduated" <?php selected($status_filter, 'graduated'); ?>>Graduated</option>
                            <option value="alumni" <?php selected($status_filter, 'alumni'); ?>>Alumni</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Intake Year</label>
                        <select name="intake_year" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            <option value="">All Years</option>
                            <?php foreach ($intake_years as $year): ?>
                                <option value="<?php echo $year; ?>" <?php selected($intake_year, $year); ?>><?php echo $year; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Semester</label>
                        <select name="intake_semester" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            <option value="">All Semesters</option>
                            <option value="January" <?php selected($intake_semester, 'January'); ?>>January</option>
                            <option value="June" <?php selected($intake_semester, 'June'); ?>>June</option>
                            <option value="September" <?php selected($intake_semester, 'September'); ?>>September</option>
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition-all duration-200 flex items-center gap-2 shadow-md hover:shadow-lg">
                            <i class="fas fa-search"></i>Filter
                        </button>
                        <a href="<?php echo admin_url('admin.php?page=nds-all-learners'); ?>" class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-lg transition-all duration-200 flex items-center gap-2 shadow-md hover:shadow-lg">
                            <i class="fas fa-times"></i>Clear
                        </a>
                    </div>
                </form>
            </div>

            <!-- Bulk Actions Bar -->
            <div id="bulkActionsBar" class="bg-green-600 rounded-xl shadow-lg border border-green-700 p-4 mb-6 hidden transition-all duration-200">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-4">
                        <span class="text-white font-medium">
                            <span id="selectedCount">0</span> learner(s) selected
                        </span>
                        <button onclick="clearSelection()" class="text-white hover:text-green-100 underline">
                            Clear Selection
                        </button>
                    </div>
                    <div class="flex items-center gap-2">
                        <select id="bulkAction" class="px-3 py-2 border border-white rounded-lg focus:outline-none focus:ring-2 focus:ring-white bg-white text-gray-900">
                            <option value="">Select Action</option>
                            <option value="activate">Set Status: Active</option>
                            <option value="deactivate">Set Status: Inactive</option>
                            <option value="prospect">Set Status: Prospect</option>
                            <option value="graduated">Set Status: Graduated</option>
                            <option value="export">Export Selected</option>
                            <option value="revert_to_applicant">Revert to Applicant</option>
                            <option value="delete">Delete Selected</option>
                        </select>
                        <button onclick="applyBulkAction()" class="bg-white text-green-600 hover:bg-green-50 font-semibold py-2 px-6 rounded-lg transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                            <i class="fas fa-check mr-2"></i>Apply
                        </button>
                    </div>
                </div>
            </div>

            <!-- Learners List -->
            <?php if ($learners): ?>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-semibold text-gray-900">
                                <i class="fas fa-list text-green-600 mr-3"></i>Learners (<?php echo count($learners); ?>)
                            </h3>
                            <div class="flex items-center gap-4">
                                <div class="text-sm text-gray-600">
                                    Showing <?php echo count($learners); ?> of <?php echo $total_learners; ?> learners
                                </div>
                                <button onclick="exportTable()" class="text-green-600 hover:text-green-700 font-medium text-sm transition-colors p-2 rounded hover:bg-green-50">
                                    <i class="fas fa-download mr-1"></i>Export
                                </button>
                                <button onclick="printTable()" class="text-green-600 hover:text-green-700 font-medium text-sm transition-colors p-2 rounded hover:bg-green-50">
                                    <i class="fas fa-print mr-1"></i>Print
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full" id="learnersTable">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left">
                                        <input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)" 
                                               class="w-4 h-4 text-green-600 bg-gray-100 border-gray-300 rounded focus:ring-green-500 focus:ring-2 cursor-pointer">
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Learner</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($learners as $learner):
                                    // Safely get all values with defaults
                                    $learner_id = isset($learner['id']) ? intval($learner['id']) : 0;
                                    $first_name = isset($learner['first_name']) ? $learner['first_name'] : '';
                                    $last_name = isset($learner['last_name']) ? $learner['last_name'] : '';
                                    $full_name = trim($first_name . ' ' . $last_name);
                                    $email = isset($learner['email']) ? $learner['email'] : '';
                                    $phone = isset($learner['phone']) ? $learner['phone'] : '';
                                    $student_number = isset($learner['student_number']) ? $learner['student_number'] : '';
                                    $status = isset($learner['status']) ? $learner['status'] : 'prospect';
                                    $intake_year = isset($learner['intake_year']) && $learner['intake_year'] !== null && $learner['intake_year'] !== '' ? $learner['intake_year'] : null;
                                    $intake_semester = isset($learner['intake_semester']) && $learner['intake_semester'] !== null && $learner['intake_semester'] !== '' ? $learner['intake_semester'] : null;
                                    
                                    $status_color = match($status) {
                                        'active' => 'bg-green-100 text-green-800',
                                        'prospect' => 'bg-yellow-100 text-yellow-800',
                                        'inactive' => 'bg-red-100 text-red-800',
                                        'graduated' => 'bg-blue-100 text-blue-800',
                                        'alumni' => 'bg-purple-100 text-purple-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                ?>
                                    <tr class="hover:bg-gray-50 cursor-pointer learner-row" data-learner-id="<?php echo $learner_id; ?>">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="checkbox" class="learner-checkbox w-4 h-4 text-green-600 bg-gray-100 border-gray-300 rounded focus:ring-green-500 focus:ring-2 cursor-pointer" 
                                                   value="<?php echo $learner_id; ?>"
                                                   data-name="<?php echo esc_attr($full_name); ?>"
                                                   data-email="<?php echo esc_attr($email); ?>"
                                                   data-status="<?php echo esc_attr($status); ?>"
                                                   onchange="updateBulkActions()">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="w-10 h-10 bg-green-50 rounded-full flex items-center justify-center mr-3">
                                                    <i class="fas fa-user text-green-600"></i>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">
                                                        <?php echo esc_html($full_name ?: 'N/A'); ?>
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        <?php if ($student_number): ?>
                                                            #<?php echo esc_html($student_number); ?>
                                                        <?php else: ?>
                                                            <span class="text-gray-400">No ID</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900"><?php echo esc_html($email ?: 'N/A'); ?></div>
                                            <?php if ($phone): ?>
                                                <div class="text-sm text-gray-500"><?php echo esc_html($phone); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs font-medium rounded-full <?php echo $status_color; ?>">
                                                <?php echo esc_html(ucfirst($status)); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php
                                            // Display snapshot intake info; underlying truth is in enrollments/cohorts
                                            if ($intake_year !== null) {
                                                echo esc_html($intake_year);
                                                if ($intake_semester !== null) {
                                                    echo ' - ' . esc_html($intake_semester);
                                                }
                                            } else {
                                                echo 'N/A';
                                            }
                                            ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" onclick="event.stopPropagation();">
                                            <div class="flex items-center gap-2">
                                                <a href="<?php echo admin_url('admin.php?page=nds-edit-learner&id=' . $learner_id); ?>"
                                                   class="text-blue-600 hover:text-blue-900 transition-colors duration-200 p-1.5 rounded hover:bg-blue-50">
                                                    <i class="fas fa-edit mr-1"></i>Edit
                                                </a>
                                                <button onclick="deleteLearner(<?php echo $learner_id; ?>, '<?php echo esc_js($full_name ?: 'Unknown'); ?>')"
                                                        class="text-red-600 hover:text-red-900 transition-colors duration-200 p-1.5 rounded hover:bg-red-50">
                                                    <i class="fas fa-trash-alt mr-1"></i>Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <!-- Empty State -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12">
                    <div class="text-center">
                        <i class="fas fa-users text-6xl text-gray-300 mb-4"></i>
                        <h3 class="text-xl font-medium text-gray-900 mb-2">No Learners Found</h3>
                        <p class="text-gray-600 mb-6">
                            <?php if ($search || $status_filter || $intake_year || $intake_semester): ?>
                                No learners match your current filters. Try adjusting your search criteria.
                            <?php else: ?>
                                Get started by adding your first learner to the system.
                            <?php endif; ?>
                        </p>
                        <?php if (!$search && !$status_filter && !$intake_year && !$intake_semester): ?>
                            <a href="<?php echo admin_url('admin.php?page=nds-add-learner'); ?>" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg flex items-center gap-2 transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                                <i class="fas fa-user-plus"></i>
                                Add First Learner
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    // Checkbox and Bulk Actions Management
    function toggleSelectAll(checkbox) {
        const checkboxes = document.querySelectorAll('.learner-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = checkbox.checked;
        });
        updateBulkActions();
    }

    function updateBulkActions() {
        const checkboxes = document.querySelectorAll('.learner-checkbox:checked');
        const bulkActionsBar = document.getElementById('bulkActionsBar');
        const selectedCount = document.getElementById('selectedCount');
        const selectAll = document.getElementById('selectAll');
        
        selectedCount.textContent = checkboxes.length;
        
        if (checkboxes.length > 0) {
            bulkActionsBar.classList.remove('hidden');
        } else {
            bulkActionsBar.classList.add('hidden');
        }

        // Update "Select All" checkbox state
        const allCheckboxes = document.querySelectorAll('.learner-checkbox');
        selectAll.checked = checkboxes.length === allCheckboxes.length && allCheckboxes.length > 0;
        selectAll.indeterminate = checkboxes.length > 0 && checkboxes.length < allCheckboxes.length;
    }

    function clearSelection() {
        document.querySelectorAll('.learner-checkbox').forEach(cb => cb.checked = false);
        document.getElementById('selectAll').checked = false;
        updateBulkActions();
    }

    function getSelectedLearners() {
        const selected = [];
        document.querySelectorAll('.learner-checkbox:checked').forEach(cb => {
            selected.push({
                id: cb.value,
                name: cb.dataset.name,
                email: cb.dataset.email,
                status: cb.dataset.status
            });
        });
        return selected;
    }

    function applyBulkAction() {
        const action = document.getElementById('bulkAction').value;
        const selected = getSelectedLearners();
        
        if (!action) {
            alert('Please select an action');
            return;
        }
        
        if (selected.length === 0) {
            alert('Please select at least one learner');
            return;
        }

        switch(action) {
            case 'delete':
                bulkDelete(selected);
                break;
            case 'activate':
                bulkStatusChange(selected, 'active');
                break;
            case 'deactivate':
                bulkStatusChange(selected, 'inactive');
                break;
            case 'prospect':
                bulkStatusChange(selected, 'prospect');
                break;
            case 'graduated':
                bulkStatusChange(selected, 'graduated');
                break;
            case 'export':
                exportSelected(selected);
                break;
            case 'revert_to_applicant':
                bulkRevertToApplicant(selected);
                break;
            default:
                alert('Action not implemented');
        }
    }

    function bulkDelete(learners) {
        if (!confirm(`Are you sure you want to delete ${learners.length} learner(s)? This action cannot be undone.`)) {
            return;
        }
        
        const ids = learners.map(l => l.id);
        console.log('Bulk Delete - IDs:', ids);
        
        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?php echo admin_url('admin-post.php'); ?>';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'nds_bulk_delete_learners';
        form.appendChild(actionInput);
        
        const idsInput = document.createElement('input');
        idsInput.type = 'hidden';
        idsInput.name = 'learner_ids';
        idsInput.value = JSON.stringify(ids);
        form.appendChild(idsInput);
        
        // Add nonce for security
        const nonceInput = document.createElement('input');
        nonceInput.type = 'hidden';
        nonceInput.name = 'nds_bulk_nonce';
        nonceInput.value = '<?php echo wp_create_nonce('nds_bulk_action'); ?>';
        form.appendChild(nonceInput);
        
        console.log('Submitting bulk delete form...');
        document.body.appendChild(form);
        form.submit();
    }

    function bulkStatusChange(learners, newStatus) {
        if (!confirm(`Change status of ${learners.length} learner(s) to "${newStatus}"?`)) {
            return;
        }
        
        const ids = learners.map(l => l.id);
        
        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?php echo admin_url('admin-post.php'); ?>';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'nds_bulk_status_change';
        form.appendChild(actionInput);
        
        const idsInput = document.createElement('input');
        idsInput.type = 'hidden';
        idsInput.name = 'learner_ids';
        idsInput.value = JSON.stringify(ids);
        form.appendChild(idsInput);
        
        const statusInput = document.createElement('input');
        statusInput.type = 'hidden';
        statusInput.name = 'new_status';
        statusInput.value = newStatus;
        form.appendChild(statusInput);
        
        // Add nonce for security
        const nonceInput = document.createElement('input');
        nonceInput.type = 'hidden';
        nonceInput.name = 'nds_bulk_nonce';
        nonceInput.value = '<?php echo wp_create_nonce('nds_bulk_action'); ?>';
        form.appendChild(nonceInput);
        
        document.body.appendChild(form);
        form.submit();
    }

    function exportSelected(learners) {
        let csv = 'Student Number,First Name,Last Name,Email,Phone,Status,Intake Year,Intake Semester\n';
        
        learners.forEach(learner => {
            const row = document.querySelector(`tr[data-learner-id="${learner.id}"]`);
            const studentNumber = row.querySelector('.text-gray-500').textContent.replace('#', '');
            const email = learner.email;
            const phone = row.querySelectorAll('.text-gray-500')[1]?.textContent || '';
            const status = learner.status;
            const intakeYear = row.querySelectorAll('td')[5]?.querySelector('div')?.textContent || '';
            const intakeSemester = row.querySelectorAll('td')[5]?.querySelector('.text-xs')?.textContent || '';
            
            csv += `"${studentNumber}","${learner.name.split(' ')[0]}","${learner.name.split(' ').slice(1).join(' ')}","${email}","${phone}","${status}","${intakeYear}","${intakeSemester}"\n`;
        });
        
        downloadCSV(csv, `learners_export_${new Date().toISOString().split('T')[0]}.csv`);
    }

    function exportTable() {
        const learners = [];
        document.querySelectorAll('#learnersTable tbody tr').forEach(row => {
            const checkbox = row.querySelector('.learner-checkbox');
            if (checkbox) {
                learners.push({
                    id: checkbox.value,
                    name: checkbox.dataset.name,
                    email: checkbox.dataset.email,
                    status: checkbox.dataset.status
                });
            }
        });
        
        exportSelected(learners);
    }

    function downloadCSV(csv, filename) {
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    }

    function printTable() {
        window.print();
    }

    // Make entire learner row clickable to open learner dashboard
    document.addEventListener('DOMContentLoaded', function() {
        updateBulkActions();

        document.querySelectorAll('.learner-row').forEach(function (row) {
            row.addEventListener('click', function (e) {
                // Don't navigate if clicking on checkbox, edit, or delete buttons
                if (e.target.closest('.learner-checkbox') || 
                    e.target.closest('a[href*="nds-edit-learner"]') || 
                    e.target.closest('a[href*="nds-learner-dashboard"]') ||
                    e.target.closest('button[onclick*="deleteLearner"]')) {
                    return;
                }
                
                const learnerId = this.getAttribute('data-learner-id');
                if (!learnerId) return;
                window.location.href = '<?php echo admin_url('admin.php?page=nds-learner-dashboard&id='); ?>' + learnerId;
            });
        });
    });

    function deleteLearner(id, name) {
        if (confirm('Are you sure you want to delete learner "' + name + '"? This action cannot be undone.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?php echo admin_url('admin-post.php'); ?>';
            
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'nds_delete_learner';
            form.appendChild(actionInput);
            
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'learner_id';
            idInput.value = id;
            form.appendChild(idInput);
            
            // Add nonce for security
            const nonceInput = document.createElement('input');
            nonceInput.type = 'hidden';
            nonceInput.name = 'nds_delete_nonce';
            nonceInput.value = '<?php echo wp_create_nonce('nds_delete_learner'); ?>';
            form.appendChild(nonceInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    }

    function bulkRevertToApplicant(learners) {
        if (!confirm(`Revert ${learners.length} learner(s) back to applicants? This will delete their learner profiles and enrollments but keep their applications.`)) {
            return;
        }

        const ids = learners.map(l => l.id);

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?php echo admin_url('admin-post.php'); ?>';

        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'nds_bulk_revert_learners_to_applicants';
        form.appendChild(actionInput);

        const idsInput = document.createElement('input');
        idsInput.type = 'hidden';
        idsInput.name = 'learner_ids';
        idsInput.value = JSON.stringify(ids);
        form.appendChild(idsInput);

        const nonceInput = document.createElement('input');
        nonceInput.type = 'hidden';
        nonceInput.name = 'nds_bulk_nonce';
        nonceInput.value = '<?php echo wp_create_nonce('nds_bulk_action'); ?>';
        form.appendChild(nonceInput);

        document.body.appendChild(form);
        form.submit();
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + A to select all
        if ((e.ctrlKey || e.metaKey) && e.key === 'a' && e.target.tagName !== 'INPUT') {
            e.preventDefault();
            document.getElementById('selectAll').checked = true;
            toggleSelectAll(document.getElementById('selectAll'));
        }
        
        // Escape to clear selection
        if (e.key === 'Escape') {
            clearSelection();
        }
    });

    </script>

    <style>
    /* Checkbox indeterminate state styling */
    input[type="checkbox"]:indeterminate {
        background-color: #16a34a;
        border-color: #16a34a;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 16 16'%3e%3cpath stroke='white' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M4 8h8'/%3e%3c/svg%3e");
    }

    /* Smooth transitions */
    .learner-checkbox {
        transition: all 0.15s ease-in-out;
    }

    .learner-checkbox:checked {
        transform: scale(1.1);
    }

    /* Row hover effect when selected */
    tr:has(.learner-checkbox:checked) {
        background-color: #f0fdf4 !important;
    }

    /* Print styles */
    @media print {
        .bg-gray-50 { background: white !important; }
        .bg-white { background: white !important; }
        .shadow-sm, .shadow-md, .shadow-lg { box-shadow: none !important; }
        .border { border: 1px solid #e5e7eb !important; }
        .bg-green-600, .bg-gray-600 { display: none !important; }
        .hover\:bg-green-700, .hover\:bg-gray-700 { display: none !important; }
        /* Hide checkboxes and bulk actions */
        input[type="checkbox"], #bulkActionsBar, button { display: none !important; }
        /* Hide first column (checkbox column) */
        th:first-child, td:first-child { display: none !important; }
        /* Hide actions column */
        th:last-child, td:last-child { display: none !important; }
    }
    </style>
    <?php
}

// Backend handlers for bulk actions
add_action('admin_post_nds_bulk_delete_learners', 'nds_handle_bulk_delete_learners');
function nds_handle_bulk_delete_learners() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized access');
    }

    // Verify nonce
    if (!isset($_POST['nds_bulk_nonce']) || !wp_verify_nonce($_POST['nds_bulk_nonce'], 'nds_bulk_action')) {
        wp_redirect(admin_url('admin.php?page=nds-all-learners&error=security_check_failed'));
        exit;
    }

    if (!isset($_POST['learner_ids'])) {
        wp_redirect(admin_url('admin.php?page=nds-all-learners&error=no_ids'));
        exit;
    }

    global $wpdb;
    $students_table = $wpdb->prefix . 'nds_students';
    $ids = json_decode(stripslashes($_POST['learner_ids']), true);
    
    // Log the request
    error_log('NDS Bulk Delete - IDs received: ' . print_r($ids, true));
    
    if (!is_array($ids) || empty($ids)) {
        error_log('NDS Bulk Delete - Invalid IDs: not an array or empty');
        wp_redirect(admin_url('admin.php?page=nds-all-learners&error=invalid_ids'));
        exit;
    }

    $deleted_count = 0;
    $failed_count = 0;
    foreach ($ids as $id) {
        $id = intval($id);
        if ($id > 0) {
            $result = $wpdb->delete($students_table, ['id' => $id], ['%d']);
            if ($result !== false) {
                $deleted_count++;
                error_log("NDS Bulk Delete - Deleted learner ID: {$id}");
            } else {
                $failed_count++;
                error_log("NDS Bulk Delete - Failed to delete learner ID: {$id}. Error: " . $wpdb->last_error);
            }
        }
    }

    error_log("NDS Bulk Delete - Complete. Deleted: {$deleted_count}, Failed: {$failed_count}");
    wp_redirect(admin_url('admin.php?page=nds-all-learners&success=bulk_delete&count=' . $deleted_count));
    exit;
}

add_action('admin_post_nds_bulk_status_change', 'nds_handle_bulk_status_change');
function nds_handle_bulk_status_change() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized access');
    }

    // Verify nonce
    if (!isset($_POST['nds_bulk_nonce']) || !wp_verify_nonce($_POST['nds_bulk_nonce'], 'nds_bulk_action')) {
        wp_redirect(admin_url('admin.php?page=nds-all-learners&error=security_check_failed'));
        exit;
    }

    if (!isset($_POST['learner_ids']) || !isset($_POST['new_status'])) {
        wp_redirect(admin_url('admin.php?page=nds-all-learners&error=missing_params'));
        exit;
    }

    global $wpdb;
    $students_table = $wpdb->prefix . 'nds_students';
    $ids = json_decode(stripslashes($_POST['learner_ids']), true);
    $new_status = sanitize_text_field($_POST['new_status']);
    
    // Log the request
    error_log("NDS Bulk Status Change - IDs: " . print_r($ids, true) . ", New Status: {$new_status}");
    
    $allowed_statuses = ['active', 'prospect', 'inactive', 'graduated', 'alumni', 'withdrawn'];
    if (!in_array($new_status, $allowed_statuses)) {
        error_log("NDS Bulk Status Change - Invalid status: {$new_status}");
        wp_redirect(admin_url('admin.php?page=nds-all-learners&error=invalid_status'));
        exit;
    }

    if (!is_array($ids) || empty($ids)) {
        error_log('NDS Bulk Status Change - Invalid IDs: not an array or empty');
        wp_redirect(admin_url('admin.php?page=nds-all-learners&error=invalid_ids'));
        exit;
    }

    $updated_count = 0;
    $failed_count = 0;
    foreach ($ids as $id) {
        $id = intval($id);
        if ($id > 0) {
            $result = $wpdb->update(
                $students_table,
                ['status' => $new_status],
                ['id' => $id],
                ['%s'],
                ['%d']
            );
            if ($result !== false) {
                $updated_count++;
                error_log("NDS Bulk Status Change - Updated learner ID: {$id} to {$new_status}");
            } else {
                $failed_count++;
                error_log("NDS Bulk Status Change - Failed to update learner ID: {$id}. Error: " . $wpdb->last_error);
            }
        }
    }

    error_log("NDS Bulk Status Change - Complete. Updated: {$updated_count}, Failed: {$failed_count}");
    wp_redirect(admin_url('admin.php?page=nds-all-learners&success=bulk_status&status=' . $new_status . '&count=' . $updated_count));
    exit;
}

/**
 * Bulk revert learners back to applicants (implementation).
 * This is called via a small wrapper registered in admin-pages.php
 * so that it is always available on admin-post.php requests.
 *
 * For each learner ID:
 * - Find their most recent application (any status)
 * - Delete enrollments for that student
 * - Delete the student record
 * - Reset that application to submitted and unlink student_id
 */
function nds_handle_bulk_revert_learners_to_applicants_impl() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized access');
    }

    if (!isset($_POST['nds_bulk_nonce']) || !wp_verify_nonce($_POST['nds_bulk_nonce'], 'nds_bulk_action')) {
        wp_redirect(admin_url('admin.php?page=nds-all-learners&error=security_check_failed'));
        exit;
    }

    if (!isset($_POST['learner_ids'])) {
        wp_redirect(admin_url('admin.php?page=nds-all-learners&error=no_ids'));
        exit;
    }

    global $wpdb;
    $students_table     = $wpdb->prefix . 'nds_students';
    $apps_table         = $wpdb->prefix . 'nds_applications';
    $enrollments_table  = $wpdb->prefix . 'nds_student_enrollments';

    $ids_raw = isset($_POST['learner_ids']) ? $_POST['learner_ids'] : '';
    $ids = json_decode(stripslashes($ids_raw), true);

    if (!is_array($ids) || empty($ids)) {
        wp_redirect(admin_url('admin.php?page=nds-all-learners&error=invalid_ids'));
        exit;
    }

    $reverted_count = 0;

    foreach ($ids as $id) {
        $student_id = intval($id);
        if ($student_id <= 0) {
            continue;
        }

        // Find most recent accepted application linked to this student
        $application = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id, status FROM {$apps_table}
                 WHERE student_id = %d
                 ORDER BY id DESC
                 LIMIT 1",
                $student_id
            ),
            ARRAY_A
        );

        // Remove enrollments for this learner
        $deleted_enrollments = $wpdb->delete(
            $enrollments_table,
            array('student_id' => $student_id),
            array('%d')
        );

        // Delete learner profile
        $deleted_student = $wpdb->delete(
            $students_table,
            array('id' => $student_id),
            array('%d')
        );

        if ($deleted_student !== false && $application) {
            $update_result = $wpdb->update(
                $apps_table,
                array(
                    'student_id'  => null,
                    'status'      => 'submitted',
                    'decision_at' => null,
                    'decided_by'  => null,
                ),
                array('id' => $application['id']),
                null, // let $wpdb infer formats so NULL is handled correctly
                array('%d')
            );
        }

        if ($deleted_student !== false) {
            $reverted_count++;
        }
    }

    wp_redirect(admin_url('admin.php?page=nds-all-learners&success=bulk_delete&count=' . $reverted_count));
    exit;
}

add_action('admin_post_nds_delete_learner', 'nds_handle_delete_learner');
function nds_handle_delete_learner() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized access');
    }

    // Verify nonce
    if (!isset($_POST['nds_delete_nonce']) || !wp_verify_nonce($_POST['nds_delete_nonce'], 'nds_delete_learner')) {
        wp_redirect(admin_url('admin.php?page=nds-all-learners&error=security_check_failed'));
        exit;
    }

    if (!isset($_POST['learner_id'])) {
        wp_redirect(admin_url('admin.php?page=nds-all-learners&error=no_id'));
        exit;
    }

    global $wpdb;
    $students_table = $wpdb->prefix . 'nds_students';
    $id = intval($_POST['learner_id']);
    
    error_log("NDS Delete Learner - ID: {$id}");
    
    if ($id <= 0) {
        error_log("NDS Delete Learner - Invalid ID: {$id}");
        wp_redirect(admin_url('admin.php?page=nds-all-learners&error=invalid_id'));
        exit;
    }

    $result = $wpdb->delete($students_table, ['id' => $id], ['%d']);
    
    if ($result !== false) {
        error_log("NDS Delete Learner - Successfully deleted learner ID: {$id}");
        wp_redirect(admin_url('admin.php?page=nds-all-learners&success=deleted'));
    } else {
        error_log("NDS Delete Learner - Failed to delete learner ID: {$id}. Error: " . $wpdb->last_error);
        wp_redirect(admin_url('admin.php?page=nds-all-learners&error=delete_failed'));
    }
    exit;
}
