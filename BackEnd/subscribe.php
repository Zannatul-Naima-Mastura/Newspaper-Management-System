<?php

session_start();

require_once "dbConnect.php";


/*
|--------------------------------------------------------------------------
| Security
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
| Check Subscription Plan
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            Subscription_ID,
            Frequency
        FROM SUBSCRIPTION
        WHERE Subscription_ID = ?
        AND Status = 'Active'";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $subscription_id
);

$stmt->execute();

$result = $stmt->get_result();

$plan = $result->fetch_assoc();

$stmt->close();


if (!$plan) {

    die("Invalid subscription plan.");

}


/*
|--------------------------------------------------------------------------
| Calculate Expiry Date
|--------------------------------------------------------------------------
*/

$subscribe_date =
    date("Y-m-d");

if ($plan["Frequency"] === "Monthly") {

    $expire_date =
        date(
            "Y-m-d",
            strtotime("+1 month")
        );

} elseif ($plan["Frequency"] === "Quarterly") {

    $expire_date =
        date(
            "Y-m-d",
            strtotime("+3 months")
        );

} else {

    $expire_date =
        date(
            "Y-m-d",
            strtotime("+1 year")
        );

}


/*
|--------------------------------------------------------------------------
| Prevent Duplicate Active Subscription
|--------------------------------------------------------------------------
*/

$check_sql = "SELECT
                  Reader_ID
              FROM READER_SUBSCRIPTION

              WHERE Reader_ID = ?

              AND Status = 'Active'

              AND Expire_Date >= CURDATE()";

$check_stmt =
    $conn->prepare($check_sql);

$check_stmt->bind_param(
    "i",
    $reader_id
);

$check_stmt->execute();

$check_result =
    $check_stmt->get_result();


if ($check_result->num_rows > 0) {

    $check_stmt->close();

    echo "<script>
            alert('You already have an active subscription.');
            window.location.href='subscription.php';
          </script>";

    exit();
}

$check_stmt->close();


/*
|--------------------------------------------------------------------------
| Create Subscription
|--------------------------------------------------------------------------
*/

$insert_sql = "INSERT INTO READER_SUBSCRIPTION
               (
                   Reader_ID,
                   Subscription_ID,
                   Subscribe_Date,
                   Expire_Date,
                   Status
               )
               VALUES
               (
                   ?,
                   ?,
                   ?,
                   ?,
                   'Active'
               )";

$insert_stmt =
    $conn->prepare($insert_sql);

$insert_stmt->bind_param(
    "iiss",
    $reader_id,
    $subscription_id,
    $subscribe_date,
    $expire_date
);

$insert_stmt->execute();

$insert_stmt->close();


echo "<script>
        alert('Subscription successful!');
        window.location.href='subscription.php';
      </script>";

exit();

?>
