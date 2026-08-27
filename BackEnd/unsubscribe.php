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


$reader_id = $_SESSION["user_id"];

$subscription_id =
    (int)$_GET["id"];


/*
|--------------------------------------------------------------------------
| Unsubscribe Only This Reader's Subscription
|--------------------------------------------------------------------------
|
| We update the status instead of deleting the record.
|
*/

$sql = "UPDATE READER_SUBSCRIPTION

        SET Status = 'Inactive'

        WHERE Reader_ID = ?

        AND Subscription_ID = ?

        AND Status = 'Active'";


$stmt = $conn->prepare($sql);


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
| Check Whether Anything Was Updated
|--------------------------------------------------------------------------
*/

if ($stmt->affected_rows === 0) {

    $stmt->close();

    echo "<script>

            alert(
                'No active subscription was found.'
            );

            window.location.href =
                'subscription.php';

          </script>";

    exit();

}


$stmt->close();


/*
|--------------------------------------------------------------------------
| Success
|--------------------------------------------------------------------------
*/

echo "<script>

        alert(
            'You have successfully unsubscribed.'
        );

        window.location.href =
            'subscription.php';

      </script>";

exit();

?>