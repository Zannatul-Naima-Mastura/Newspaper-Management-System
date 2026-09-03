<?php

session_start();

require_once "dbConnect.php";

/*
|--------------------------------------------------------------------------
| SECURITY CHECK
|--------------------------------------------------------------------------
| Only logged-in admins can access this page.
*/

if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["role"] !== "admin"
) {
    header("Location: login.php");
    exit();
}

$admin_name = $_SESSION["name"];
$admin_id = $_SESSION["user_id"];

$message = "";
$error = "";


/*
|--------------------------------------------------------------------------
| ADD ADVERTISEMENT
|--------------------------------------------------------------------------
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["add_advertisement"])
) {

    $brand = trim($_POST["brand"] ?? "");
    $duration = $_POST["duration"] ?? "";
    $status = $_POST["status"] ?? "Active";

    if ($brand === "") {

        $error = "Brand name is required.";

    } elseif (!is_numeric($duration) || $duration <= 0) {

        $error = "Duration must be a positive number.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | Generate next Advertisement ID
        |--------------------------------------------------------------------------
        */

        $id_result = $conn->query(
            "SELECT COALESCE(MAX(Advertisement_ID), 0) + 1 AS next_id
             FROM ADVERTISEMENT"
        );

        $next_id = $id_result->fetch_assoc()["next_id"];


        /*
        |--------------------------------------------------------------------------
        | Insert Advertisement
        |--------------------------------------------------------------------------
        */

        $sql = "INSERT INTO ADVERTISEMENT
                (
                    Advertisement_ID,
                    Brand,
                    Duration,
                    Status,
                    Admin_ID
                )
                VALUES
                (?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {

            $error = "Database error: " . $conn->error;

        } else {

            $stmt->bind_param(
                "isisi",
                $next_id,
                $brand,
                $duration,
                $status,
                $admin_id
            );

            if ($stmt->execute()) {

                $message = "Advertisement added successfully.";

            } else {

                $error = "Unable to add advertisement: "
                    . $stmt->error;
            }

            $stmt->close();
        }
    }
}


/*
|--------------------------------------------------------------------------
| EDIT ADVERTISEMENT
|--------------------------------------------------------------------------
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["edit_advertisement"])
) {

    $advertisement_id =
        $_POST["advertisement_id"] ?? "";

    $brand =
        trim($_POST["brand"] ?? "");

    $duration =
        $_POST["duration"] ?? "";

    $status =
        $_POST["status"] ?? "Active";


    if (!is_numeric($advertisement_id)) {

        $error = "Invalid advertisement ID.";

    } elseif ($brand === "") {

        $error = "Brand name is required.";

    } elseif (!is_numeric($duration) || $duration <= 0) {

        $error = "Duration must be a positive number.";

    } else {

        $advertisement_id = (int)$advertisement_id;
        $duration = (int)$duration;


        $sql = "UPDATE ADVERTISEMENT
                SET Brand = ?,
                    Duration = ?,
                    Status = ?,
                    Admin_ID = ?
                WHERE Advertisement_ID = ?";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {

            $error = "Database error: " . $conn->error;

        } else {

            $stmt->bind_param(
                "sisii",
                $brand,
                $duration,
                $status,
                $admin_id,
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
    }
}


/*
|--------------------------------------------------------------------------
| DELETE ADVERTISEMENT
|--------------------------------------------------------------------------
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["delete_advertisement"])
) {

    $advertisement_id =
        $_POST["advertisement_id"] ?? "";

    if (!is_numeric($advertisement_id)) {

        $error = "Invalid advertisement ID.";

    } else {

        $advertisement_id = (int)$advertisement_id;

        $sql = "DELETE FROM ADVERTISEMENT
                WHERE Advertisement_ID = ?";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {

            $error = "Database error: " . $conn->error;

        } else {

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
}


/*
|--------------------------------------------------------------------------
| DELETE COMMENT
|--------------------------------------------------------------------------
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["delete_comment"])
) {

    $comment_id =
        $_POST["comment_id"] ?? "";

    if (!is_numeric($comment_id)) {

        $error = "Invalid comment ID.";

    } else {

        $comment_id = (int)$comment_id;

        $sql = "DELETE FROM `COMMENT`
                WHERE Comment_ID = ?";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {

            $error = "Database error: " . $conn->error;

        } else {

            $stmt->bind_param(
                "i",
                $comment_id
            );

            if ($stmt->execute()) {

                $message =
                    "Comment deleted successfully.";

            } else {

                $error =
                    "Unable to delete comment: "
                    . $stmt->error;
            }

            $stmt->close();
        }
    }
}


/*
|--------------------------------------------------------------------------
| BAN / UNBAN REPORTER
|--------------------------------------------------------------------------
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["reporter_action"])
) {

    $reporter_id =
        $_POST["reporter_id"] ?? "";

    $action =
        $_POST["reporter_action"] ?? "";

    if (!is_numeric($reporter_id)) {

        $error = "Invalid reporter ID.";

    } elseif (
        $action !== "ban" &&
        $action !== "unban"
    ) {

        $error = "Invalid reporter action.";

    } else {

        $reporter_id = (int)$reporter_id;

        $new_status =
            ($action === "ban")
            ? "Banned"
            : "Active";


        $sql = "UPDATE REPORTER
                SET Status = ?
                WHERE Staff_ID = ?";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {

            $error = "Database error: " . $conn->error;

        } else {

            $stmt->bind_param(
                "si",
                $new_status,
                $reporter_id
            );

            if ($stmt->execute()) {

                if ($action === "ban") {

                    $message =
                        "Reporter banned successfully.";

                } else {

                    $message =
                        "Reporter unbanned successfully.";
                }

            } else {

                $error =
                    "Unable to update reporter status: "
                    . $stmt->error;
            }

            $stmt->close();
        }
    }
}


/*
|--------------------------------------------------------------------------
| GET ADVERTISEMENTS
|--------------------------------------------------------------------------
*/

$advertisements = $conn->query(
    "SELECT
        a.Advertisement_ID,
        a.Brand,
        a.Duration,
        a.Status,
        a.Admin_ID,
        w.Name AS Admin_Name
     FROM ADVERTISEMENT a
     LEFT JOIN WEBSITE_ADMIN w
        ON a.Admin_ID = w.Admin_ID
     ORDER BY a.Advertisement_ID DESC"
);


/*
|--------------------------------------------------------------------------
| GET COMMENTS
|--------------------------------------------------------------------------
*/

$comments = $conn->query(
    "SELECT
        cm.Comment_ID,
        cm.Comment_Text,
        cm.Time_Stamp,
        cm.Status,
        r.Name AS Reader_Name,
        a.Title AS Article_Title
     FROM `COMMENT` cm
     LEFT JOIN REGISTERED_READER r
        ON cm.Reader_ID = r.Reader_ID
     LEFT JOIN ARTICLE a
        ON cm.Article_ID = a.Article_ID
     ORDER BY cm.Time_Stamp DESC"
);


/*
|--------------------------------------------------------------------------
| GET REPORTERS
|--------------------------------------------------------------------------
*/

$reporters = $conn->query(
    "SELECT
        Staff_ID,
        Name,
        Email,
        Status,
        Specialization,
        Joining_Date
     FROM REPORTER
     ORDER BY Staff_ID ASC"
);


/*
|--------------------------------------------------------------------------
| STATISTICS
|--------------------------------------------------------------------------
*/

$advertisement_count = 0;
$comment_count = 0;
$reporter_count = 0;
$banned_reporter_count = 0;

$count_result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM ADVERTISEMENT"
);

if ($count_result) {

    $advertisement_count =
        $count_result->fetch_assoc()["total"];
}


$count_result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM `COMMENT`"
);

if ($count_result) {

    $comment_count =
        $count_result->fetch_assoc()["total"];
}


$count_result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM REPORTER"
);

if ($count_result) {

    $reporter_count =
        $count_result->fetch_assoc()["total"];
}


$count_result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM REPORTER
     WHERE Status = 'Banned'"
);

if ($count_result) {

    $banned_reporter_count =
        $count_result->fetch_assoc()["total"];
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
        Admin Dashboard - The Daily News
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

        .container {
            max-width: 1250px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .welcome {
            margin-bottom: 30px;
        }

        .welcome h1 {
            font-family: Georgia, serif;
            margin-bottom: 7px;
        }

        .welcome p {
            color: #666;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
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

        .notice {
            padding: 14px 18px;
            margin-bottom: 25px;
            border-radius: 6px;
            font-weight: bold;
        }

        .success {
            background: #d4edda;
            color: #155724;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
        }

        .section {
            background: white;
            border-radius: 8px;

            box-shadow:
                0 2px 8px rgba(0,0,0,0.08);

            margin-bottom: 30px;

            overflow: hidden;
        }

        .section-header {
            padding: 20px 25px;
            border-bottom: 1px solid #ddd;
        }

        .section-header h2 {
            font-family: Georgia, serif;
        }

        .add-form {
            padding: 25px;
            background: #fafafa;
            border-bottom: 1px solid #ddd;
        }

        .form-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto;
            gap: 12px;
            align-items: end;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 7px;
            font-size: 13px;
        }

        input,
        select {
            width: 100%;
            padding: 10px;

            border: 1px solid #ccc;
            border-radius: 5px;

            font-size: 14px;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: #555;
        }

        button {
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-weight: bold;
        }

        .add-button {
            background: #111;
            color: white;
            padding: 10px 18px;
        }

        .add-button:hover {
            background: #333;
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
            padding: 14px;
            font-size: 13px;
            border-bottom: 1px solid #ddd;
        }

        td {
            padding: 14px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
            font-size: 14px;
        }

        tr:hover {
            background: #fafafa;
        }

        .inline-form {
            display: flex;
            gap: 6px;
            align-items: center;
        }

        .inline-form input,
        .inline-form select {
            width: auto;
        }

        .update-button {
            background: #e7f0ff;
            color: #1a56db;
            padding: 7px 10px;
        }

        .delete-button {
            background: #f8d7da;
            color: #721c24;
            padding: 7px 10px;
        }

        .delete-button:hover {
            background: #f1c1c5;
        }

        .ban-button {
            background: #f8d7da;
            color: #721c24;
            padding: 7px 10px;
        }

        .unban-button {
            background: #d4edda;
            color: #155724;
            padding: 7px 10px;
        }

        .status {
            display: inline-block;

            padding: 5px 10px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: bold;
        }

        .active {
            background: #d4edda;
            color: #155724;
        }

        .pending {
            background: #fff3cd;
            color: #856404;
        }

        .banned {
            background: #f8d7da;
            color: #721c24;
        }

        .comment-text {
            max-width: 300px;
        }

        .actions {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .small-text {
            color: #777;
            font-size: 12px;
        }

        @media (max-width: 900px) {

            .stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .form-row {
                grid-template-columns: 1fr 1fr;
            }

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

            .stats {
                grid-template-columns: 1fr;
            }

            .form-row {
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


    <div class="header-right">

        <div class="user-info">

            <div class="user-name">
                <?php
                echo htmlspecialchars($admin_name);
                ?>
            </div>

            <div class="user-role">
                Website Administrator
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


    <div class="welcome">

        <h1>
            Admin Dashboard
        </h1>

        <p>
            Manage advertisements, reader comments,
            and reporter accounts.
        </p>

    </div>


    <?php if ($message): ?>

        <div class="notice success">
            <?php
            echo htmlspecialchars($message);
            ?>
        </div>

    <?php endif; ?>


    <?php if ($error): ?>

        <div class="notice error">
            <?php
            echo htmlspecialchars($error);
            ?>
        </div>

    <?php endif; ?>


    <!-- =====================================================
         STATISTICS
    ====================================================== -->

    <section class="stats">

        <div class="stat-card">

            <div class="stat-number">
                <?php echo $advertisement_count; ?>
            </div>

            <div class="stat-label">
                Advertisements
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-number">
                <?php echo $comment_count; ?>
            </div>

            <div class="stat-label">
                Reader Comments
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-number">
                <?php echo $reporter_count; ?>
            </div>

            <div class="stat-label">
                Reporters
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-number">
                <?php echo $banned_reporter_count; ?>
            </div>

            <div class="stat-label">
                Banned Reporters
            </div>

        </div>

    </section>



    <!-- =====================================================
         ADVERTISEMENTS
    ====================================================== -->

    <section class="section">

        <div class="section-header">

            <h2>
                Advertisement Management
            </h2>

        </div>


        <!-- ADD ADVERTISEMENT -->

        <div class="add-form">

            <form method="POST">

                <div class="form-row">

                    <div class="form-group">

                        <label>
                            Brand
                        </label>

                        <input
                            type="text"
                            name="brand"
                            placeholder="Enter brand name"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Duration
                        </label>

                        <input
                            type="number"
                            name="duration"
                            min="1"
                            placeholder="Seconds"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Status
                        </label>

                        <select name="status">

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


                    <button
                        type="submit"
                        name="add_advertisement"
                        class="add-button"
                    >
                        + Add Advertisement
                    </button>

                </div>

            </form>

        </div>


        <!-- ADVERTISEMENT LIST -->

        <div class="table-container">

            <table>

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Brand</th>

                        <th>Duration</th>

                        <th>Status</th>

                        <th>Added By</th>

                        <th>Actions</th>

                    </tr>

                </thead>


                <tbody>

                <?php if ($advertisements && $advertisements->num_rows > 0): ?>

                    <?php while (
                        $ad = $advertisements->fetch_assoc()
                    ): ?>

                        <tr>

                            <td>
                                <?php
                                echo $ad["Advertisement_ID"];
                                ?>
                            </td>


                            <td>

                                <form
                                    method="POST"
                                    class="inline-form"
                                >

                                    <input
                                        type="hidden"
                                        name="advertisement_id"
                                        value="<?php
                                        echo $ad["Advertisement_ID"];
                                        ?>"
                                    >

                                    <input
                                        type="text"
                                        name="brand"
                                        value="<?php
                                        echo htmlspecialchars(
                                            $ad["Brand"]
                                        );
                                        ?>"
                                        required
                                    >

                            </td>


                            <td>

                                    <input
                                        type="number"
                                        name="duration"
                                        min="1"
                                        value="<?php
                                        echo $ad["Duration"];
                                        ?>"
                                        required
                                    >

                            </td>


                            <td>

                                    <select name="status">

                                        <option
                                            value="Active"
                                            <?php
                                            if (
                                                $ad["Status"]
                                                === "Active"
                                            ) {
                                                echo "selected";
                                            }
                                            ?>
                                        >
                                            Active
                                        </option>

                                        <option
                                            value="Pending"
                                            <?php
                                            if (
                                                $ad["Status"]
                                                === "Pending"
                                            ) {
                                                echo "selected";
                                            }
                                            ?>
                                        >
                                            Pending
                                        </option>

                                        <option
                                            value="Inactive"
                                            <?php
                                            if (
                                                $ad["Status"]
                                                === "Inactive"
                                            ) {
                                                echo "selected";
                                            }
                                            ?>
                                        >
                                            Inactive
                                        </option>

                                    </select>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $ad["Admin_Name"]
                                    ?? "Unknown"
                                );
                                ?>

                            </td>


                            <td>

                                    <div class="actions">

                                        <button
                                            type="submit"
                                            name="edit_advertisement"
                                            class="update-button"
                                        >
                                            Save
                                        </button>

                                </form>


                                <form
                                    method="POST"
                                    onsubmit="return confirm(
                                        'Delete this advertisement?'
                                    );"
                                >

                                    <input
                                        type="hidden"
                                        name="advertisement_id"
                                        value="<?php
                                        echo $ad["Advertisement_ID"];
                                        ?>"
                                    >

                                    <button
                                        type="submit"
                                        name="delete_advertisement"
                                        class="delete-button"
                                    >
                                        Delete
                                    </button>

                                </form>

                                    </div>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>

                        <td colspan="6">
                            No advertisements found.
                        </td>

                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </section>



    <!-- =====================================================
         COMMENTS
    ====================================================== -->

    <section class="section">

        <div class="section-header">

            <h2>
                Reader Comment Management
            </h2>

        </div>


        <div class="table-container">

            <table>

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Comment</th>

                        <th>Reader</th>

                        <th>Article</th>

                        <th>Status</th>

                        <th>Time</th>

                        <th>Action</th>

                    </tr>

                </thead>


                <tbody>

                <?php if ($comments && $comments->num_rows > 0): ?>

                    <?php while (
                        $comment = $comments->fetch_assoc()
                    ): ?>

                        <tr>

                            <td>
                                <?php
                                echo $comment["Comment_ID"];
                                ?>
                            </td>


                            <td class="comment-text">

                                <?php
                                echo htmlspecialchars(
                                    $comment["Comment_Text"]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $comment["Reader_Name"]
                                    ?? "Unknown"
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $comment["Article_Title"]
                                    ?? "Unknown"
                                );
                                ?>

                            </td>


                            <td>

                                <span class="status">

                                    <?php
                                    echo htmlspecialchars(
                                        $comment["Status"]
                                    );
                                    ?>

                                </span>

                            </td>


                            <td>

                                <span class="small-text">

                                    <?php
                                    echo date(
                                        "d M Y H:i",
                                        strtotime(
                                            $comment["Time_Stamp"]
                                        )
                                    );
                                    ?>

                                </span>

                            </td>


                            <td>

                                <form
                                    method="POST"
                                    onsubmit="return confirm(
                                        'Permanently delete this comment?'
                                    );"
                                >

                                    <input
                                        type="hidden"
                                        name="comment_id"
                                        value="<?php
                                        echo $comment["Comment_ID"];
                                        ?>"
                                    >

                                    <button
                                        type="submit"
                                        name="delete_comment"
                                        class="delete-button"
                                    >
                                        Delete
                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>

                        <td colspan="7">
                            No comments found.
                        </td>

                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </section>



    <!-- =====================================================
         REPORTERS
    ====================================================== -->

    <section class="section">

        <div class="section-header">

            <h2>
                Reporter Management
            </h2>

        </div>


        <div class="table-container">

            <table>

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Name</th>

                        <th>Email</th>

                        <th>Specialization</th>

                        <th>Joining Date</th>

                        <th>Status</th>

                        <th>Action</th>

                    </tr>

                </thead>


                <tbody>

                <?php if ($reporters && $reporters->num_rows > 0): ?>

                    <?php while (
                        $reporter = $reporters->fetch_assoc()
                    ): ?>

                        <tr>

                            <td>
                                <?php
                                echo $reporter["Staff_ID"];
                                ?>
                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $reporter["Name"]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $reporter["Email"]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $reporter["Specialization"]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo date(
                                    "d M Y",
                                    strtotime(
                                        $reporter["Joining_Date"]
                                    )
                                );
                                ?>

                            </td>


                            <td>

                                <span
                                    class="status
                                    <?php
                                    echo strtolower(
                                        $reporter["Status"]
                                    );
                                    ?>"
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $reporter["Status"]
                                    );
                                    ?>

                                </span>

                            </td>


                            <td>

                                <?php if (
                                    strtolower(
                                        $reporter["Status"]
                                    ) === "banned"
                                ): ?>

                                    <form
                                        method="POST"
                                        onsubmit="return confirm(
                                            'Unban this reporter?'
                                        );"
                                    >

                                        <input
                                            type="hidden"
                                            name="reporter_id"
                                            value="<?php
                                            echo $reporter["Staff_ID"];
                                            ?>"
                                        >

                                        <button
                                            type="submit"
                                            name="reporter_action"
                                            value="unban"
                                            class="unban-button"
                                        >
                                            Unban
                                        </button>

                                    </form>

                                <?php else: ?>

                                    <form
                                        method="POST"
                                        onsubmit="return confirm(
                                            'Ban this reporter?'
                                        );"
                                    >

                                        <input
                                            type="hidden"
                                            name="reporter_id"
                                            value="<?php
                                            echo $reporter["Staff_ID"];
                                            ?>"
                                        >

                                        <button
                                            type="submit"
                                            name="reporter_action"
                                            value="ban"
                                            class="ban-button"
                                        >
                                            Ban Reporter
                                        </button>

                                    </form>

                                <?php endif; ?>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>

                        <td colspan="7">
                            No reporters found.
                        </td>

                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </section>


</main>

</body>

</html>

