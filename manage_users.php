<?php
// Kết nối cơ sở dữ liệu
$host = "localhost";
$username = "root";
$password = "";
$dbname = "StudentManagement";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

// Xử lý thêm/sửa người dùng
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'] ?? null;
    $username = $_POST['username'] ?? "";
    $password = $_POST['password'] ?? "";
    $role = $_POST['role'] ?? "";
    $full_name = $_POST['full_name'] ?? "";
    $email = $_POST['email'] ?? "";
    $phone = $_POST['phone'] ?? "";

    if ($user_id) {
        // Cập nhật người dùng
        $sql_update = "UPDATE Users SET username = ?, password = ?, role = ?, full_name = ?, email = ?, phone = ? WHERE user_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("ssssssi", $username, $password, $role, $full_name, $email, $phone, $user_id);
        if ($stmt_update->execute()) {
            $message = "User updated successfully!";
        } else {
            $message = "Error updating user.";
        }
        $stmt_update->close();
    } else {
        // Thêm người dùng
        $sql_insert = "INSERT INTO Users (username, password, role, full_name, email, phone) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("ssssss", $username, $password, $role, $full_name, $email, $phone);
        if ($stmt_insert->execute()) {
            $message = "User added successfully!";
        } else {
            $message = "Error adding user.";
        }
        $stmt_insert->close();
    }
}

// Xử lý xóa người dùng
if (isset($_GET['delete_user']) && filter_var($_GET['delete_user'], FILTER_VALIDATE_INT)) {
    $user_id = intval($_GET['delete_user']);
    $sql_delete = "DELETE FROM Users WHERE user_id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $user_id);
    if ($stmt_delete->execute()) {
        $message = "User deleted successfully!";
    } else {
        $message = "Error deleting user.";
    }
    $stmt_delete->close();
}

// Tìm kiếm người dùng
$search_query = $_GET['search'] ?? '';
$sql_view = "SELECT * FROM Users WHERE username LIKE ? OR full_name LIKE ? OR user_id LIKE ?";
$search_term = "%$search_query%";
$stmt_view = $conn->prepare($sql_view);
$stmt_view->bind_param("sss", $search_term, $search_term, $search_term);
$stmt_view->execute();
$result = $stmt_view->get_result();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
 /* Tổng thể */
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f4f4f9;
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
    max-width: 1200px;
    margin: 20px auto;
    padding: 20px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

/* Tiêu đề */
h1 {
    text-align: center;
    color: #4CAF50;
    margin-bottom: 20px;
}

/* Bảng */
table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
    background: white;
    border-radius: 8px;
    overflow: hidden;
}

th, td {
    padding: 12px;
    text-align: left;
    border: 1px solid #ddd;
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
button, .button {
    padding: 10px 15px;
    font-size: 14px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s;
}

button {
    background-color: #4CAF50;
    color: white;
}

button:hover {
    background-color: #45a049;
}

a.button {
    background-color: #f44336;
    color: white;
    text-decoration: none;
    display: inline-block;
}

a.button:hover {
    background-color: #e53935;
}

/* Delete button */
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

/* Modal */
.modal {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 20px;
    width: 100%;
    max-width: 400px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    z-index: 1000;
}

.modal input, .modal select {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
}

.modal button {
    width: 100%;
    background-color: #4CAF50;
    margin-top: 10px;
}

.modal button:hover {
    background-color: #45a049;
}

/* Close Button */
.modal .close {
    position: absolute;
    top: 10px;
    right: 10px;
    background: none;
    font-size: 20px;
    cursor: pointer;
    border: none;
}

/* Responsive */
@media (max-width: 768px) {
    table {
        font-size: 12px;
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

    <div class="container">
        <h1>Manage Users</h1>
        <?php if (!empty($message)): ?>
            <p class="message"><?= htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <div class="search-bar">
            <form method="GET" action="">
                <input type="text" name="search" placeholder="Search by ID, username, or name" value="<?= htmlspecialchars($search_query); ?>">
                <button type="submit">Search</button>
            </form>
            <button onclick="openModal()">Add User</button>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['user_id']); ?></td>
                            <td><?= htmlspecialchars($row['username']); ?></td>
                            <td><?= htmlspecialchars($row['role']); ?></td>
                            <td><?= htmlspecialchars($row['full_name']); ?></td>
                            <td><?= htmlspecialchars($row['email']); ?></td>
                            <td><?= htmlspecialchars($row['phone']); ?></td>
                            <td class="actions">
                                <button onclick="openModal(<?= htmlspecialchars(json_encode($row)); ?>)">Edit</button>
                                <a href="manage_users.php?delete_user=<?= htmlspecialchars($row['user_id']); ?>" class="delete" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No users found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="modal" id="userModal">
        <div class="modal-content">
            <button class="close" onclick="closeModal()">×</button>
            <form id="userForm" method="POST" action="manage_users.php">
                <input type="hidden" name="user_id">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <select name="role" required>
                    <option value="">Select Role</option>
                    <option value="admin">Admin</option>
                    <option value="teacher">Teacher</option>
                    <option value="student">Student</option>
                </select>
                <input type="text" name="full_name" placeholder="Full Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="text" name="phone" placeholder="Phone">
                <button type="submit">Save</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(data = null) {
            const modal = document.getElementById('userModal');
            const form = document.getElementById('userForm');
            
            form.user_id.value = data?.user_id || '';
            form.username.value = data?.username || '';
            form.password.value = data?.password || '';
            form.role.value = data?.role || '';
            form.full_name.value = data?.full_name || '';
            form.email.value = data?.email || '';
            form.phone.value = data?.phone || '';
            
            modal.style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('userModal').style.display = 'none';
        }
    </script>
</body>
</html>
