<?php
// Prevent direct access - this file should only be included by WordPress
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Ensure WordPress functions are available
if (!function_exists('current_user_can')) {
    return;
}

// Enhanced Faculties Page with Modern UI/UX
function nds_education_paths_enhanced() {
    if (!current_user_can('manage_options')) { wp_die('Unauthorized'); }

    global $wpdb;
    $table_faculties = $wpdb->prefix . 'nds_faculties';
    $table_programs = $wpdb->prefix . 'nds_programs';
    $table_courses = $wpdb->prefix . 'nds_courses';

    // Handle delete action
    if (isset($_GET['delete']) && !empty($_GET['delete'])) {
        $delete_id = intval($_GET['delete']);
        nds_delete_education_path($delete_id);
    }

    // Get data
    $paths = $wpdb->get_results("SELECT * FROM {$table_faculties} ORDER BY name", ARRAY_A);
    $total_paths = count($paths);
    $total_programs = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table_programs}");
    $total_courses = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table_courses}");

    // Check for success/error messages
    $success = isset($_GET['success']) ? sanitize_text_field($_GET['success']) : '';
    $error = isset($_GET['error']) ? sanitize_text_field($_GET['error']) : '';

    // Force-load Tailwind and icons for this screen to avoid admin CSS overrides
    $plugin_dir = plugin_dir_path(dirname(__FILE__));
    $css_file   = $plugin_dir . 'assets/css/frontend.css';
    if (file_exists($css_file)) {
        wp_enqueue_style(
            'nds-tailwindcss-education-paths',
            plugin_dir_url(dirname(__FILE__)) . 'assets/css/frontend.css',
            array(),
            filemtime($css_file),
            'all'
        );
        // High-specificity wrapper utilities
        wp_add_inline_style('nds-tailwindcss-education-paths', '
            .nds-tailwind-wrapper { all: initial !important; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important; }
            .nds-tailwind-wrapper * { box-sizing: border-box !important; }
            .nds-tailwind-wrapper .bg-white { background-color: #ffffff !important; }
            .nds-tailwind-wrapper .bg-gray-50 { background-color: #f9fafb !important; }
            .nds-tailwind-wrapper .text-gray-900 { color: #111827 !important; }
            .nds-tailwind-wrapper .text-gray-600 { color: #4b5563 !important; }
            .nds-tailwind-wrapper .rounded-xl { border-radius: 0.75rem !important; }
            .nds-tailwind-wrapper .rounded-2xl { border-radius: 1rem !important; }
            .nds-tailwind-wrapper .shadow-lg { box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -2px rgb(0 0 0 / 0.05) !important; }
            .nds-tailwind-wrapper .border { border-width: 1px !important; }
            .nds-tailwind-wrapper .border-gray-200 { border-color: #e5e7eb !important; }
            .nds-tailwind-wrapper .px-4 { padding-left: 1rem !important; padding-right: 1rem !important; }
            .nds-tailwind-wrapper .py-4 { padding-top: 1rem !important; padding-bottom: 1rem !important; }
            .nds-tailwind-wrapper .p-6 { padding: 1.5rem !important; }
            .nds-tailwind-wrapper .p-8 { padding: 2rem !important; }
            .nds-tailwind-wrapper .mb-6 { margin-bottom: 1.5rem !important; }
            .nds-tailwind-wrapper .mb-8 { margin-bottom: 2rem !important; }
            .nds-tailwind-wrapper .max-w-7xl { max-width: 80rem !important; }
            .nds-tailwind-wrapper .mx-auto { margin-left: auto !important; margin-right: auto !important; }
            .nds-tailwind-wrapper .grid { display: grid !important; }
            .nds-tailwind-wrapper .gap-6 { gap: 1.5rem !important; }
            .nds-tailwind-wrapper .gap-8 { gap: 2rem !important; }
            .nds-tailwind-wrapper .rounded-full { border-radius: 9999px !important; }
            .nds-tailwind-wrapper .bg-gradient-to-r { background-image: linear-gradient(to right, var(--tw-gradient-stops)) !important; }
            .nds-tailwind-wrapper .from-blue-600 { --tw-gradient-from: #2563eb !important; --tw-gradient-to: rgb(37 99 235 / 0) !important; --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to) !important; }
            .nds-tailwind-wrapper .via-purple-600 { --tw-gradient-to: rgb(147 51 234 / 0) !important; --tw-gradient-stops: var(--tw-gradient-from), #9333ea, var(--tw-gradient-to) !important; }
            .nds-tailwind-wrapper .to-indigo-700 { --tw-gradient-to: #4338ca !important; }
            .nds-tailwind-wrapper .hover\:scale-105:hover { transform: scale(1.05) !important; }
            .nds-tailwind-wrapper .transition-all { transition-property: all !important; transition-duration: 300ms !important; }
        ');
    }
    wp_enqueue_style('nds-icons', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css', array(), null, 'all');

    ?>
    <div class="nds-tailwind-wrapper bg-gray-50 min-h-screen" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
        <!-- Header - aligned with main dashboard -->
        <div class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-purple-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-graduation-cap text-white text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Faculties Management</h1>
                            <p class="text-gray-600">Design and manage comprehensive learning journeys across your academy.</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="text-right">
                            <p class="text-xs uppercase tracking-wide text-gray-500">Last updated</p>
                            <p class="text-sm font-medium text-gray-900"><?php echo esc_html(date_i18n('M j, Y \a\t g:i A')); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
            <!-- Action Buttons -->
            <div class="flex items-center gap-2">
                <button id="addPathBtn" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 px-3 rounded-lg flex items-center justify-center gap-1.5 transition-colors duration-200 shadow-sm hover:shadow-md text-xs" style="background-color: #059669 !important; color: #ffffff !important;">
                    <i class="fas fa-book text-xs"></i>Add Faculty
                </button>
                <a href="<?php echo esc_url(admin_url('admin.php?page=nds-programs')); ?>"
                   class="bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-3 rounded-lg flex items-center justify-center gap-1.5 transition-colors duration-200 shadow-sm hover:shadow-md text-xs" style="background-color: #9333ea !important; color: #ffffff !important;">
                    <i class="fas fa-graduation-cap text-xs"></i>Manage Programs
                </a>
            </div>

            <!-- Success/Error Messages -->
            <?php if ($success): ?>
                <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 flex items-center">
                    <span class="dashicons dashicons-yes-alt text-emerald-600 mr-3 text-xl"></span>
                    <div>
                        <h3 class="text-sm font-semibold text-emerald-800">Success</h3>
                        <p class="text-sm text-emerald-700"><?php echo esc_html($success); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 flex items-center">
                    <span class="dashicons dashicons-warning text-red-600 mr-3 text-xl"></span>
                    <div>
                        <h3 class="text-sm font-semibold text-red-800">Error</h3>
                        <p class="text-sm text-red-700"><?php echo esc_html($error); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Stats Cards (aligned with dashboard KPIs) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Faculties</p>
                            <p class="mt-2 text-2xl font-semibold text-gray-900">
                                <?php echo number_format_i18n($total_paths); ?>
                            </p>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                            <span class="dashicons dashicons-networking text-blue-600 text-xl"></span>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Programs</p>
                            <p class="mt-2 text-2xl font-semibold text-gray-900">
                                <?php echo number_format_i18n($total_programs); ?>
                            </p>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center">
                            <span class="dashicons dashicons-admin-page text-emerald-600 text-xl"></span>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-xl p-5 border border-gray-100 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Courses</p>
                            <p class="mt-2 text-2xl font-semibold text-gray-900">
                                <?php echo number_format_i18n($total_courses); ?>
                            </p>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center">
                            <span class="dashicons dashicons-welcome-learn-more text-purple-600 text-xl"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Faculties grid / empty state -->
            <?php if ($paths): 
                // PERFORMANCE FIX: Get all counts in batch queries instead of N+1 queries
                $path_ids = array_column($paths, 'id');
                if (!empty($path_ids)) {
                    $placeholders = implode(',', array_fill(0, count($path_ids), '%d'));
                    
                    // Get programs counts for all faculties at once
                    $programs_counts = $wpdb->get_results($wpdb->prepare(
                        "SELECT faculty_id, COUNT(*) as count 
                         FROM {$table_programs} 
                         WHERE faculty_id IN ($placeholders)
                         GROUP BY faculty_id",
                        ...$path_ids
                    ), ARRAY_A);
                    
                    // Get courses counts for all faculties at once
                    $courses_counts = $wpdb->get_results($wpdb->prepare(
                        "SELECT p.faculty_id, COUNT(DISTINCT c.id) as count
                         FROM {$table_courses} c
                         JOIN {$table_programs} p ON c.program_id = p.id
                         WHERE p.faculty_id IN ($placeholders)
                         GROUP BY p.faculty_id",
                        ...$path_ids
                    ), ARRAY_A);
                    
                    // Convert to associative arrays for O(1) lookup
                    $programs_lookup = array();
                    foreach ($programs_counts as $pc) {
                        $programs_lookup[$pc['faculty_id']] = (int) $pc['count'];
                    }
                    
                    $courses_lookup = array();
                    foreach ($courses_counts as $cc) {
                        $courses_lookup[$cc['faculty_id']] = (int) $cc['count'];
                    }
                } else {
                    $programs_lookup = array();
                    $courses_lookup = array();
                }
            ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($paths as $path):
                        // Get counts from lookup arrays (O(1) instead of database query)
                        $programs_count = isset($programs_lookup[$path['id']]) ? $programs_lookup[$path['id']] : 0;
                        $courses_count = isset($courses_lookup[$path['id']]) ? $courses_lookup[$path['id']] : 0;
                    ?>
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200">
                            <!-- Path Header -->
                            <div class="px-5 py-4 border-b border-gray-100">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-graduation-cap text-blue-600 text-lg"></i>
                                        </div>
                                        <div>
                                            <h3 class="text-sm font-semibold text-gray-900"><?php echo esc_html($path['name']); ?></h3>
                                            <p class="text-xs text-gray-500">Faculty</p>
                                        </div>
                                    </div>
                                    <div class="flex gap-2">
                                        <a href="<?php echo admin_url('admin.php?page=nds-edit-faculty&edit=' . $path['id']); ?>"
                                           class="text-gray-600 hover:text-gray-700 text-xs px-2 py-1 rounded hover:bg-gray-100 transition-colors"
                                           title="Edit Faculty">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?php echo admin_url('admin.php?page=nds-faculties&delete=' . $path['id']); ?>"
                                           class="text-red-500 hover:text-red-700 text-xs px-2 py-1 rounded hover:bg-red-50 transition-colors"
                                           title="Delete Faculty"
                                           onclick="return confirm('Are you sure you want to delete this faculty?')">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Path Content -->
                            <div class="p-5">
                                <?php if ($path['description']): ?>
                                    <p class="text-gray-600 text-sm mb-4 line-clamp-2"><?php echo esc_html($path['description']); ?></p>
                                <?php endif; ?>

                                <!-- Stats -->
                                <div class="grid grid-cols-2 gap-3 mb-4">
                                    <div class="text-center p-2.5 bg-gray-50 rounded-lg">
                                        <p class="text-lg font-semibold text-gray-900"><?php echo number_format_i18n($programs_count); ?></p>
                                        <p class="text-xs text-gray-500 mt-1">Programs</p>
                                    </div>
                                    <div class="text-center p-2.5 bg-gray-50 rounded-lg">
                                        <p class="text-lg font-semibold text-gray-900"><?php echo number_format_i18n($courses_count); ?></p>
                                        <p class="text-xs text-gray-500 mt-1">Courses</p>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex gap-2">
                                    <a href="<?php echo admin_url('admin.php?page=nds-programs&faculty_id=' . $path['id']); ?>"
                                       class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 px-3 rounded-lg text-center text-xs flex items-center justify-center gap-1.5 transition-colors duration-200 shadow-sm hover:shadow-md" style="background-color: #059669 !important; color: #ffffff !important;">
                                        <i class="fas fa-book text-xs"></i>Programs
                                    </a>
                                    <a href="<?php echo admin_url('admin.php?page=nds-courses&faculty_id=' . $path['id']); ?>"
                                       class="flex-1 bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-3 rounded-lg text-center text-xs flex items-center justify-center gap-1.5 transition-colors duration-200 shadow-sm hover:shadow-md" style="background-color: #9333ea !important; color: #ffffff !important;">
                                        <i class="fas fa-graduation-cap text-xs"></i>Courses
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- Empty State -->
                <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-12">
                    <div class="text-center">
                        <i class="fas fa-graduation-cap text-5xl text-gray-300 mb-4"></i>
                        <h3 class="text-sm font-medium text-gray-900 mb-1">No Faculties Yet</h3>
                        <p class="text-xs text-gray-500 mb-4">Get started by creating your first faculty to organize your programs and courses.</p>
                        <button id="addFirstPathBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-5 rounded-lg text-sm flex items-center gap-2 transition-colors duration-200 shadow-sm hover:shadow-md mx-auto">
                            <i class="fas fa-plus text-sm"></i>
                            Create First Faculty
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Path Modal -->
    <div id="addPathModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden" style="display: none;">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-2xl">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-xl font-semibold text-gray-900">
                    <i class="fas fa-plus text-blue-600 mr-3"></i>Add Faculty
                </h3>
                <button id="closeAddPathModal" class="text-gray-400 hover:text-gray-600 text-xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6">
                <form method="POST" action="<?php echo admin_url('admin-post.php'); ?>" id="addPathForm">
                    <?php wp_nonce_field('nds_add_education_path_nonce', 'nds_add_education_path_nonce'); ?>
                    <input type="hidden" name="action" value="nds_add_education_path">

                    <?php
                    // Get default color for new faculty
                    require_once plugin_dir_path(__FILE__) . 'color-palette-generator.php';
                    $color_generator = new NDS_ColorPaletteGenerator();
                    global $wpdb;
                    $faculties_table = $wpdb->prefix . 'nds_faculties';
                    $faculty_count = $wpdb->get_var("SELECT COUNT(*) FROM $faculties_table");
                    $default_color = $color_generator->get_default_faculty_color($faculty_count);
                    ?>
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Faculty Name *</label>
                            <input type="text" name="path_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., Faculty of Culinary Arts" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea name="path_description" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Describe the faculty's focus, programs, and career outcomes..."></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Parent Color *</label>
                            <p class="text-sm text-gray-500 mb-3">Choose a color for this faculty. Programs within this faculty will automatically use shades of this color.</p>
                            <div class="flex items-center gap-3">
                                <div class="relative">
                                    <input type="color" name="color_primary" id="modal_color_primary" value="<?php echo esc_attr($default_color); ?>"
                                        class="h-14 w-24 border-2 border-gray-300 rounded-lg cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm">
                                    <div class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full border-2 border-white shadow-sm" style="background-color: <?php echo esc_attr($default_color); ?>;"></div>
                                </div>
                                <div class="flex-1">
                                    <input type="text" id="modal_color_primary_text" value="<?php echo esc_attr($default_color); ?>"
                                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm"
                                        placeholder="#E53935" pattern="^#[0-9A-Fa-f]{6}$">
                                    <p class="text-xs text-gray-400 mt-1">Hex color code</p>
                                </div>
                            </div>
                            <div class="mt-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <p class="text-xs text-gray-600 mb-2"><strong>Selected Color:</strong></p>
                                <div class="flex items-center gap-2">
                                    <div class="w-12 h-12 rounded-lg shadow-sm border border-gray-300" id="modal_color_preview" style="background-color: <?php echo esc_attr($default_color); ?>;"></div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900" id="modal_color_display"><?php echo esc_html($default_color); ?></p>
                                        <p class="text-xs text-gray-500">This color will be used as the base for all programs</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3 pt-6 border-t border-gray-100">
                        <button type="button" id="cancelAddPath" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-5 rounded-lg text-sm transition-colors duration-200">Cancel</button>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-5 rounded-lg text-sm flex items-center gap-2 transition-colors duration-200 shadow-sm hover:shadow-md">
                            <i class="fas fa-save text-sm"></i>Add Faculty
                        </button>
                    </div>
                </form>
            </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Color picker sync functionality
        const modalColorPicker = document.getElementById('modal_color_primary');
        const modalColorText = document.getElementById('modal_color_primary_text');
        const modalColorPreview = document.getElementById('modal_color_preview');
        const modalColorDisplay = document.getElementById('modal_color_display');
        
        if (modalColorPicker && modalColorText && modalColorPreview && modalColorDisplay) {
            // Sync color picker to text input and preview
            modalColorPicker.addEventListener('input', function(e) {
                const color = e.target.value.toUpperCase();
                modalColorText.value = color;
                modalColorPreview.style.backgroundColor = color;
                modalColorDisplay.textContent = color;
            });
            
            // Sync text input to color picker and preview
            modalColorText.addEventListener('input', function(e) {
                const value = e.target.value;
                if (/^#[0-9A-Fa-f]{6}$/.test(value)) {
                    modalColorPicker.value = value;
                    modalColorPreview.style.backgroundColor = value;
                    modalColorDisplay.textContent = value.toUpperCase();
                }
            });
            
            // Update preview when text input loses focus (to show validation)
            modalColorText.addEventListener('blur', function(e) {
                const value = e.target.value;
                if (!/^#[0-9A-Fa-f]{6}$/.test(value) && value.trim() !== '') {
                    // Invalid color, reset to picker value
                    modalColorText.value = modalColorPicker.value.toUpperCase();
                }
            });
        }
        
        // Modal functionality
        const modal = document.getElementById('addPathModal');
        const openBtns = [document.getElementById('addPathBtn'), document.getElementById('addFirstPathBtn')];
        const closeBtn = document.getElementById('closeAddPathModal');
        const cancelBtn = document.getElementById('cancelAddPath');

        function openAddPathModal() {
            if (!modal) return;

            // Ensure modal overlay is attached directly to <body> so it centers over the full viewport
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex', 'items-center', 'justify-center');
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeAddPathModal() {
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex', 'items-center', 'justify-center');
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }

        openBtns.forEach(btn => {
            if (btn) {
                btn.addEventListener('click', openAddPathModal);
            }
        });

        [closeBtn, cancelBtn].forEach(btn => {
            if (btn) {
                btn.addEventListener('click', closeAddPathModal);
            }
        });

        if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                    closeAddPathModal();
            }
        });
        }
    });
    </script>

    <style>
    @media print {
        .bg-gray-50 { background: white !important; }
        .bg-white { background: white !important; }
        .shadow-sm, .shadow-md { box-shadow: none !important; }
        .border { border: 1px solid #e5e7eb !important; }
        .bg-blue-600, .bg-green-600, .bg-purple-600 { display: none !important; }
        .hover\:bg-blue-700, .hover\:bg-green-700, .hover\:bg-purple-700 { display: none !important; }
    }
    </style>
    <?php
}
