<?php
/**
 * Rooms Management - CRUD Interface
 * Manage halls, classrooms, kitchens, and other venues
 */
if (!defined('ABSPATH')) {
    exit;
}

// Handle form submissions
if (isset($_POST['nds_room_action'])) {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }
    
    check_admin_referer('nds_room_action');
    
    global $wpdb;
    $rooms_table = $wpdb->prefix . 'nds_rooms';
    
    $action = sanitize_text_field($_POST['nds_room_action']);
    
    if ($action === 'add' || $action === 'edit') {
        $room_id = isset($_POST['room_id']) ? intval($_POST['room_id']) : 0;
        $code = sanitize_text_field($_POST['code']);
        $name = sanitize_text_field($_POST['name']);
        $type = sanitize_text_field($_POST['type']);
        $capacity = isset($_POST['capacity']) ? intval($_POST['capacity']) : 0;
        $location = sanitize_text_field($_POST['location'] ?? '');
        $equipment = sanitize_textarea_field($_POST['equipment'] ?? '');
        $amenities = sanitize_textarea_field($_POST['amenities'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $data = [
            'code' => $code,
            'name' => $name,
            'type' => $type,
            'capacity' => $capacity,
            'location' => $location,
            'equipment' => $equipment,
            'amenities' => $amenities,
            'is_active' => $is_active,
            'updated_at' => current_time('mysql')
        ];
        
        $format = ['%s', '%s', '%s', '%d', '%s', '%s', '%s', '%d', '%s'];
        
        if ($action === 'add') {
            $data['created_at'] = current_time('mysql');
            $format[] = '%s';
            
            $result = $wpdb->insert($rooms_table, $data, $format);
            if ($result) {
                wp_redirect(admin_url('admin.php?page=nds-rooms&success=added'));
                exit;
            } else {
                wp_redirect(admin_url('admin.php?page=nds-rooms&error=' . urlencode($wpdb->last_error)));
                exit;
            }
        } else {
            $result = $wpdb->update($rooms_table, $data, ['id' => $room_id], $format, ['%d']);
            if ($result !== false) {
                wp_redirect(admin_url('admin.php?page=nds-rooms&success=updated'));
                exit;
            } else {
                wp_redirect(admin_url('admin.php?page=nds-rooms&error=' . urlencode($wpdb->last_error)));
                exit;
            }
        }
    } elseif ($action === 'delete') {
        $room_id = isset($_POST['room_id']) ? intval($_POST['room_id']) : 0;
        if ($room_id) {
            $result = $wpdb->delete($rooms_table, ['id' => $room_id], ['%d']);
            if ($result) {
                wp_redirect(admin_url('admin.php?page=nds-rooms&success=deleted'));
                exit;
            } else {
                wp_redirect(admin_url('admin.php?page=nds-rooms&error=' . urlencode($wpdb->last_error)));
                exit;
            }
        }
    }
}

// Main Rooms Management Page
function nds_rooms_page() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }
    
    global $wpdb;
    $rooms_table = $wpdb->prefix . 'nds_rooms';
    
    // Handle edit mode
    $edit_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
    $edit_room = null;
    if ($edit_id) {
        $edit_room = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$rooms_table} WHERE id = %d", $edit_id), ARRAY_A);
    }
    
    // Handle search and filters
    $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
    $type_filter = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : '';
    $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
    
    $where_conditions = [];
    $where_values = [];
    
    if ($search) {
        $where_conditions[] = "(name LIKE %s OR code LIKE %s OR location LIKE %s)";
        $search_term = '%' . $wpdb->esc_like($search) . '%';
        $where_values[] = $search_term;
        $where_values[] = $search_term;
        $where_values[] = $search_term;
    }
    
    if ($type_filter) {
        $where_conditions[] = "type = %s";
        $where_values[] = $type_filter;
    }
    
    if ($status_filter === 'active') {
        $where_conditions[] = "is_active = 1";
    } elseif ($status_filter === 'inactive') {
        $where_conditions[] = "is_active = 0";
    }
    
    $where_sql = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    if (!empty($where_values)) {
        $query = "SELECT * FROM {$rooms_table} {$where_sql} ORDER BY type, name";
        $rooms = $wpdb->get_results($wpdb->prepare($query, $where_values), ARRAY_A);
    } else {
        $rooms = $wpdb->get_results("SELECT * FROM {$rooms_table} ORDER BY type, name", ARRAY_A);
    }
    
    // Get statistics
    $total_rooms = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$rooms_table}");
    $active_rooms = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$rooms_table} WHERE is_active = 1");
    $rooms_by_type = $wpdb->get_results("SELECT type, COUNT(*) as count FROM {$rooms_table} GROUP BY type", ARRAY_A);
    
    // Show success/error messages
    if (isset($_GET['success'])) {
        $messages = [
            'added' => 'Room added successfully!',
            'updated' => 'Room updated successfully!',
            'deleted' => 'Room deleted successfully!'
        ];
        $message = $messages[$_GET['success']] ?? 'Operation completed successfully!';
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
    }
    
    if (isset($_GET['error'])) {
        echo '<div class="notice notice-error is-dismissible"><p>Error: ' . esc_html($_GET['error']) . '</p></div>';
    }
    ?>
    
    <div class="wrap">
        <h1 class="wp-heading-inline">Rooms & Venues</h1>
        <a href="<?php echo admin_url('admin.php?page=nds-rooms&add=1'); ?>" class="page-title-action">Add New Room</a>
        <hr class="wp-header-end">
        
        <!-- Statistics Cards -->
        <div class="nds-stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin: 20px 0;">
            <div class="nds-stat-card" style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <div style="font-size: 2rem; font-weight: bold; color: #2271b1;"><?php echo $total_rooms; ?></div>
                <div style="color: #666; margin-top: 0.5rem;">Total Rooms</div>
            </div>
            <div class="nds-stat-card" style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <div style="font-size: 2rem; font-weight: bold; color: #00a32a;"><?php echo $active_rooms; ?></div>
                <div style="color: #666; margin-top: 0.5rem;">Active Rooms</div>
            </div>
            <?php foreach ($rooms_by_type as $type_stat): ?>
                <div class="nds-stat-card" style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <div style="font-size: 2rem; font-weight: bold; color: #d63638;"><?php echo $type_stat['count']; ?></div>
                    <div style="color: #666; margin-top: 0.5rem;"><?php echo ucfirst(str_replace('_', ' ', $type_stat['type'])); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Add/Edit Form -->
        <?php if (isset($_GET['add']) || $edit_room): ?>
            <div class="nds-form-card" style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin: 20px 0;">
                <h2><?php echo $edit_room ? 'Edit Room' : 'Add New Room'; ?></h2>
                <form method="post" action="">
                    <?php wp_nonce_field('nds_room_action'); ?>
                    <input type="hidden" name="nds_room_action" value="<?php echo $edit_room ? 'edit' : 'add'; ?>">
                    <?php if ($edit_room): ?>
                        <input type="hidden" name="room_id" value="<?php echo intval($edit_room['id']); ?>">
                    <?php endif; ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="code">Room Code <span style="color: red;">*</span></label></th>
                            <td>
                                <input type="text" id="code" name="code" value="<?php echo esc_attr($edit_room['code'] ?? ''); ?>" class="regular-text" required>
                                <p class="description">Unique identifier (e.g., HALL-001, CLASS-001)</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="name">Room Name <span style="color: red;">*</span></label></th>
                            <td>
                                <input type="text" id="name" name="name" value="<?php echo esc_attr($edit_room['name'] ?? ''); ?>" class="regular-text" required>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="type">Room Type <span style="color: red;">*</span></label></th>
                            <td>
                                <select id="type" name="type" required>
                                    <option value="">Select Type</option>
                                    <option value="hall" <?php selected($edit_room['type'] ?? '', 'hall'); ?>>Hall</option>
                                    <option value="classroom" <?php selected($edit_room['type'] ?? '', 'classroom'); ?>>Classroom</option>
                                    <option value="kitchen" <?php selected($edit_room['type'] ?? '', 'kitchen'); ?>>Kitchen</option>
                                    <option value="lab" <?php selected($edit_room['type'] ?? '', 'lab'); ?>>Laboratory</option>
                                    <option value="workshop" <?php selected($edit_room['type'] ?? '', 'workshop'); ?>>Workshop</option>
                                    <option value="other" <?php selected($edit_room['type'] ?? '', 'other'); ?>>Other</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="capacity">Capacity</label></th>
                            <td>
                                <input type="number" id="capacity" name="capacity" value="<?php echo esc_attr($edit_room['capacity'] ?? 0); ?>" class="small-text" min="0">
                                <p class="description">Maximum number of people</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="location">Location</label></th>
                            <td>
                                <input type="text" id="location" name="location" value="<?php echo esc_attr($edit_room['location'] ?? ''); ?>" class="regular-text">
                                <p class="description">Building/Floor (e.g., "Ground Floor", "First Floor")</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="equipment">Equipment</label></th>
                            <td>
                                <textarea id="equipment" name="equipment" rows="3" class="large-text"><?php echo esc_textarea($edit_room['equipment'] ?? ''); ?></textarea>
                                <p class="description">List available equipment (e.g., "Projector, Whiteboard, Sound System")</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="amenities">Amenities</label></th>
                            <td>
                                <textarea id="amenities" name="amenities" rows="3" class="large-text"><?php echo esc_textarea($edit_room['amenities'] ?? ''); ?></textarea>
                                <p class="description">List amenities (e.g., "Air Conditioning, WiFi, Ventilation System")</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="is_active">Status</label></th>
                            <td>
                                <label>
                                    <input type="checkbox" id="is_active" name="is_active" value="1" <?php checked($edit_room['is_active'] ?? 1, 1); ?>>
                                    Active (available for scheduling)
                                </label>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo $edit_room ? 'Update Room' : 'Add Room'; ?>">
                        <a href="<?php echo admin_url('admin.php?page=nds-rooms'); ?>" class="button">Cancel</a>
                    </p>
                </form>
            </div>
        <?php endif; ?>
        
        <!-- Search and Filters -->
        <div class="nds-filters" style="background: white; padding: 1rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin: 20px 0;">
            <form method="get" action="">
                <input type="hidden" name="page" value="nds-rooms">
                <div style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 1rem; align-items: end;">
                    <div>
                        <label for="search" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Search</label>
                        <input type="text" id="search" name="search" value="<?php echo esc_attr($search); ?>" placeholder="Search by name, code, or location..." class="regular-text" style="width: 100%;">
                    </div>
                    <div>
                        <label for="type" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Type</label>
                        <select id="type" name="type" style="width: 100%;">
                            <option value="">All Types</option>
                            <option value="hall" <?php selected($type_filter, 'hall'); ?>>Hall</option>
                            <option value="classroom" <?php selected($type_filter, 'classroom'); ?>>Classroom</option>
                            <option value="kitchen" <?php selected($type_filter, 'kitchen'); ?>>Kitchen</option>
                            <option value="lab" <?php selected($type_filter, 'lab'); ?>>Laboratory</option>
                            <option value="workshop" <?php selected($type_filter, 'workshop'); ?>>Workshop</option>
                            <option value="other" <?php selected($type_filter, 'other'); ?>>Other</option>
                        </select>
                    </div>
                    <div>
                        <label for="status" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Status</label>
                        <select id="status" name="status" style="width: 100%;">
                            <option value="">All Status</option>
                            <option value="active" <?php selected($status_filter, 'active'); ?>>Active</option>
                            <option value="inactive" <?php selected($status_filter, 'inactive'); ?>>Inactive</option>
                        </select>
                    </div>
                    <div>
                        <input type="submit" class="button" value="Filter">
                        <?php if ($search || $type_filter || $status_filter): ?>
                            <a href="<?php echo admin_url('admin.php?page=nds-rooms'); ?>" class="button" style="margin-left: 0.5rem;">Clear</a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Rooms Table -->
        <div class="nds-table-card" style="background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow-x: auto; overflow-y: visible; max-height: none;">
            <table class="wp-list-table widefat striped" id="nds-rooms-table" style="position: static !important; table-layout: auto;">
                <thead>
                    <tr>
                        <th style="width: 100px;">Code</th>
                        <th>Name</th>
                        <th style="width: 120px;">Type</th>
                        <th style="width: 100px;">Capacity</th>
                        <th>Location</th>
                        <th style="width: 100px;">Status</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rooms)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem; color: #666;">
                                No rooms found. <a href="<?php echo admin_url('admin.php?page=nds-rooms&add=1'); ?>">Add your first room</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rooms as $room): ?>
                            <tr>
                                <td><strong><?php echo esc_html($room['code']); ?></strong></td>
                                <td>
                                    <strong><?php echo esc_html($room['name']); ?></strong>
                                    <?php if ($room['equipment']): ?>
                                        <br><small style="color: #666;"><?php echo esc_html(wp_trim_words($room['equipment'], 10)); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span style="padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.875rem; background: #f0f0f1; color: #1d2327;">
                                        <?php echo esc_html(ucfirst(str_replace('_', ' ', $room['type']))); ?>
                                    </span>
                                </td>
                                <td><?php echo $room['capacity'] > 0 ? number_format($room['capacity']) : 'N/A'; ?></td>
                                <td><?php echo esc_html($room['location'] ?: '—'); ?></td>
                                <td>
                                    <?php if ($room['is_active']): ?>
                                        <span style="color: #00a32a; font-weight: 600;">● Active</span>
                                    <?php else: ?>
                                        <span style="color: #d63638; font-weight: 600;">● Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=nds-rooms&edit=' . intval($room['id'])); ?>" class="button button-small">Edit</a>
                                    <form method="post" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this room? This action cannot be undone.');">
                                        <?php wp_nonce_field('nds_room_action'); ?>
                                        <input type="hidden" name="nds_room_action" value="delete">
                                        <input type="hidden" name="room_id" value="<?php echo intval($room['id']); ?>">
                                        <input type="submit" class="button button-small" value="Delete" style="color: #b32d2e;">
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php
}
