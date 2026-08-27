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


$subscription_id = (int)$_GET["id"];


/*
|--------------------------------------------------------------------------
| Check That Subscription Plan Exists
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            Subscription_ID,
            Name,
            Frequency

        FROM SUBSCRIPTION

        WHERE Subscription_ID = ?

        AND Status = 'Active'";


$stmt = $conn->prepare($sql);


if (!$stmt) {

    die(
        "Database error: "
        . $conn->error
    );

}


$stmt->bind_param(
    "i",
    $subscription_id
);


$stmt->execute();


$result = $stmt->get_result();


$plan = $result->fetch_assoc();


$stmt->close();


if (!$plan) {

    die(
        "Invalid or inactive subscription plan."
    );

}


/*
|--------------------------------------------------------------------------
| Check Existing Active Subscription
|--------------------------------------------------------------------------
|
| A reader can have only one active subscription.
|
*/

$check_sql = "SELECT
                  Reader_ID,
                  Subscription_ID,
                  Expire_Date

              FROM READER_SUBSCRIPTION

              WHERE Reader_ID = ?

              AND Status = 'Active'

              AND Expire_Date >= CURDATE()

              LIMIT 1";


$check_stmt = $conn->prepare($check_sql);


if (!$check_stmt) {

    die(
        "Database error: "
        . $conn->error
    );

}


$check_stmt->bind_param(
    "i",
    $reader_id
);


$check_stmt->execute();


$check_result = $check_stmt->get_result();


if ($check_result->num_rows > 0) {

    $existing = $check_result->fetch_assoc();

    $check_stmt->close();

    echo "<script>

            alert(
                'You already have an active subscription.'
            );

            window.location.href =
                'subscription.php';

          </script>";

    exit();

}


$check_stmt->close();


/*
|--------------------------------------------------------------------------
| Calculate Subscription Dates
|--------------------------------------------------------------------------
*/

$subscribe_date = date("Y-m-d");


if (
    $plan["Frequency"] === "Monthly"
) {

    $expire_date = date(
        "Y-m-d",
        strtotime("+1 month")
    );

} elseif (
    $plan["Frequency"] === "Quarterly"
) {

    $expire_date = date(
        "Y-m-d",
        strtotime("+3 months")
    );

} elseif (
    $plan["Frequency"] === "Yearly"
    ||
    $plan["Frequency"] === "Annual"
) {

    $expire_date = date(
        "Y-m-d",
        strtotime("+1 year")
    );

} else {

    $expire_date = date(
        "Y-m-d",
        strtotime("+1 month")
    );

}


/*
|--------------------------------------------------------------------------
| Check If This Exact Subscription Row Already Exists
|--------------------------------------------------------------------------
|
| Because Reader_ID + Subscription_ID is the PRIMARY KEY,
| we cannot INSERT another row with the same combination.
|
| If an old expired row exists, we reuse it by updating it.
|
*/

$existing_sql = "SELECT
                     Reader_ID,
                     Subscription_ID,
                     Status,
                     Expire_Date

                 FROM READER_SUBSCRIPTION

                 WHERE Reader_ID = ?

                 AND Subscription_ID = ?

                 LIMIT 1";


$existing_stmt = $conn->prepare($existing_sql);


if (!$existing_stmt) {

    die(
        "Database error: "
        . $conn->error
    );

}


$existing_stmt->bind_param(
    "ii",
    $reader_id,
    $subscription_id
);


$existing_stmt->execute();


$existing_result =
    $existing_stmt->get_result();


$existing_row =
    $existing_result->fetch_assoc();


$existing_stmt->close();


/*
|--------------------------------------------------------------------------
| If Existing Row Is Found
|--------------------------------------------------------------------------
|
| Reuse the old row instead of creating a duplicate.
|
*/

if ($existing_row) {

    $update_sql = "UPDATE READER_SUBSCRIPTION

                   SET
                       Subscribe_Date = ?,
                       Expire_Date = ?,
                       Status = 'Active'

                   WHERE Reader_ID = ?

                   AND Subscription_ID = ?";


    $update_stmt =
        $conn->prepare($update_sql);


    if (!$update_stmt) {

        die(
            "Database error: "
            . $conn->error
        );

    }


    $update_stmt->bind_param(
        "ssii",
        $subscribe_date,
        $expire_date,
        $reader_id,
        $subscription_id
    );


    if ($update_stmt->execute()) {

        $update_stmt->close();

        echo "<script>

                alert(
                    'Subscription successful!'
                );

                window.location.href =
                    'subscription.php';

              </script>";

        exit();

    }


    $error =
        $update_stmt->error;


    $update_stmt->close();


    die(
        "Unable to renew subscription: "
        . htmlspecialchars($error)
    );

}


/*
|--------------------------------------------------------------------------
| No Existing Row
|--------------------------------------------------------------------------
|
| Safe to INSERT a completely new subscription.
|
*/

$insert_sql = "

    INSERT INTO READER_SUBSCRIPTION

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
    )

";


$insert_stmt =
    $conn->prepare($insert_sql);


if (!$insert_stmt) {

    die(
        "Database error: "
        . $conn->error
    );

}


$insert_stmt->bind_param(
    "iiss",
    $reader_id,
    $subscription_id,
    $subscribe_date,
    $expire_date
);


if ($insert_stmt->execute()) {

    $insert_stmt->close();

    echo "<script>

            alert(
                'Subscription successful!'
            );

            window.location.href =
                'subscription.php';

          </script>";

    exit();

}


$error =
    $insert_stmt->error;


$insert_stmt->close();


die(
    "Unable to subscribe: "
    . htmlspecialchars($error)
);

?>