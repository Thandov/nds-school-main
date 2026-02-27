<?php
require_once 'C:/xampp/htdocs/nds/wp-load.php';
require_once 'C:/xampp/htdocs/nds/wp-admin/includes/upgrade.php';
try {
    nds_school_create_tables(); 
    echo 'Tables updated successfully.';
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
