<?php
/**
 * TRUNCATE wp_nds_students table (FASTER method)
 * WARNING: This will delete ALL data immediately
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    // Load WordPress
    require_once('../../../wp-config.php');
}

global $wpdb;

echo "=== TRUNCATING wp_nds_students table ===\n\n";

// Check current record count
$count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}nds_students");
echo "Current students count: {$count}\n";

if ($count == 0) {
    echo "✅ Students table is already empty.\n";
    exit;
}

// Check for related data
echo "\nChecking related data...\n";
$enrollments_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}nds_student_enrollments");
$progressions_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}nds_student_progressions");
$applications_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}nds_applications WHERE student_id IS NOT NULL");

echo "Student enrollments: {$enrollments_count}\n";
echo "Student progressions: {$progressions_count}\n";
echo "Applications with student_id: {$applications_count}\n";

if ($enrollments_count > 0 || $progressions_count > 0 || $applications_count > 0) {
    echo "\n⚠️  WARNING: There is related data that will be affected!\n";
    echo "This method will:\n";
    echo "- Delete ALL student enrollments\n";
    echo "- Delete ALL student progressions\n";
    echo "- Set student_id to NULL in applications\n";
    echo "\nContinue? (y/N): ";
    
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);
    
    if (trim($line) !== 'y' && trim($line) !== 'Y') {
        echo "❌ Operation cancelled.\n";
        exit;
    }
}

echo "\n=== TRUNCATING TABLES ===\n";

// Start transaction
$wpdb->query('START TRANSACTION');

try {
    // Step 1: Set student_id to NULL in applications
    echo "1. Updating applications...\n";
    $wpdb->query("UPDATE {$wpdb->prefix}nds_applications SET student_id = NULL WHERE student_id IS NOT NULL");
    
    // Step 2: Truncate related tables first
    echo "2. Truncating student_enrollments...\n";
    $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}nds_student_enrollments");
    
    echo "3. Truncating student_progressions...\n";
    $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}nds_student_progressions");
    
    // Step 3: Truncate students table
    echo "4. Truncating students table...\n";
    $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}nds_students");
    
    // Commit transaction
    $wpdb->query('COMMIT');
    
    echo "\n✅ SUCCESS: All tables truncated successfully!\n";
    
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

