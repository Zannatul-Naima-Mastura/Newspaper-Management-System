<?php
session_start();
require_once "dbConnect.php";

if (!isset($_SESSION["user_id"]) || !in_array($_SESSION["role"], ["reporter", "editor", "admin"], true)) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION["role"];
$message = "";
$statuses = ["Draft", "Reviewed", "Published", "Rejected"];
if ($_SERVER["REQUEST_METHOD"] === "POST" && in_array($role, ["editor", "admin"], true)) {
    $articleId = filter_input(INPUT_POST, "article_id", FILTER_VALIDATE_INT);
    $status = $_POST["status"] ?? "";
    if ($articleId && in_array($status, $statuses, true)) {
        $sql = "UPDATE ARTICLE SET Status = ?, Reviewed_At = IF(? IN ('Reviewed', 'Published'), NOW(), Reviewed_At), Published_At = IF(? = 'Published', NOW(), Published_At), Updated_At = NOW()";
        if ($role === "editor") {
            $sql .= ", Editor_ID = ?";
        }
        $sql .= " WHERE Article_ID = ?";
        $stmt = $conn->prepare($sql);
        if ($role === "editor") {
            $stmt->bind_param("sssii", $status, $status, $status, $_SESSION["user_id"], $articleId);
        } else {
            $stmt->bind_param("sssi", $status, $status, $status, $articleId);
        }
        $stmt->execute();
        $message = "Article status updated.";
    }
}

$sql = "SELECT a.Article_ID, a.Title, a.Status, c.Category_Name FROM ARTICLE a JOIN CATEGORY c ON c.Category_ID = a.Category_ID";
if ($role === "reporter") {
    $sql .= " WHERE a.Reporter_ID = " . (int) $_SESSION["user_id"];
}
$sql .= " ORDER BY a.Updated_At DESC, a.Created_At DESC";
$articles = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Article Workflow</title><link rel="stylesheet" href="style.css"></head>
<body class="dashboard-body">
<main class="dashboard">
    <header class="dashboard-header"><div><p class="eyebrow">THE DAILY NEWS</p><h1>Article workflow</h1></div><a class="button-link" href="dashboard.php">Dashboard</a></header>
    <?php if ($message): ?><p class="notice success"><?= htmlspecialchars($message) ?></p><?php endif; ?>
    <section><div class="table-wrap"><table><tr><th>Article</th><th>Category</th><th>Status</th><th>Change</th></tr><?php while ($article = $articles->fetch_assoc()): ?><tr><td><?= htmlspecialchars($article["Title"]) ?></td><td><?= htmlspecialchars($article["Category_Name"]) ?></td><td><span class="status"><?= htmlspecialchars($article["Status"]) ?></span></td><td><?php if (in_array($role, ["editor", "admin"], true)): ?><form method="post" class="inline-form"><input type="hidden" name="article_id" value="<?= (int) $article["Article_ID"] ?>"><select name="status"><?php foreach ($statuses as $status): ?><option <?= $status === $article["Status"] ? "selected" : "" ?>><?= $status ?></option><?php endforeach; ?></select><button type="submit">Update</button></form><?php else: ?>Awaiting editorial review<?php endif; ?></td></tr><?php endwhile; ?></table></div></section>
</main>
</body>
</html>
