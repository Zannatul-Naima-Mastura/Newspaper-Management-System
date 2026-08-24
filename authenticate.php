<?php

session_start();

require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit();
}

$login = trim($_POST["login"]);
$password = $_POST["password"];


/*
|--------------------------------------------------------------------------
| Check registered_reader table
|--------------------------------------------------------------------------
*/

$sql = "SELECT reader_id, name, email, password
        FROM registered_reader
        WHERE email = ?";

$stmt = $conn->prepare($sql);

$stmt->bind_param("s", $login);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 1) {

    $reader = $result->fetch_assoc();

    if (password_verify($password, $reader["password"])) {

        $_SESSION["user_id"] = $reader["reader_id"];
        $_SESSION["name"] = $reader["name"];
        $_SESSION["email"] = $reader["email"];
        $_SESSION["role"] = "reader";

        header("Location: dashboard.php");
        exit();
    }
}


/*
|--------------------------------------------------------------------------
| Check user table
|--------------------------------------------------------------------------
*/

$sql = "SELECT user_id, username, password, role
        FROM user
        WHERE username = ?";

$stmt = $conn->prepare($sql);

$stmt->bind_param("s", $login);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 1) {

    $user = $result->fetch_assoc();

    if (password_verify($password, $user["password"])) {

        $_SESSION["user_id"] = $user["user_id"];
        $_SESSION["name"] = $user["username"];
        $_SESSION["role"] = $user["role"];

        header("Location: dashboard.php");
        exit();
    }
}


/*
|--------------------------------------------------------------------------
| Login failed
|--------------------------------------------------------------------------
*/

echo "<script>
        alert('Invalid username/email or password!');
        window.location.href='login.php';
      </script>";

?>