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
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        body.nds-portal-body {
            margin: 0;
            padding: 0;
        }
        
        /* Hide website header/navbar completely on portal */
        body.nds-portal-body header,
        body.nds-portal-body .site-header,
        body.nds-portal-body .main-header,
        body.nds-portal-body nav.site-navigation,
        body.nds-portal-body #site-header,
        body.nds-portal-body #masthead,
        body.nds-portal-body .ast-primary-header-bar,
        body.nds-portal-body .site-navigation,
        body.nds-portal-body #header,
        body.nds-portal-body .header {
            display: none !important;
            height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: hidden !important;
        }
        
        /* Remove gap - use margin instead of top */
        .nds-portal-offset {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }
        body.admin-bar .nds-portal-offset { 
            margin-top: 32px !important; 
        }
        @media screen and (max-width: 782px) {
            body.admin-bar .nds-portal-offset { 
                margin-top: 46px !important; 
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
if ($student_id <= 0) {
    echo '<div class="nds-tailwind-wrapper bg-gray-50 py-16"><div class="max-w-3xl mx-auto bg-white shadow-sm rounded-xl p-8 text-center text-gray-700">We could not find a learner profile linked to your account. Please contact the school.</div></div>';
    return;
}

$learner   = nds_get_student($student_id);
if (!$learner) {
    echo '<div class="nds-tailwind-wrapper bg-gray-50 py-16"><div class="max-w-3xl mx-auto bg-white shadow-sm rounded-xl p-8 text-center text-gray-700">Your learner profile could not be loaded. Please contact the school.</div></div>';
    return;
}

$learner_data = (array) $learner;
$full_name    = trim(($learner_data['first_name'] ?? '') . ' ' . ($learner_data['last_name'] ?? ''));

// Enrollments (used for multiple sections)
$enrollments = $wpdb->get_results(
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

// Recent enrollments (for Overview tab)
$recent_enrollments = array_slice($enrollments, 0, 5);

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

// Certificates count (placeholder - will be implemented when certificates table exists)
$certificates_count = 0;

// Latest application linked to this user (if any)
// Show application even for active students if they have no enrollments yet (course_name display)
$latest_application = null;
$status = $learner_data['status'] ?? 'prospect';
$is_applicant = in_array($status, ['prospect', 'applicant'], true);
// Also show application for active students if they have no enrollments (to display course_name)
$has_no_enrollments = empty($enrollments);
if ($is_applicant || ($status === 'active' && $has_no_enrollments)) {
    $latest_application = function_exists('nds_portal_get_latest_application_for_current_user')
        ? nds_portal_get_latest_application_for_current_user()
        : null;
}

// Learner-facing programme name (what the learner actually applied/enrolled for)
$display_program_name = '';
if (!empty($enrollments)) {
    foreach ($enrollments as $row) {
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
$enrolled_courses_count = count($enrollments);
$applied_courses_count  = 0;
if ($is_applicant && !empty($latest_application)) {
    // For now we show the latest application only; this can be expanded to count all active applications
    $applied_courses_count = 1;
}

// Current tab (frontend-safe, no admin links)
$current_tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'overview';
$valid_tabs  = $is_applicant
    ? array('overview')
    : array('overview', 'courses', 'timetable', 'finances', 'results', 'graduation', 'certificates', 'documents', 'activity');
if (!in_array($current_tab, $valid_tabs, true)) {
    $current_tab = 'overview';
}

// Helper to build tab URLs on the same /portal/ URL
function nds_learner_portal_tab_url($tab)
{
    $base = home_url('/portal/');
    if ($tab === 'overview') {
        return $base;
    }
    return add_query_arg('tab', $tab, $base);
}
?>

<div class="nds-tailwind-wrapper bg-gray-50 min-h-screen nds-portal-offset" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
    <?php
    // Show success modal if redirected from application form and we have an application record
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
            <!-- Compact centered dialog, with a hard max-width so it never spans the full viewport -->
            <div
                class="bg-white rounded-2xl shadow-2xl p-6 sm:p-7 md:p-8"
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
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
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
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100 flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">
                            <?php echo $is_applicant ? 'Applied Courses' : 'Enrolled Courses'; ?>
                        </p>
                        <p class="mt-2 text-2xl font-semibold text-gray-900">
                            <?php echo $is_applicant ? $applied_courses_count : $enrolled_courses_count; ?>
                        </p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                        <i class="fas fa-book text-blue-600 text-xl"></i>
                    </div>
                </div>
                <p class="mt-3 text-xs text-gray-500">
                    <?php echo $is_applicant ? 'Courses you have applied for.' : 'Courses you are currently enrolled in.'; ?>
                </p>
            </div>

            <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100 flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Status</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-900">
                            <span class="px-2 py-1 rounded-full text-xs font-medium
                                <?php
                                echo $status === 'active'
                                    ? 'bg-green-100 text-green-800'
                                    : ($status === 'prospect'
                                        ? 'bg-yellow-100 text-yellow-800'
                                        : 'bg-gray-100 text-gray-800');
                                ?>">
                                <?php echo esc_html(ucfirst($status)); ?>
                            </span>
                        </p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center">
                        <i class="fas fa-user-check text-emerald-600 text-xl"></i>
                    </div>
                </div>
                <p class="mt-3 text-xs text-gray-500">
                    Your current learner status.
                </p>
            </div>

            <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100 flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Average Grade</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-900">
                            <?php echo $avg_grade ? number_format((float) $avg_grade, 1) . '%' : 'N/A'; ?>
                        </p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center">
                        <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                    </div>
                </div>
                <p class="mt-3 text-xs text-gray-500">
                    <?php echo $avg_grade ? 'Your overall academic performance.' : 'No grades recorded yet.'; ?>
                </p>
            </div>

            <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100 flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Certificates</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-900">
                            <?php echo $certificates_count; ?>
                        </p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center">
                        <i class="fas fa-certificate text-amber-600 text-xl"></i>
                    </div>
                </div>
                <p class="mt-3 text-xs text-gray-500">
                    Certificates earned.
                </p>
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-100 mb-6">
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px overflow-x-auto" aria-label="Tabs">
                    <?php
                    $tabs = array(
                        'overview'     => array('icon' => 'fa-home', 'label' => 'Overview'),
                        'courses'      => array('icon' => 'fa-book', 'label' => 'Courses'),
                        'timetable'   => array('icon' => 'fa-calendar-alt', 'label' => 'Timetable'),
                        'finances'    => array('icon' => 'fa-dollar-sign', 'label' => '$ Finances'),
                        'results'     => array('icon' => 'fa-chart-bar', 'label' => 'Results'),
                        'graduation'  => array('icon' => 'fa-graduation-cap', 'label' => 'Graduation'),
                        'certificates' => array('icon' => 'fa-certificate', 'label' => 'Certificates'),
                        'documents'   => array('icon' => 'fa-file', 'label' => 'Documents'),
                        'activity'    => array('icon' => 'fa-history', 'label' => 'Activity'),
                    );

                    // Applicants only see a simplified overview (no extra tabs)
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
                // Set learner_id for partials (they expect $_GET['id'], but we'll override)
                $_GET['id'] = $student_id;
                
                switch ($current_tab) {
                    case 'overview':
                        ?>
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                            <!-- Personal Information (first column) -->
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 <?php echo !empty($latest_application) ? 'lg:col-span-2 order-1' : 'lg:col-span-3'; ?>">
                                <h2 class="text-xl font-semibold text-gray-900 mb-4">Personal Information</h2>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Full Name</label>
                                        <p class="mt-1 text-sm text-gray-900"><?php echo esc_html($full_name); ?></p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Student Number</label>
                                        <p class="mt-1 text-sm text-gray-900"><?php echo esc_html($learner_data['student_number'] ?? 'N/A'); ?></p>
                                    </div>
                                    <?php if ($display_program_name) : ?>
                                        <div>
                                            <label class="text-sm font-medium text-gray-500">Programme</label>
                                            <p class="mt-1 text-sm text-gray-900"><?php echo esc_html($display_program_name); ?></p>
                                        </div>
                                    <?php endif; ?>
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
                                    <div class="mt-2">
                                        <label class="text-sm font-medium text-gray-500">Status</label>
                                        <p class="mt-1">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                <?php 
                                                echo $status === 'active' ? 'bg-green-100 text-green-800' : 
                                                     ($status === 'prospect' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800');
                                                ?>">
                                                <?php echo esc_html(ucfirst($status)); ?>
                                            </span>
                                        </p>
                                    </div>
                                    <div class="mt-2">
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
                            </div>

                            <?php if (!empty($latest_application)) : ?>
                                <!-- Application status card (smaller info card, right column) -->
                                <div class="lg:col-span-1 order-2">
                                    <div class="bg-emerald-50 rounded-lg p-4">
                                        <div class="text-xs font-semibold tracking-wide text-emerald-700 uppercase">Application status</div>
                                        <div class="mt-1 text-lg font-semibold text-emerald-900">
                                            <?php
                                            $status_label = isset($latest_application['status'])
                                                ? str_replace('_', ' ', $latest_application['status'])
                                                : 'submitted';
                                            echo esc_html(ucfirst($status_label));
                                            ?>
                                        </div>
                                        <div class="mt-1 text-sm text-emerald-800">
                                            <?php if (!empty($latest_application['course_name'])) : ?>
                                                Applied for: <?php echo esc_html($latest_application['course_name']); ?>
                                                <?php if (!empty($latest_application['level'])) : ?>
                                                    (NQF <?php echo esc_html($latest_application['level']); ?>)
                                                <?php endif; ?>
                                            <?php else : ?>
                                                Your course choice will appear here.
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($latest_application['application_no'])) : ?>
                                            <div class="mt-2 text-xs text-emerald-900/80">
                                                Application number:
                                                <span class="font-mono">
                                                    <?php echo esc_html($latest_application['application_no']); ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                        <p class="mt-3 text-xs text-emerald-900/80">
                                            While your application is being reviewed, some dashboard features may be limited.
                                        </p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Recent Courses -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-xl font-semibold text-gray-900">Recent Courses</h2>
                                <a href="<?php echo esc_url(nds_learner_portal_tab_url('courses')); ?>"
                                   class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                                    View All
                                </a>
                            </div>
                            <?php if (!empty($recent_enrollments)): ?>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Program</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Grade</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <?php foreach ($recent_enrollments as $enrollment): ?>
                                                <tr>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        <?php echo esc_html($enrollment['course_name'] ?? 'N/A'); ?>
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                                        <?php echo esc_html($enrollment['program_name'] ?? 'N/A'); ?>
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap">
                                                        <span class="px-2 py-1 text-xs font-medium rounded-full 
                                                            <?php 
                                                            $enroll_status = $enrollment['status'] ?? '';
                                                            echo $enroll_status === 'enrolled' ? 'bg-green-100 text-green-800' : 
                                                                 ($enroll_status === 'completed' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800');
                                                            ?>">
                                                            <?php echo esc_html(ucfirst($enroll_status)); ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                                        <?php 
                                                        if (!empty($enrollment['final_percentage'])) {
                                                            echo esc_html($enrollment['final_percentage']) . '%';
                                                        } elseif (!empty($enrollment['final_grade'])) {
                                                            echo esc_html($enrollment['final_grade']);
                                                        } else {
                                                            echo 'N/A';
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-gray-500 text-sm">No courses enrolled yet.</p>
                            <?php endif; ?>
                        </div>
                        <?php
                        break;

                    case 'courses':
                        // Learner view: show ONLY courses the learner is actually enrolled in,
                        // grouped by the programs those courses belong to.
                        $courses_by_program = [];
                        foreach ($enrollments as $row) {
                            $pid   = $row['program_id'] ?? 0;
                            $pname = $row['program_name'] ?? __('Unassigned Program', 'nds-school');
                            if (!isset($courses_by_program[$pid])) {
                                $courses_by_program[$pid] = [
                                    'name'    => $pname,
                                    'rows'    => [],
                                ];
                            }
                            $courses_by_program[$pid]['rows'][] = $row;
                        }
                        ?>
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Programme modules</h2>
                        <?php if (empty($courses_by_program)) : ?>
                            <p class="text-sm text-gray-600">You are not enrolled in any courses yet.</p>
                        <?php else : ?>
                            <div class="space-y-6">
                                <?php foreach ($courses_by_program as $program): ?>
                                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                        <h3 class="text-md font-semibold text-gray-900 mb-3">
                                            <?php echo esc_html($program['name']); ?>
                                        </h3>
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                                <thead class="bg-gray-100">
                                                <tr>
                                                    <th class="px-3 py-2 text-left font-medium text-gray-700">Course</th>
                                                    <th class="px-3 py-2 text-left font-medium text-gray-700">Code</th>
                                                    <th class="px-3 py-2 text-left font-medium text-gray-700">Year / Semester</th>
                                                    <th class="px-3 py-2 text-left font-medium text-gray-700">Status</th>
                                                    <th class="px-3 py-2 text-left font-medium text-gray-700">Final %</th>
                                                    <th class="px-3 py-2 text-left font-medium text-gray-700">Grade</th>
                                                </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-100">
                                                <?php foreach ($program['rows'] as $row): ?>
                                                    <tr>
                                                        <td class="px-3 py-2 text-gray-800">
                                                            <?php echo esc_html($row['course_name'] ?? 'Course'); ?>
                                                        </td>
                                                        <td class="px-3 py-2 text-gray-700">
                                                            <?php echo esc_html($row['course_code'] ?? ''); ?>
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
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                                                                <?php echo esc_html(ucfirst($row['status'] ?? 'enrolled')); ?>
                                                            </span>
                                                        </td>
                                                        <td class="px-3 py-2 text-gray-800">
                                                            <?php
                                                            if (isset($row['final_percentage']) && $row['final_percentage'] !== null) {
                                                                echo esc_html(number_format((float) $row['final_percentage'], 1)) . '%';
                                                            } else {
                                                                echo '—';
                                                            }
                                                            ?>
                                                        </td>
                                                        <td class="px-3 py-2 text-gray-800">
                                                            <?php echo esc_html($row['final_grade'] ?? '—'); ?>
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
                        include plugin_dir_path(__FILE__) . '../includes/partials/learner-dashboard-timetable.php';
                        break;

                    case 'finances':
                        include plugin_dir_path(__FILE__) . '../includes/partials/learner-dashboard-finances.php';
                        break;

                    case 'results':
                        include plugin_dir_path(__FILE__) . '../includes/partials/learner-dashboard-results.php';
                        break;

                    case 'graduation':
                        include plugin_dir_path(__FILE__) . '../includes/partials/learner-dashboard-graduation.php';
                        break;

                    case 'certificates':
                        include plugin_dir_path(__FILE__) . '../includes/partials/learner-dashboard-certificates.php';
                        break;

                    case 'documents':
                        include plugin_dir_path(__FILE__) . '../includes/partials/learner-dashboard-documents.php';
                        break;

                    case 'activity':
                        include plugin_dir_path(__FILE__) . '../includes/partials/learner-dashboard-activity.php';
                        break;

                    default:
                        // Fallback to overview
                        break;
                }
                ?>
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
                // Clean query params so the modal doesn't reappear on refresh
                try {
                    var url = new URL(window.location.href);
                    url.searchParams.delete('application');
                    url.searchParams.delete('id');
                    var params = url.searchParams.toString();
                    var newUrl = url.pathname + (params ? '?' + params : '');
                    window.history.replaceState({}, '', newUrl);
                } catch (e) {
                    // no-op
                }
            });
        }
    });
    </script>
<?php endif; ?>

<script>
// #region agent log: detect top elements pushing navbar down
(function() {
    document.addEventListener('DOMContentLoaded', function() {
        const logData = {
            sessionId: 'debug-session',
            runId: 'pre-fix',
            hypothesisId: 'H1',
            location: 'learner-portal.php:DOMContentLoaded',
            message: 'Checking top elements that push navbar down',
            data: {},
            timestamp: Date.now()
        };
        
        // Check for WordPress admin bar
        const adminBar = document.getElementById('wpadminbar');
        logData.data.hasAdminBar = !!adminBar;
        if (adminBar) {
            const adminBarRect = adminBar.getBoundingClientRect();
            logData.data.adminBarHeight = adminBarRect.height;
            logData.data.adminBarTop = adminBarRect.top;
            const computedStyle = window.getComputedStyle(adminBar);
            logData.data.adminBarBgColor = computedStyle.backgroundColor;
            logData.data.adminBarDisplay = computedStyle.display;
        }
        
        // Check all elements before the main portal wrapper
        const portalWrapper = document.querySelector('.nds-tailwind-wrapper');
        if (portalWrapper) {
            const wrapperRect = portalWrapper.getBoundingClientRect();
            logData.data.wrapperTop = wrapperRect.top;
            logData.data.wrapperHeight = wrapperRect.height;
            
            // Find all elements before the wrapper
            const allElements = document.querySelectorAll('body > *');
            const elementsBeforeWrapper = [];
            allElements.forEach(function(el) {
                if (el === portalWrapper) return;
                const rect = el.getBoundingClientRect();
                if (rect.top < wrapperRect.top && rect.height > 0) {
                    const computed = window.getComputedStyle(el);
                    elementsBeforeWrapper.push({
                        tagName: el.tagName,
                        id: el.id || '',
                        className: el.className || '',
                        height: rect.height,
                        top: rect.top,
                        backgroundColor: computed.backgroundColor,
                        display: computed.display,
                        position: computed.position
                    });
                }
            });
            logData.data.elementsBeforeWrapper = elementsBeforeWrapper;
        }
        
        // Check for any green-colored elements at top
        const bodyChildren = Array.from(document.body.children);
        const topGreenElements = [];
        bodyChildren.forEach(function(el) {
            const rect = el.getBoundingClientRect();
            if (rect.top < 100 && rect.height > 20) {
                const computed = window.getComputedStyle(el);
                const bgColor = computed.backgroundColor;
                // Check if green-ish (simplified check)
                if (bgColor.includes('rgb') || bgColor.includes('#')) {
                    topGreenElements.push({
                        tagName: el.tagName,
                        id: el.id || '',
                        className: el.className || '',
                        height: rect.height,
                        top: rect.top,
                        backgroundColor: bgColor,
                        zIndex: computed.zIndex
                    });
                }
            }
        });
        logData.data.topGreenElements = topGreenElements;
        
        fetch('http://127.0.0.1:7247/ingest/dd126561-a5b5-4577-8b70-512cd5168604', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(logData)
        }).catch(function() {});
    });
})();
// #endregion
</script>

<?php wp_footer(); ?>
</body>
</html>
