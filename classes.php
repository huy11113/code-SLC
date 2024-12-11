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

$teacher_id = 1; // Giả sử giảng viên có ID là 1, thay đổi theo người dùng đăng nhập

// Lấy danh sách lớp học mà giảng viên đang giảng dạy
$sql = "SELECT c.class_id, c.class_name, c.start_date, c.end_date 
        FROM Classes c
        JOIN Course_Classes cc ON c.class_id = cc.class_id
        WHERE cc.teacher_id = $teacher_id";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Classes</title>
</head>
<body>

<h2>Manage Your Classes</h2>

<?php
if ($result->num_rows > 0) {
    echo "<table border='1'>
            <tr>
                <th>Class Name</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Action</th>
            </tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . $row['class_name'] . "</td>
                <td>" . $row['start_date'] . "</td>
                <td>" . $row['end_date'] . "</td>
                <td><a href='view_students.php?class_id=" . $row['class_id'] . "'>View Students</a></td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "You are not teaching any classes.";
}

$conn->close();
?>

</body>
</html>
