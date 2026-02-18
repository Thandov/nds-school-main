<?php
/**
 * Learner Dashboard - Documents Tab
 */
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$learner_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$learner = nds_get_student($learner_id);
$learner_data = (array) $learner;

// Helper function to get file icon based on extension
if (!function_exists('nds_get_file_icon')) {
    function nds_get_file_icon($file_path) {
        $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        switch ($ext) {
            case 'pdf':
                return '<i class="fas fa-file-pdf text-red-600"></i>';
            case 'doc':
            case 'docx':
                return '<i class="fas fa-file-word text-blue-600"></i>';
            case 'xls':
            case 'xlsx':
                return '<i class="fas fa-file-excel text-green-600"></i>';
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
                return '<i class="fas fa-file-image text-purple-600"></i>';
            case 'zip':
            case 'rar':
                return '<i class="fas fa-file-archive text-yellow-600"></i>';
            default:
                return '<i class="fas fa-file text-gray-600"></i>';
        }
    }
}

// Define required documents
$required_docs_mapping = [
    'id_passport_applicant' => 'ID/Passport (Applicant)',
    'id_passport_responsible' => 'ID/Passport (Responsible Person)',
    'saqa_certificate' => 'SAQA Certificate',
    'study_permit' => 'Study Permit',
    'parent_spouse_id' => 'Parent/Spouse ID',
    'latest_results' => 'Latest Results',
    'proof_residence' => 'Proof of Residence',
    'highest_grade_cert' => 'Highest Grade Certificate',
    'proof_medical_aid' => 'Proof of Medical Aid'
];

// Get uploaded documents
$uploaded_docs = [];

// 1. From application forms (legacy/initial submission)
$application_forms = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}nds_application_forms 
     WHERE email = %s 
     ORDER BY submitted_at DESC 
     LIMIT 1",
    $learner_data['email'] ?? ''
), ARRAY_A);

if (!empty($application_forms)) {
    $app = $application_forms[0];
    foreach ($required_docs_mapping as $field => $label) {
        if (!empty($app[$field])) {
            $uploaded_docs[$label] = [
                'path' => $app[$field],
                'date' => $app['submitted_at'] ?? 'N/A',
                'source' => 'Application'
            ];
        }
    }
}

// 2. From new uploads (nds_application_documents)
$app_ids = $wpdb->get_col($wpdb->prepare(
    "SELECT id FROM {$wpdb->prefix}nds_applications WHERE student_id = %d OR wp_user_id = %d",
    $learner_id,
    $learner_data['wp_user_id'] ?? 0
));

if (!empty($app_ids)) {
    $placeholders = implode(',', array_fill(0, count($app_ids), '%d'));
    $query = $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}nds_application_documents WHERE application_id IN ($placeholders) ORDER BY uploaded_at DESC",
        ...$app_ids
    );
    $extra_docs = $wpdb->get_results($query, ARRAY_A);
    
    foreach ($extra_docs as $doc) {
        // If we have a newer upload for a required doc, use it
        if (isset($uploaded_docs[$doc['file_name']])) {
            // Check if newer
            if (strtotime($doc['uploaded_at']) > strtotime($uploaded_docs[$doc['file_name']]['date'])) {
                $uploaded_docs[$doc['file_name']] = [
                    'path' => $doc['file_path'],
                    'date' => $doc['uploaded_at'],
                    'source' => 'Recent Upload'
                ];
            }
        } else {
            $uploaded_docs[$doc['file_name']] = [
                'path' => $doc['file_path'],
                'date' => $doc['uploaded_at'],
                'source' => 'Recent Upload'
            ];
        }
    }
}
// Helper to get student info for naming
$student_number = $learner->student_number ?? 'STU' . $learner_id;
$initials = '';
if (!empty($learner->first_name)) {
    $initials .= substr($learner->first_name, 0, 1);
}
if (!empty($learner->last_name)) {
    $initials .= substr($learner->last_name, 0, 1);
}
$initials = strtoupper($initials);
?>

<div class="space-y-8">
    <div class="flex items-center justify-between border-b border-gray-100 pb-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Document Requirements</h2>
            <div class="flex items-center mt-1 space-x-3">
                <p class="text-sm text-gray-500">Please ensure all required documents are uploaded to complete your profile.</p>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                    <i class="fas fa-info-circle mr-1"></i> PDF is the preferred format
                </span>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            <?php 
            $uploaded_count = count(array_intersect_key($uploaded_docs, array_flip($required_docs_mapping)));
            $total_count = count($required_docs_mapping);
            $percentage = ($total_count > 0) ? round(($uploaded_count / $total_count) * 100) : 0;
            ?>
            <div class="text-right mr-4">
                <span class="text-sm font-semibold text-gray-700"><?php echo $uploaded_count; ?>/<?php echo $total_count; ?> Uploaded</span>
                <div class="w-32 h-2 bg-gray-200 rounded-full mt-1">
                    <div class="h-full bg-blue-600 rounded-full" style="width: <?php echo $percentage; ?>%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Required Documents Checklist -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Document Name</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Uploaded Date</th>
                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($required_docs_mapping as $field => $label): 
                    $is_uploaded = isset($uploaded_docs[$label]);
                    $doc = $is_uploaded ? $uploaded_docs[$label] : null;
                    
                    // Generate download filename: [StudentNum]_[Initials]_[DocName]
                    $ext = $is_uploaded ? pathinfo($doc['path'], PATHINFO_EXTENSION) : 'pdf';
                    $download_filename = sanitize_file_name($student_number . '_' . $initials . '_' . $label) . '.' . $ext;
                ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-lg <?php echo $is_uploaded ? 'bg-blue-50 text-blue-600' : 'bg-gray-50 text-gray-400'; ?> flex items-center justify-center mr-3 shadow-sm border border-current opacity-20">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <span class="text-sm font-semibold text-gray-900"><?php echo esc_html($label); ?></span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <?php if ($is_uploaded): ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700 border border-green-200 shadow-sm">
                                    <i class="fas fa-check-circle mr-1.5"></i> YES
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700 border border-red-200 shadow-sm">
                                    <i class="fas fa-times-circle mr-1.5"></i> NO
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo $is_uploaded && $doc['date'] !== 'N/A' ? date_i18n(get_option('date_format'), strtotime($doc['date'])) : '-'; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="flex justify-end items-center space-x-2">
                                <?php if ($is_uploaded): ?>
                                    <!-- View/Download -->
                                    <a href="<?php echo esc_url(plugin_dir_url(dirname(dirname(__FILE__))) . 'public/' . $doc['path']); ?>" 
                                       target="_blank"
                                       class="inline-flex items-center p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors border border-blue-100 shadow-sm"
                                       title="View Document">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?php echo esc_url(plugin_dir_url(dirname(dirname(__FILE__))) . 'public/' . $doc['path']); ?>" 
                                       download="<?php echo esc_attr($download_filename); ?>"
                                       class="inline-flex items-center p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors border border-green-100 shadow-sm"
                                       title="Download Document">
                                        <i class="fas fa-download"></i>
                                    </a>

                                    <!-- Replace (Upload new) -->
                                    <button onclick="document.getElementById('upload-<?php echo $field; ?>').click()" 
                                            class="inline-flex items-center p-2 text-amber-600 hover:bg-amber-50 rounded-lg transition-colors border border-amber-100 shadow-sm"
                                            title="Replace/Reupload">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>

                                    <!-- Remove/Delete -->
                                    <button onclick="handleDocumentRemoval('<?php echo esc_js($label); ?>')" 
                                            class="inline-flex items-center p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors border border-red-100 shadow-sm"
                                            title="Remove Document">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                <?php else: ?>
                                    <button onclick="document.getElementById('upload-<?php echo $field; ?>').click()" 
                                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition-colors shadow-[0_4px_12px_-4px_rgba(37,99,235,0.4)] border border-blue-500">
                                        <i class="fas fa-upload mr-2"></i> Upload
                                    </button>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Hidden File Input for this specific document -->
                            <form class="hidden nds-inline-upload-form" enctype="multipart/form-data">
                                <?php wp_nonce_field('nds_upload_learner_document', 'nds_upload_document_nonce'); ?>
                                <input type="hidden" name="action" value="nds_upload_learner_document">
                                <input type="hidden" name="learner_id" value="<?php echo esc_attr($learner_id); ?>">
                                <input type="hidden" name="document_name" value="<?php echo esc_attr($label); ?>">
                                <input type="hidden" name="document_category" value="other">
                                <input type="file" id="upload-<?php echo $field; ?>" name="document_file" 
                                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                       onchange="handleInlineUpload(this)">
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Inline Upload Loader Overlay -->
<div id="nds-upload-overlay" class="fixed inset-0 bg-slate-900/40 backdrop-blur-[2px] z-[100000] hidden flex items-center justify-center transition-all duration-300 opacity-0">
    <div class="bg-white p-8 rounded-3xl shadow-2xl border-2 border-blue-400 text-center transform scale-95 transition-all duration-300">
        <div class="relative w-24 h-24 mx-auto mb-6">
            <div class="absolute inset-0 animate-ping opacity-20 rounded-full bg-blue-400"></div>
            <div class="relative w-24 h-24 rounded-full border-4 border-blue-50 border-t-blue-600 animate-spin"></div>
            <div class="absolute inset-0 flex items-center justify-center">
                <i class="fas fa-cloud-upload-alt text-3xl text-blue-600"></i>
            </div>
        </div>
        <h3 id="overlay-title" class="text-xl font-bold text-slate-900">Uploading Document</h3>
        <p id="overlay-msg" class="text-slate-500 text-sm mt-2">Please wait while we secure your file...</p>
        <div id="upload-progress-text" class="mt-4 text-xs font-bold text-blue-600 uppercase tracking-widest">Processing...</div>
    </div>
</div>

<script>
function handleInlineUpload(input) {
    if (!input.files || !input.files[0]) return;
    
    const file = input.files[0];
    const form = input.closest('.nds-inline-upload-form');
    const formData = new FormData(form);
    
    const overlay = document.getElementById('nds-upload-overlay');
    const overlayTitle = document.getElementById('overlay-title');
    const progressText = document.getElementById('upload-progress-text');
    
    overlayTitle.innerText = 'Uploading Document';
    overlay.classList.remove('hidden');
    setTimeout(() => {
        overlay.classList.add('opacity-100');
        overlay.firstElementChild.classList.remove('scale-95');
        overlay.firstElementChild.classList.add('scale-100');
    }, 10);
    
    progressText.innerText = 'Uploading ' + file.name + '...';
    
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            progressText.innerText = 'Upload Complete!';
            progressText.classList.remove('text-blue-600');
            progressText.classList.add('text-green-600');
            setTimeout(() => {
                location.reload();
            }, 800);
        } else {
            alert('Error: ' + (data.data || 'Failed to upload document'));
            overlay.classList.add('hidden');
            overlay.classList.remove('opacity-100');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
        overlay.classList.add('hidden');
        overlay.classList.remove('opacity-100');
    });
}

function handleDocumentRemoval(docName) {
    if (!confirm('Are you sure you want to remove this document? This cannot be undone.')) {
        return;
    }
    
    const overlay = document.getElementById('nds-upload-overlay');
    const overlayTitle = document.getElementById('overlay-title');
    const overlayMsg = document.getElementById('overlay-msg');
    const progressText = document.getElementById('upload-progress-text');
    
    overlayTitle.innerText = 'Removing Document';
    overlayMsg.innerText = 'Please wait while we update your records...';
    overlay.classList.remove('hidden');
    setTimeout(() => {
        overlay.classList.add('opacity-100');
    }, 10);
    
    const formData = new FormData();
    formData.append('action', 'nds_remove_learner_document');
    formData.append('nonce', '<?php echo wp_create_nonce('nds_remove_learner_document'); ?>');
    formData.append('learner_id', '<?php echo $learner_id; ?>');
    formData.append('document_name', docName);
    
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            progressText.innerText = 'Removed Successfully';
            progressText.classList.add('text-green-600');
            setTimeout(() => {
                location.reload();
            }, 800);
        } else {
            alert('Error: ' + (data.data || 'Failed to remove document'));
            overlay.classList.add('hidden');
            overlay.classList.remove('opacity-100');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while removing the document.');
        overlay.classList.add('hidden');
        overlay.classList.remove('opacity-100');
    });
}
</script>
