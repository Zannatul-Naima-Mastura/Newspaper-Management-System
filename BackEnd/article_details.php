<?php

session_start();

require_once "dbConnect.php";

/*
|--------------------------------------------------------------------------
| Validate Article ID
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


/*
|--------------------------------------------------------------------------
| Get Published Article
|--------------------------------------------------------------------------
*/

$sql = "SELECT
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

        WHERE a.Article_ID = ?
        AND a.Status = 'Published'";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param(
    "i",
    $article_id
);

$stmt->execute();

$result = $stmt->get_result();

$article = $result->fetch_assoc();

$stmt->close();


/*
|--------------------------------------------------------------------------
| Article Not Found
|--------------------------------------------------------------------------
*/

if (!$article) {

    echo "<h2>Article not found.</h2>";

    echo '<a href="reader_home.php">
            Back to News
          </a>';

    exit();
}


/*
|--------------------------------------------------------------------------
| Get Tags
|--------------------------------------------------------------------------
*/

$tag_sql = "SELECT
                t.Tag_Name

            FROM TAG t

            INNER JOIN ARTICLE_TAG at
                ON t.Tag_ID = at.Tag_ID

            WHERE at.Article_ID = ?

            ORDER BY t.Tag_Name ASC";

$tag_stmt = $conn->prepare($tag_sql);

$tag_stmt->bind_param(
    "i",
    $article_id
);

$tag_stmt->execute();

$tag_result = $tag_stmt->get_result();


/*
|--------------------------------------------------------------------------
| Check Bookmark
|--------------------------------------------------------------------------
*/

$is_bookmarked = false;

if (
    isset($_SESSION["user_id"])
    &&
    $_SESSION["role"] === "reader"
) {

    $bookmark_sql = "SELECT 1
                     FROM BOOKMARK
                     WHERE Reader_ID = ?
                     AND Article_ID = ?";

    $bookmark_stmt =
        $conn->prepare($bookmark_sql);

    $bookmark_stmt->bind_param(
        "ii",
        $_SESSION["user_id"],
        $article_id
    );

    $bookmark_stmt->execute();

    $bookmark_result =
        $bookmark_stmt->get_result();

    if ($bookmark_result->num_rows > 0) {
        $is_bookmarked = true;
    }

    $bookmark_stmt->close();
}

/*
|--------------------------------------------------------------------------
| Get Advertisement for This Article
|--------------------------------------------------------------------------
*/

$ad_sql = "SELECT
              Advertisement_ID,
              Brand,
              Duration
           FROM ADVERTISEMENT
           WHERE Article_ID = ?
           AND Status = 'Active'
           ORDER BY Advertisement_ID DESC";

$ad_stmt = $conn->prepare($ad_sql);
$ad_stmt->bind_param("i", $article_id);
$ad_stmt->execute();

$ad_result = $ad_stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        <?php echo htmlspecialchars($article["Title"]); ?>
    </title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background: #f4f5f7;
            color: #222;
            font-family: Arial, sans-serif;
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

        .header a {
            color: white;
            text-decoration: none;
            border: 1px solid #aaa;
            padding: 8px 14px;
            border-radius: 5px;
        }

        .container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .back {
            display: inline-block;
            margin-bottom: 20px;
            color: #555;
            text-decoration: none;
        }

        .article {
            background: white;
            padding: 45px;
            border-radius: 8px;

            box-shadow:
                0 2px 8px rgba(0,0,0,0.08);
        }

        .category {
            display: inline-block;
            background: #eee;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 12px;
            margin-bottom: 15px;
        }

        h1 {
            font-family: Georgia, serif;
            font-size: 42px;
            line-height: 1.2;
            margin-bottom: 20px;
        }

        .metadata {
            color: #666;
            padding-bottom: 20px;
            border-bottom: 1px solid #ddd;
            margin-bottom: 25px;
            line-height: 1.7;
        }

        .tags {
            margin-bottom: 30px;
        }

        .tag {
            display: inline-block;
            background: #f0f0f0;
            padding: 5px 10px;
            margin: 3px;
            border-radius: 15px;
            font-size: 12px;
        }

        .content {
            font-family: Georgia, serif;
            font-size: 18px;
            line-height: 1.8;
            white-space: pre-wrap;
        }

        .bookmark-section {
            margin-top: 35px;
            padding-top: 25px;
            border-top: 1px solid #ddd;
        }

        .bookmark-button {
            display: inline-block;
            padding: 11px 18px;
            background: #111;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .bookmark-button:hover {
            background: #444;
        }

        .login-message {
            color: #666;
        }

.advertisement {
    background: #fff;
    border: 1px solid #ddd;
    padding: 20px;
    margin: 25px 0;
    text-align: center;
    border-radius: 8px;
}

.ad-label {
    font-size: 11px;
    color: #777;
    letter-spacing: 1px;
    margin-bottom: 8px;
}

.advertisement h3 {
    font-size: 22px;
    margin-bottom: 8px;
}

.advertisement p {
    color: #777;
}



    </style>

</head>

<body>

<header class="header">

    <div class="logo">
        The Daily News
    </div>

    <a href="reader_home.php">
        Back to News
    </a>

</header>


<main class="container">

    <a
        href="reader_home.php"
        class="back"
    >
        ← Back to News
    </a>


    <article class="article">

        <span class="category">

            <?php
            echo htmlspecialchars(
                $article["Category_Name"]
                ?? "Uncategorized"
            );
            ?>

        </span>


        <h1>

            <?php
            echo htmlspecialchars(
                $article["Title"]
            );
            ?>

        </h1>


        <div class="metadata">

            <strong>Author:</strong>

            <?php
            echo htmlspecialchars(
                $article["Reporter_Name"]
                ?? "Unknown"
            );
            ?>

            <br>

            <strong>Published:</strong>

            <?php
            echo date(
                "d F Y, h:i A",
                strtotime(
                    $article["Published_At"]
                )
            );
            ?>

        </div>


        <div class="tags">

            <strong>Tags:</strong>

            <?php while (
                $tag = $tag_result->fetch_assoc()
            ): ?>

                <span class="tag">

                    <?php
                    echo htmlspecialchars(
                        $tag["Tag_Name"]
                    );
                    ?>

                </span>

            <?php endwhile; ?>

        </div>


<?php if ($ad_result->num_rows > 0): ?>

    <?php while ($ad = $ad_result->fetch_assoc()): ?>

        <div class="advertisement">
            <div class="ad-label">ADVERTISEMENT</div>

            <h3>
                <?php echo htmlspecialchars($ad["Brand"]); ?>
            </h3>

            <p>
                Sponsored advertisement
            </p>
        </div>

    <?php endwhile; ?>

<?php endif; ?>



        <div class="content">

            <?php
            echo htmlspecialchars(
                $article["Content"]
            );
            ?>

        </div>


        <div class="bookmark-section">

            <?php if (
                isset($_SESSION["user_id"])
                &&
                $_SESSION["role"] === "reader"
            ): ?>

                <?php if ($is_bookmarked): ?>

                    <a
                        class="bookmark-button"
                        href="remove_bookmark.php?id=<?php
                            echo $article_id;
                        ?>"
                    >
                        ★ Remove Bookmark
                    </a>

                <?php else: ?>

                    <a
                        class="bookmark-button"
                        href="add_bookmark.php?id=<?php
                            echo $article_id;
                        ?>"
                    >
                        ☆ Bookmark Article
                    </a>

                <?php endif; ?>

            <?php else: ?>

                <p class="login-message">

                    Please
                    <a href="login.php">
                        login
                    </a>
                    as a registered reader to bookmark articles.

                </p>

            <?php endif; ?>

        </div>

    </article>

</main>

</body>

</html>