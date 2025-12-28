<?php
// test_json.php - Test pure JSON output
header('Content-Type: application/json');

// Test with simple JSON
echo json_encode([
    'test' => true,
    'message' => 'Pure JSON test',
    'timestamp' => date('Y-m-d H:i:s')
]);
exit();
?>