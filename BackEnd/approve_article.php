<?php

session_start();

require_once "dbConnect.php";


/*
|--------------------------------------------------------------------------
| Security Check
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "editor") {
    header("Location: login.php");
    exit();
}


$editor_id = $_SESSION["user_id"];


/*
|--------------------------------------------------------------------------
| Only Accept POST
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: editor_dashboard.php");
    exit();
}


/*
|--------------------------------------------------------------------------
| Get Data
|--------------------------------------------------------------------------
*/

$article_id = $_POST["article_id"] ?? "";

$feedback = trim(
    $_POST["feedback"] ?? ""
);


if (!is_numeric($article_id)) {
    die("Invalid article ID.");
}


$article_id = (int) $article_id;


/*
|--------------------------------------------------------------------------
| Approve Article
|--------------------------------------------------------------------------
|
| IMPORTANT:
|
| Status must still be Pending.
|
| This prevents an article from being approved
| twice or changing an already published article.
|
*/

$sql = "UPDATE ARTICLE

        SET Status = 'Published',
            Editor_ID = ?,
            Reviewed_At = NOW(),
            Published_At = NOW(),
            Editors_Feedback = ?

        WHERE Article_ID = ?
        AND Status = 'Pending'";


$stmt = $conn->prepare($sql);


if (!$stmt) {
    die("Database error: " . $conn->error);
}


$stmt->bind_param(
    "isi",
    $editor_id,
    $feedback,
    $article_id
);


if ($stmt->execute()) {

    if ($stmt->affected_rows === 0) {

        $stmt->close();

        die(
            "This article is no longer pending "
            . "or does not exist."
        );
    }


    $stmt->close();


    header(
        "Location: editor_dashboard.php"
    );

    exit();
}


$error = $stmt->error;

$stmt->close();


die(
    "Unable to publish article: "
    . htmlspecialchars($error)
);

?>