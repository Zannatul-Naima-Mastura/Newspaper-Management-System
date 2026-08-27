<?php
session_start();
require_once "dbConnect.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$categories = $conn->query("SELECT Category_ID, Category_Name FROM CATEGORY ORDER BY Category_Name");
$articles = $conn->query("SELECT a.Title, a.Content, c.Category_Name FROM ARTICLE a JOIN CATEGORY c ON c.Category_ID = a.Category_ID WHERE a.Status = 'Published' ORDER BY c.Category_Name, a.Published_At DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Article Categories</title><link rel="stylesheet" href="style.css"></head>
<body class="dashboard-body">
<main class="dashboard">
    <header class="dashboard-header"><div><p class="eyebrow">THE DAILY NEWS</p><h1>Article categories</h1></div><a class="button-link" href="dashboard.php">Dashboard</a></header>
    <section><div class="section-heading"><h2>Categories</h2></div><div class="table-wrap"><table><tr><th>ID</th><th>Category</th></tr><?php while ($category = $categories->fetch_assoc()): ?><tr><td><?= (int) $category["Category_ID"] ?></td><td><?= htmlspecialchars($category["Category_Name"]) ?></td></tr><?php endwhile; ?></table></div></section>
    <section><div class="section-heading"><h2>Published articles by category</h2></div><?php while ($article = $articles->fetch_assoc()): ?><article class="article"><span class="category"><?= htmlspecialchars($article["Category_Name"]) ?></span><h3><?= htmlspecialchars($article["Title"]) ?></h3><p><?= nl2br(htmlspecialchars($article["Content"])) ?></p></article><?php endwhile; ?></section>
</main>
</body>
</html>
