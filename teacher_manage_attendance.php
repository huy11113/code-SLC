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

// Kiểm tra nếu có yêu cầu cập nhật điểm danh
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['attendance_id']) && isset($_POST['new_status'])) {
    $attendance_id = $_POST['attendance_id'];
    $new_status = $_POST['new_status'];

    // Cập nhật điểm danh cho sinh viên
    $sql = "UPDATE Attendance SET status = ? WHERE attendance_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_status, $attendance_id);
    if ($stmt->execute()) {
        echo "<script>alert('Attendance status updated successfully');</script>";
    } else {
        echo "<script>alert('Error updating attendance');</script>";
    }
}

// Lấy danh sách lớp học mà giảng viên đang giảng dạy
$sql = "SELECT c.class_id, c.class_name 
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
                <th>Action</th>
            </tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . $row['class_name'] . "</td>
                <td><button onclick='viewStudents(" . $row['class_id'] . ", \"" . $row['class_name'] . "\")'>View Attendance</button></td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "You are not teaching any classes.";
}

$conn->close();
?>

    <!-- Modal -->
    <div id="studentModal" style="display:none;">
        <div>
            <h2 id="classTitle"></h2>
            <table id="studentTable">
                <tr>
                    <th>Student Name</th>
                    <th>Email</th>
                    <th>Major</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                <!-- Student list and attendance status will be populated here -->
            </table>
        </div>
    </div>

    <script>
    // Show the modal and fetch student data
    function viewStudents(classId, className) {
        document.getElementById("classTitle").innerText = "Attendance for Class: " + className;
        fetchStudents(classId);
        document.getElementById("studentModal").style.display = "block";
    }

    // Close the modal
    function closeModal() {
        document.getElementById("studentModal").style.display = "none";
    }

    // Fetch students and their attendance status using AJAX
    function fetchStudents(classId) {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "fetch_students_attendance.php?class_id=" + classId, true);
        xhr.onload = function() {
            if (xhr.status == 200) {
                var students = JSON.parse(xhr.responseText);
                var table = document.getElementById("studentTable");
                table.innerHTML =
                    "<tr><th>Student Name</th><th>Email</th><th>Major</th><th>Status</th><th>Action</th></tr>"; // Reset table
                students.forEach(function(student) {
                    var row = table.insertRow();
                    row.insertCell(0).innerText = student.full_name;
                    row.insertCell(1).innerText = student.email;
                    row.insertCell(2).innerText = student.major;
                    row.insertCell(3).innerText = student.status;
                    var updateBtn = row.insertCell(4);
                    updateBtn.innerHTML =
                        `<button onclick='updateAttendance(${student.attendance_id}, "${student.status}")'>Update Status</button>`;
                });
            }
        };
        xhr.send();
    }

    // Update attendance status function
    function updateAttendance(attendanceId, currentStatus) {
        var newStatus = prompt("Enter new attendance status (Present/Absent):", currentStatus);
        if (newStatus !== null && (newStatus.toLowerCase() === "present" || newStatus.toLowerCase() === "absent")) {
            // Submit form to update attendance
            var formData = new FormData();
            formData.append('attendance_id', attendanceId);
            formData.append('new_status', newStatus);
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "manage_attendance.php", true);
            xhr.onload = function() {
                if (xhr.status == 200) {
                    alert("Attendance status updated successfully!");
                    location.reload(); // Reload the page to refresh the data
                }
            };
            xhr.send(formData);
        } else {
            alert("Invalid status. Please enter either 'Present' or 'Absent'.");
        }
    }
    </script>

</body>

</html>