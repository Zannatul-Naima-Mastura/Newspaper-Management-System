<?php

session_start();

require_once "dbConnect.php";


/*
|--------------------------------------------------------------------------
| Security Check
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "editor") {
    header("Location: login.php");
    exit();
}


$editor_name = $_SESSION["name"];


/*
|--------------------------------------------------------------------------
| Get Article ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: editor_dashboard.php");
    exit();
}


$article_id = (int) $_GET["id"];


/*
|--------------------------------------------------------------------------
| Get Pending Article
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            a.Article_ID,
            a.Title,
            a.Content,
            a.Created_At,
            a.Status,
            r.Name AS Reporter_Name,
            r.Email AS Reporter_Email,
            c.Category_Name
        FROM ARTICLE a

        LEFT JOIN REPORTER r
            ON a.Reporter_ID = r.Staff_ID

        LEFT JOIN CATEGORY c
            ON a.Category_ID = c.Category_ID

        WHERE a.Article_ID = ?
        AND a.Status = 'Pending'";


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

    echo "<p>
            This article may already have been reviewed
            or does not exist.
          </p>";

    echo '<a href="editor_dashboard.php">
            Back to Editor Dashboard
          </a>';

    exit();
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        Review Article - The Daily News
    </title>


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
            max-width: 950px;

            margin: 40px auto;

            padding: 0 20px;
        }


        .back-link {
            display: inline-block;

            margin-bottom: 20px;

            color: #555;

            text-decoration: none;
        }


        .back-link:hover {
            color: #111;
        }


        /* ARTICLE */

        .article-card {
            background: white;

            padding: 40px;

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

            margin-bottom: 15px;
        }


        .article-title {
            font-family: Georgia, serif;

            font-size: 36px;

            line-height: 1.2;

            margin-bottom: 15px;
        }


        .metadata {
            color: #666;

            font-size: 14px;

            padding-bottom: 25px;

            border-bottom: 1px solid #ddd;

            margin-bottom: 30px;

            line-height: 1.7;
        }


        .article-content {
            font-family: Georgia, serif;

            font-size: 18px;

            line-height: 1.8;

            white-space: pre-wrap;
        }


        /* REVIEW */

        .review-box {
            margin-top: 35px;

            background: white;

            padding: 30px;

            border-radius: 8px;

            box-shadow:
                0 2px 8px rgba(0,0,0,0.08);
        }


        .review-box h2 {
            font-family: Georgia, serif;

            margin-bottom: 8px;
        }


        .review-box p {
            color: #666;

            margin-bottom: 20px;
        }


        textarea {
            width: 100%;

            min-height: 120px;

            resize: vertical;

            padding: 12px;

            border: 1px solid #ccc;

            border-radius: 5px;

            font-size: 14px;

            line-height: 1.5;
        }


        /* BUTTONS */

        .actions {
            display: flex;

            gap: 12px;

            margin-top: 20px;
        }


        .button {
            border: none;

            border-radius: 5px;

            padding: 12px 22px;

            font-weight: bold;

            cursor: pointer;

            font-size: 14px;
        }


        .approve {
            background: #198754;

            color: white;
        }


        .approve:hover {
            background: #157347;
        }


        .reject {
            background: #dc3545;

            color: white;
        }


        .reject:hover {
            background: #bb2d3b;
        }


        @media (max-width: 600px) {

            .header {
                padding: 15px 20px;
            }


            .logo {
                font-size: 22px;
            }


            .user-info {
                display: none;
            }


            .article-card,
            .review-box {
                padding: 20px;
            }


            .article-title {
                font-size: 28px;
            }


            .actions {
                flex-direction: column;
            }


            .button {
                width: 100%;
            }

        }

    </style>

</head>


<body>


<header class="header">

    <div class="logo">
        The Daily News
    </div>


    <div class="header-right">

        <div class="user-info">

            <div class="user-name">

                <?php

                echo htmlspecialchars(
                    $editor_name
                );

                ?>

            </div>

            <div class="user-role">
                Editor
            </div>

        </div>


        <a
            href="logout.php"
            class="logout-button"
        >

            Logout

        </a>

    </div>

</header>



<main class="container">


    <a
        href="editor_dashboard.php"
        class="back-link"
    >

        ← Back to Editor Dashboard

    </a>



    <!-- ARTICLE -->

    <article class="article-card">


        <span class="category">

            <?php

            echo htmlspecialchars(
                $article["Category_Name"]
                ?? "Uncategorized"
            );

            ?>

        </span>


        <h1 class="article-title">

            <?php

            echo htmlspecialchars(
                $article["Title"]
            );

            ?>

        </h1>


        <div class="metadata">

            <strong>
                Author:
            </strong>

            <?php

            echo htmlspecialchars(
                $article["Reporter_Name"]
                ?? "Unknown"
            );

            ?>

            <br>


            <strong>
                Submitted:
            </strong>

            <?php

            echo date(
                "d F Y, h:i A",
                strtotime(
                    $article["Created_At"]
                )
            );

            ?>

        </div>


        <div class="article-content">

            <?php

            echo htmlspecialchars(
                $article["Content"]
            );

            ?>

        </div>


    </article>



    <!-- REVIEW -->

    <section class="review-box">


        <h2>
            Editorial Decision
        </h2>


        <p>
            Review the article carefully before approving
            or rejecting it.
        </p>


        <form
  	  method="POST"
  	  action="approve_article.php"
	>

   	 <input
   	     type="hidden"
   	     name="article_id"
  	      value="<?php echo $article["Article_ID"]; ?>"
 	   >
	
   	 <label for="feedback">
  	      Editor's Feedback
 	   </label>

  	  <br><br>

 	   <textarea
  		id="feedback"
	  	name="feedback"
       		 placeholder="Optional feedback for the reporter..."
  	  ></textarea>

   	 <div class="actions">

     	   <!-- APPROVE -->
    	    <button
        	    type="submit"
            	class="button approve"
            	onclick="return confirm('Approve this article and publish it?');"
        	>
            	✓ Approve & Publish
	        </button>


        	<!-- REJECT -->
        	<button
            type="submit"
            formaction="reject_article.php"
            class="button reject"
            onclick="return confirm('Reject this article?');"
        >
            ✕ Reject
        </button>

    </div>

</form>


    </section>


</main>


</body>

</html>