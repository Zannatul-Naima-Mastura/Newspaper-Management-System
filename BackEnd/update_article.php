<?php

session_start();

require_once "dbConnect.php";


/*
|--------------------------------------------------------------------------
| Security Check
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "reporter") {
    header("Location: login.php");
    exit();
}


$reporter_id = $_SESSION["user_id"];


/*
|--------------------------------------------------------------------------
| Only Accept POST
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: reporter_dashboard.php");
    exit();
}


/*
|--------------------------------------------------------------------------
| Get Form Data
|--------------------------------------------------------------------------
*/

$article_id = $_POST["article_id"] ?? "";

$title = trim($_POST["title"] ?? "");

$content = trim($_POST["content"] ?? "");

$category_id = $_POST["category_id"] ?? "";


/*
|--------------------------------------------------------------------------
| Validate
|--------------------------------------------------------------------------
*/

if (!is_numeric($article_id)) {
    die("Invalid article.");
}

if ($title === "") {
    die("Article title is required.");
}

if ($content === "") {
    die("Article content is required.");
}

if ($category_id === "" || !is_numeric($category_id)) {
    die("Please select a category.");
}


$article_id = (int) $article_id;

$category_id = (int) $category_id;


/*
|--------------------------------------------------------------------------
| Make Sure Article Belongs to Reporter
|--------------------------------------------------------------------------
*/

$check_sql = "SELECT Status
              FROM ARTICLE
              WHERE Article_ID = ?
              AND Reporter_ID = ?";


$check_stmt = $conn->prepare($check_sql);


if (!$check_stmt) {
    die("Database error: " . $conn->error);
}


$check_stmt->bind_param(
    "ii",
    $article_id,
    $reporter_id
);


$check_stmt->execute();


$check_result = $check_stmt->get_result();


$article = $check_result->fetch_assoc();


$check_stmt->close();


if (!$article) {

    die(
        "Article not found or you do not have permission to edit it."
    );

}


/*
|--------------------------------------------------------------------------
| Only Draft or Rejected Can Be Edited
|--------------------------------------------------------------------------
*/

$status = strtolower($article["Status"]);


if ($status !== "draft" && $status !== "rejected") {

    die(
        "Only Draft or Rejected articles can be edited."
    );

}


/*
|--------------------------------------------------------------------------
| Verify Category
|--------------------------------------------------------------------------
*/

$category_sql = "SELECT Category_ID
                 FROM CATEGORY
                 WHERE Category_ID = ?";


$category_stmt =
    $conn->prepare($category_sql);


if (!$category_stmt) {
    die("Database error: " . $conn->error);
}


$category_stmt->bind_param(
    "i",
    $category_id
);


$category_stmt->execute();


$category_result =
    $category_stmt->get_result();


if ($category_result->num_rows === 0) {

    $category_stmt->close();

    die("Invalid category.");

}


$category_stmt->close();


/*
|--------------------------------------------------------------------------
| Update Article
|--------------------------------------------------------------------------
|
| IMPORTANT:
| We do NOT change the status here.
|
| Draft stays Draft.
| Rejected stays Rejected.
|
| The reporter can later use Submit for Review.
|
*/

$update_sql = "UPDATE ARTICLE
               SET Title = ?,
                   Content = ?,
                   Category_ID = ?,
                   Updated_At = NOW()
               WHERE Article_ID = ?
               AND Reporter_ID = ?
               AND (Status = 'Draft'
                    OR Status = 'Rejected')";


$update_stmt =
    $conn->prepare($update_sql);


if (!$update_stmt) {

    die(
        "Database error: "
        . $conn->error
    );

}


$update_stmt->bind_param(
    "ssiii",
    $title,
    $content,
    $category_id,
    $article_id,
    $reporter_id
);


if ($update_stmt->execute()) {

    $update_stmt->close();

    header(
        "Location: reporter_dashboard.php"
    );

    exit();

}


$error = $update_stmt->error;

$update_stmt->close();


die(
    "Unable to update article: "
    . htmlspecialchars($error)
);

?>