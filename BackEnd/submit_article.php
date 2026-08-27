<?php

session_start();

require_once "dbConnect.php";


/*
|--------------------------------------------------------------------------
| Security Check
|--------------------------------------------------------------------------
| Only logged-in reporters can submit articles.
*/

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "reporter") {
    header("Location: login.php");
    exit();
}


$reporter_id = $_SESSION["user_id"];


/*
|--------------------------------------------------------------------------
| Check Article ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: reporter_dashboard.php");
    exit();
}


$article_id = (int) $_GET["id"];


/*
|--------------------------------------------------------------------------
| Check That Article Belongs to This Reporter
|--------------------------------------------------------------------------
*/

$sql = "SELECT Article_ID, Status
        FROM ARTICLE
        WHERE Article_ID = ?
        AND Reporter_ID = ?";


$stmt = $conn->prepare($sql);


if (!$stmt) {
    die("Database error: " . $conn->error);
}


$stmt->bind_param(
    "ii",
    $article_id,
    $reporter_id
);


$stmt->execute();


$result = $stmt->get_result();


$article = $result->fetch_assoc();


$stmt->close();


/*
|--------------------------------------------------------------------------
| Article Not Found
|--------------------------------------------------------------------------
*/

if (!$article) {

    die(
        "Article not found or you do not have permission to submit it."
    );

}


/*
|--------------------------------------------------------------------------
| Check Current Status
|--------------------------------------------------------------------------
|
| Only Draft and Rejected articles can be submitted.
|
*/

$status = strtolower($article["Status"]);


if ($status !== "draft" && $status !== "rejected") {

    die(
        "This article cannot be submitted for review. "
        . "Only Draft or Rejected articles can be submitted."
    );

}


/*
|--------------------------------------------------------------------------
| Change Status to Reviewed
|--------------------------------------------------------------------------
|
| The article is now waiting for an editor.
|
| We also clear previous editor information because
| a rejected article is being submitted again.
|
*/

$update_sql = "UPDATE ARTICLE
               SET Status = 'Reviewed',
                   Editor_ID = NULL,
                   Reviewed_At = NULL,
                   Published_At = NULL,
                   Editors_Feedback = NULL,
                   Updated_At = NOW()
               WHERE Article_ID = ?
               AND Reporter_ID = ?
               AND (Status = 'Draft'
                    OR Status = 'Rejected')";


$update_stmt = $conn->prepare($update_sql);


if (!$update_stmt) {
    die("Database error: " . $conn->error);
}


$update_stmt->bind_param(
    "ii",
    $article_id,
    $reporter_id
);


/*
|--------------------------------------------------------------------------
| Submit Article
|--------------------------------------------------------------------------
*/

if ($update_stmt->execute()) {

    $update_stmt->close();

    header(
        "Location: reporter_dashboard.php"
    );

    exit();

}


/*
|--------------------------------------------------------------------------
| Update Failed
|--------------------------------------------------------------------------
*/

$error = $update_stmt->error;

$update_stmt->close();


die(
    "Unable to submit article: "
    . htmlspecialchars($error)
);

?>