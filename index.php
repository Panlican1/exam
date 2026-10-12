<?php
session_start();
require_once "config/database.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (isset($_SESSION["role"])) {
    if ($_SESSION["role"] === "admin") {
        header("Location: admin/dashboard.php");
        exit;
    } elseif ($_SESSION["role"] === "student") {
        header("Location: student/dashboard.php");
        exit;
    }
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["login"])) {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username === "" || $password === "") {
        $error = "Please enter your username and password.";
    } else {
        try {
            $sql = "SELECT * FROM users WHERE username = ? LIMIT 1";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($result);

            if ($user && password_verify($password, $user["password"])) {
                if (!in_array($user["role"], ["admin", "student"], true)) {
                    $error = "Invalid user role in database.";
                } else {
                    session_regenerate_id(true);
                    $_SESSION["user_id"] = $user["id"];
                    $_SESSION["full_name"] = $user["full_name"];
                    $_SESSION["role"] = $user["role"];

                    header(
                        $user["role"] === "admin"
                            ? "Location: admin/dashboard.php"
                            : "Location: student/dashboard.php"
                    );
                    exit;
                }
            } else {
                $error = "Invalid username or password.";
            }

            mysqli_stmt_close($stmt);
        } catch (mysqli_sql_exception $e) {
            error_log($e->getMessage());
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>  

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Student Portal</title>
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

<div class="container">
    <div class="login-box">
        <div class="card">
            <div class="card-body p-4">

                <h2 class="text-center">Student Portal</h2>
                <p class="text-center text-muted">
                    Admin and Student Login
                </p>

                <?php if ($error !== ""): ?>
                    <div class="alert alert-danger" role="alert">
                        <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            class="form-control"
                            autocomplete="username"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control"
                            autocomplete="current-password"
                            required
                        >
                    </div>

                    <button
                        class="btn btn-primary w-100"
                        type="submit"
                        name="login"
                        value="1"
                    >
                        Login
                    </button>
                </form>

            </div>
        </div>
    </div>
</div>

</body>
</html>