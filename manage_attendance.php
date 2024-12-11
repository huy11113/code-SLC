<?php
// Kết nối đến database
$conn = new mysqli('localhost', 'root', '', 'StudentManagement');
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Xử lý cập nhật trạng thái điểm danh
if (isset($_POST['update_attendance'])) {
    $attendance_id = $_POST['attendance_id'];
    $status = $_POST['status'];
    $sql_update = "UPDATE Attendance SET status = '$status' WHERE attendance_id = $attendance_id";
    $conn->query($sql_update);
}

// Xử lý thêm điểm danh mới
if (isset($_POST['add_attendance'])) {
    $student_id = $_POST['student_id'];
    $class_id = $_POST['class_id'];
    $date = $_POST['date'];
    $status = $_POST['status'];
    $sql_insert = "INSERT INTO Attendance (student_id, class_id, date, status) VALUES ($student_id, $class_id, '$date', '$status')";
    $conn->query($sql_insert);
}

// Lấy danh sách điểm danh
$sql_attendance = "
    SELECT a.attendance_id, s.student_id, s.user_id, u.full_name AS student_name, c.class_name, a.date, a.status
    FROM Attendance a
    JOIN Students s ON a.student_id = s.student_id
    JOIN Classes c ON a.class_id = c.class_id
    JOIN Users u ON s.user_id = u.user_id
";
$result_attendance = $conn->query($sql_attendance);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Attendance</title>
    <style>
   /* Tổng thể */
body {
    font-family: 'Arial', sans-serif;
    background-color: #f9f9f9;
    margin: 20px;
    color: #333;
}

/* Thanh menu */
.navbar {
    background-color: #333;
    overflow: hidden;
    padding: 0;
}

.navbar ul {
    list-style-type: none;
    margin: 0;
    padding: 0;
    overflow: hidden;
}

.navbar li {
    float: left;
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
    font-size: 28px;
    margin-bottom: 20px;
}

h2 {
    color: #555;
    font-size: 22px;
    margin-bottom: 15px;
}

/* Bảng */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background: white;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    overflow: hidden;
}

table th, table td {
    padding: 15px;
    text-align: left;
    font-size: 14px;
}

table th {
    background-color: #4CAF50;
    color: white;
}

table tr:nth-child(even) {
    background-color: #f2f2f2;
}

table tr:hover {
    background-color: #f1f1f1;
}

/* Nút */
.btn {
    padding: 10px 15px;
    font-size: 14px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
    color: white;
}

.btn.add {
    background-color: #28a745;
}

.btn.add:hover {
    background-color: #218838;
}

.btn.update {
    background-color: #007bff;
}

.btn.update:hover {
    background-color: #0056b3;
}

/* Modal */
.modal {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 50%;
    background: white;
    padding: 20px;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    border-radius: 10px;
    z-index: 1000;
}

.modal-content {
    margin: 0 auto;
    border-radius: 10px;
}

.modal input, .modal select {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
    border-radius: 5px;
    border: 1px solid #ccc;
    box-sizing: border-box;
}

.modal .close {
    color: #aaa;
    font-size: 24px;
    font-weight: bold;
    float: right;
    cursor: pointer;
}

.modal .close:hover {
    color: black;
}

/* Hiệu ứng modal */
.modal.fade {
    animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
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
    <h1>Manage Attendance</h1>

    <!-- Nút Add để hiển thị hộp thoại -->
    <button class="btn add" onclick="openModal()">Add Attendance</button>

    <!-- Hộp thoại thêm điểm danh -->
    <div id="attendanceModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Add New Attendance</h2>
            <form method="POST">
                <label for="student_id">Student ID:</label>
                <input type="number" name="student_id" required>
                <br><br>
                <label for="class_id">Class ID:</label>
                <input type="number" name="class_id" required>
                <br><br>
                <label for="date">Date:</label>
                <input type="date" name="date" required>
                <br><br>
                <label for="status">Status:</label>
                <select name="status" required>
                    <option value="present">Present</option>
                    <option value="absent">Absent</option>
                </select>
                <br><br>
                <button class="btn add" type="submit" name="add_attendance">Submit</button>
            </form>
        </div>
    </div>

    <!-- Hiển thị danh sách điểm danh -->
    <h2>Attendance List</h2>
    <table>
        <thead>
            <tr>
                <th>Student Name</th>
                <th>Class Name</th>
                <th>Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result_attendance->fetch_assoc()) { ?>
                <tr>
                    <td><?= htmlspecialchars($row['student_name']) ?></td>
                    <td><?= htmlspecialchars($row['class_name']) ?></td>
                    <td><?= htmlspecialchars($row['date']) ?></td>
                    <td><?= htmlspecialchars($row['status']) ?></td>
                    <td>
                        <!-- Form cập nhật trạng thái -->
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="attendance_id" value="<?= $row['attendance_id'] ?>">
                            <select name="status">
                                <option value="present" <?= $row['status'] === 'present' ? 'selected' : '' ?>>Present</option>
                                <option value="absent" <?= $row['status'] === 'absent' ? 'selected' : '' ?>>Absent</option>
                            </select>
                            <button class="btn update" type="submit" name="update_attendance">Update</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <script>
        // Hiển thị hộp thoại
        function openModal() {
            document.getElementById('attendanceModal').style.display = 'block';
        }

        // Đóng hộp thoại
        function closeModal() {
            document.getElementById('attendanceModal').style.display = 'none';
        }
    </script>
</body>

</html>
