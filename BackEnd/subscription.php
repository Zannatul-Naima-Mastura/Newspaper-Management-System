<?php

session_start();

require_once "dbConnect.php";


/*
|--------------------------------------------------------------------------
| Security Check
|--------------------------------------------------------------------------
|
| Only registered readers can subscribe/unsubscribe.
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
| Get Current Active Subscription
|--------------------------------------------------------------------------
*/

$current_sql = "SELECT
                    rs.Subscription_ID,
                    rs.Subscribe_Date,
                    rs.Expire_Date,
                    rs.Status,
                    s.Name,
                    s.Code,
                    s.Frequency,
                    s.Time

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


if (!$current_stmt) {

    die(
        "Database error: "
        . $conn->error
    );

}


$current_stmt->bind_param(
    "i",
    $reader_id
);


$current_stmt->execute();


$current_result =
    $current_stmt->get_result();


$current_subscription =
    $current_result->fetch_assoc();


$current_stmt->close();


/*
|--------------------------------------------------------------------------
| Get Available Subscription Plans
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


if (!$result) {

    die(
        "Database error: "
        . $conn->error
    );

}

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Subscription - The Daily News
    </title>


    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }


        body {

            font-family: Arial, sans-serif;

            background: #f4f5f7;

            color: #222;

        }


        .header {

            background: #111;

            color: white;

            padding: 18px 40px;

            display: flex;

            justify-content: space-between;

            align-items: center;

        }


        .header a {

            color: white;

            text-decoration: none;

            font-size: 15px;

        }


        .logo {

            font-family: Georgia, serif;

            font-size: 28px;

            font-weight: bold;

        }


        .container {

            max-width: 1000px;

            margin: 40px auto;

            padding: 20px;

        }


        h1 {

            font-family: Georgia, serif;

            margin-bottom: 10px;

        }


        .description {

            color: #666;

            margin-bottom: 30px;

        }


        /*
        |--------------------------------------------------------------------------
        | Current Subscription
        |--------------------------------------------------------------------------
        */

        .current {

            background: white;

            padding: 25px;

            border-radius: 8px;

            margin-bottom: 35px;

            box-shadow:
                0 2px 8px rgba(0,0,0,0.08);

        }


        .current h2 {

            font-family: Georgia, serif;

            margin-bottom: 18px;

        }


        .current p {

            margin-bottom: 8px;

            line-height: 1.6;

        }


        /*
        |--------------------------------------------------------------------------
        | Unsubscribe Button
        |--------------------------------------------------------------------------
        */

        .unsubscribe {

            display: inline-block;

            margin-top: 15px;

            background: #b00020;

            color: white;

            padding: 11px 18px;

            text-decoration: none;

            border-radius: 5px;

        }


        .unsubscribe:hover {

            background: #8a0019;

        }


        /*
        |--------------------------------------------------------------------------
        | Subscription Plans
        |--------------------------------------------------------------------------
        */

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
                0 2px 8px rgba(0,0,0,0.08);

        }


        .plan h2 {

            font-family: Georgia, serif;

            margin-bottom: 15px;

        }


        .plan p {

            margin-bottom: 10px;

            color: #555;

        }


        .subscribe {

            display: inline-block;

            margin-top: 15px;

            background: #111;

            color: white;

            padding: 11px 18px;

            text-decoration: none;

            border-radius: 5px;

        }


        .subscribe:hover {

            background: #333;

        }


        /*
        |--------------------------------------------------------------------------
        | Responsive
        |--------------------------------------------------------------------------
        */

        @media (max-width: 700px) {

            .plans {

                grid-template-columns: 1fr;

            }

            .header {

                padding: 15px 20px;

            }

            .container {

                margin-top: 20px;

            }

        }

    </style>

</head>


<body>


<header class="header">

    <div class="logo">

        The Daily News

    </div>


    <a href="reader_home.php">

        ← Back to News

    </a>

</header>



<main class="container">


    <h1>

        News Updates Subscription

    </h1>


    <p class="description">

        Subscribe to receive future news updates.

    </p>



    <!--
    ========================================================================
    CURRENT SUBSCRIPTION
    ========================================================================
    -->


    <?php if ($current_subscription): ?>


        <section class="current">


            <h2>

                Your Current Subscription

            </h2>


            <p>

                <strong>
                    Plan:
                </strong>

                <?php

                echo htmlspecialchars(
                    $current_subscription["Name"]
                );

                ?>

            </p>


            <p>

                <strong>
                    Frequency:
                </strong>

                <?php

                echo htmlspecialchars(
                    $current_subscription["Frequency"]
                );

                ?>

            </p>


            <p>

                <strong>
                    Subscription Code:
                </strong>

                <?php

                echo htmlspecialchars(
                    $current_subscription["Code"]
                );

                ?>

            </p>


            <p>

                <strong>
                    Subscribed On:
                </strong>

                <?php

                echo htmlspecialchars(
                    $current_subscription["Subscribe_Date"]
                );

                ?>

            </p>


            <p>

                <strong>
                    Expires On:
                </strong>

                <?php

                echo htmlspecialchars(
                    $current_subscription["Expire_Date"]
                );

                ?>

            </p>


            <!--
            ================================================================
            UNSUBSCRIBE
            ================================================================
            -->


            <a
                href="unsubscribe.php?id=<?php

                    echo $current_subscription[
                        "Subscription_ID"
                    ];

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

                Choose a subscription plan below
                to receive future news updates.

            </p>


        </section>


    <?php endif; ?>



    <!--
    ========================================================================
    SUBSCRIPTION PLANS
    ========================================================================
    -->


    <h2 style="margin-bottom: 20px;">

        Available Subscription Plans

    </h2>


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


                <p>

                    <strong>
                        Frequency:
                    </strong>

                    <?php

                    echo htmlspecialchars(
                        $plan["Frequency"]
                    );

                    ?>

                </p>


                <p>

                    <strong>
                        Code:
                    </strong>

                    <?php

                    echo htmlspecialchars(
                        $plan["Code"]
                    );

                    ?>

                </p>


                <?php if (!$current_subscription): ?>


                    <a
                        href="subscribe.php?id=<?php

                            echo $plan[
                                "Subscription_ID"
                            ];

                        ?>"
                        class="subscribe"
                    >

                        Subscribe

                    </a>


                <?php else: ?>


                    <p style="margin-top: 15px;">

                        You already have an active
                        subscription.

                    </p>


                <?php endif; ?>


            </div>


        <?php endwhile; ?>


    </section>


</main>


</body>

</html>