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

    <h2>Your Classes</h2>

    <?php
if ($result->num_rows > 0) {
    echo "<table>
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