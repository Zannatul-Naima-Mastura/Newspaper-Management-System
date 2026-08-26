<?php

session_start();

require_once "dbConnect.php";

/*
|--------------------------------------------------------------------------
| Security Check
|--------------------------------------------------------------------------
| Only logged-in reporters can create articles.
*/

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "reporter") {
    header("Location: login.php");
    exit();
}

$reporter_name = $_SESSION["name"];


/*
|--------------------------------------------------------------------------
| Get Categories
|--------------------------------------------------------------------------
*/

$sql = "SELECT Category_ID, Category_Name
        FROM CATEGORY
        ORDER BY Category_Name ASC";

$result = $conn->query($sql);

if (!$result) {
    die("Error loading categories: " . $conn->error);
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Create Article - The Daily News</title>


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
            max-width: 900px;

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


        .page-title {
            font-family: Georgia, serif;

            font-size: 32px;

            margin-bottom: 8px;
        }


        .page-description {
            color: #666;

            margin-bottom: 30px;
        }


        /* FORM */

        .form-card {
            background: white;

            padding: 30px;

            border-radius: 8px;

            box-shadow:
                0 2px 8px rgba(0, 0, 0, 0.08);
        }


        .form-group {
            margin-bottom: 22px;
        }


        label {
            display: block;

            font-weight: bold;

            margin-bottom: 8px;
        }


        .required {
            color: #c62828;
        }


        input,
        select,
        textarea {

            width: 100%;

            padding: 12px;

            border: 1px solid #ccc;

            border-radius: 5px;

            font-size: 15px;

            outline: none;
        }


        input:focus,
        select:focus,
        textarea:focus {

            border-color: #555;
        }


        textarea {

            min-height: 350px;

            resize: vertical;

            line-height: 1.6;
        }


        .help-text {

            margin-top: 6px;

            color: #777;

            font-size: 13px;
        }


        /* BUTTONS */

        .button-row {

            display: flex;

            justify-content: flex-end;

            gap: 12px;

            margin-top: 30px;
        }


        .button {

            padding: 11px 20px;

            border-radius: 5px;

            text-decoration: none;

            border: none;

            cursor: pointer;

            font-size: 14px;

            font-weight: bold;
        }


        .cancel-button {

            background: #eee;

            color: #333;
        }


        .cancel-button:hover {

            background: #ddd;
        }


        .save-button {

            background: #111;

            color: white;
        }


        .save-button:hover {

            background: #333;
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


            .container {

                margin-top: 25px;
            }


            .form-card {

                padding: 20px;
            }


            .button-row {

                flex-direction: column;
            }


            .button {

                width: 100%;

                text-align: center;
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


    <a href="reporter_dashboard.php"
       class="back-link">

        ← Back to Dashboard

    </a>


    <h1 class="page-title">
        Create New Article
    </h1>


    <p class="page-description">
        Write your article and save it as a draft.
        You can submit it for editorial review from your dashboard.
    </p>



    <!-- =================================================
         ARTICLE FORM
    ================================================= -->

    <div class="form-card">

        <form action="save_article.php"
              method="POST">


            <!-- TITLE -->

            <div class="form-group">

                <label for="title">

                    Article Title
                    <span class="required">*</span>

                </label>


                <input
                    type="text"
                    id="title"
                    name="title"
                    placeholder="Enter the article title"
                    maxlength="255"
                    required
                >

            </div>



            <!-- CATEGORY -->

            <div class="form-group">

                <label for="category_id">

                    Category
                    <span class="required">*</span>

                </label>


                <select
                    id="category_id"
                    name="category_id"
                    required
                >

                    <option value="">
                        -- Select Category --
                    </option>


                    <?php while ($category = $result->fetch_assoc()): ?>

                        <option
                            value="<?php echo $category["Category_ID"]; ?>"
                        >

                            <?php

                            echo htmlspecialchars(
                                $category["Category_Name"]
                            );

                            ?>

                        </option>

                    <?php endwhile; ?>


                </select>

            </div>



            <!-- CONTENT -->

            <div class="form-group">

                <label for="content">

                    Article Content
                    <span class="required">*</span>

                </label>


                <textarea
                    id="content"
                    name="content"
                    placeholder="Write your article here..."
                    required
                ></textarea>


                <div class="help-text">

                    Your article will be saved as a draft.
                    You can edit it before submitting it for review.

                </div>

            </div>



            <!-- BUTTONS -->

            <div class="button-row">


                <a
                    href="reporter_dashboard.php"
                    class="button cancel-button"
                >

                    Cancel

                </a>


                <button
                    type="submit"
                    class="button save-button"
                >

                    Save Draft

                </button>


            </div>


        </form>

    </div>


</main>


</body>

</html>