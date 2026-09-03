<?php

session_start();
require_once "dbConnect.php";


/*
|--------------------------------------------------------------------------
| ADMIN SECURITY CHECK
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["role"] !== "admin"
) {
    header("Location: login.php");
    exit();
}


$admin_id = $_SESSION["user_id"];

$message = "";
$error = "";


/*
|--------------------------------------------------------------------------
| HANDLE ADMIN ACTIONS
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| ADD ADVERTISEMENT
|--------------------------------------------------------------------------
*/

if (isset($_POST["add_advertisement"])) {

    $brand = trim($_POST["brand"] ?? "");
  
    $status = $_POST["status"] ?? "Pending";
    $article_id = intval($_POST["article_id"] ?? 0);


    if (
        $brand === "" ||
        
        $article_id <= 0
    ) {

        $error = "Please provide all advertisement information.";

    } else {

        /*
        | Check that the selected article exists
        | and is published.
        */

        $check = $conn->prepare("
            SELECT Article_ID
            FROM ARTICLE
            WHERE Article_ID = ?
            AND Status = 'Published'
        ");

        $check->bind_param(
            "i",
            $article_id
        );

        $check->execute();

        $result = $check->get_result();


        if ($result->num_rows !== 1) {

            $error = "Selected article does not exist or is not published.";

        } else {

            /*
            | Generate next Advertisement ID.
            */

            $id_result = $conn->query("
                SELECT
                    COALESCE(MAX(Advertisement_ID), 0) + 1
                    AS next_id
                FROM ADVERTISEMENT
            ");

            $next_id =
                $id_result->fetch_assoc()["next_id"];


            /*
            | Insert advertisement.
            */

            $stmt = $conn->prepare("
                INSERT INTO ADVERTISEMENT
                (
                    Advertisement_ID,
                    Brand,
                   
                    Status,
                    Admin_ID,
                    Article_ID
                )
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $stmt->bind_param(
                "isissi",
                $next_id,
                $brand,
                
                $status,
                $admin_id,
                $article_id
            );


            if ($stmt->execute()) {

                $message =
                    "Advertisement added successfully.";

            } else {

                $error =
                    "Unable to add advertisement: "
                    . $stmt->error;
            }


            $stmt->close();
        }


        $check->close();
    }
}


/*
|--------------------------------------------------------------------------
| EDIT ADVERTISEMENT
|--------------------------------------------------------------------------
*/

if (isset($_POST["edit_advertisement"])) {

    $advertisement_id =
        intval($_POST["advertisement_id"] ?? 0);

    $brand =
        trim($_POST["brand"] ?? "");

    

    $status =
        $_POST["status"] ?? "Pending";

    $article_id =
        intval($_POST["article_id"] ?? 0);


    if (
        $advertisement_id <= 0 ||
        $brand === "" ||
        
        $article_id <= 0
    ) {

        $error =
            "Please provide all advertisement information.";

    } else {

        /*
        | Make sure selected article is published.
        */

        $check = $conn->prepare("
            SELECT Article_ID
            FROM ARTICLE
            WHERE Article_ID = ?
            AND Status = 'Published'
        ");

        $check->bind_param(
            "i",
            $article_id
        );

        $check->execute();

        $result =
            $check->get_result();


        if ($result->num_rows !== 1) {

            $error =
                "Selected article does not exist or is not published.";

        } else {

            $stmt = $conn->prepare("
                UPDATE ADVERTISEMENT
                SET
                    Brand = ?,
                    
                    Status = ?,
                    Article_ID = ?
                WHERE Advertisement_ID = ?
            ");

            $stmt->bind_param(
                "sisii",
                $brand,
                
                $status,
                $article_id,
                $advertisement_id
            );


            if ($stmt->execute()) {

                $message =
                    "Advertisement updated successfully.";

            } else {

                $error =
                    "Unable to update advertisement: "
                    . $stmt->error;
            }


            $stmt->close();
        }


        $check->close();
    }
}


/*
|--------------------------------------------------------------------------
| DELETE ADVERTISEMENT
|--------------------------------------------------------------------------
*/

if (isset($_POST["delete_advertisement"])) {

    $advertisement_id =
        intval($_POST["advertisement_id"] ?? 0);


    if ($advertisement_id > 0) {

        $stmt = $conn->prepare("
            DELETE FROM ADVERTISEMENT
            WHERE Advertisement_ID = ?
        ");

        $stmt->bind_param(
            "i",
            $advertisement_id
        );


        if ($stmt->execute()) {

            $message =
                "Advertisement deleted successfully.";

        } else {

            $error =
                "Unable to delete advertisement: "
                . $stmt->error;
        }


        $stmt->close();
    }
}


/*
|--------------------------------------------------------------------------
| APPROVE COMMENT
|--------------------------------------------------------------------------
*/

if (isset($_POST["approve_comment"])) {

    $comment_id =
        intval($_POST["comment_id"] ?? 0);


    if ($comment_id > 0) {

        $stmt = $conn->prepare("
            UPDATE `COMMENT`
            SET Status = 'Approved'
            WHERE Comment_ID = ?
        ");

        $stmt->bind_param(
            "i",
            $comment_id
        );

        if ($stmt->execute()) {

            $message =
                "Comment approved successfully.";

        } else {

            $error =
                "Unable to approve comment.";
        }

        $stmt->close();
    }
}


/*
|--------------------------------------------------------------------------
| FLAG COMMENT
|--------------------------------------------------------------------------
*/

if (isset($_POST["flag_comment"])) {

    $comment_id =
        intval($_POST["comment_id"] ?? 0);


    if ($comment_id > 0) {

        $stmt = $conn->prepare("
            UPDATE `COMMENT`
            SET Status = 'Flagged'
            WHERE Comment_ID = ?
        ");

        $stmt->bind_param(
            "i",
            $comment_id
        );

        if ($stmt->execute()) {

            $message =
                "Comment flagged successfully.";

        } else {

            $error =
                "Unable to flag comment.";
        }

        $stmt->close();
    }
}


/*
|--------------------------------------------------------------------------
| DELETE COMMENT
|--------------------------------------------------------------------------
*/

if (isset($_POST["delete_comment"])) {

    $comment_id =
        intval($_POST["comment_id"] ?? 0);


    if ($comment_id > 0) {

        $stmt = $conn->prepare("
            DELETE FROM `COMMENT`
            WHERE Comment_ID = ?
        ");

        $stmt->bind_param(
            "i",
            $comment_id
        );


        if ($stmt->execute()) {

            $message =
                "Comment deleted successfully.";

        } else {

            $error =
                "Unable to delete comment.";
        }


        $stmt->close();
    }
}


/*
|--------------------------------------------------------------------------
| BAN REPORTER
|--------------------------------------------------------------------------
*/

if (isset($_POST["ban_reporter"])) {

    $reporter_id =
        intval($_POST["reporter_id"] ?? 0);


    if ($reporter_id > 0) {

        $stmt = $conn->prepare("
            UPDATE REPORTER
            SET Status = 'Banned'
            WHERE Staff_ID = ?
        ");

        $stmt->bind_param(
            "i",
            $reporter_id
        );


        if ($stmt->execute()) {

            $message =
                "Reporter has been banned.";

        } else {

            $error =
                "Unable to ban reporter.";
        }


        $stmt->close();
    }
}


/*
|--------------------------------------------------------------------------
| UNBAN REPORTER
|--------------------------------------------------------------------------
*/

if (isset($_POST["unban_reporter"])) {

    $reporter_id =
        intval($_POST["reporter_id"] ?? 0);


    if ($reporter_id > 0) {

        $stmt = $conn->prepare("
            UPDATE REPORTER
            SET Status = 'Active'
            WHERE Staff_ID = ?
        ");

        $stmt->bind_param(
            "i",
            $reporter_id
        );


        if ($stmt->execute()) {

            $message =
                "Reporter has been unbanned.";

        } else {

            $error =
                "Unable to unban reporter.";
        }


        $stmt->close();
    }
}


/*
|--------------------------------------------------------------------------
| GET DASHBOARD STATISTICS
|--------------------------------------------------------------------------
*/


/* Advertisement count */

$ad_count_result = $conn->query("
    SELECT COUNT(*) AS total
    FROM ADVERTISEMENT
");

$ad_count =
    $ad_count_result->fetch_assoc()["total"];


/* Comment count */

$comment_count_result = $conn->query("
    SELECT COUNT(*) AS total
    FROM `COMMENT`
");

$comment_count =
    $comment_count_result->fetch_assoc()["total"];


/* Reporter count */

$reporter_count_result = $conn->query("
    SELECT COUNT(*) AS total
    FROM REPORTER
");

$reporter_count =
    $reporter_count_result->fetch_assoc()["total"];


/* Banned reporter count */

$banned_count_result = $conn->query("
    SELECT COUNT(*) AS total
    FROM REPORTER
    WHERE Status = 'Banned'
");

$banned_count =
    $banned_count_result->fetch_assoc()["total"];


/* Pending comment count */

$pending_comment_result = $conn->query("
    SELECT COUNT(*) AS total
    FROM `COMMENT`
    WHERE Status = 'Pending'
");

$pending_comments =
    $pending_comment_result->fetch_assoc()["total"];


/*
|--------------------------------------------------------------------------
| GET PUBLISHED ARTICLES
|--------------------------------------------------------------------------
|
| These are the articles that admins can attach advertisements to.
|
*/

$articles = $conn->query("
    SELECT
        Article_ID,
        Title
    FROM ARTICLE
    WHERE Status = 'Published'
    ORDER BY Published_At DESC, Title ASC
");


/*
|--------------------------------------------------------------------------
| GET ADVERTISEMENTS
|--------------------------------------------------------------------------
*/

$advertisements = $conn->query("
    SELECT
        ad.Advertisement_ID,
        ad.Brand,

        ad.Status,
        ad.Admin_ID,
        ad.Article_ID,
        a.Title AS Article_Title
    FROM ADVERTISEMENT ad
    LEFT JOIN ARTICLE a
        ON ad.Article_ID = a.Article_ID
    ORDER BY ad.Advertisement_ID DESC
");


/*
|--------------------------------------------------------------------------
| GET COMMENTS
|--------------------------------------------------------------------------
*/

$comments = $conn->query("
    SELECT
        c.Comment_ID,
        c.Comment_Text,
        c.Time_Stamp,
        c.Status,
        r.Name AS Reader_Name,
        a.Title AS Article_Title
    FROM `COMMENT` c
    JOIN REGISTERED_READER r
        ON c.Reader_ID = r.Reader_ID
    JOIN ARTICLE a
        ON c.Article_ID = a.Article_ID
    ORDER BY c.Time_Stamp DESC
");


/*
|--------------------------------------------------------------------------
| GET REPORTERS
|--------------------------------------------------------------------------
*/

$reporters = $conn->query("
    SELECT
        Staff_ID,
        Name,
        Email,
        Status,
        Specialization,
        Joining_Date
    FROM REPORTER
    ORDER BY Staff_ID ASC
");

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Admin Dashboard</title>


    <style>

        * {
            box-sizing: border-box;
        }


        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            color: #222;
        }


        .dashboard {
            width: 95%;
            max-width: 1400px;
            margin: 30px auto;
        }


        .dashboard-header {
            background: #1f2937;
            color: white;
            padding: 25px;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }


        .dashboard-header h1 {
            margin: 5px 0;
        }


        .eyebrow {
            margin: 0;
            font-size: 13px;
            letter-spacing: 2px;
            opacity: 0.8;
        }


        .header-buttons {
            display: flex;
            gap: 10px;
        }


        .button-link {
            display: inline-block;
            padding: 10px 16px;
            background: white;
            color: #1f2937;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
        }


        .button-link:hover {
            opacity: 0.9;
        }


        .notice {
            padding: 14px 18px;
            margin: 20px 0;
            border-radius: 6px;
        }


        .success {
            background: #dcfce7;
            color: #166534;
        }


        .error {
            background: #fee2e2;
            color: #991b1b;
        }


        /* STATISTICS */

        .stats {
            display: grid;
            grid-template-columns:
                repeat(5, 1fr);

            gap: 15px;

            margin: 25px 0;
        }


        .stat-card {
            background: white;
            padding: 22px;
            border-radius: 10px;
            box-shadow:
                0 2px 8px rgba(0,0,0,0.08);
        }


        .stat-card h3 {
            margin: 0;
            font-size: 14px;
            color: #666;
        }


        .stat-number {
            font-size: 30px;
            font-weight: bold;
            margin-top: 8px;
        }


        /* SECTIONS */

        .section {
            background: white;
            padding: 25px;
            margin-bottom: 25px;
            border-radius: 10px;
            box-shadow:
                0 2px 8px rgba(0,0,0,0.08);
        }


        .section h2 {
            margin-top: 0;
            border-bottom: 1px solid #ddd;
            padding-bottom: 12px;
        }


        /* FORMS */

        .form-grid {
            display: grid;
            grid-template-columns:
                repeat(4, 1fr);

            gap: 15px;
            align-items: end;
        }


        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }


        .form-group label {
            font-weight: bold;
            font-size: 14px;
        }


        input,
        select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 100%;
        }


        button {
            border: none;
            padding: 10px 14px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }


        .add-btn {
            background: #2563eb;
            color: white;
        }


        .save-btn {
            background: #2563eb;
            color: white;
        }


        .approve-btn {
            background: #16a34a;
            color: white;
        }


        .flag-btn {
            background: #f59e0b;
            color: white;
        }


        .delete-btn {
            background: #dc2626;
            color: white;
        }


        .ban-btn {
            background: #dc2626;
            color: white;
        }


        .unban-btn {
            background: #16a34a;
            color: white;
        }


        button:hover {
            opacity: 0.85;
        }


        /* TABLE */

        .table-wrap {
            overflow-x: auto;
        }


        table {
            width: 100%;
            border-collapse: collapse;
        }


        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
            vertical-align: middle;
        }


        th {
            background: #f3f4f6;
            font-size: 14px;
        }


        td {
            font-size: 14px;
        }


        .inline-form {
            display: inline;
        }


        .edit-form {
            display: grid;
            grid-template-columns:
                1fr 100px 120px 1fr auto;

            gap: 8px;

            align-items: center;
        }


        /* STATUS */

        .status {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }


        .status.active,
        .status.approved {
            background: #dcfce7;
            color: #166534;
        }


        .status.pending {
            background: #fef3c7;
            color: #92400e;
        }


        .status.flagged,
        .status.banned {
            background: #fee2e2;
            color: #991b1b;
        }


        .status.inactive {
            background: #e5e7eb;
            color: #374151;
        }


        .action-buttons {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }


        .small-button {
            font-size: 12px;
            padding: 7px 10px;
        }


        .comment-text {
            max-width: 300px;
            word-wrap: break-word;
        }


        @media (max-width: 1000px) {

            .stats {
                grid-template-columns:
                    repeat(2, 1fr);
            }


            .form-grid {
                grid-template-columns:
                    repeat(2, 1fr);
            }


            .edit-form {
                grid-template-columns:
                    1fr 1fr;
            }

        }


        @media (max-width: 650px) {

            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
            }


            .stats {
                grid-template-columns: 1fr;
            }


            .form-grid {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>


<body>

<main class="dashboard">


    <!-- HEADER -->

    <header class="dashboard-header">

        <div>

            <p class="eyebrow">
                THE DAILY NEWS
            </p>

            <h1>
                Admin Dashboard
            </h1>

            <p>
                Welcome,
                <?= htmlspecialchars($_SESSION["name"]) ?>
            </p>

        </div>


        <div class="header-buttons">

            <a
                class="button-link"
                href="dashboard.php"
            >
                Dashboard
            </a>

            <a
                class="button-link"
                href="logout.php"
            >
                Logout
            </a>

        </div>

    </header>


    <!-- MESSAGES -->

    <?php if ($message): ?>

        <div class="notice success">
            <?= htmlspecialchars($message) ?>
        </div>

    <?php endif; ?>


    <?php if ($error): ?>

        <div class="notice error">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>


    <!-- STATISTICS -->

    <section class="stats">

        <div class="stat-card">

            <h3>
                Advertisements
            </h3>

            <div class="stat-number">
                <?= $ad_count ?>
            </div>

        </div>


        <div class="stat-card">

            <h3>
                Comments
            </h3>

            <div class="stat-number">
                <?= $comment_count ?>
            </div>

        </div>


        <div class="stat-card">

            <h3>
                Pending Comments
            </h3>

            <div class="stat-number">
                <?= $pending_comments ?>
            </div>

        </div>


        <div class="stat-card">

            <h3>
                Reporters
            </h3>

            <div class="stat-number">
                <?= $reporter_count ?>
            </div>

        </div>


        <div class="stat-card">

            <h3>
                Banned Reporters
            </h3>

            <div class="stat-number">
                <?= $banned_count ?>
            </div>

        </div>

    </section>


    <!-- =========================================================
         ADVERTISEMENT MANAGEMENT
    ========================================================== -->

    <section class="section">

        <h2>
            Advertisement Management
        </h2>


        <!-- ADD ADVERTISEMENT -->

        <h3>
            Add Advertisement
        </h3>


        <form
            method="POST"
            class="form-grid"
        >

            <div class="form-group">

                <label>
                    Brand
                </label>

                <input
                    type="text"
                    name="brand"
                    placeholder="Brand name"
                    required
                >

            </div>





            <div class="form-group">

                <label>
                    Status
                </label>

                <select
                    name="status"
                    required
                >

                    <option value="Active">
                        Active
                    </option>

                    <option value="Pending">
                        Pending
                    </option>

                    <option value="Inactive">
                        Inactive
                    </option>

                </select>

            </div>


            <div class="form-group">

                <label>
                    Article
                </label>

                <select
                    name="article_id"
                    required
                >

                    <option value="">
                        -- Select Article --
                    </option>


                    <?php

                    /*
                    | Re-query because the result may have
                    | been consumed later.
                    */

                    $article_options =
                        $conn->query("
                            SELECT
                                Article_ID,
                                Title
                            FROM ARTICLE
                            WHERE Status = 'Published'
                            ORDER BY
                                Published_At DESC,
                                Title ASC
                        ");

                    while (
                        $article =
                        $article_options->fetch_assoc()
                    ):

                    ?>

                        <option
                            value="<?= (int)$article["Article_ID"] ?>"
                        >

                            <?= htmlspecialchars(
                                $article["Title"]
                            ) ?>

                        </option>

                    <?php endwhile; ?>

                </select>

            </div>


            <div>

                <button
                    type="submit"
                    name="add_advertisement"
                    class="add-btn"
                >
                    Add Advertisement
                </button>

            </div>

        </form>


        <br>


        <!-- EXISTING ADVERTISEMENTS -->

        <h3>
            Existing Advertisements
        </h3>


        <?php if (
            $advertisements &&
            $advertisements->num_rows > 0
        ): ?>


            <div class="table-wrap">

                <table>

                    <thead>

                        <tr>

                            <th>
                                ID
                            </th>

                            <th>
                                Brand
                            </th>


                            <th>
                                Article
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php while (
                        $ad =
                        $advertisements->fetch_assoc()
                    ): ?>


                        <tr>

                            <td>
                                <?= (int)$ad["Advertisement_ID"] ?>
                            </td>


                            <td>

                                <form
                                    method="POST"
                                    class="edit-form"
                                >

                                    <input
                                        type="hidden"
                                        name="advertisement_id"
                                        value="<?= (int)$ad["Advertisement_ID"] ?>"
                                    >


                                    <input
                                        type="text"
                                        name="brand"
                                        value="<?= htmlspecialchars($ad["Brand"]) ?>"
                                        required
                                    >


                                   


                                    <select
                                        name="article_id"
                                        required
                                    >

                                        <?php

                                        $edit_articles =
                                            $conn->query("
                                                SELECT
                                                    Article_ID,
                                                    Title
                                                FROM ARTICLE
                                                WHERE Status = 'Published'
                                                ORDER BY Title ASC
                                            ");

                                        while (
                                            $edit_article =
                                            $edit_articles->fetch_assoc()
                                        ):

                                        ?>

                                            <option
                                                value="<?= (int)$edit_article["Article_ID"] ?>"
                                                <?= (
                                                    (int)$edit_article["Article_ID"]
                                                    ===
                                                    (int)$ad["Article_ID"]
                                                )
                                                    ? "selected"
                                                    : ""
                                                ?>
                                            >

                                                <?= htmlspecialchars(
                                                    $edit_article["Title"]
                                                ) ?>

                                            </option>

                                        <?php endwhile; ?>

                                    </select>


                                    <select
                                        name="status"
                                        required
                                    >

                                        <option
                                            value="Active"
                                            <?= $ad["Status"] === "Active"
                                                ? "selected"
                                                : ""
                                            ?>
                                        >
                                            Active
                                        </option>


                                        <option
                                            value="Pending"
                                            <?= $ad["Status"] === "Pending"
                                                ? "selected"
                                                : ""
                                            ?>
                                        >
                                            Pending
                                        </option>


                                        <option
                                            value="Inactive"
                                            <?= $ad["Status"] === "Inactive"
                                                ? "selected"
                                                : ""
                                            ?>
                                        >
                                            Inactive
                                        </option>

                                    </select>


                                    <button
                                        type="submit"
                                        name="edit_advertisement"
                                        class="save-btn"
                                    >
                                        Save
                                    </button>

                                </form>

                            </td>


                            


                            <td>

                                <?= htmlspecialchars(
                                    $ad["Article_Title"]
                                    ?? "Not linked"
                                ) ?>

                            </td>


                            <td>

                                <span
                                    class="status <?= strtolower(
                                        $ad["Status"]
                                    ) ?>"
                                >

                                    <?= htmlspecialchars(
                                        $ad["Status"]
                                    ) ?>

                                </span>

                            </td>


                            <td>

                                <form
                                    method="POST"
                                    onsubmit="
                                        return confirm(
                                            'Are you sure you want to delete this advertisement?'
                                        );
                                    "
                                >

                                    <input
                                        type="hidden"
                                        name="advertisement_id"
                                        value="<?= (int)$ad["Advertisement_ID"] ?>"
                                    >

                                    <button
                                        type="submit"
                                        name="delete_advertisement"
                                        class="delete-btn small-button"
                                    >
                                        Delete
                                    </button>

                                </form>

                            </td>

                        </tr>


                    <?php endwhile; ?>


                    </tbody>

                </table>

            </div>


        <?php else: ?>

            <p>
                No advertisements found.
            </p>

        <?php endif; ?>

    </section>


    <!-- =========================================================
         COMMENT MANAGEMENT
    ========================================================== -->

    <section class="section">

        <h2>
            Comment Management
        </h2>


        <p>
            Approve comments before they become publicly visible.
            Flag inappropriate comments or permanently delete them.
        </p>


        <?php if (
            $comments &&
            $comments->num_rows > 0
        ): ?>


            <div class="table-wrap">

                <table>

                    <thead>

                        <tr>

                            <th>
                                ID
                            </th>

                            <th>
                                Reader
                            </th>

                            <th>
                                Article
                            </th>

                            <th>
                                Comment
                            </th>

                            <th>
                                Date
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php while (
                        $comment =
                        $comments->fetch_assoc()
                    ): ?>


                        <tr>

                            <td>
                                <?= (int)$comment["Comment_ID"] ?>
                            </td>


                            <td>
                                <?= htmlspecialchars(
                                    $comment["Reader_Name"]
                                ) ?>
                            </td>


                            <td>
                                <?= htmlspecialchars(
                                    $comment["Article_Title"]
                                ) ?>
                            </td>


                            <td class="comment-text">

                                <?= htmlspecialchars(
                                    $comment["Comment_Text"]
                                ) ?>

                            </td>


                            <td>
                                <?= htmlspecialchars(
                                    $comment["Time_Stamp"]
                                ) ?>
                            </td>


                            <td>

                                <span
                                    class="status <?= strtolower(
                                        $comment["Status"]
                                    ) ?>"
                                >

                                    <?= htmlspecialchars(
                                        $comment["Status"]
                                    ) ?>

                                </span>

                            </td>


                            <td>

                                <div class="action-buttons">


                                    <!-- APPROVE -->

                                    <?php if (
                                        $comment["Status"]
                                        !==
                                        "Approved"
                                    ): ?>

                                        <form
                                            method="POST"
                                            class="inline-form"
                                        >

                                            <input
                                                type="hidden"
                                                name="comment_id"
                                                value="<?= (int)$comment["Comment_ID"] ?>"
                                            >

                                            <button
                                                type="submit"
                                                name="approve_comment"
                                                class="approve-btn small-button"
                                            >
                                                Approve
                                            </button>

                                        </form>

                                    <?php endif; ?>


                                    <!-- FLAG -->

                                    <?php if (
                                        $comment["Status"]
                                        !==
                                        "Flagged"
                                    ): ?>

                                        <form
                                            method="POST"
                                            class="inline-form"
                                        >

                                            <input
                                                type="hidden"
                                                name="comment_id"
                                                value="<?= (int)$comment["Comment_ID"] ?>"
                                            >

                                            <button
                                                type="submit"
                                                name="flag_comment"
                                                class="flag-btn small-button"
                                            >
                                                Flag
                                            </button>

                                        </form>

                                    <?php endif; ?>


                                    <!-- DELETE -->

                                    <form
                                        method="POST"
                                        class="inline-form"
                                        onsubmit="
                                            return confirm(
                                                'Are you sure you want to permanently delete this comment?'
                                            );
                                        "
                                    >

                                        <input
                                            type="hidden"
                                            name="comment_id"
                                            value="<?= (int)$comment["Comment_ID"] ?>"
                                        >

                                        <button
                                            type="submit"
                                            name="delete_comment"
                                            class="delete-btn small-button"
                                        >
                                            Delete
                                        </button>

                                    </form>


                                </div>

                            </td>

                        </tr>


                    <?php endwhile; ?>


                    </tbody>

                </table>

            </div>


        <?php else: ?>

            <p>
                No comments found.
            </p>

        <?php endif; ?>

    </section>


    <!-- =========================================================
         REPORTER MANAGEMENT
    ========================================================== -->

    <section class="section">

        <h2>
            Reporter Management
        </h2>


        <p>
            Admins can ban reporters from submitting or managing
            articles. Banning does not delete the reporter.
        </p>


        <?php if (
            $reporters &&
            $reporters->num_rows > 0
        ): ?>


            <div class="table-wrap">

                <table>

                    <thead>

                        <tr>

                            <th>
                                Staff ID
                            </th>

                            <th>
                                Name
                            </th>

                            <th>
                                Email
                            </th>

                            <th>
                                Specialization
                            </th>

                            <th>
                                Joining Date
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
                        $reporter =
                        $reporters->fetch_assoc()
                    ): ?>


                        <tr>

                            <td>
                                <?= (int)$reporter["Staff_ID"] ?>
                            </td>


                            <td>
                                <?= htmlspecialchars(
                                    $reporter["Name"]
                                ) ?>
                            </td>


                            <td>
                                <?= htmlspecialchars(
                                    $reporter["Email"]
                                ) ?>
                            </td>


                            <td>
                                <?= htmlspecialchars(
                                    $reporter["Specialization"]
                                ) ?>
                            </td>


                            <td>
                                <?= htmlspecialchars(
                                    $reporter["Joining_Date"]
                                ) ?>
                            </td>


                            <td>

                                <span
                                    class="status <?= strtolower(
                                        $reporter["Status"]
                                    ) ?>"
                                >

                                    <?= htmlspecialchars(
                                        $reporter["Status"]
                                    ) ?>

                                </span>

                            </td>


                            <td>


                                <?php if (
                                    strtolower(
                                        $reporter["Status"]
                                    )
                                    ===
                                    "banned"
                                ): ?>


                                    <!-- UNBAN -->

                                    <form
                                        method="POST"
                                        class="inline-form"
                                    >

                                        <input
                                            type="hidden"
                                            name="reporter_id"
                                            value="<?= (int)$reporter["Staff_ID"] ?>"
                                        >

                                        <button
                                            type="submit"
                                            name="unban_reporter"
                                            class="unban-btn small-button"
                                        >
                                            Unban
                                        </button>

                                    </form>


                                <?php else: ?>


                                    <!-- BAN -->

                                    <form
                                        method="POST"
                                        class="inline-form"
                                        onsubmit="
                                            return confirm(
                                                'Are you sure you want to ban this reporter?'
                                            );
                                        "
                                    >

                                        <input
                                            type="hidden"
                                            name="reporter_id"
                                            value="<?= (int)$reporter["Staff_ID"] ?>"
                                        >

                                        <button
                                            type="submit"
                                            name="ban_reporter"
                                            class="ban-btn small-button"
                                        >
                                            Ban Reporter
                                        </button>

                                    </form>


                                <?php endif; ?>


                            </td>

                        </tr>


                    <?php endwhile; ?>


                    </tbody>

                </table>

            </div>


        <?php else: ?>

            <p>
                No reporters found.
            </p>

        <?php endif; ?>

    </section>


</main>

</body>

</html>

