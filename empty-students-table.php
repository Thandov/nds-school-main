<?php
/**
 * Empty wp_nds_students table safely
 * This script handles foreign key constraints properly
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    // Load WordPress
    require_once('../../../wp-config.php');
}

global $wpdb;

echo "=== Emptying wp_nds_students table ===\n\n";

// Check current record count
$count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}nds_students");
echo "Current students count: {$count}\n";

if ($count == 0) {
    echo "✅ Students table is already empty.\n";
    exit;
}

// Check for foreign key constraints
echo "\nChecking foreign key relationships...\n";

// Check student_enrollments
$enrollments_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}nds_student_enrollments");
echo "Student enrollments: {$enrollments_count}\n";

// Check student_progressions  
$progressions_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}nds_student_progressions");
echo "Student progressions: {$progressions_count}\n";

// Check applications
$applications_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}nds_applications WHERE student_id IS NOT NULL");
echo "Applications with student_id: {$applications_count}\n";

echo "\n=== SAFE EMPTYING PROCESS ===\n";

// Start transaction
$wpdb->query('START TRANSACTION');

try {
    // Step 1: Set student_id to NULL in applications table
    echo "1. Updating applications table...\n";
    $result1 = $wpdb->query("UPDATE {$wpdb->prefix}nds_applications SET student_id = NULL WHERE student_id IS NOT NULL");
    echo "   ✅ Updated {$result1} applications\n";
    
    // Step 2: Delete student enrollments (CASCADE will handle this)
    echo "2. Deleting student enrollments...\n";
    $result2 = $wpdb->query("DELETE FROM {$wpdb->prefix}nds_student_enrollments");
    echo "   ✅ Deleted {$result2} enrollments\n";
    
    // Step 3: Delete student progressions (CASCADE will handle this)
    echo "3. Deleting student progressions...\n";
    $result3 = $wpdb->query("DELETE FROM {$wpdb->prefix}nds_student_progressions");
    echo "   ✅ Deleted {$result3} progressions\n";
    
    // Step 4: Finally, delete all students
    echo "4. Deleting all students...\n";
    $result4 = $wpdb->query("DELETE FROM {$wpdb->prefix}nds_students");
    echo "   ✅ Deleted {$result4} students\n";
    
    // Commit transaction
    $wpdb->query('COMMIT');
    
    echo "\n✅ SUCCESS: Students table emptied successfully!\n";
    
    // Verify
    $final_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}nds_students");
    echo "Final students count: {$final_count}\n";
    
} catch (Exception $e) {
    // Rollback on error
    $wpdb->query('ROLLBACK');
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Transaction rolled back. No changes made.\n";
}

echo "\n=== COMPLETED ===\n";
?>

