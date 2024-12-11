<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Home</title>
    <link rel="stylesheet" href="css/styles.css"> <!-- Kết nối file CSS -->
    <style>
        /* Thiết lập kiểu cho toàn bộ trang */
body {
    font-family: 'Arial', sans-serif;
    background-color: #f4f7fa;
    margin: 0;
    padding: 0;
    color: #333;
}

.container {
    width: 100%;
    max-width: 1200px;
    margin: auto;
    padding: 20px;
}

header {
    background-color: #007bff;
    color: white;
    padding: 20px 0;
    text-align: center;
    border-radius: 8px 8px 0 0;
}

header h1 {
    margin: 0;
    font-size: 2.5em;
}

.navbar {
    background-color: #343a40;
    border-radius: 5px;
    margin-top: 10px;
}

.navbar ul {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    justify-content: center;
}

.navbar li {
    margin: 0 15px;
}

.navbar a {
    color: white;
    text-decoration: none;
    font-size: 1.2em;
    padding: 12px 20px;
    display: block;
    transition: background-color 0.3s ease;
}

.navbar a:hover {
    background-color: #0056b3;
    border-radius: 5px;
}

main {
    padding: 30px 0;
    text-align: center;
}

main h2 {
    font-size: 2em;
    color: #007bff;
    margin-bottom: 15px;
}

main p {
    font-size: 1.2em;
    color: #555;
}

/* Đảm bảo responsive */
@media (max-width: 768px) {
    .navbar ul {
        flex-direction: column;
    }

    .navbar li {
        margin: 10px 0;
    }

    header h1 {
        font-size: 2em;
    }

    main h2 {
        font-size: 1.5em;
    }
}

    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Admin Dashboard</h1>
            <nav class="navbar">
                <ul>
                    <li><a href="manage_users.php">Manage Users</a></li>
                    <li><a href="manage_classes_courses.php">Manage Classes & Courses</a></li>
                    <li><a href="admin/manage_grades.php">Manage Grades</a></li>
                    <li><a href="admin/manage_attendance.php">Manage Attendance</a></li>
                </ul>
            </nav>
        </header>
        <main>
            <h2>Welcome to Admin Dashboard</h2>
            <p>Select a function from the menu above to begin managing the system.</p>
        </main>
    </div>
</body>
</html>
