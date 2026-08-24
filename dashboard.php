<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Dashboard - Newspaper Management System</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

    <div class="login-container">

        <div class="login-box">

            <h1>Welcome!</h1>

            <h2>
                <?php echo htmlspecialchars($_SESSION["name"]); ?>
            </h2>

            <p style="text-align:center; margin-bottom:20px;">

                You are logged in as:

                <strong>
                    <?php echo htmlspecialchars($_SESSION["role"]); ?>
                </strong>

            </p>

            <a href="logout.php">
                <button>Logout</button>
            </a>

        </div>

    </div>

</body>

</html>