<?php

session_start();

require_once "dbConnect.php";


/*
|--------------------------------------------------------------------------
| Only Registered Readers
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
| Get User's Bookmarks
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            a.Article_ID,
            a.Title,
            a.Published_At,
            r.Name AS Reporter_Name,
            c.Category_Name,
            b.Bookmarked_At

        FROM BOOKMARK b

        INNER JOIN ARTICLE a
            ON b.Article_ID = a.Article_ID

        LEFT JOIN REPORTER r
            ON a.Reporter_ID = r.Staff_ID

        LEFT JOIN CATEGORY c
            ON a.Category_ID = c.Category_ID

        WHERE b.Reader_ID = ?

        AND a.Status = 'Published'

        ORDER BY b.Bookmarked_At DESC";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $reader_id
);

$stmt->execute();

$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>My Bookmarks - The Daily News</title>

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

            display: flex;
            justify-content: space-between;
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

        .card {
            background: white;
            padding: 22px;
            margin-bottom: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,.08);
        }

        .card h2 {
            font-family: Georgia, serif;
            margin-bottom: 10px;
        }

        .card h2 a {
            color: #111;
            text-decoration: none;
        }

        .metadata {
            color: #777;
            font-size: 13px;
        }

        .remove {
            display: inline-block;
            margin-top: 15px;
            color: #b00020;
            text-decoration: none;
        }

        .empty {
            background: white;
            padding: 50px;
            text-align: center;
        }

    </style>

</head>

<body>

<header class="header">

    <strong>
        The Daily News
    </strong>

    <a href="reader_home.php">
        ← Back to News
    </a>

</header>


<main class="container">

    <h1>
        My Bookmarked Articles
    </h1>

    <br>


    <?php if ($result->num_rows === 0): ?>

        <div class="empty">

            <h2>
                No bookmarks yet.
            </h2>

            <p>
                Bookmark articles to read them later.
            </p>

        </div>

    <?php else: ?>


        <?php while (
            $article = $result->fetch_assoc()
        ): ?>

            <div class="card">

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

                    Author:
                    <?php
                    echo htmlspecialchars(
                        $article["Reporter_Name"]
                    );
                    ?>

                    |

                    Category:
                    <?php
                    echo htmlspecialchars(
                        $article["Category_Name"]
                    );
                    ?>

                    |

                    Published:
                    <?php
                    echo date(
                        "d F Y",
                        strtotime(
                            $article["Published_At"]
                        )
                    );
                    ?>

                    |

                    Bookmarked:
                    <?php
                    echo date(
                        "d F Y",
                        strtotime(
                            $article["Bookmarked_At"]
                        )
                    );
                    ?>

                </div>


                <a
                    class="remove"
                    href="remove_bookmark.php?id=<?php
                        echo $article["Article_ID"];
                    ?>"
                    onclick="return confirm(
                        'Remove this bookmark?'
                    );"
                >
                    Remove Bookmark
                </a>

            </div>

        <?php endwhile; ?>


    <?php endif; ?>

</main>

</body>

</html>