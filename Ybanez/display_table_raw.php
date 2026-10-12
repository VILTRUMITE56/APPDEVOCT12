<?php

require_once 'dbconfig.php';

// Escape HTML output (XSS prevention)
function h($string) {
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

try {
    $pdo = getPDO();
    
    $sql = "SELECT item_id, item_name, brand_name, category, price_aud, stock_quantity, created_at
            FROM grocery_items 
            ORDER BY item_id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $results = $stmt->fetchAll();
    
} catch (PDOException $e) {
    // Seed $results so the table below still renders (empty) on failure
    $results = [];
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Grocery Items - Raw Table</title>
</head>
<body>
    <?php require_once 'navbar.php'; ?>
    <h1>Australian Grocery Items</h1>
    
    <?php if (isset($error)): ?>
        <p style="color:red;">Error: <?= h($error) ?></p>
    <?php endif; ?>
    
    <p>Total Records: <?= count($results) ?></p>
    
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Item Name</th>
                <th>Brand</th>
                <th>Category</th>
                <th>Price (AUD)</th>
                <th>Stock</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($results as $row): ?>
            <tr>
                <td><?= h($row['item_id']) ?></td>
                <td><?= h($row['item_name']) ?></td>
                <td><?= h($row['brand_name']) ?></td>
                <td><?= h($row['category']) ?></td>
                <td>$<?= number_format($row['price_aud'], 2) ?></td>
                <td><?= h($row['stock_quantity']) ?></td>
                <td><?= h($row['created_at']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>