<?php

session_start();

require_once "dbConnect.php";

/*
|--------------------------------------------------------------------------
| Security Check
|--------------------------------------------------------------------------
| Only logged-in reporters can access this page.
*/

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "reporter") {
    header("Location: login.php");
    exit();
}

$reporter_id = $_SESSION["user_id"];
$reporter_name = $_SESSION["name"];


/*
|--------------------------------------------------------------------------
| Get Reporter's Articles
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            a.Article_ID,
            a.Title,
            a.Content,
            a.Created_At,
            a.Updated_At,
            a.Status,
            a.Reviewed_At,
            a.Published_At,
            a.Editors_Feedback,
            c.Category_Name,
            e.Name AS Editor_Name
        FROM ARTICLE a

        LEFT JOIN CATEGORY c
            ON a.Category_ID = c.Category_ID

        LEFT JOIN EDITOR e
            ON a.Editor_ID = e.Staff_ID

        WHERE a.Reporter_ID = ?

        ORDER BY a.Created_At DESC";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $reporter_id);
$stmt->execute();

$result = $stmt->get_result();


/*
|--------------------------------------------------------------------------
| Count Articles
|--------------------------------------------------------------------------
*/

$total_articles = 0;
$draft_count = 0;
$pending_count = 0;
$published_count = 0;
$rejected_count = 0;


/*
|--------------------------------------------------------------------------
| Store Articles
|--------------------------------------------------------------------------
*/

$articles = [];

while ($article = $result->fetch_assoc()) {

    $articles[] = $article;

    $total_articles++;

    switch (strtolower($article["Status"])) {

        case "draft":
            $draft_count++;
            break;

        case "pending":
            $pending_count++;
            break;

        case "published":
            $published_count++;
            break;

        case "rejected":
            $rejected_count++;
            break;
    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Reporter Dashboard - The Daily News</title>


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


        /* -------------------------------------------------
           HEADER
        ------------------------------------------------- */

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


        /* -------------------------------------------------
           MAIN CONTAINER
        ------------------------------------------------- */

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }


        .welcome-section {
            display: flex;
            justify-content: space-between;
            align-items: center;

            margin-bottom: 30px;
        }


        .welcome-section h1 {
            font-family: Georgia, serif;
            margin-bottom: 5px;
        }


        .welcome-section p {
            color: #666;
        }


        .create-button {
            background: #111;
            color: white;

            text-decoration: none;

            padding: 12px 20px;

            border-radius: 6px;

            font-weight: bold;
        }


        .create-button:hover {
            background: #333;
        }


        /* -------------------------------------------------
           STATISTICS
        ------------------------------------------------- */

        .stats {
            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

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


        /* -------------------------------------------------
           ARTICLE SECTION
        ------------------------------------------------- */

        .articles-section {
            background: white;

            border-radius: 8px;

            box-shadow:
                0 2px 8px rgba(0,0,0,0.08);

            overflow: hidden;
        }


        .section-header {
            padding: 20px 25px;

            border-bottom: 1px solid #ddd;
        }


        .section-header h2 {
            font-family: Georgia, serif;
        }


        /* -------------------------------------------------
           TABLE
        ------------------------------------------------- */

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

            vertical-align: top;

            font-size: 14px;
        }


        tr:hover {
            background: #fafafa;
        }


        .article-title {
            font-weight: bold;

            max-width: 300px;
        }


        .category {
            color: #666;
        }


        /* -------------------------------------------------
           STATUS
        ------------------------------------------------- */

        .status {
            display: inline-block;

            padding: 5px 10px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: bold;
        }


        .status-draft {
            background: #eee;
            color: #555;
        }


        .status-pending {
            background: #fff3cd;
            color: #856404;
        }


        .status-published {
            background: #d4edda;
            color: #155724;
        }


        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }


        /* -------------------------------------------------
           ACTION BUTTONS
        ------------------------------------------------- */

        .actions {
            display: flex;

            flex-wrap: wrap;

            gap: 6px;
        }


        .action-button {
            display: inline-block;

            padding: 6px 10px;

            border-radius: 4px;

            text-decoration: none;

            font-size: 12px;

            border: none;

            cursor: pointer;
        }


        .edit-button {
            background: #e7f0ff;
            color: #1a56db;
        }


        .edit-button:hover {
            background: #d5e5ff;
        }


        .submit-button {
            background: #e8f7ed;
            color: #1e7e34;
        }


        .submit-button:hover {
            background: #d5f0dd;
        }


        .delete-button {
            background: #f8d7da;
            color: #721c24;
        }


        .delete-button:hover {
            background: #f1c1c5;
        }


        .disabled-action {
            color: #999;
            font-size: 12px;
        }


        /* -------------------------------------------------
           FEEDBACK
        ------------------------------------------------- */

        .feedback {
            margin-top: 8px;

            padding: 8px;

            background: #fff8e1;

            border-left: 3px solid #e0a800;

            font-size: 12px;

            color: #555;

            max-width: 250px;
        }


        .feedback strong {
            color: #333;
        }


        /* -------------------------------------------------
           EMPTY STATE
        ------------------------------------------------- */

        .empty-state {
            text-align: center;

            padding: 60px 20px;

            color: #777;
        }


        .empty-state h3 {
            margin-bottom: 10px;

            color: #444;
        }


        /* -------------------------------------------------
           RESPONSIVE
        ------------------------------------------------- */

        @media (max-width: 900px) {

            .stats {
                grid-template-columns:
                    repeat(2, 1fr);
            }

        }


        @media (max-width: 700px) {

            .header {
                padding: 15px 20px;
            }


            .logo {
                font-size: 22px;
            }


            .header-right {
                gap: 10px;
            }


            .user-info {
                display: none;
            }


            .welcome-section {
                flex-direction: column;

                align-items: flex-start;

                gap: 20px;
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
                <?php echo htmlspecialchars($reporter_name); ?>
            </div>

            <div class="user-role">
                Reporter
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


    <!-- WELCOME -->

    <section class="welcome-section">

        <div>

            <h1>
                Reporter Dashboard
            </h1>

            <p>
                Manage your articles and submit them for editorial review.
            </p>

        </div>


        <a href="create_article.php"
           class="create-button">

            + Create New Article

        </a>

    </section>



    <!-- =================================================
         STATISTICS
    ================================================= -->

    <section class="stats">


        <div class="stat-card">

            <div class="stat-number">
                <?php echo $total_articles; ?>
            </div>

            <div class="stat-label">
                Total Articles
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-number">
                <?php echo $draft_count; ?>
            </div>

            <div class="stat-label">
                Drafts
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-number">
                <?php echo $pending_count; ?>
            </div>

            <div class="stat-label">
                Pending Review
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-number">
                <?php echo $published_count; ?>
            </div>

            <div class="stat-label">
                Published
            </div>

        </div>


    </section>



    <!-- =================================================
         ARTICLES
    ================================================= -->

    <section class="articles-section">


        <div class="section-header">

            <h2>
                My Articles
            </h2>

        </div>


        <?php if (count($articles) === 0): ?>

            <div class="empty-state">

                <h3>
                    You haven't created any articles yet.
                </h3>

                <p>
                    Start by creating your first article.
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
                                Category
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Created
                            </th>

                            <th>
                                Feedback
                            </th>

                            <th>
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php foreach ($articles as $article): ?>


                        <?php

                        $status = strtolower(
                            $article["Status"]
                        );

                        ?>


                        <tr>


                            <!-- ARTICLE -->

                            <td>

                                <div class="article-title">

                                    <?php
                                    echo htmlspecialchars(
                                        $article["Title"]
                                    );
                                    ?>

                                </div>

                            </td>



                            <!-- CATEGORY -->

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



                            <!-- STATUS -->

                            <td>

                                <?php

                                if ($status === "draft") {

                                    $status_class =
                                        "status-draft";

                                } elseif ($status === "pending") {

                                    $status_class =
                                        "status-pending";

                                } elseif ($status === "published") {

                                    $status_class =
                                        "status-published";

                                } elseif ($status === "rejected") {

                                    $status_class =
                                        "status-rejected";

                                } else {

                                    $status_class =
                                        "status-draft";
                                }

                                ?>


                                <span class="status <?php echo $status_class; ?>">

                                    <?php
                                    echo htmlspecialchars(
                                        $article["Status"]
                                    );
                                    ?>

                                </span>

                            </td>



                            <!-- CREATED DATE -->

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



                            <!-- FEEDBACK -->

                            <td>

                                <?php

                                if (
                                    $status === "rejected"
                                    &&
                                    !empty(
                                        $article["Editors_Feedback"]
                                    )
                                ):

                                ?>

                                    <div class="feedback">

                                        <strong>
                                            Editor:
                                        </strong>

                                        <br>

                                        <?php

                                        echo htmlspecialchars(
                                            $article["Editors_Feedback"]
                                        );

                                        ?>

                                    </div>

                                <?php else: ?>

                                    <span class="disabled-action">
                                        —
                                    </span>

                                <?php endif; ?>

                            </td>



                            <!-- ACTIONS -->

                            <td>

                                <div class="actions">


                                    <?php

                                    /*
                                    ---------------------------------
                                    EDIT
                                    ---------------------------------

                                    Reporter can edit:

                                    Draft
                                    Rejected
                                    */

                                    if (
                                        $status === "draft"
                                        ||
                                        $status === "rejected"
                                    ):

                                    ?>

                                        <a
                                            href="edit_article.php?id=<?php echo $article["Article_ID"]; ?>"
                                            class="action-button edit-button"
                                        >
                                            Edit
                                        </a>

                                    <?php endif; ?>



                                    <?php

                                    /*
                                    ---------------------------------
                                    SUBMIT FOR REVIEW
                                    ---------------------------------

                                    Draft and Rejected articles
                                    can be submitted.
                                    */

                                    if (
                                        $status === "draft"
                                        ||
                                        $status === "rejected"
                                    ):

                                    ?>

                                        <a
                                            href="submit_article.php?id=<?php echo $article["Article_ID"]; ?>"
                                            class="action-button submit-button"
                                            onclick="return confirm('Submit this article for editorial review?');"
                                        >
                                            Submit
                                        </a>

                                    <?php endif; ?>



                                    <?php

                                    /*
                                    ---------------------------------
                                    DELETE
                                    ---------------------------------

                                    Only Draft articles can be deleted.
                                    */

                                    if ($status === "draft"):

                                    ?>

                                        <a
                                            href="delete_article.php?id=<?php echo $article["Article_ID"]; ?>"
                                            class="action-button delete-button"
                                            onclick="return confirm('Are you sure you want to delete this draft?');"
                                        >
                                            Delete
                                        </a>

                                    <?php endif; ?>



                                    <?php

                                    /*
                                    ---------------------------------
                                    PENDING
                                    ---------------------------------
                                    */

                                    if ($status === "pending"):

                                    ?>

                                        <span class="disabled-action">
                                            Waiting for editor
                                        </span>

                                    <?php endif; ?>



                                    <?php

                                    /*
                                    ---------------------------------
                                    PUBLISHED
                                    ---------------------------------
                                    */

                                    if ($status === "published"):

                                    ?>

                                        <span class="disabled-action">
                                            Published
                                        </span>

                                    <?php endif; ?>


                                </div>

                            </td>


                        </tr>


                    <?php endforeach; ?>


                    </tbody>

                </table>

            </div>


        <?php endif; ?>


    </section>


</main>


</body>

</html>