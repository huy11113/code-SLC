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

// Truy vấn lấy danh sách sinh viên trong lớp qua bảng Attendance
$sql = "SELECT s.student_id, u.full_name, u.email, s.major 
        FROM Students s
        JOIN Users u ON s.user_id = u.user_id
        JOIN Attendance a ON s.student_id = a.student_id
        WHERE a.class_id = $class_id";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wellcome Teacher</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f9f9f9;
        margin: 0;
        padding: 0;
    }

    nav {
        background-color: #4CAF50;
        padding: 10px;
    }

    nav a {
        color: white;
        padding: 10px 15px;
        text-decoration: none;
        display: inline-block;
    }

    nav a:hover {
        background-color: #45a049;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
    }

    table,
    th,
    td {
        border: 1px solid #ddd;
    }

    th,
    td {
        padding: 10px;
        text-align: left;
    }

    h1 {
        color: #4CAF50;
        text-align: center;
        margin-top: 10px;
    }

    h2 {
        color: #333;
        text-align: center;
        margin-top: 20px;
    }
    </style>
</head>

<body>

    <h1>Wellcome Teacher</h1>

    <nav>
        <a href="Teacherhome.php">Manage Classes</a>
        <a href="grades.php">Manage Grades</a>
        <a href="teacher_manage_attendance.php">Manage Attendance</a>
    </nav>

    <h2>Students in Class</h2>

    <?php
if ($result->num_rows > 0) {
    echo "<table>
            <tr>
                <th>Student Name</th>
                <th>Email</th>
                <th>Major</th>
            </tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . $row['full_name'] . "</td>
                <td>" . $row['email'] . "</td>
                <td>" . $row['major'] . "</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "No students found in this class.";
}

$conn->close();
?>

</body>

</html>