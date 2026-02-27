<?php
require_once '../../../wp-load.php';
require_once 'includes/notification-functions.php';

// Test creating a notification
$result = nds_create_notification(1, 'Test Schedule Change', 'This is a test notification for schedule changes', 'timetable');
echo 'Notification created: ' . ($result ? 'Yes (ID: ' . $result . ')' : 'No') . PHP_EOL;

// Test getting notifications
$notifications = nds_get_unread_notifications(1);
echo 'Total unread notifications: ' . count($notifications) . PHP_EOL;

if (!empty($notifications)) {
    echo 'Latest notification: ' . $notifications[0]->title . PHP_EOL;
}
?>