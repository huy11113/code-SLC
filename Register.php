<?php
session_start();

// Thông tin kết nối đến cơ sở dữ liệu MySQL
$host = "localhost";  // Địa chỉ server MySQL
$username = "root";   // Tên người dùng MySQL
$password = "";       // Mật khẩu MySQL
$dbname = "StudentManagement"; // Tên cơ sở dữ liệu

// Kết nối đến MySQL
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Xử lý khi người dùng nhấn nút đăng ký
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = $_POST['username'];
    $pass = $_POST['password'];
    $role = $_POST['role'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    // Mã hóa mật khẩu
    $hashed_password = password_hash($pass, PASSWORD_BCRYPT);

    // Kiểm tra nếu tên người dùng đã tồn tại
    $sql_check = "SELECT * FROM Users WHERE username = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $user);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        echo "<script>alert('Tên đăng nhập đã tồn tại! Vui lòng chọn tên khác.');</script>";
    } else {
        // Thêm người dùng vào bảng Users
        $sql_insert = "INSERT INTO Users (username, password, role, full_name, email, phone) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("ssssss", $user, $hashed_password, $role, $full_name, $email, $phone);

        if ($stmt_insert->execute()) {
            echo "<script>alert('Đăng ký thành công! Vui lòng đăng nhập.'); window.location.href = 'login.php';</script>";
        } else {
            echo "<script>alert('Có lỗi xảy ra. Vui lòng thử lại sau.');</script>";
        }
    }

    $stmt_check->close();
    $stmt_insert->close();
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
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

        .signup-container {
            background: #ffffff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
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

        input[type="text"], input[type="password"], input[type="email"], input[type="phone"], select {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ccc;
            margin-bottom: 20px;
            font-size: 16px;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus, input[type="password"]:focus, input[type="email"]:focus, input[type="phone"]:focus, select:focus {
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
    <div class="signup-container">
        <h2>Sign Up</h2>
        <form action="" method="POST">
            <!-- Trường tên đăng nhập -->
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>
            
            <!-- Trường mật khẩu -->
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <!-- Trường tên đầy đủ -->
            <label for="full_name">Full Name</label>
            <input type="text" id="full_name" name="full_name" required>

            <!-- Trường email -->
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>

            <!-- Trường số điện thoại -->
            <label for="phone">Phone</label>
            <input type="phone" id="phone" name="phone" required>

            <!-- Chọn quyền người dùng (Admin, Teacher, Student) -->
            <label for="role">Role</label>
            <select id="role" name="role" required>
                <option value="admin">Admin</option>
                <option value="teacher">Teacher</option>
                <option value="student">Student</option>
            </select>

            <!-- Nút đăng ký -->
            <button type="submit" class="btn">Sign Up</button>
        </form>
        <p class="text-center">Already have an account? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>
