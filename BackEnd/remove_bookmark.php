<?php

session_start();

require_once "dbConnect.php";


/*
|--------------------------------------------------------------------------
| Security Check
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
| Validate Article
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
| Delete Bookmark
|--------------------------------------------------------------------------
*/

$sql = "DELETE FROM BOOKMARK
        WHERE Reader_ID = ?
        AND Article_ID = ?";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "ii",
    $reader_id,
    $article_id
);

$stmt->execute();

$stmt->close();


header(
    "Location: article_details.php?id="
    . $article_id
);

exit();

?>