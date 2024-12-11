<?php
// Kết nối cơ sở dữ liệu
include('db.php');

// Kiểm tra nếu người dùng đã đăng nhập và có quyền 'student'
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php"); // Nếu chưa đăng nhập hoặc không phải sinh viên, chuyển hướng đến trang đăng nhập
    exit;
}

$user_id = $_SESSION['user_id'];

// Lấy thông tin sinh viên
$sql = "SELECT u.full_name, u.email, u.phone FROM Users u 
        JOIN Students s ON u.user_id = s.user_id
        WHERE u.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_info = $stmt->get_result()->fetch_assoc();

// Lấy danh sách lớp học và môn học
function getClasses($conn, $user_id) {
    $sql = "SELECT c.class_name, c.schedule, u.full_name AS teacher_name
            FROM Classes c
            JOIN Course_Classes cc ON c.class_id = cc.class_id
            JOIN Teachers t ON cc.teacher_id = t.teacher_id
            JOIN Users u ON t.user_id = u.user_id
            WHERE EXISTS (
                SELECT 1 
                FROM Grades g
                WHERE g.class_id = cc.class_id 
                AND g.student_id = (
                    SELECT student_id FROM Students WHERE user_id = ? 
                )
            )";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Lấy điểm và điểm danh
function getGradesAndAttendance($conn, $user_id) {
    $grades_sql = "SELECT c.course_name, g.grade
                   FROM Grades g
                   JOIN Courses c ON g.course_id = c.course_id
                   WHERE g.student_id = (
                       SELECT student_id FROM Students WHERE user_id = ? 
                   )";
    $grades_stmt = $conn->prepare($grades_sql);
    $grades_stmt->bind_param("i", $user_id);
    $grades_stmt->execute();
    $grades = $grades_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $attendance_sql = "SELECT cl.class_name, a.date, a.status
                       FROM Attendance a
                       JOIN Classes cl ON a.class_id = cl.class_id
                       WHERE a.student_id = (
                           SELECT student_id FROM Students WHERE user_id = ? 
                       )";
    $attendance_stmt = $conn->prepare($attendance_sql);
    $attendance_stmt->bind_param("i", $user_id);
    $attendance_stmt->execute();
    $attendance = $attendance_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    return ["grades" => $grades, "attendance" => $attendance];
}

$classes = getClasses($conn, $user_id);
$data = getGradesAndAttendance($conn, $user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        function showSection(sectionId) {
            const sections = document.querySelectorAll('.section');
            sections.forEach(function(section) {
                section.style.display = 'none';
            });

            const activeSection = document.getElementById(sectionId);
            if (activeSection) {
                activeSection.style.display = 'block';
            }
        }

        // Chức năng chỉnh sửa thông tin cá nhân
        function editPersonalInfo() {
            document.getElementById('edit_personal_info').style.display = 'block';
            document.getElementById('personal_info').style.display = 'none';
        }

        function savePersonalInfo() {
            // Thực hiện AJAX hoặc gửi form để cập nhật thông tin sinh viên
            alert("Personal information updated!");
        }
    </script>
    <style>
       /* General Styles */
body {
    font-family: Arial, sans-serif;
    background-color: #f0f2f5;
    margin: 0;
    padding: 0;
}

.navbar {
    background-color: #333;
    color: white;
    padding: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.navbar h1 {
    margin: 0;
}

.navbar a {
    color: white;
    text-decoration: none;
    padding: 10px;
    background-color: #f44336;
    border-radius: 5px;
}

.navbar a:hover {
    background-color: #d32f2f;
}

.container {
    display: flex;
    margin: 20px;
}

/* Sidebar Styles */
.sidebar {
    width: 200px; /* Fixed width for the sidebar */
    background-color: #000;
    color: white;
    padding: 10px;
    position: fixed;
    height: 100%;
}

.sidebar ul {
    list-style-type: none;
    padding: 0;
}

.sidebar ul li {
    margin: 10px 0;
}

.sidebar ul li a {
    color: white;
    text-decoration: none;
}

.sidebar ul li a:hover {
    text-decoration: underline;
}

/* Main Content Styles */
.main-content {
    margin-left: 220px; /* Create space for the fixed sidebar */
    padding: 20px;
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    flex-grow: 1;
}

.section {
    display: none;
}

h2 {
    border-bottom: 2px solid #333;
    padding-bottom: 10px;
}

/* Personal Info Section */
#personal_info p {
    font-size: 18px;
    margin: 10px 0;
}

button {
    background-color: #333;
    color: white;
    border: none;
    padding: 10px 20px;
    cursor: pointer;
    border-radius: 5px;
}

button:hover {
    background-color: #555;
}

.edit-form input {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
    border: 1px solid #ccc;
    border-radius: 5px;
}

    </style>
</head>
<body onload="showSection('personal_info')">
    <div class="navbar">
        <h1>Welcome, <?php echo htmlspecialchars($user_info['full_name']); ?></h1>
        <a href="Login.php" style="color:white;">Logout</a>
    </div>

    <div class="container">
        <div class="sidebar">
            <ul>
                <li><a href="javascript:void(0)" onclick="showSection('personal_info')">Personal Info</a></li>
                <li><a href="javascript:void(0)" onclick="showSection('class_list')">Class List</a></li>
                <li><a href="javascript:void(0)" onclick="showSection('grades')">Grades</a></li>
                <li><a href="javascript:void(0)" onclick="showSection('attendance')">Attendance</a></li>
            </ul>
        </div>

        <div class="main-content">
            <!-- Personal Info Section -->
            <div id="personal_info" class="section">
                <h2>Personal Info</h2>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($user_info['full_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user_info['email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($user_info['phone']); ?></p>
                <button onclick="editPersonalInfo()">Edit</button>
            </div>

            <!-- Edit Personal Info Section -->
            <div id="edit_personal_info" class="section" style="display: none;">
                <h2>Edit Personal Info</h2>
                <form class="edit-form">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" value="<?php echo htmlspecialchars($user_info['full_name']); ?>" />

                    <label for="email">Email</label>
                    <input type="email" id="email" value="<?php echo htmlspecialchars($user_info['email']); ?>" />

                    <label for="phone">Phone</label>
                    <input type="text" id="phone" value="<?php echo htmlspecialchars($user_info['phone']); ?>" />

                    <button type="button" onclick="savePersonalInfo()">Save</button>
                </form>
                <button onclick="showSection('personal_info')">Cancel</button>
            </div>

            <!-- Class List Section -->
            <div id="class_list" class="section">
                <h2>Class List</h2>
                <ul>
                    <?php
                    if (empty($classes)) {
                        echo "<p>No classes found!</p>";
                    } else {
                        foreach ($classes as $class) {
                            echo "<li>{$class['class_name']} - {$class['schedule']} (Instructor: {$class['teacher_name']})</li>";
                        }
                    }
                    ?>
                </ul>
            </div>

            <!-- Grades Section -->
            <div id="grades" class="section">
                <h2>Grades</h2>
                <ul>
                    <?php
                    if (empty($data['grades'])) {
                        echo "<p>No grades found!</p>";
                    } else {
                        foreach ($data['grades'] as $grade) {
                            echo "<li>{$grade['course_name']} - Grade: {$grade['grade']}</li>";
                        }
                    }
                    ?>
                </ul>
            </div>

            <!-- Attendance Section -->
            <div id="attendance" class="section">
                <h2>Attendance</h2>
                <ul>
                    <?php
                    if (empty($data['attendance'])) {
                        echo "<p>No attendance records found!</p>";
                    } else {
                        foreach ($data['attendance'] as $attendance) {
                            echo "<li>{$attendance['class_name']} - {$attendance['date']} - Status: {$attendance['status']}</li>";
                        }
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>
