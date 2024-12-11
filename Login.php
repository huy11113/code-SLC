<?php
session_start();

// Thông tin kết nối cơ sở dữ liệu
$host = "localhost";  // Địa chỉ server MySQL
$username = "root";   // Tên người dùng MySQL
$password = "";       // Mật khẩu MySQL
$dbname = "StudentManagement"; // Tên cơ sở dữ liệu

// Kết nối đến MySQL
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Xử lý khi người dùng nhấn nút Đăng nhập
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = $_POST['username'];
    $pass = $_POST['password'];
    $role = $_POST['role'];

    // Truy vấn để lấy thông tin người dùng
    $sql = "SELECT * FROM Users WHERE username = ? AND role = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $user, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Kiểm tra mật khẩu
        if (password_verify($pass, $row['password'])) {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['role'] = $row['role'];

            // Chuyển hướng theo vai trò
            switch ($row['role']) {
                case 'admin':
                    header("Location: Adminhome.php");
                    break;
                case 'teacher':
                    header("Location: Teacherhome.php");
                    break;
                case 'student':
                    header("Location: student_dashboard.php");
                    break;
                default:
                    echo "<script>alert('Quyền không hợp lệ!');</script>";
                    break;
            }
        } else {
            echo "<script>alert('Mật khẩu không chính xác!');</script>";
        }
    } else {
        echo "<script>alert('Tên đăng nhập hoặc quyền không đúng!');</script>";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            background-color: #f7f9fc;
            font-family: 'Arial', sans-serif;
            padding: 50px;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-container {
            background: #ffffff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }

        label {
            font-size: 14px;
            color: #555;
            margin-bottom: 5px;
            display: block;
        }

        input[type="text"], input[type="password"], select {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ccc;
            margin-bottom: 20px;
            font-size: 16px;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus, input[type="password"]:focus, select:focus {
            border-color: #007bff;
            outline: none;
        }

        .btn {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .text-center {
            text-align: center;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <form action="" method="POST">
            <!-- Trường tên đăng nhập -->
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>
            
            <!-- Trường mật khẩu -->
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <!-- Chọn quyền người dùng (Admin, Teacher, Student) -->
            <label for="role">Role</label>
            <select id="role" name="role" required>
                <option value="admin">Admin</option>
                <option value="teacher">Teacher</option>
                <option value="student">Student</option>
            </select>
            
            <!-- Nút đăng nhập -->
            <button type="submit" class="btn">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php" class="text-primary">Register here</a></p>
    </div>
</body>
</html>
