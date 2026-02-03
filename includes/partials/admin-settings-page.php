<?php
/**
 * NDS Academy Settings page content.
 * Expects: $seed_status, $wipe_status, $wipe_tables_status, $import_export_status, $msg (set by nds_settings_page).
 */
if (!defined('ABSPATH')) {
    exit;
}
?>
    <div class="wrap">
        <h1>NDS Academy Settings</h1>
        <style>
            .nds-settings-row {
                display: flex;
                flex-wrap: wrap;
                gap: 20px;
                margin-top: 20px;
                margin-bottom: 20px;
            }
            .nds-settings-card {
                background: #fff;
                border: 1px solid #dcdcde;
                box-shadow: 0 1px 1px rgba(0,0,0,0.04);
                padding: 16px;
                box-sizing: border-box;
                flex: 1 1 260px;
                min-width: 260px;
            }
            .nds-settings-card h2 {
                margin-top: 0;
            }
            .nds-settings-card-full {
                width: 100%;
            }
            /* NDS brand: primary purple */
            .nds-btn-brand {
                background: #9333ea !important;
                border-color: #9333ea !important;
                color: #fff !important;
            }
            .nds-btn-brand:hover {
                background: #7c3aed !important;
                border-color: #7c3aed !important;
                color: #fff !important;
            }
            .nds-import-export-card {
                border-left: 4px solid #9333ea;
            }
            @media (max-width: 782px) {
                .nds-settings-row {
                    display: block;
                }
                .nds-settings-card {
                    margin-bottom: 20px;
                }
            }
        </style>
        <?php if ($seed_status === 'success'): ?>
            <div class="notice notice-success">
                <p><strong>Seed completed successfully.</strong></p>
                <?php if (!empty($msg)): ?>
                    <p><?php echo esc_html($msg); ?></p>
                <?php endif; ?>
            </div>
        <?php elseif ($seed_status === 'error'): ?>
            <div class="notice notice-error"><p><strong>Seed failed:</strong> <?php echo esc_html($msg ?: 'Unknown error occurred'); ?></p></div>
        <?php endif; ?>
        <?php if ($wipe_status === 'success'): ?>
            <div class="notice notice-success"><p><?php echo esc_html($msg ?: 'Core tables wiped successfully.'); ?></p></div>
        <?php elseif ($wipe_status === 'error'): ?>
            <div class="notice notice-error"><p><?php echo esc_html($msg ?: 'Wipe operation failed'); ?></p></div>
        <?php endif; ?>
        <?php if ($wipe_tables_status === 'success'): ?>
            <div class="notice notice-success"><p><?php echo esc_html($msg ?: 'Selected tables wiped successfully.'); ?></p></div>
        <?php elseif ($wipe_tables_status === 'error'): ?>
            <div class="notice notice-error"><p><?php echo esc_html($msg ?: 'Wipe selected tables operation failed'); ?></p></div>
        <?php endif; ?>
        <?php if ($import_export_status === 'success'): ?>
            <div class="notice notice-success">
                <p><?php echo esc_html($msg ?: 'Import/Export completed successfully.'); ?></p>
                <?php
                $skipped_details = get_transient('nds_import_skipped_details');
                if (!empty($skipped_details) && is_array($skipped_details)) {
                    delete_transient('nds_import_skipped_details');
                    $count = count($skipped_details);
                    ?>
                    <p><strong>Skipped rows (<?php echo (int) $count; ?>):</strong></p>
                    <table class="widefat striped" style="max-width:800px; margin-top:8px;">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Table</th>
                                <th>Reason</th>
                                <th>Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($skipped_details as $i => $s) : ?>
                                <tr>
                                    <td><?php echo (int) ($i + 1); ?></td>
                                    <td><code><?php echo esc_html($s['table']); ?></code></td>
                                    <td><?php echo esc_html($s['reason'] === 'duplicate' ? 'Duplicate key (row already exists)' : 'Empty required field: ' . esc_html($s['column'])); ?></td>
                                    <td><?php echo esc_html($s['row_hint']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php } ?>
            </div>
        <?php elseif ($import_export_status === 'error'): ?>
            <div class="notice notice-error"><p><?php echo esc_html($msg ?: 'Import/Export failed'); ?></p></div>
        <?php endif; ?>

        <div class="nds-settings-row">
            <div class="nds-settings-card">
                <h2>Seed</h2>
                <p style="margin-bottom:12px;">Run sample data seed: All (LMS + Staff + Students), LMS only, Staff only, or Students only.</p>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return nds_show_loader_and_confirm(this, 'Run this seed now?', 'Seeding', 'Running seed...');">
                    <?php wp_nonce_field('nds_seed_nonce'); ?>
                    <input type="hidden" name="action" value="nds_seed" />
                    <label for="nds_seed_type" class="screen-reader-text">Seed type</label>
                    <select name="nds_seed_type" id="nds_seed_type" style="min-width:140px; margin-right:8px;">
                        <option value="all">All</option>
                        <option value="lms">LMS</option>
                        <option value="staff">Staff</option>
                        <option value="students">Students</option>
                    </select>
                    <button type="submit" class="button button-primary">Run seed</button>
                </form>
            </div>

            <div class="nds-settings-card">
                <h2>Danger Zone</h2>
                <p style="margin-bottom:8px;">Careful – these actions permanently remove data.</p>
                <?php
                global $wpdb;
                $nds_tables = $wpdb->get_col(
                    $wpdb->prepare(
                        'SHOW TABLES LIKE %s',
                        $wpdb->esc_like($wpdb->prefix . 'nds_') . '%'
                    )
                );
                ?>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return nds_show_loader_and_confirm(this, 'This will TRUNCATE the selected tables. Continue?', 'Wiping Selected Tables', 'Truncating selected nds_ tables...');">
                    <?php wp_nonce_field('nds_wipe_selected_nds_tables_nonce'); ?>
                    <input type="hidden" name="action" value="nds_wipe_selected_nds_tables" />
                    <p><strong>Wipe Selected nds_ Tables</strong></p>
                    <p class="description">Tick specific nds_ tables to wipe instead of wiping everything.</p>
                    <p>
                        <label><input type="checkbox" id="nds-wipe-select-all" /> Select all</label>
                    </p>
                    <div style="max-height:200px; overflow:auto; border:1px solid #dcdcde; padding:8px; background:#fff;">
                        <?php if (!empty($nds_tables)) : ?>
                            <?php foreach ($nds_tables as $table_name) : ?>
                                <label style="display:block; margin-bottom:4px;">
                                    <input type="checkbox" class="nds-wipe-table-checkbox" name="nds_tables[]" value="<?php echo esc_attr($table_name); ?>" />
                                    <code><?php echo esc_html($table_name); ?></code>
                                </label>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <p class="description">No nds_ tables found.</p>
                        <?php endif; ?>
                    </div>
                    <p style="margin-top:8px;">
                        <button type="submit" class="button button-secondary">Wipe Selected Tables</button>
                    </p>
                </form>
                <script>
                    (function() {
                        var selectAll = document.getElementById('nds-wipe-select-all');
                        if (!selectAll) return;
                        selectAll.addEventListener('change', function() {
                            var boxes = document.querySelectorAll('.nds-wipe-table-checkbox');
                            for (var i = 0; i < boxes.length; i++) {
                                boxes[i].checked = selectAll.checked;
                            }
                        });
                    })();
                </script>
            </div>

            <div class="nds-settings-card nds-import-export-card">
                <h2>Import / Export</h2>
                <p style="margin-bottom:12px;">Export database to CSV (ZIP) or import from <strong>NDS Database System.xlsx</strong>. If no file is selected, import uses the default <strong>assets/NDS Database System.xlsx</strong>. Columns named <code>*_ignore</code> are skipped on import.</p>
                <div style="display:flex; flex-wrap:wrap; gap:12px; align-items:flex-start;">
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin:0;">
                        <?php wp_nonce_field('nds_export_database_nonce'); ?>
                        <input type="hidden" name="action" value="nds_export_database" />
                        <button type="submit" class="button nds-btn-brand">Export database (ZIP)</button>
                    </form>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data" style="margin:0;">
                        <?php wp_nonce_field('nds_import_excel_nonce'); ?>
                        <input type="hidden" name="action" value="nds_import_excel" />
                        <label for="nds_import_xlsx" class="screen-reader-text">Select Excel file</label>
                        <input type="file" name="nds_import_xlsx" id="nds_import_xlsx" accept=".xlsx" style="margin-right:8px;" />
                        <button type="submit" class="button nds-btn-brand" onclick="return nds_show_loader_and_confirm(this.form, 'Import will insert rows from the Excel file. Existing rows may cause duplicate key errors. Continue?', 'Import', 'Importing...');">Import from Excel</button>
                    </form>
                </div>
                <p class="description" style="margin-top:10px;">Export creates CSVs + setup guide in a ZIP. Import respects FK order and skips <code>*_ignore</code> columns. If import fails, check the PHP error log (e.g. <code>wp-content/debug.log</code> when <code>WP_DEBUG_LOG</code> is on, or your server log); errors are prefixed with <code>[NDS Import]</code>.</p>
            </div>
        </div>

        <div class="nds-settings-card" style="margin-bottom:20px;">
            <h2>Access Control</h2>
            <p>Control subscriber access to the WordPress backend.</p>
            <?php
            if (isset($_POST['nds_save_access_settings']) && check_admin_referer('nds_access_settings_nonce')) {
                $block_subscribers = isset($_POST['nds_block_subscribers_backend']) ? '1' : '0';
                $hide_admin_bar = isset($_POST['nds_hide_subscriber_admin_bar']) ? '1' : '0';
                update_option('nds_block_subscribers_backend', $block_subscribers);
                update_option('nds_hide_subscriber_admin_bar', $hide_admin_bar);
                echo '<div class="notice notice-success is-dismissible"><p>Settings saved successfully.</p></div>';
            }
            $block_subscribers = get_option('nds_block_subscribers_backend', '1');
            $hide_admin_bar = get_option('nds_hide_subscriber_admin_bar', '0');
            ?>
            <form method="post" action="">
                <?php wp_nonce_field('nds_access_settings_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="nds_block_subscribers_backend">Block Subscribers from Backend</label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" name="nds_block_subscribers_backend" value="1" <?php checked($block_subscribers, '1'); ?> />
                                Redirect subscribers to the learner portal instead of allowing access to /wp-admin/
                            </label>
                            <p class="description">When enabled, users with the "subscriber" role will be redirected to <code>/portal/</code> when they try to access the WordPress admin area.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="nds_hide_subscriber_admin_bar">Hide Admin Bar for Subscribers</label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" name="nds_hide_subscriber_admin_bar" value="1" <?php checked($hide_admin_bar, '1'); ?> />
                                Hide the WordPress admin bar on the front-end for subscribers
                            </label>
                            <p class="description">This only takes effect when "Block Subscribers from Backend" is enabled.</p>
                        </td>
                    </tr>
                </table>
                <input type="hidden" name="nds_save_access_settings" value="1" />
                <?php submit_button('Save Access Control Settings'); ?>
            </form>
        </div>

        <div class="nds-settings-card nds-settings-card-full" style="margin-top:20px; margin-bottom:20px;">
            <h2>Available Shortcodes</h2>
            <p>Use these shortcodes in your pages and posts to display NDS Academy content.</p>
            <table class="widefat" style="margin-top: 15px;">
                <thead>
                    <tr>
                        <th style="width: 200px;">Shortcode</th>
                        <th>Description</th>
                        <th style="width: 300px;">Usage Example</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>[nds_login]</code></td>
                        <td><strong>Custom Login Page</strong><br>Displays a modern split-screen login form. Automatically redirects users based on role.</td>
                        <td><code>[nds_login]</code></td>
                    </tr>
                    <tr>
                        <td><code>[nds_recipes]</code></td>
                        <td><strong>Recipe Grid</strong><br>Displays a grid of recipes with customizable columns, layout, and display options.</td>
                        <td><code>[nds_recipes limit="12" columns="4"]</code></td>
                    </tr>
                    <tr>
                        <td><code>[nds_recipe_grid]</code></td>
                        <td><strong>Recipe Grid (Alias)</strong><br>Same as <code>[nds_recipes]</code> with grid layout preset.</td>
                        <td><code>[nds_recipe_grid limit="8"]</code></td>
                    </tr>
                    <tr>
                        <td><code>[nds_recipe_single]</code></td>
                        <td><strong>Single Recipe Display</strong><br>Displays a single recipe with full details including ingredients, steps, and images.</td>
                        <td><code>[nds_recipe_single id="5"]</code></td>
                    </tr>
                    <tr>
                        <td><code>[nds_recipe_carousel]</code></td>
                        <td><strong>Recipe Carousel</strong><br>Displays recipes in a carousel/slider format.</td>
                        <td><code>[nds_recipe_carousel limit="6"]</code></td>
                    </tr>
                    <tr>
                        <td><code>[nds_calendar]</code></td>
                        <td><strong>Academic Calendar</strong><br>Displays an interactive calendar showing course schedules, events, and academic dates.</td>
                        <td><code>[nds_calendar]</code></td>
                    </tr>
                </tbody>
            </table>
            <div style="margin-top: 20px; padding: 15px; background: #f0f6ff; border-left: 4px solid #2563eb; border-radius: 4px;">
                <h3 style="margin-top: 0; color: #2563eb;">💡 Tips</h3>
                <ul style="margin: 10px 0 0 20px; padding: 0;">
                    <li>Copy the shortcode and paste it into any WordPress page or post</li>
                    <li>Most shortcodes support additional attributes - check the usage examples above</li>
                    <li>Shortcodes work in page builders like Elementor, Gutenberg, and Classic Editor</li>
                </ul>
            </div>
        </div>

        <div class="card" style="padding:16px; max-width:800px; margin-top:20px; border-left: 4px solid #dc3232;">
            <h2 style="color: #dc3232;">⚠️ Wipe Core Tables</h2>
            <p><strong>DANGER:</strong> This will permanently delete ALL data from faculties, programs, courses, and staff tables. This action cannot be undone!</p>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return nds_show_loader_and_confirm(this, '⚠️ WARNING: This will DELETE ALL data from:\n\n- Faculties\n- Programs\n- Courses\n- Staff\n\nThis action CANNOT be undone!\n\nAre you absolutely sure?', 'Wiping Core Tables', 'Deleting all faculties, programs, courses and staff...');">
                <?php wp_nonce_field('nds_wipe_core_tables_nonce'); ?>
                <input type="hidden" name="action" value="nds_wipe_core_tables" />
                <button type="submit" class="button button-secondary" style="background-color: #dc3232; border-color: #dc3232; color: white;">Wipe All Core Tables</button>
            </form>
        </div>
    </div>
    <script type="text/javascript">
        function nds_show_loader_and_confirm(form, confirmMessage, loaderTitle, loaderMessage) {
            if (!window.NDSNotification || typeof NDSNotification.loading !== 'function') {
                return confirm(confirmMessage);
            }
            if (!confirm(confirmMessage)) {
                return false;
            }
            NDSNotification.loading(loaderMessage || 'Please wait...', loaderTitle || 'Processing...');
            return true;
        }
    </script>
