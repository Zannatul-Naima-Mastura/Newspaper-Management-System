<?php

session_start();

require_once "dbConnect.php";


/*
|--------------------------------------------------------------------------
| Only Registered Readers
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION["user_id"])
    ||
    $_SESSION["role"] !== "reader"
) {

    header("Location: login.php");
    exit();
}


/*
|--------------------------------------------------------------------------
| Validate Article ID
|--------------------------------------------------------------------------
*/

if (
    !isset($_GET["id"])
    ||
    !is_numeric($_GET["id"])
) {

    header("Location: reader_home.php");
    exit();
}

$article_id = (int)$_GET["id"];

$reader_id = $_SESSION["user_id"];


/*
|--------------------------------------------------------------------------
| Make Sure Article Is Published
|--------------------------------------------------------------------------
*/

$check_sql = "SELECT Article_ID
              FROM ARTICLE
              WHERE Article_ID = ?
              AND Status = 'Published'";

$check_stmt =
    $conn->prepare($check_sql);

$check_stmt->bind_param(
    "i",
    $article_id
);

$check_stmt->execute();

$check_result =
    $check_stmt->get_result();

if ($check_result->num_rows === 0) {

    die("This article cannot be bookmarked.");
}

$check_stmt->close();


/*
|--------------------------------------------------------------------------
| Insert Bookmark
|--------------------------------------------------------------------------
*/

$sql = "INSERT INTO BOOKMARK
        (
            Reader_ID,
            Article_ID,
            Bookmarked_At
        )
        VALUES
        (
            ?,
            ?,
            NOW()
        )";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "ii",
    $reader_id,
    $article_id
);


/*
|--------------------------------------------------------------------------
| Execute
|--------------------------------------------------------------------------
*/

$stmt->execute();

$stmt->close();


header(
    "Location: article_details.php?id="
    . $article_id
);

exit();

?>