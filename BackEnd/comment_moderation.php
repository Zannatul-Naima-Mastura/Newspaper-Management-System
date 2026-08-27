<?php
session_start();
require_once "dbConnect.php";

if (!isset($_SESSION["user_id"]) || !in_array($_SESSION["role"], ["editor", "admin"], true)) {
    header("Location: login.php");
    exit();
}

$backUrl = $_SESSION["role"] === "editor" ? "editor_dashboard.php" : "dashboard.php";
$statuses = ["Pending", "Approved", "Flagged"];
$message = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $commentId = filter_input(INPUT_POST, "comment_id", FILTER_VALIDATE_INT);
    $status = $_POST["status"] ?? "";
    if ($commentId && in_array($status, $statuses, true)) {
        $stmt = $conn->prepare("UPDATE `COMMENT` SET Status = ? WHERE Comment_ID = ?");
        $stmt->bind_param("si", $status, $commentId);
        $stmt->execute();
        $message = "Comment moderation status updated.";
    }
}

$comments = $conn->query("SELECT cm.Comment_ID, cm.Comment_Text, cm.Status, a.Title, r.Name FROM `COMMENT` cm JOIN ARTICLE a ON a.Article_ID = cm.Article_ID JOIN REGISTERED_READER r ON r.Reader_ID = cm.Reader_ID ORDER BY cm.Time_Stamp DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Comment Moderation</title><link rel="stylesheet" href="style.css"></head>
<body class="dashboard-body">
<main class="dashboard">
    <header class="dashboard-header"><div><p class="eyebrow">THE DAILY NEWS</p><h1>Comment moderation</h1></div><div><a class="button-link" href="<?= $backUrl ?>">Back</a> <a class="button-link" href="dashboard.php">Dashboard</a></div></header>
    <?php if ($message): ?><p class="notice success"><?= htmlspecialchars($message) ?></p><?php endif; ?>
    <section><div class="table-wrap"><table><tr><th>Comment</th><th>Article</th><th>Reader</th><th>Status</th><th>Change</th></tr><?php while ($comment = $comments->fetch_assoc()): ?><tr><td><?= htmlspecialchars($comment["Comment_Text"]) ?></td><td><?= htmlspecialchars($comment["Title"]) ?></td><td><?= htmlspecialchars($comment["Name"]) ?></td><td><span class="status"><?= htmlspecialchars($comment["Status"]) ?></span></td><td><form method="post" class="inline-form"><input type="hidden" name="comment_id" value="<?= (int) $comment["Comment_ID"] ?>"><select name="status"><?php foreach ($statuses as $status): ?><option <?= $status === $comment["Status"] ? "selected" : "" ?>><?= $status ?></option><?php endforeach; ?></select><button type="submit">Update</button></form></td></tr><?php endwhile; ?></table></div></section>
</main>
</body>
</html>
