<?php
session_start();
require_once "dbConnect.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "reader") {
    header("Location: login.php");
    exit();
}

$message = "";
$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $articleId = filter_input(INPUT_POST, "article_id", FILTER_VALIDATE_INT);
    $commentText = trim($_POST["comment_text"] ?? "");
    $check = $conn->prepare("SELECT Article_ID FROM ARTICLE WHERE Article_ID = ? AND Status = 'Published'");
    $check->bind_param("i", $articleId);
    $check->execute();
    if ($articleId && $commentText !== "" && $check->get_result()->num_rows === 1) {
        $nextId = $conn->query("SELECT COALESCE(MAX(Comment_ID), 0) + 1 AS next_id FROM `COMMENT`")->fetch_assoc()["next_id"];
        $stmt = $conn->prepare("INSERT INTO `COMMENT` (Comment_ID, Reader_ID, Article_ID, Comment_Text, Time_Stamp, Status) VALUES (?, ?, ?, ?, NOW(), 'Pending')");
        $stmt->bind_param("iiis", $nextId, $_SESSION["user_id"], $articleId, $commentText);
        $stmt->execute();
        $message = "Comment submitted for moderation.";
    } else {
        $error = "Choose a published article and enter a comment.";
    }
}

$articles = $conn->query("SELECT a.Article_ID, a.Title, a.Content, c.Category_Name FROM ARTICLE a JOIN CATEGORY c ON c.Category_ID = a.Category_ID WHERE a.Status = 'Published' ORDER BY a.Published_At DESC, a.Created_At DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Comments</title><link rel="stylesheet" href="style.css"></head>
<body class="dashboard-body">
<main class="dashboard">
    <header class="dashboard-header"><div><p class="eyebrow">THE DAILY NEWS</p><h1>Published articles</h1><p>Signed in as <?= htmlspecialchars($_SESSION["name"]) ?></p></div><div><a class="button-link" href="reader_home.php">Back</a> <a class="button-link" href="dashboard.php">Dashboard</a></div></header>
    <?php if ($message): ?><p class="notice success"><?= htmlspecialchars($message) ?></p><?php endif; ?><?php if ($error): ?><p class="notice error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <?php while ($article = $articles->fetch_assoc()): ?><?php $articleId = (int) $article["Article_ID"]; $visibleComments = $conn->query("SELECT cm.Comment_Text, r.Name FROM `COMMENT` cm JOIN REGISTERED_READER r ON r.Reader_ID = cm.Reader_ID WHERE cm.Article_ID = $articleId AND cm.Status = 'Approved' ORDER BY cm.Time_Stamp"); ?><article class="article"><span class="category"><?= htmlspecialchars($article["Category_Name"]) ?></span><h2><?= htmlspecialchars($article["Title"]) ?></h2><p><?= nl2br(htmlspecialchars($article["Content"])) ?></p><div class="comments"><h3>Approved comments</h3><?php while ($comment = $visibleComments->fetch_assoc()): ?><p><strong><?= htmlspecialchars($comment["Name"]) ?>:</strong> <?= htmlspecialchars($comment["Comment_Text"]) ?></p><?php endwhile; ?></div><form method="post" class="inline-form"><input type="hidden" name="article_id" value="<?= $articleId ?>"><input name="comment_text" placeholder="Add a comment" required><button type="submit">Submit</button></form></article><?php endwhile; ?>
</main>
</body>
</html>
