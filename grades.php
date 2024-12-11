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

// Kiểm tra nếu có yêu cầu cập nhật điểm
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['grade_id']) && isset($_POST['new_grade'])) {
    $grade_id = $_POST['grade_id'];
    $new_grade = $_POST['new_grade'];

    // Cập nhật điểm cho sinh viên
    $sql = "UPDATE Grades SET grade = ? WHERE grade_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("di", $new_grade, $grade_id);
    if ($stmt->execute()) {
        echo "<script>alert('Grade updated successfully');</script>";
    } else {
        echo "<script>alert('Error updating grade');</script>";
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
                <td><button onclick='viewStudents(" . $row['class_id'] . ", \"" . $row['class_name'] . "\")'>View Students</button></td>
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
                    <th>Grade</th>
                    <th>Action</th>
                </tr>
                <!-- Student list and grades will be populated here -->
            </table>
        </div>
    </div>

    <script>
    // Show the modal and fetch student data
    function viewStudents(classId, className) {
        document.getElementById("classTitle").innerText = "Students in Class: " + className;
        fetchStudents(classId);
        document.getElementById("studentModal").style.display = "block";
    }

    // Close the modal
    function closeModal() {
        document.getElementById("studentModal").style.display = "none";
    }

    // Fetch students and their grades using AJAX
    function fetchStudents(classId) {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "fetch_students_grades.php?class_id=" + classId, true);
        xhr.onload = function() {
            if (xhr.status == 200) {
                var students = JSON.parse(xhr.responseText);
                var table = document.getElementById("studentTable");
                table.innerHTML =
                    "<tr><th>Student Name</th><th>Email</th><th>Major</th><th>Grade</th><th>Action</th></tr>"; // Reset table
                students.forEach(function(student) {
                    var row = table.insertRow();
                    row.insertCell(0).innerText = student.full_name;
                    row.insertCell(1).innerText = student.email;
                    row.insertCell(2).innerText = student.major;
                    row.insertCell(3).innerText = student.grade;
                    var updateBtn = row.insertCell(4);
                    updateBtn.innerHTML =
                        `<button onclick='updateGrade(${student.grade_id}, ${student.grade})'>Update Grade</button>`;
                });
            }
        };
        xhr.send();
    }

    // Update grade function
    function updateGrade(gradeId, currentGrade) {
        var newGrade = prompt("Enter new grade for the student:", currentGrade);
        if (newGrade !== null && !isNaN(newGrade) && newGrade >= 0 && newGrade <= 10) {
            // Submit form to update grade
            var formData = new FormData();
            formData.append('grade_id', gradeId);
            formData.append('new_grade', newGrade);
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "manage_grades.php", true);
            xhr.onload = function() {
                if (xhr.status == 200) {
                    alert("Grade updated successfully!");
                    location.reload(); // Reload the page to refresh the data
                }
            };
            xhr.send(formData);
        } else {
            alert("Invalid grade.");
        }
    }
    </script>

</body>

</html>