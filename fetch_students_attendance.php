<?php
// Kết nối cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "StudentManagement";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$class_id = $_GET['class_id']; // Lấy class_id từ GET

// Lấy thông tin sinh viên và trạng thái điểm danh
$sql = "SELECT a.attendance_id, u.full_name, u.email, s.major, a.status
        FROM Students s
        JOIN Users u ON s.user_id = u.user_id
        LEFT JOIN Attendance a ON a.student_id = s.student_id
        WHERE a.class_id = $class_id";
$result = $conn->query($sql);

$students = [];

while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

echo json_encode($students);

$conn->close();
?>
