```php
<?php

session_start();

require_once "dbConnect.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit();
}

$login = trim($_POST["login"]);
$password = $_POST["password"];


/*
|--------------------------------------------------------------------------
| 1. Check Registered Reader
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

    if ($password === $reader["password"]) {

        $_SESSION["user_id"] = $reader["reader_id"];
        $_SESSION["name"] = $reader["name"];
        $_SESSION["email"] = $reader["email"];
        $_SESSION["role"] = "reader";

        header("Location: reader_home.php");
        exit();
    }
}


/*
|--------------------------------------------------------------------------
| 2. Check Website Admin
|--------------------------------------------------------------------------
*/

$sql = "SELECT Admin_ID, Name, Email, Password
        FROM WEBSITE_ADMIN
        WHERE Email = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $login);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 1) {

    $admin = $result->fetch_assoc();

    if ($password === $admin["Password"]) {

        $_SESSION["user_id"] = $admin["Admin_ID"];
        $_SESSION["name"] = $admin["Name"];
        $_SESSION["email"] = $admin["Email"];
        $_SESSION["role"] = "admin";

        header("Location: dashboard.php");
        exit();
    }
}


/*
|--------------------------------------------------------------------------
| 3. Check Reporter
|--------------------------------------------------------------------------
*/

$sql = "SELECT Staff_ID, Name, Email, Password
        FROM REPORTER
        WHERE Email = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $login);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 1) {

    $reporter = $result->fetch_assoc();

    if ($password === $reporter["Password"]) {

        $_SESSION["user_id"] = $reporter["Staff_ID"];
        $_SESSION["name"] = $reporter["Name"];
        $_SESSION["email"] = $reporter["Email"];
        $_SESSION["role"] = "reporter";

        header("Location: reporter_dashboard.php");
        exit();
    }
}


/*
|--------------------------------------------------------------------------
| 4. Check Editor
|--------------------------------------------------------------------------
*/

$sql = "SELECT Staff_ID, Name, Email, Password
        FROM EDITOR
        WHERE Email = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $login);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 1) {

    $editor = $result->fetch_assoc();

    if ($password === $editor["Password"]) {

        $_SESSION["user_id"] = $editor["Staff_ID"];
        $_SESSION["name"] = $editor["Name"];
        $_SESSION["email"] = $editor["Email"];
        $_SESSION["role"] = "editor";

        header("Location: editor_dashboard.php");
        exit();
    }
}


/*
|--------------------------------------------------------------------------
| Login Failed
|--------------------------------------------------------------------------
*/

echo "<script>
        alert('Invalid email or password!');
        window.location.href='login.php';
      </script>";

?>
```
