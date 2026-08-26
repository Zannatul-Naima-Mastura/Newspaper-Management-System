<?php

session_start();

require_once "dbConnect.php";


/*
|--------------------------------------------------------------------------
| Security Check
|--------------------------------------------------------------------------
| Only logged-in reporters can create articles.
*/

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "reporter") {
    header("Location: login.php");
    exit();
}


/*
|--------------------------------------------------------------------------
| Only Accept POST Requests
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: create_article.php");
    exit();
}


/*
|--------------------------------------------------------------------------
| Get Logged-in Reporter
|--------------------------------------------------------------------------
*/

$reporter_id = $_SESSION["user_id"];


/*
|--------------------------------------------------------------------------
| Get Form Data
|--------------------------------------------------------------------------
*/

$title = trim($_POST["title"] ?? "");

$content = trim($_POST["content"] ?? "");

$category_id = $_POST["category_id"] ?? "";


/*
|--------------------------------------------------------------------------
| Validate Input
|--------------------------------------------------------------------------
*/

if ($title === "") {

    die("Article title is required.");

}


if ($content === "") {

    die("Article content is required.");

}


if ($category_id === "" || !is_numeric($category_id)) {

    die("Please select a valid category.");

}


$category_id = (int) $category_id;


/*
|--------------------------------------------------------------------------
| Verify Category Exists
|--------------------------------------------------------------------------
*/

$category_check_sql =
    "SELECT Category_ID
     FROM CATEGORY
     WHERE Category_ID = ?";


$category_stmt =
    $conn->prepare($category_check_sql);


if (!$category_stmt) {

    die(
        "Database error: "
        . $conn->error
    );

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

    die("Invalid category selected.");

}


$category_stmt->close();



/*
|--------------------------------------------------------------------------
| Insert Article
|--------------------------------------------------------------------------
|
| Reporter_ID comes from the SESSION.
|
| The reporter cannot choose their own Reporter_ID.
|
| Status is automatically set to Draft.
|
*/

$sql = "INSERT INTO ARTICLE
        (
            Title,
            Content,
            Created_At,
            Updated_At,
            Status,
            Reporter_ID,
            Category_ID
        )
        VALUES
        (
            ?,
            ?,
            NOW(),
            NOW(),
            'Draft',
            ?,
            ?
        )";


$stmt = $conn->prepare($sql);


if (!$stmt) {

    die(
        "Database error: "
        . $conn->error
    );

}


$stmt->bind_param(
    "ssii",
    $title,
    $content,
    $reporter_id,
    $category_id
);


/*
|--------------------------------------------------------------------------
| Save Article
|--------------------------------------------------------------------------
*/

if ($stmt->execute()) {

    $stmt->close();

    header(
        "Location: reporter_dashboard.php"
    );

    exit();

}


/*
|--------------------------------------------------------------------------
| Insert Failed
|--------------------------------------------------------------------------
*/

$error = $stmt->error;

$stmt->close();

die(
    "Unable to save article. Error: "
    . htmlspecialchars($error)
);

?>