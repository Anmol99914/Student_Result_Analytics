<?php
require_once "../../config.php";

$teachers = [
    ['Ram Sharma', 'ram@college.com', 'ram123', 'active'],
    ['Sita Karki', 'sita@college.com', 'sita123', 'active'],
    ['Hari Thapa', 'hari@college.com', 'hari123', 'suspended'],
    ['Gita Rai', 'gita@college.com', 'gita123', 'active'],
];

$stmt = $connection->prepare(
    "INSERT INTO teacher (name, email, password, status) VALUES (?, ?, ?, ?)"
);

foreach ($teachers as $t) {
    $hashed = password_hash($t[2], PASSWORD_DEFAULT);
    $stmt->bind_param("ssss", $t[0], $t[1], $hashed, $t[3]);
    $stmt->execute();
}

echo "Teachers inserted securely.";
