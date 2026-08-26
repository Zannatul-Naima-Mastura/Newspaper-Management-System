<?php

session_start();

require_once "dbConnect.php";


/*
|--------------------------------------------------------------------------
| Security Check
|--------------------------------------------------------------------------
| Only logged-in editors can access this dashboard.
*/

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "editor") {
    header("Location: login.php");
    exit();
}

$editor_name = $_SESSION["name"];


/*
|--------------------------------------------------------------------------
| Get Pending Articles
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            a.Article_ID,
            a.Title,
            a.Created_At,
            a.Status,
            r.Name AS Reporter_Name,
            c.Category_Name
        FROM ARTICLE a

        LEFT JOIN REPORTER r
            ON a.Reporter_ID = r.Staff_ID

        LEFT JOIN CATEGORY c
            ON a.Category_ID = c.Category_ID

        WHERE a.Status = 'Pending'

        ORDER BY a.Created_At ASC";


$result = $conn->query($sql);


if (!$result) {
    die("Database error: " . $conn->error);
}


$pending_count = $result->num_rows;


/*
|--------------------------------------------------------------------------
| Get Editor's Previously Reviewed Articles
|--------------------------------------------------------------------------
*/

$reviewed_sql = "SELECT
                    a.Article_ID,
                    a.Title,
                    a.Status,
                    a.Reviewed_At,
                    a.Published_At,
                    r.Name AS Reporter_Name
                 FROM ARTICLE a

                 LEFT JOIN REPORTER r
                    ON a.Reporter_ID = r.Staff_ID

                 WHERE a.Editor_ID = ?

                 ORDER BY a.Reviewed_At DESC
                 LIMIT 10";


$reviewed_stmt = $conn->prepare($reviewed_sql);


if (!$reviewed_stmt) {
    die("Database error: " . $conn->error);
}


$editor_id = $_SESSION["user_id"];

$reviewed_stmt->bind_param(
    "i",
    $editor_id
);

$reviewed_stmt->execute();

$reviewed_result =
    $reviewed_stmt->get_result();

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Editor Dashboard - The Daily News</title>


    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }


        body {
            background: #f4f5f7;
            color: #222;
        }


        /* HEADER */

        .header {
            background: #111;
            color: white;

            padding: 18px 40px;

            display: flex;
            justify-content: space-between;
            align-items: center;
        }


        .logo {
            font-family: Georgia, serif;

            font-size: 28px;

            font-weight: bold;
        }


        .header-right {
            display: flex;

            align-items: center;

            gap: 20px;
        }


        .user-info {
            text-align: right;
        }


        .user-name {
            font-weight: bold;
        }


        .user-role {
            font-size: 13px;

            color: #ccc;
        }


        .logout-button {
            text-decoration: none;

            color: white;

            border: 1px solid #aaa;

            padding: 8px 15px;

            border-radius: 5px;
        }


        .logout-button:hover {
            background: white;

            color: #111;
        }


        /* MAIN */

        .container {
            max-width: 1200px;

            margin: 40px auto;

            padding: 0 20px;
        }


        .welcome {
            margin-bottom: 30px;
        }


        .welcome h1 {
            font-family: Georgia, serif;

            margin-bottom: 6px;
        }


        .welcome p {
            color: #666;
        }


        /* STAT */

        .stats {
            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 20px;

            margin-bottom: 35px;
        }


        .stat-card {
            background: white;

            padding: 22px;

            border-radius: 8px;

            box-shadow:
                0 2px 8px rgba(0,0,0,0.08);
        }


        .stat-number {
            font-size: 30px;

            font-weight: bold;

            margin-bottom: 5px;
        }


        .stat-label {
            color: #777;

            font-size: 14px;
        }


        /* SECTION */

        .section {
            background: white;

            border-radius: 8px;

            box-shadow:
                0 2px 8px rgba(0,0,0,0.08);

            overflow: hidden;

            margin-bottom: 30px;
        }


        .section-header {
            padding: 20px 25px;

            border-bottom: 1px solid #ddd;
        }


        .section-header h2 {
            font-family: Georgia, serif;
        }


        .table-container {
            overflow-x: auto;
        }


        table {
            width: 100%;

            border-collapse: collapse;
        }


        th {
            background: #f7f7f7;

            text-align: left;

            padding: 15px;

            font-size: 14px;

            border-bottom: 1px solid #ddd;
        }


        td {
            padding: 15px;

            border-bottom: 1px solid #eee;

            font-size: 14px;

            vertical-align: middle;
        }


        tr:hover {
            background: #fafafa;
        }


        .article-title {
            font-weight: bold;

            max-width: 350px;
        }


        .category {
            color: #666;
        }


        /* STATUS */

        .status {
            display: inline-block;

            padding: 5px 10px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: bold;
        }


        .pending {
            background: #fff3cd;

            color: #856404;
        }


        .published {
            background: #d4edda;

            color: #155724;
        }


        .rejected {
            background: #f8d7da;

            color: #721c24;
        }


        /* BUTTON */

        .review-button {
            display: inline-block;

            background: #111;

            color: white;

            padding: 8px 14px;

            border-radius: 5px;

            text-decoration: none;

            font-size: 13px;

            font-weight: bold;
        }


        .review-button:hover {
            background: #333;
        }


        /* EMPTY */

        .empty {
            padding: 50px;

            text-align: center;

            color: #777;
        }


        .empty h3 {
            color: #444;

            margin-bottom: 8px;
        }


        @media (max-width: 700px) {

            .header {
                padding: 15px 20px;
            }


            .logo {
                font-size: 22px;
            }


            .user-info {
                display: none;
            }


            .stats {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>


<body>


<!-- =====================================================
     HEADER
===================================================== -->

<header class="header">

    <div class="logo">
        The Daily News
    </div>


    <div class="header-right">

        <div class="user-info">

            <div class="user-name">

                <?php
                echo htmlspecialchars($editor_name);
                ?>

            </div>


            <div class="user-role">
                Editor
            </div>

        </div>


        <a href="logout.php"
           class="logout-button">

            Logout

        </a>

    </div>

</header>



<!-- =====================================================
     MAIN
===================================================== -->

<main class="container">


    <div class="welcome">

        <h1>
            Editor Dashboard
        </h1>

        <p>
            Review submitted articles and manage publication.
        </p>

    </div>



    <!-- =================================================
         STATISTICS
    ================================================= -->

    <section class="stats">


        <div class="stat-card">

            <div class="stat-number">
                <?php echo $pending_count; ?>
            </div>

            <div class="stat-label">
                Articles Awaiting Review
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-number">
                <?php

                $published_count = 0;

                $published_sql =
                    "SELECT COUNT(*) AS total
                     FROM ARTICLE
                     WHERE Status = 'Published'";

                $published_result =
                    $conn->query($published_sql);

                if ($published_result) {

                    $row =
                        $published_result->fetch_assoc();

                    $published_count =
                        $row["total"];
                }

                echo $published_count;

                ?>
            </div>

            <div class="stat-label">
                Published Articles
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-number">

                <?php

                $rejected_count = 0;

                $rejected_sql =
                    "SELECT COUNT(*) AS total
                     FROM ARTICLE
                     WHERE Status = 'Rejected'
                     AND Editor_ID = ?";

                $rejected_stmt =
                    $conn->prepare($rejected_sql);

                if ($rejected_stmt) {

                    $rejected_stmt->bind_param(
                        "i",
                        $editor_id
                    );

                    $rejected_stmt->execute();

                    $rejected_result =
                        $rejected_stmt->get_result();

                    $row =
                        $rejected_result->fetch_assoc();

                    $rejected_count =
                        $row["total"];

                    $rejected_stmt->close();
                }

                echo $rejected_count;

                ?>

            </div>

            <div class="stat-label">
                Articles Rejected by Me
            </div>

        </div>


    </section>



    <!-- =================================================
         PENDING ARTICLES
    ================================================= -->

    <section class="section">


        <div class="section-header">

            <h2>
                Articles Awaiting Review
            </h2>

        </div>


        <?php if ($pending_count === 0): ?>


            <div class="empty">

                <h3>
                    No articles are waiting for review.
                </h3>

                <p>
                    New submissions will appear here.
                </p>

            </div>


        <?php else: ?>


            <div class="table-container">

                <table>

                    <thead>

                        <tr>

                            <th>
                                Article
                            </th>

                            <th>
                                Author
                            </th>

                            <th>
                                Category
                            </th>

                            <th>
                                Submitted
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php while (
                        $article =
                        $result->fetch_assoc()
                    ): ?>


                        <tr>


                            <td>

                                <div class="article-title">

                                    <?php

                                    echo htmlspecialchars(
                                        $article["Title"]
                                    );

                                    ?>

                                </div>

                            </td>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $article["Reporter_Name"]
                                    ?? "Unknown"
                                );

                                ?>

                            </td>


                            <td>

                                <span class="category">

                                    <?php

                                    echo htmlspecialchars(
                                        $article["Category_Name"]
                                        ?? "Uncategorized"
                                    );

                                    ?>

                                </span>

                            </td>


                            <td>

                                <?php

                                echo date(
                                    "d M Y",
                                    strtotime(
                                        $article["Created_At"]
                                    )
                                );

                                ?>

                            </td>


                            <td>

                                <span class="status pending">

                                    Pending

                                </span>

                            </td>


                            <td>

                                <a
                                    href="review_article.php?id=<?php echo $article["Article_ID"]; ?>"
                                    class="review-button"
                                >

                                    Review

                                </a>

                            </td>


                        </tr>


                    <?php endwhile; ?>


                    </tbody>

                </table>

            </div>


        <?php endif; ?>


    </section>



    <!-- =================================================
         RECENTLY REVIEWED
    ================================================= -->

    <section class="section">


        <div class="section-header">

            <h2>
                Recently Reviewed by Me
            </h2>

        </div>


        <?php if ($reviewed_result->num_rows === 0): ?>


            <div class="empty">

                <p>
                    You haven't reviewed any articles yet.
                </p>

            </div>


        <?php else: ?>


            <div class="table-container">

                <table>

                    <thead>

                        <tr>

                            <th>
                                Article
                            </th>

                            <th>
                                Author
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Reviewed
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php while (
                        $article =
                        $reviewed_result->fetch_assoc()
                    ): ?>


                        <?php

                        $status =
                            strtolower(
                                $article["Status"]
                            );

                        ?>


                        <tr>


                            <td>

                                <div class="article-title">

                                    <?php

                                    echo htmlspecialchars(
                                        $article["Title"]
                                    );

                                    ?>

                                </div>

                            </td>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $article["Reporter_Name"]
                                    ?? "Unknown"
                                );

                                ?>

                            </td>


                            <td>

                                <?php

                                $status_class =
                                    "pending";

                                if (
                                    $status ===
                                    "published"
                                ) {

                                    $status_class =
                                        "published";

                                } elseif (
                                    $status ===
                                    "rejected"
                                ) {

                                    $status_class =
                                        "rejected";
                                }

                                ?>


                                <span
                                    class="status
                                    <?php
                                    echo $status_class;
                                    ?>"
                                >

                                    <?php

                                    echo htmlspecialchars(
                                        $article["Status"]
                                    );

                                    ?>

                                </span>

                            </td>


                            <td>

                                <?php

                                if (
                                    !empty(
                                        $article["Reviewed_At"]
                                    )
                                ) {

                                    echo date(
                                        "d M Y",
                                        strtotime(
                                            $article["Reviewed_At"]
                                        )
                                    );

                                } else {

                                    echo "—";

                                }

                                ?>

                            </td>


                        </tr>


                    <?php endwhile; ?>


                    </tbody>

                </table>

            </div>


        <?php endif; ?>


    </section>


</main>


</body>

</html>