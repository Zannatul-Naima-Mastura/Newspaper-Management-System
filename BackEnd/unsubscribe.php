<?php

session_start();

require_once "dbConnect.php";


/*
|--------------------------------------------------------------------------
| Security Check
|--------------------------------------------------------------------------
|
| Only registered readers can unsubscribe.
|
*/

if (
    !isset($_SESSION["user_id"])
    ||
    $_SESSION["role"] !== "reader"
) {

    header("Location: login.php");

    exit();

}


$reader_id = $_SESSION["user_id"];


/*
|--------------------------------------------------------------------------
| Validate Subscription ID
|--------------------------------------------------------------------------
*/

if (
    !isset($_GET["id"])
    ||
    !is_numeric($_GET["id"])
) {

    header("Location: subscription.php");

    exit();

}


$subscription_id =
    (int)$_GET["id"];


/*
|--------------------------------------------------------------------------
| DELETE Subscription
|--------------------------------------------------------------------------
|
| We delete ONLY the subscription belonging
| to the currently logged-in reader.
|
*/

$sql = "DELETE FROM READER_SUBSCRIPTION

        WHERE Reader_ID = ?

        AND Subscription_ID = ?";


$stmt =
    $conn->prepare($sql);


if (!$stmt) {

    die(
        "Database error: "
        . $conn->error
    );

}


$stmt->bind_param(
    "ii",
    $reader_id,
    $subscription_id
);


$stmt->execute();


/*
|--------------------------------------------------------------------------
| Check Result
|--------------------------------------------------------------------------
*/

if ($stmt->affected_rows > 0) {

    $stmt->close();

    echo "<script>

            alert(
                'You have successfully unsubscribed.'
            );

            window.location.href =
                'subscription.php';

          </script>";

    exit();

}


/*
|--------------------------------------------------------------------------
| No Subscription Found
|--------------------------------------------------------------------------
*/

$stmt->close();


echo "<script>

        alert(
            'No subscription was found.'
        );

        window.location.href =
            'subscription.php';

      </script>";

exit();

?>