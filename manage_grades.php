<?php
// Kết nối với cơ sở dữ liệu
$mysqli = new mysqli("localhost", "root", "", "StudentManagement");

// Kiểm tra kết nối
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Lấy danh sách điểm sinh viên
$query_grades = "
    SELECT 
        Grades.grade_id, 
        Students.student_id, 
        Students.user_id, 
        Users.full_name AS student_name, 
        Courses.course_name, 
        Classes.class_name, 
        Grades.grade 
    FROM Grades
    JOIN Students ON Grades.student_id = Students.student_id
    JOIN Users ON Students.user_id = Users.user_id
    JOIN Courses ON Grades.course_id = Courses.course_id
    JOIN Classes ON Grades.class_id = Classes.class_id";

$result_grades = $mysqli->query($query_grades);

// Thêm hoặc cập nhật điểm
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_grade'])) {
    $grade_id = $_POST['grade_id'];
    $student_id = $_POST['student_id'];
    $course_id = $_POST['course_id'];
    $class_id = $_POST['class_id'];
    $grade = $_POST['grade'];

    if (!empty($grade_id)) {
        // Cập nhật điểm
        $stmt = $mysqli->prepare("UPDATE Grades SET grade = ? WHERE grade_id = ?");
        $stmt->bind_param("di", $grade, $grade_id);
    } else {
        // Thêm mới điểm
        $stmt = $mysqli->prepare("INSERT INTO Grades (student_id, course_id, class_id, grade) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiid", $student_id, $course_id, $class_id, $grade);
    }
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
}

// Xóa điểm
if (isset($_GET['delete_grade'])) {
    $grade_id = $_GET['delete_grade'];
    $stmt = $mysqli->prepare("DELETE FROM Grades WHERE grade_id = ?");
    $stmt->bind_param("i", $grade_id);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Grades</title>
    <style>
     /* Tổng thể */
body {
    font-family: Arial, sans-serif;
    margin: 20px;
    background-color: #f4f4f9;
    color: #333;
}

/* Thanh menu */
.navbar {
    background-color: #333;
    overflow: hidden;
    padding: 0; /* Remove default padding */
}

.navbar ul {
    list-style-type: none; /* Remove default bullets */
    margin: 0; /* Remove default margin */
    padding: 0; /* Remove default padding */
    overflow: hidden;
}

.navbar li {
    float: left; /* Float list items to the left */
}

.navbar li a {
    display: block;
    color: white;
    text-align: center;
    padding: 14px 20px;
    text-decoration: none;
}

.navbar li a:hover {
    background-color: #ddd;
    color: black;
}

/* Container */
.container {
    padding: 20px;
}

h1 {
    text-align: center;
    color: #4CAF50;
}

/* Bảng */
table {
    border-collapse: collapse;
    width: 100%;
    margin-bottom: 20px;
    background: white;
    border-radius: 8px;
    overflow: hidden;
}

table, th, td {
    border: 1px solid #ddd;
}

th, td {
    padding: 10px;
    text-align: left;
    font-size: 14px;
}

th {
    background-color: #4CAF50;
    color: white;
}

tr:hover {
    background-color: #f1f1f1;
}

/* Nút */
button, a.button {
    padding: 10px 15px;
    font-size: 14px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s;
    text-decoration: none;
    color: white;
    display: inline-block;
    margin-top: 10px;
}

button {
    background-color: #4CAF50;
}

button:hover {
    background-color: #45a049;
}

a.button {
    background-color: #f44336;
}

a.button:hover {
    background-color: #e53935;
}

a.delete {
    background-color: #e53935;
    color: white;
    text-decoration: none;
    padding: 10px 15px;
    border-radius: 5px;
    display: inline-block;
    margin-top: 10px;
    transition: background 0.3s;
}

a.delete:hover {
    background-color: #d32f2f;
}

/* Modal styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.5);
}

.modal-content {
    background-color: white;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 50%;
    border-radius: 8px;
}

.modal-header {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 10px;
}

.modal-footer {
    text-align: right;
    margin-top: 20px;
}

.close {
    color: red;
    font-size: 20px;
    float: right;
    cursor: pointer;
}

/* Modal styles */

/* Nút */
button {
    padding: 10px 20px;
    margin: 5px;
    background-color: #4CAF50;
    color: white;
    border: none;
    cursor: pointer;
    border-radius: 5px;
    transition: background-color 0.3s ease;
}

button:hover {
    background-color: #45a049;
}

a.button {
    padding: 10px 20px;
    background-color: #f44336;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    transition: background-color 0.3s ease;
}

a.button:hover {
    background-color: #e53935;
}

/* Modal */
.modal {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 20px;
    border: 1px solid #ccc;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    z-index: 100;
    border-radius: 10px;
}

.modal input {
    width: 100%;
    padding: 8px;
    margin: 5px 0;
    border-radius: 5px;
    border: 1px solid #ccc;
}

.modal button {
    background-color: #f44336;
}

.modal button:hover {
    background-color: #e53935;
}
/* Nút Delete */
a.delete {
    background-color: #ff5252; /* Đỏ sáng hơn để thu hút sự chú ý */
    color: white;
    text-decoration: none;
    padding: 10px 15px;
    border-radius: 5px;
    display: inline-block;
    transition: transform 0.2s, background-color 0.3s;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Tạo hiệu ứng đổ bóng */
}

a.delete:hover {
    background-color: #ff1744; /* Đậm hơn khi hover */
    transform: scale(1.05); /* Phóng to nhẹ khi hover */
    box-shadow: 0 6px 10px rgba(0, 0, 0, 0.15);
}


    </style>
</head>

<body>
    <!-- Thanh menu -->
    <h1>Admin Dashboard</h1>
            <nav class="navbar">
                <ul>
                    <li><a href="manage_users.php">Manage Users</a></li>
                    <li><a href="manage_classes_courses.php">Manage Classes & Courses</a></li>
                    <li><a href="manage_grades.php">Manage Grades</a></li>
                    <li><a href="manage_attendance.php">Manage Attendance</a></li>
                </ul>
            </nav>

    <h1>Manage Grades</h1>
    <button onclick="openGradeModal()">Add New Grade</button>
    <table>
        <thead>
            <tr>
                <th>Student Name</th>
                <th>Course Name</th>
                <th>Class Name</th>
                <th>Grade</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result_grades->fetch_assoc()) { ?>
                <tr>
                    <td><?= htmlspecialchars($row['student_name']) ?></td>
                    <td><?= htmlspecialchars($row['course_name']) ?></td>
                    <td><?= htmlspecialchars($row['class_name']) ?></td>
                    <td><?= htmlspecialchars($row['grade']) ?></td>
                    <td>
                        <button onclick="editGrade(<?= htmlspecialchars(json_encode($row)) ?>)">Edit</button>
                        <a href="?delete_grade=<?= $row['grade_id'] ?>" class="delete" onclick="return confirm('Are you sure?')">Delete</a>

                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <!-- Modal Form -->
    <div id="gradeModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <div class="modal-header">Grade Information</div>
            <form method="POST">
                <input type="hidden" name="grade_id" id="grade_id">
                <label for="student_id">Student ID:</label>
                <input type="number" name="student_id" id="student_id" required>
                <br><br>
                <label for="course_id">Course ID:</label>
                <input type="number" name="course_id" id="course_id" required>
                <br><br>
                <label for="class_id">Class ID:</label>
                <input type="number" name="class_id" id="class_id" required>
                <br><br>
                <label for="grade">Grade:</label>
                <input type="number" step="0.01" name="grade" id="grade" required>
                <div class="modal-footer">
                    <button type="submit" name="save_grade">Save</button>
                    <button type="button" onclick="closeModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Open the modal
        function openGradeModal() {
            document.getElementById("grade_id").value = ""; // Clear form for new entry
            document.getElementById("student_id").value = "";
            document.getElementById("course_id").value = "";
            document.getElementById("class_id").value = "";
            document.getElementById("grade").value = "";
            document.getElementById("gradeModal").style.display = "block";
        }

        // Close the modal
        function closeModal() {
            document.getElementById("gradeModal").style.display = "none";
        }

        // Edit existing grade
        function editGrade(data) {
            document.getElementById("grade_id").value = data.grade_id;
            document.getElementById("student_id").value = data.student_id;
            document.getElementById("course_id").value = data.course_id;
            document.getElementById("class_id").value = data.class_id;
            document.getElementById("grade").value = data.grade;
            document.getElementById("gradeModal").style.display = "block";
        }

        // Close modal when clicking outside of it
        window.onclick = function (event) {
            const modal = document.getElementById("gradeModal");
            if (event.target === modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>

</html>

