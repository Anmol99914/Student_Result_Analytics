<?php
// test_paths.php - Place in PHP_Files/admin/pages/
echo "Current directory: " . __DIR__ . "<br>";
echo "Project root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Base URL: " . $_SERVER['REQUEST_URI'] . "<br>";
?>