<?php
// Kết nối với cơ sở dữ liệu
$mysqli = new mysqli("localhost", "root", "", "StudentManagement");

// Kiểm tra kết nối
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Lấy danh sách lớp học
$query_classes = "SELECT * FROM Classes";
$result_classes = $mysqli->query($query_classes);

// Lấy danh sách môn học
$query_courses = "SELECT * FROM Courses";
$result_courses = $mysqli->query($query_courses);

// Thêm hoặc sửa lớp học
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_class'])) {
    $class_id = $_POST['class_id'];
    $class_name = $_POST['class_name'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $schedule = $_POST['schedule'];

    if (!empty($class_id)) {
        // Cập nhật lớp học
        $stmt = $mysqli->prepare("UPDATE Classes SET class_name = ?, start_date = ?, end_date = ?, schedule = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $class_name, $start_date, $end_date, $schedule, $class_id);
    } else {
        // Thêm mới lớp học
        $stmt = $mysqli->prepare("INSERT INTO Classes (class_name, start_date, end_date, schedule) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $class_name, $start_date, $end_date, $schedule);
    }
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
}

// Xóa lớp học
if (isset($_GET['delete_class'])) {
    $class_id = $_GET['delete_class'];
    $stmt = $mysqli->prepare("DELETE FROM Classes WHERE id = ?");
    $stmt->bind_param("i", $class_id);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
}

// Thêm hoặc sửa môn học
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_course'])) {
    $course_id = $_POST['course_id'];
    $course_name = $_POST['course_name'];
    $credits = $_POST['credits'];

    if (!empty($course_id)) {
        // Cập nhật môn học
        $stmt = $mysqli->prepare("UPDATE Courses SET course_name = ?, credits = ? WHERE id = ?");
        $stmt->bind_param("sii", $course_name, $credits, $course_id);
    } else {
        // Thêm mới môn học
        $stmt = $mysqli->prepare("INSERT INTO Courses (course_name, credits) VALUES (?, ?)");
        $stmt->bind_param("si", $course_name, $credits);
    }
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
    <title>Manage Classes & Courses</title>
    <style>
       /* Tổng thể */
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f9f9f9;
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
    color: #333;
}

/* Bảng */
table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
    background-color: white;
}

th, td {
    padding: 10px;
    text-align: left;
    border: 1px solid #ddd;
}

th {
    background-color: #4CAF50;
    color: white;
}

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

    <!-- Nội dung -->
    <div class="container">
        <h1 id="manageClasses">Manage Classes</h1>
        <button onclick="openClassModal()">Add New Class</button>
        <table>
            <thead>
                <tr>
                    <th>Class Name</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Schedule</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result_classes->fetch_assoc()) { ?>
                <tr>
                    <td><?= $row['class_name'] ?></td>
                    <td><?= $row['start_date'] ?></td>
                    <td><?= $row['end_date'] ?></td>
                    <td><?= $row['schedule'] ?></td>
                    <td>
                        <button onclick="editClass(<?= htmlspecialchars(json_encode($row)) ?>)">Edit</button>
                        <a href="?delete_class=<?= $row['class_id'] ?>" class="button" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <h1 id="manageCourses">Manage Courses</h1>
        <button onclick="openCourseModal()">Add New Course</button>
        <table>
            <thead>
                <tr>
                    <th>Course Name</th>
                    <th>Credits</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result_courses->fetch_assoc()) { ?>
                <tr>
                    <td><?= $row['course_name'] ?></td>
                    <td><?= $row['credits'] ?></td>
                    <td>
                        <button onclick="editCourse(<?= htmlspecialchars(json_encode($row)) ?>)">Edit</button>
                        <a href="?delete_course=<?= $row['course_id'] ?>" class="button" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Modal Forms -->
    <div id="classModal" class="modal">
        <form method="POST">
            <input type="hidden" name="class_id" id="class_id">
            <label>Class Name:</label>
            <input type="text" name="class_name" id="class_name" required>
            <label>Start Date:</label>
            <input type="date" name="start_date" id="start_date" required>
            <label>End Date:</label>
            <input type="date" name="end_date" id="end_date" required>
            <label>Schedule:</label>
            <input type="text" name="schedule" id="schedule" required>
            <button type="submit" name="save_class">Save</button>
            <button type="button" onclick="closeModal('classModal')">Cancel</button>
        </form>
    </div>

    <div id="courseModal" class="modal">
        <form method="POST">
            <input type="hidden" name="course_id" id="course_id">
            <label>Course Name:</label>
            <input type="text" name="course_name" id="course_name" required>
            <label>Credits:</label>
            <input type="number" name="credits" id="credits" required>
            <button type="submit" name="save_course">Save</button>
            <button type="button" onclick="closeModal('courseModal')">Cancel</button>
        </form>
    </div>

    <script>
        function openClassModal() {
            document.getElementById("classModal").style.display = "block";
        }

        function openCourseModal() {
            document.getElementById("courseModal").style.display = "block";
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = "none";
        }

        function editClass(data) {
            document.getElementById("class_id").value = data.id;
            document.getElementById("class_name").value = data.class_name;
            document.getElementById("start_date").value = data.start_date;
            document.getElementById("end_date").value = data.end_date;
            document.getElementById("schedule").value = data.schedule;
            openClassModal();
        }

        function editCourse(data) {
            document.getElementById("course_id").value = data.id;
            document.getElementById("course_name").value = data.course_name;
            document.getElementById("credits").value = data.credits;
            openCourseModal();
        }
    </script>
</body>

</html>
