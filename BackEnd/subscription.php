<?php

session_start();

require_once "dbConnect.php";


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
| Get Subscription Plans
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            Subscription_ID,
            Name,
            Code,
            Frequency,
            Status,
            Time,
            Expire_Date

        FROM SUBSCRIPTION

        WHERE Status = 'Active'

        ORDER BY Subscription_ID";

$result = $conn->query($sql);


/*
|--------------------------------------------------------------------------
| Current Subscription
|--------------------------------------------------------------------------
*/

$current_sql = "SELECT
                    rs.Subscription_ID,
                    rs.Subscribe_Date,
                    rs.Expire_Date,
                    rs.Status,
                    s.Name,
                    s.Frequency

                FROM READER_SUBSCRIPTION rs

                INNER JOIN SUBSCRIPTION s
                    ON rs.Subscription_ID =
                       s.Subscription_ID

                WHERE rs.Reader_ID = ?

                AND rs.Status = 'Active'

                AND rs.Expire_Date >= CURDATE()

                ORDER BY rs.Expire_Date DESC

                LIMIT 1";

$current_stmt =
    $conn->prepare($current_sql);

$current_stmt->bind_param(
    "i",
    $reader_id
);

$current_stmt->execute();

$current_result =
    $current_stmt->get_result();

$current_subscription =
    $current_result->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Subscription - The Daily News</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f4f5f7;
            margin: 0;
        }

        .header {
            background: #111;
            color: white;
            padding: 18px 40px;
        }

        .header a {
            color: white;
            text-decoration: none;
        }

        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
        }

        .current {
            background: white;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .plans {
            display: grid;
            grid-template-columns:
                repeat(3, 1fr);
            gap: 20px;
        }

        .plan {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow:
                0 2px 8px rgba(0,0,0,.08);
        }

        .plan h2 {
            font-family: Georgia, serif;
        }

        .subscribe {
            display: inline-block;
            margin-top: 20px;
            background: #111;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
        }
		.unsubscribe {
    display: inline-block;

    background: #b00020;

    color: white;

    padding: 10px 18px;

    text-decoration: none;

    border-radius: 5px;
}

.unsubscribe:hover {
    background: #8a0019;
}

        @media(max-width:700px) {
            .plans {
                grid-template-columns: 1fr;
            }
        }

    </style>

</head>

<body>

<header class="header">

    <a href="reader_home.php">
        ← The Daily News
    </a>

</header>


<main class="container">

    <h1>
        News Updates Subscription
    </h1>

    <br>

<?php if ($current_subscription): ?>

    <section class="current">

        <h2>
            Your Current Subscription
        </h2>

        <br>

        <p>
            <strong>Plan:</strong>

            <?php
            echo htmlspecialchars(
                $current_subscription["Name"]
            );
            ?>
        </p>

        <p>
            <strong>Frequency:</strong>

            <?php
            echo htmlspecialchars(
                $current_subscription["Frequency"]
            );
            ?>
        </p>

        <p>
            <strong>Subscribed:</strong>

            <?php
            echo htmlspecialchars(
                $current_subscription["Subscribe_Date"]
            );
            ?>
        </p>

        <p>
            <strong>Expires:</strong>

            <?php
            echo htmlspecialchars(
                $current_subscription["Expire_Date"]
            );
            ?>
        </p>

        <br>

        <a
            href="unsubscribe.php?id=<?php
                echo $current_subscription["Subscription_ID"];
            ?>"
            class="unsubscribe"
            onclick="return confirm(
                'Are you sure you want to unsubscribe?'
            );"
        >
            Unsubscribe
        </a>

    </section>

<?php else: ?>

    <section class="current">

        <h2>
            You are not currently subscribed.
        </h2>

        <p>
            Subscribe to receive future news updates.
        </p>

    </section>

<?php endif; ?>


    <section class="plans">

        <?php while (
            $plan = $result->fetch_assoc()
        ): ?>

            <div class="plan">

                <h2>

                    <?php
                    echo htmlspecialchars(
                        $plan["Name"]
                    );
                    ?>

                </h2>

                <br>

                <p>

                    Receive news updates:

                    <strong>

                        <?php
                        echo htmlspecialchars(
                            $plan["Frequency"]
                        );
                        ?>

                    </strong>

                </p>

                <br>

                <p>
                    Code:

                    <?php
                    echo htmlspecialchars(
                        $plan["Code"]
                    );
                    ?>

                </p>


                <a
                    class="subscribe"
                    href="subscribe.php?id=<?php
                        echo $plan["Subscription_ID"];
                    ?>"
                >
                    Subscribe
                </a>

            </div>

        <?php endwhile; ?>

    </section>

</main>

</body>

</html>