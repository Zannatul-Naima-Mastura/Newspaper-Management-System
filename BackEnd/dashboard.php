<?php
session_start();
require_once "dbConnect.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION["role"];
$message = "";
$error = "";
$articleStatuses = ["Draft", "Reviewed", "Published", "Rejected"];
$commentStatuses = ["Pending", "Approved", "Flagged"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "comment" && $role === "reader") {
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
    } elseif ($action === "article_status" && in_array($role, ["editor", "admin"], true)) {
        $articleId = filter_input(INPUT_POST, "article_id", FILTER_VALIDATE_INT);
        $status = $_POST["status"] ?? "";
        if ($articleId && in_array($status, $articleStatuses, true)) {
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
    } elseif ($action === "comment_status" && in_array($role, ["editor", "admin"], true)) {
        $commentId = filter_input(INPUT_POST, "comment_id", FILTER_VALIDATE_INT);
        $status = $_POST["status"] ?? "";
        if ($commentId && in_array($status, $commentStatuses, true)) {
            $stmt = $conn->prepare("UPDATE `COMMENT` SET Status = ? WHERE Comment_ID = ?");
            $stmt->bind_param("si", $status, $commentId);
            $stmt->execute();
            $message = "Comment moderation status updated.";
        }
    } elseif ($action === "create_article" && $role === "reporter") {
        $title = trim($_POST["title"] ?? "");
        $content = trim($_POST["content"] ?? "");
        $categoryId = filter_input(INPUT_POST, "category_id", FILTER_VALIDATE_INT);
        if ($title !== "" && $content !== "" && $categoryId) {
            $nextId = $conn->query("SELECT COALESCE(MAX(Article_ID), 1000) + 1 AS next_id FROM ARTICLE")->fetch_assoc()["next_id"];
            $stmt = $conn->prepare("INSERT INTO ARTICLE (Article_ID, Title, Content, Created_At, Status, Reporter_ID, Category_ID) VALUES (?, ?, ?, NOW(), 'Draft', ?, ?)");
            $stmt->bind_param("issii", $nextId, $title, $content, $_SESSION["user_id"], $categoryId);
            $stmt->execute();
            $message = "Draft article created.";
        } else {
            $error = "Title, content, and category are required.";
        }
    }
}

$categories = $conn->query("SELECT Category_ID, Category_Name FROM CATEGORY ORDER BY Category_Name");
$published = $conn->query("SELECT a.Article_ID, a.Title, a.Content, a.Created_At, c.Category_Name FROM ARTICLE a JOIN CATEGORY c ON c.Category_ID = a.Category_ID WHERE a.Status = 'Published' ORDER BY a.Published_At DESC, a.Created_At DESC");
$articles = null;
$comments = null;
if (in_array($role, ["editor", "admin", "reporter"], true)) {
    $articles = $conn->query("SELECT a.Article_ID, a.Title, a.Status, c.Category_Name FROM ARTICLE a JOIN CATEGORY c ON c.Category_ID = a.Category_ID ORDER BY a.Updated_At DESC, a.Created_At DESC");
}
if (in_array($role, ["editor", "admin"], true)) {
    $comments = $conn->query("SELECT cm.Comment_ID, cm.Comment_Text, cm.Status, a.Title, r.Name FROM `COMMENT` cm JOIN ARTICLE a ON a.Article_ID = cm.Article_ID JOIN REGISTERED_READER r ON r.Reader_ID = cm.Reader_ID ORDER BY cm.Time_Stamp DESC");
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Newsroom Dashboard</title><link rel="stylesheet" href="style.css"></head>
<body class="dashboard-body">
<main class="dashboard">
    <header class="dashboard-header"><div><p class="eyebrow">THE DAILY NEWS</p><h1>Newsroom dashboard</h1><p>Signed in as <?= htmlspecialchars($_SESSION["name"]) ?> (<?= htmlspecialchars($role) ?>)</p></div><a class="button-link" href="logout.php">Logout</a></header>
    <?php if ($message): ?><p class="notice success"><?= htmlspecialchars($message) ?></p><?php endif; ?>
    <?php if ($error): ?><p class="notice error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

    <section><div class="section-heading"><h2>Published articles</h2><span><?= $published->num_rows ?> live</span></div>
        <?php while ($article = $published->fetch_assoc()): ?>
            <article class="article"><span class="category"><?= htmlspecialchars($article["Category_Name"]) ?></span><h3><?= htmlspecialchars($article["Title"]) ?></h3><p><?= nl2br(htmlspecialchars($article["Content"])) ?></p>
                <?php $articleId = (int) $article["Article_ID"]; $visibleComments = $conn->query("SELECT cm.Comment_Text, r.Name FROM `COMMENT` cm JOIN REGISTERED_READER r ON r.Reader_ID = cm.Reader_ID WHERE cm.Article_ID = $articleId AND cm.Status = 'Approved' ORDER BY cm.Time_Stamp"); ?>
                <div class="comments"><h4>Approved comments</h4><?php while ($comment = $visibleComments->fetch_assoc()): ?><p><strong><?= htmlspecialchars($comment["Name"]) ?>:</strong> <?= htmlspecialchars($comment["Comment_Text"]) ?></p><?php endwhile; ?></div>
                <?php if ($role === "reader"): ?><form method="post" class="inline-form"><input type="hidden" name="action" value="comment"><input type="hidden" name="article_id" value="<?= $articleId ?>"><input name="comment_text" placeholder="Add a comment" required><button type="submit">Submit</button></form><?php endif; ?>
            </article>
        <?php endwhile; ?>
    </section>

    <?php if ($role === "reporter"): ?><section><div class="section-heading"><h2>Submit a draft</h2></div><form method="post" class="editor-form"><input type="hidden" name="action" value="create_article"><label>Title<input name="title" required></label><label>Category<select name="category_id" required><?php while ($category = $categories->fetch_assoc()): ?><option value="<?= $category["Category_ID"] ?>"><?= htmlspecialchars($category["Category_Name"]) ?></option><?php endwhile; ?></select></label><label>Content<textarea name="content" rows="6" required></textarea></label><button type="submit">Create draft</button></form></section><?php endif; ?>

    <?php if ($articles): ?><section><div class="section-heading"><h2>Article workflow</h2></div><div class="table-wrap"><table><tr><th>Article</th><th>Category</th><th>Status</th><th>Change</th></tr><?php while ($article = $articles->fetch_assoc()): ?><tr><td><?= htmlspecialchars($article["Title"]) ?></td><td><?= htmlspecialchars($article["Category_Name"]) ?></td><td><span class="status"><?= htmlspecialchars($article["Status"]) ?></span></td><td><?php if (in_array($role, ["editor", "admin"], true)): ?><form method="post" class="inline-form"><input type="hidden" name="action" value="article_status"><input type="hidden" name="article_id" value="<?= $article["Article_ID"] ?>"><select name="status"><?php foreach ($articleStatuses as $status): ?><option <?= $status === $article["Status"] ? "selected" : "" ?>><?= $status ?></option><?php endforeach; ?></select><button type="submit">Update</button></form><?php else: ?>Awaiting editorial review<?php endif; ?></td></tr><?php endwhile; ?></table></div></section><?php endif; ?>

    <?php if ($comments): ?><section><div class="section-heading"><h2>Comment moderation</h2></div><div class="table-wrap"><table><tr><th>Comment</th><th>Article</th><th>Reader</th><th>Status</th><th>Change</th></tr><?php while ($comment = $comments->fetch_assoc()): ?><tr><td><?= htmlspecialchars($comment["Comment_Text"]) ?></td><td><?= htmlspecialchars($comment["Title"]) ?></td><td><?= htmlspecialchars($comment["Name"]) ?></td><td><span class="status"><?= htmlspecialchars($comment["Status"]) ?></span></td><td><form method="post" class="inline-form"><input type="hidden" name="action" value="comment_status"><input type="hidden" name="comment_id" value="<?= $comment["Comment_ID"] ?>"><select name="status"><?php foreach ($commentStatuses as $status): ?><option <?= $status === $comment["Status"] ? "selected" : "" ?>><?= $status ?></option><?php endforeach; ?></select><button type="submit">Update</button></form></td></tr><?php endwhile; ?></table></div></section><?php endif; ?>
</main>
</body>
</html>
