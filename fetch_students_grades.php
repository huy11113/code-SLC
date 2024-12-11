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

$class_id = $_GET['class_id']; // Nhận class_id từ yêu cầu GET

// Truy vấn lấy danh sách sinh viên và điểm trong lớp
$sql = "SELECT s.student_id, u.full_name, u.email, s.major, g.grade, g.grade_id
        FROM Students s
        JOIN Users u ON s.user_id = u.user_id
        LEFT JOIN Grades g ON s.student_id = g.student_id
        WHERE g.class_id = $class_id";
$result = $conn->query($sql);

$students = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
}

echo json_encode($students); // Trả dữ liệu dưới dạng JSON

$conn->close();
?>
