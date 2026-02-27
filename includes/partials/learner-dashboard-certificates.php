<?php
/**
 * Learner Dashboard - Certificates Tab
 */
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$learner_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get certificates from certificates table
$table_certificates = $wpdb->prefix . 'nds_certificates';
$table_courses = $wpdb->prefix . 'nds_courses';

$certificates = $wpdb->get_results($wpdb->prepare("
    SELECT c.*, 
           course.name as course_name,
           course.program_name
    FROM {$table_certificates} c
    LEFT JOIN {$table_courses} course ON c.course_id = course.id
    WHERE c.student_id = %d AND c.status = 'active'
    ORDER BY c.issued_date DESC
", $learner_id), ARRAY_A);

if (!$certificates) {
    $certificates = [];
}
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-900">Certificates & Awards</h2>
        <button class="inline-flex items-center px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium shadow-sm transition-colors">
            <i class="fas fa-plus mr-2"></i>
            Issue Certificate
        </button>
    </div>

    <?php if (!empty($certificates)): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($certificates as $cert): ?>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-lg bg-amber-50 flex items-center justify-center">
                            <i class="fas fa-certificate text-amber-600 text-xl"></i>
                        </div>
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                            Verified
                        </span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2"><?php echo esc_html($cert['title']); ?></h3>
                    <p class="text-sm text-gray-500 mb-4"><?php echo esc_html($cert['description'] ?: 'Certificate of completion'); ?></p>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Issued: <?php echo esc_html(date('M j, Y', strtotime($cert['issued_date']))); ?></span>
                        <a href="?page=nds-download-certificate&cert_id=<?php echo $cert['id']; ?>" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                            Download <i class="fas fa-download ml-1"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
            <i class="fas fa-certificate text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-medium text-gray-900 mb-2">No Certificates Issued</h3>
            <p class="text-gray-600 mb-6">Certificates will appear here once issued upon course/program completion.</p>
            <button class="inline-flex items-center px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium shadow-sm transition-colors">
                <i class="fas fa-plus mr-2"></i>
                Issue First Certificate
            </button>
        </div>
    <?php endif; ?>

    <!-- Certificate Templates -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Available Certificate Types</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-4 border border-gray-200 rounded-lg hover:border-blue-300 transition-colors">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-certificate text-blue-600"></i>
                    <div>
                        <h3 class="font-medium text-gray-900">Course Completion</h3>
                        <p class="text-sm text-gray-500">Issued upon successful course completion</p>
                    </div>
                </div>
            </div>
            <div class="p-4 border border-gray-200 rounded-lg hover:border-blue-300 transition-colors">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-graduation-cap text-purple-600"></i>
                    <div>
                        <h3 class="font-medium text-gray-900">Program Diploma</h3>
                        <p class="text-sm text-gray-500">Awarded upon program graduation</p>
                    </div>
                </div>
            </div>
            <div class="p-4 border border-gray-200 rounded-lg hover:border-blue-300 transition-colors">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-trophy text-amber-600"></i>
                    <div>
                        <h3 class="font-medium text-gray-900">Achievement Award</h3>
                        <p class="text-sm text-gray-500">For outstanding academic performance</p>
                    </div>
                </div>
            </div>
            <div class="p-4 border border-gray-200 rounded-lg hover:border-blue-300 transition-colors">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-medal text-yellow-600"></i>
                    <div>
                        <h3 class="font-medium text-gray-900">Excellence Certificate</h3>
                        <p class="text-sm text-gray-500">For maintaining high grades</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
