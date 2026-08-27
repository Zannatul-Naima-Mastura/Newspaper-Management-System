<?php

session_start();

require_once "dbConnect.php";

/*
|--------------------------------------------------------------------------
| Search and Date Filters
|--------------------------------------------------------------------------
*/

$search = trim($_GET["search"] ?? "");
$year = $_GET["year"] ?? "";
$month = $_GET["month"] ?? "";

/*
|--------------------------------------------------------------------------
| Get Published Articles
|--------------------------------------------------------------------------
*/

$sql = "SELECT DISTINCT
            a.Article_ID,
            a.Title,
            a.Content,
            a.Published_At,
            r.Name AS Reporter_Name,
            c.Category_Name
        FROM ARTICLE a

        LEFT JOIN REPORTER r
            ON a.Reporter_ID = r.Staff_ID

        LEFT JOIN CATEGORY c
            ON a.Category_ID = c.Category_ID

        LEFT JOIN ARTICLE_TAG at
            ON a.Article_ID = at.Article_ID

        LEFT JOIN TAG t
            ON at.Tag_ID = t.Tag_ID

        WHERE a.Status = 'Published'";

$params = [];
$types = "";

/*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
*/

if ($search !== "") {

    $sql .= " AND (
                a.Title LIKE ?
                OR r.Name LIKE ?
                OR c.Category_Name LIKE ?
                OR t.Tag_Name LIKE ?
              )";

    $search_value = "%" . $search . "%";

    $params[] = $search_value;
    $params[] = $search_value;
    $params[] = $search_value;
    $params[] = $search_value;

    $types .= "ssss";
}

/*
|--------------------------------------------------------------------------
| Year Filter
|--------------------------------------------------------------------------
*/

if ($year !== "" && is_numeric($year)) {

    $sql .= " AND YEAR(a.Published_At) = ?";

    $params[] = (int)$year;
    $types .= "i";
}

/*
|--------------------------------------------------------------------------
| Month Filter
|--------------------------------------------------------------------------
*/

if ($month !== "" && is_numeric($month)) {

    $sql .= " AND MONTH(a.Published_At) = ?";

    $params[] = (int)$month;
    $types .= "i";
}

$sql .= " ORDER BY a.Published_At DESC";

/*
|--------------------------------------------------------------------------
| Prepare
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

/*
|--------------------------------------------------------------------------
| Bind Parameters
|--------------------------------------------------------------------------
*/

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();

$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>The Daily News</title>

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

        .logo {
            font-family: Georgia, serif;
            font-size: 28px;
            font-weight: bold;
        }

        .nav {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .nav a {
            color: white;
            text-decoration: none;
            padding: 8px 12px;
            border: 1px solid #777;
            border-radius: 5px;
        }

        .nav a:hover {
            background: white;
            color: #111;
        }

        .container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .page-title {
            font-family: Georgia, serif;
            font-size: 36px;
            margin-bottom: 10px;
        }

        .page-description {
            color: #666;
            margin-bottom: 30px;
        }

        .filters {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;

            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .filters form {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .filters input,
        .filters select {

            padding: 11px;

            border: 1px solid #ccc;

            border-radius: 5px;

            font-size: 14px;
        }

        .search-input {
            flex: 1;
            min-width: 250px;
        }

        .filter-button {

            background: #111;
            color: white;

            border: none;

            padding: 11px 18px;

            border-radius: 5px;

            cursor: pointer;
        }

        .clear-button {

            padding: 11px 18px;

            background: #eee;

            color: #333;

            text-decoration: none;

            border-radius: 5px;
        }

        .articles {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 25px;
        }

        .article-card {

            background: white;

            padding: 25px;

            border-radius: 8px;

            box-shadow:
                0 2px 8px rgba(0,0,0,0.08);
        }

        .category {

            display: inline-block;

            background: #eee;

            padding: 5px 10px;

            border-radius: 15px;

            font-size: 12px;

            margin-bottom: 12px;
        }

        .article-card h2 {

            font-family: Georgia, serif;

            font-size: 25px;

            margin-bottom: 12px;
        }

        .article-card h2 a {

            color: #111;

            text-decoration: none;
        }

        .article-card h2 a:hover {
            text-decoration: underline;
        }

        .metadata {

            color: #777;

            font-size: 13px;

            margin-bottom: 15px;
        }

        .preview {

            color: #555;

            line-height: 1.6;

            margin-bottom: 20px;
        }

        .read-button {

            display: inline-block;

            background: #111;

            color: white;

            padding: 9px 15px;

            border-radius: 5px;

            text-decoration: none;
        }

        .empty {

            background: white;

            padding: 50px;

            text-align: center;

            border-radius: 8px;

            color: #777;
        }

        @media(max-width: 700px) {

            .header {
                padding: 15px 20px;
            }

            .nav {
                flex-wrap: wrap;
            }

            .articles {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body>

<header class="header">

    <div class="logo">
        The Daily News
    </div>

    <div class="nav">

        <?php if (
            isset($_SESSION["user_id"])
            &&
            $_SESSION["role"] === "reader"
        ): ?>

            <a href="bookmarks.php">
                My Bookmarks
            </a>

            <a href="subscription.php">
                Subscription
            </a>

            <a href="categories.php">
                Categories
            </a>

            <a href="comments.php">
                Comments
            </a>

            <a href="dashboard.php">
                Dashboard
            </a>

            <a href="logout.php">
                Logout
            </a>

        <?php else: ?>

            <a href="login.php">
                Login
            </a>

        <?php endif; ?>

    </div>

</header>


<main class="container">

    <h1 class="page-title">
        Latest News
    </h1>

    <p class="page-description">
        Read the latest published news and explore our archives.
    </p>


    <!-- SEARCH + DATE FILTER -->

    <section class="filters">

        <form method="GET"
              action="reader_home.php">

            <input
                type="text"
                name="search"
                class="search-input"
                placeholder="Search title, author, category or tag..."
                value="<?php echo htmlspecialchars($search); ?>"
            >


            <select name="month">

                <option value="">
                    All Months
                </option>

                <?php

                $months = [
                    1 => "January",
                    2 => "February",
                    3 => "March",
                    4 => "April",
                    5 => "May",
                    6 => "June",
                    7 => "July",
                    8 => "August",
                    9 => "September",
                    10 => "October",
                    11 => "November",
                    12 => "December"
                ];

                foreach ($months as $number => $name):

                ?>

                    <option
                        value="<?php echo $number; ?>"
                        <?php
                        if ((string)$month === (string)$number) {
                            echo "selected";
                        }
                        ?>
                    >

                        <?php echo $name; ?>

                    </option>

                <?php endforeach; ?>

            </select>


            <select name="year">

                <option value="">
                    All Years
                </option>

                <?php

                $year_result = $conn->query(
                    "SELECT DISTINCT YEAR(Published_At) AS Year
                     FROM ARTICLE
                     WHERE Status = 'Published'
                     AND Published_At IS NOT NULL
                     ORDER BY Year DESC"
                );

                while ($year_row =
                    $year_result->fetch_assoc()
                ):

                ?>

                    <option
                        value="<?php echo $year_row["Year"]; ?>"
                        <?php
                        if ((string)$year ===
                            (string)$year_row["Year"]) {
                            echo "selected";
                        }
                        ?>
                    >

                        <?php echo $year_row["Year"]; ?>

                    </option>

                <?php endwhile; ?>

            </select>


            <button
                type="submit"
                class="filter-button"
            >
                Search
            </button>


            <a
                href="reader_home.php"
                class="clear-button"
            >
                Clear
            </a>

        </form>

    </section>


    <!-- ARTICLES -->

    <?php if ($result->num_rows === 0): ?>

        <div class="empty">

            <h2>
                No articles found
            </h2>

            <p>
                Try another search or date.
            </p>

        </div>

    <?php else: ?>

        <section class="articles">

            <?php while (
                $article = $result->fetch_assoc()
            ): ?>

                <article class="article-card">

                    <span class="category">

                        <?php
                        echo htmlspecialchars(
                            $article["Category_Name"]
                            ?? "Uncategorized"
                        );
                        ?>

                    </span>


                    <h2>

                        <a
                            href="article_details.php?id=<?php
                                echo $article["Article_ID"];
                            ?>"
                        >

                            <?php
                            echo htmlspecialchars(
                                $article["Title"]
                            );
                            ?>

                        </a>

                    </h2>


                    <div class="metadata">

                        By
                        <strong>
                            <?php
                            echo htmlspecialchars(
                                $article["Reporter_Name"]
                                ?? "Unknown"
                            );
                            ?>
                        </strong>

                        |

                        <?php
                        echo date(
                            "d F Y",
                            strtotime(
                                $article["Published_At"]
                            )
                        );
                        ?>

                    </div>


                    <p class="preview">

                        <?php

                        $content =
                            $article["Content"];

                        echo htmlspecialchars(
                            strlen($content) > 180
                            ? substr($content, 0, 180) . "..."
                            : $content
                        );

                        ?>

                    </p>


                    <a
                        class="read-button"
                        href="article_details.php?id=<?php
                            echo $article["Article_ID"];
                        ?>"
                    >
                        Read Article
                    </a>

                </article>

            <?php endwhile; ?>

        </section>

    <?php endif; ?>

</main>

</body>
</html>