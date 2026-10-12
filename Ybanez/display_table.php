<?php
require_once 'dbconfig.php';

// Escape HTML output (XSS prevention) - use for every value echoed to the page
function h($string) {
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

function formatPrice($price) {
    return '$' . number_format((float)$price, 2);
}

// Low stock threshold (below 50 units) drives the badge colour
function getStockStatus($quantity) {
    if ($quantity == 0) return '<span class="badge out-of-stock">Out of Stock</span>';
    if ($quantity < 50) return '<span class="badge low-stock">Low Stock</span>';
    return '<span class="badge in-stock">In Stock</span>';
}

try {
    $pdo = getPDO();
    
    $categoryFilter = isset($_GET['category']) ? $_GET['category'] : '';
    $minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
    $maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 999999;
    $searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
    $sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'item_id';
    $sortOrder = isset($_GET['order']) && $_GET['order'] === 'desc' ? 'DESC' : 'ASC';
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $offset = ($page - 1) * $limit;
    
    // Filters build the WHERE clause; values never get concatenated, only placeholders
    $whereConditions = [];
    $params = [];
    
    if ($categoryFilter) {
        $whereConditions[] = "category = ?";
        $params[] = $categoryFilter;
    }
    
    if ($minPrice > 0) {
        $whereConditions[] = "price_aud >= ?";
        $params[] = $minPrice;
    }
    
    if ($maxPrice < 999999) {
        $whereConditions[] = "price_aud <= ?";
        $params[] = $maxPrice;
    }
    
    if ($searchTerm) {
        $whereConditions[] = "(item_name LIKE ? OR brand_name LIKE ?)";
        $searchParam = "%$searchTerm%";
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    $whereClause = '';
    if (!empty($whereConditions)) {
        $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
    }
    
    // Sort column is user input, so validate it against a whitelist before it reaches the SQL
    $allowedSortColumns = ['item_id', 'item_name', 'brand_name', 'category', 'price_aud', 'stock_quantity', 'created_at'];
    if (!in_array($sortBy, $allowedSortColumns)) {
        $sortBy = 'item_id';
    }
    
    // Count first so the UI can build pagination links
    $countSql = "SELECT COUNT(*) FROM grocery_items $whereClause";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $totalRecords = $countStmt->fetchColumn();
    $totalPages = ceil($totalRecords / $limit);
    
    // Main query
    $sql = "SELECT item_id, item_name, brand_name, category, price_aud, stock_quantity, created_at
            FROM grocery_items 
            $whereClause
            ORDER BY $sortBy $sortOrder
            LIMIT ? OFFSET ?";
    
    $stmt = $pdo->prepare($sql);
    
    // LIMIT/OFFSET are bound last, so $params must keep the same order as the placeholders
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt->execute($params);
    $results = $stmt->fetchAll();
    
    $catStmt = $pdo->prepare("SELECT DISTINCT category FROM grocery_items ORDER BY category");
    $catStmt->execute();
    $categories = $catStmt->fetchAll(PDO::FETCH_COLUMN);
    
} catch (PDOException $e) {
    $error = $e->getMessage();
    $results = [];
    $categories = [];
    $totalRecords = 0;
    $totalPages = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Australian Grocery Items - PDO Table Display</title>
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1400px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; border-bottom: 3px solid #27ae60; padding-bottom: 10px; }
        h2 { color: #34495e; }
        
        .filter-form { background: #ecf0f1; padding: 20px; border-radius: 6px; margin-bottom: 20px; }
        .filter-row { display: flex; gap: 15px; flex-wrap: wrap; align-items: end; margin-bottom: 15px; }
        .filter-group { display: flex; flex-direction: column; gap: 5px; }
        .filter-group label { font-weight: 600; font-size: 14px; color: #2c3e50; }
        .filter-group input, .filter-group select { padding: 8px 12px; border: 1px solid #bdc3c7; border-radius: 4px; font-size: 14px; }
        .filter-group input[type="text"] { min-width: 200px; }
        .filter-group select { min-width: 150px; }
        .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 600; transition: background 0.3s; }
        .btn-primary { background: #27ae60; color: white; }
        .btn-primary:hover { background: #229954; }
        .btn-secondary { background: #95a5a6; color: white; }
        .btn-secondary:hover { background: #7f8c8d; }
        
        .table-wrapper { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ecf0f1; }
        th { background: #2c3e50; color: white; font-weight: 600; position: sticky; top: 0; }
        th a { color: white; text-decoration: none; display: flex; align-items: center; gap: 5px; }
        th a:hover { color: #2ecc71; }
        tr:hover { background: #f8f9fa; }
        tr:nth-child(even) { background: #fafafa; }
        
        td.id { text-align: center; font-family: monospace; }
        td.price { text-align: right; font-family: monospace; font-weight: 600; color: #27ae60; }
        td.stock { text-align: center; }
        td.category { text-transform: capitalize; }
        
        .badge { padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 600; }
        .badge.in-stock { background: #d5f5e3; color: #1e8a49; }
        .badge.low-stock { background: #fef9e7; color: #b7950b; }
        .badge.out-of-stock { background: #fadbd8; color: #c0392b; }
        
        .pagination { display: flex; justify-content: center; gap: 5px; margin-top: 20px; flex-wrap: wrap; }
        .pagination a, .pagination span { padding: 8px 12px; border: 1px solid #bdc3c7; border-radius: 4px; text-decoration: none; color: #2c3e50; }
        .pagination a:hover { background: #27ae60; color: white; border-color: #27ae60; }
        .pagination .current { background: #27ae60; color: white; border-color: #27ae60; }
        .pagination .disabled { color: #bdc3c7; pointer-events: none; }
        
        .stats { display: flex; gap: 20px; margin-bottom: 20px; flex-wrap: wrap; }
        .stat-card { background: #27ae60; color: white; padding: 15px 20px; border-radius: 6px; min-width: 150px; }
        .stat-card .label { font-size: 12px; opacity: 0.9; }
        .stat-card .value { font-size: 24px; font-weight: 700; }
        
        .error { background: #fadbd8; color: #c0392b; padding: 15px; border-radius: 6px; margin-bottom: 20px; }
        
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ecf0f1; color: #7f8c8d; font-size: 14px; }
    </style>
</head>
<body>
    <?php require_once 'navbar.php'; ?>
    <div class="container">
        <h1>🛒 Australian Grocery Items Database</h1>
        <p style="color: #7f8c8d;">PDO Methods Activity - Display Query Results in HTML Table</p>
        
        <?php if (isset($error)): ?>
            <div class="error">
                <strong>Database Error:</strong> <?= h($error) ?>
            </div>
        <?php endif; ?>
        
        <div class="stats">
            <div class="stat-card">
                <div class="label">Total Records</div>
                <div class="value"><?= number_format($totalRecords) ?></div>
            </div>
            <div class="stat-card">
                <div class="label">Showing</div>
                <div class="value"><?= count($results) ?> of <?= number_format($totalRecords) ?></div>
            </div>
            <div class="stat-card">
                <div class="label">Page</div>
                <div class="value"><?= $page ?> / <?= $totalPages ?></div>
            </div>
            <div class="stat-card">
                <div class="label">Categories</div>
                <div class="value"><?= count($categories) ?></div>
            </div>
        </div>
        
        <form method="GET" class="filter-form">
            <div class="filter-row">
                <div class="filter-group">
                    <label for="search">🔍 Search</label>
                    <input type="text" id="search" name="search" placeholder="Search item or brand..." value="<?= h($searchTerm) ?>">
                </div>
                <div class="filter-group">
                    <label for="category">📂 Category</label>
                    <select id="category" name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= h($cat) ?>" <?= $categoryFilter === $cat ? 'selected' : '' ?>>
                                <?= h($cat) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="min_price">💲 Min Price</label>
                    <input type="number" id="min_price" name="min_price" step="0.01" min="0" value="<?= $minPrice > 0 ? $minPrice : '' ?>" placeholder="0.00">
                </div>
                <div class="filter-group">
                    <label for="max_price">💲 Max Price</label>
                    <input type="number" id="max_price" name="max_price" step="0.01" min="0" value="<?= $maxPrice < 999999 ? $maxPrice : '' ?>" placeholder="999.99">
                </div>
                <div class="filter-group">
                    <label for="limit">📄 Per Page</label>
                    <select id="limit" name="limit">
                        <option value="10" <?= $limit === 10 ? 'selected' : '' ?>>10</option>
                        <option value="25" <?= $limit === 25 ? 'selected' : '' ?>>25</option>
                        <option value="50" <?= $limit === 50 ? 'selected' : '' ?>>50</option>
                        <option value="100" <?= $limit === 100 ? 'selected' : '' ?>>100</option>
                    </select>
                </div>
                <div class="filter-group">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="display_table.php" class="btn btn-secondary">Reset</a>
                </div>
            </div>
            
            <input type="hidden" name="sort" value="<?= h($sortBy) ?>">
            <input type="hidden" name="order" value="<?= h(strtolower($sortOrder)) ?>">
        </form>
        
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <?php /* Sorting links: clicking toggles ASC/DESC; array_filter drops sort/order/page so active filters and search survive */ ?>
                        <th class="id">
                            <a href="?sort=item_id&order=<?= $sortBy === 'item_id' && $sortOrder === 'ASC' ? 'desc' : 'asc' ?>&<?= http_build_query(array_filter($_GET, function($k) { return !in_array($k, ['sort', 'order', 'page']); }, ARRAY_FILTER_USE_KEY)) ?>">
                                ID <?= $sortBy === 'item_id' ? ($sortOrder === 'ASC' ? '↑' : '↓') : '' ?>
                            </a>
                        </th>
                        <th>
                            <a href="?sort=item_name&order=<?= $sortBy === 'item_name' && $sortOrder === 'ASC' ? 'desc' : 'asc' ?>&<?= http_build_query(array_filter($_GET, function($k) { return !in_array($k, ['sort', 'order', 'page']); }, ARRAY_FILTER_USE_KEY)) ?>">
                                Item Name <?= $sortBy === 'item_name' ? ($sortOrder === 'ASC' ? '↑' : '↓') : '' ?>
                            </a>
                        </th>
                        <th>
                            <a href="?sort=brand_name&order=<?= $sortBy === 'brand_name' && $sortOrder === 'ASC' ? 'desc' : 'asc' ?>&<?= http_build_query(array_filter($_GET, function($k) { return !in_array($k, ['sort', 'order', 'page']); }, ARRAY_FILTER_USE_KEY)) ?>">
                                Brand <?= $sortBy === 'brand_name' ? ($sortOrder === 'ASC' ? '↑' : '↓') : '' ?>
                            </a>
                        </th>
                        <th>
                            <a href="?sort=category&order=<?= $sortBy === 'category' && $sortOrder === 'ASC' ? 'desc' : 'asc' ?>&<?= http_build_query(array_filter($_GET, function($k) { return !in_array($k, ['sort', 'order', 'page']); }, ARRAY_FILTER_USE_KEY)) ?>">
                                Category <?= $sortBy === 'category' ? ($sortOrder === 'ASC' ? '↑' : '↓') : '' ?>
                            </a>
                        </th>
                        <th class="price">
                            <a href="?sort=price_aud&order=<?= $sortBy === 'price_aud' && $sortOrder === 'ASC' ? 'desc' : 'asc' ?>&<?= http_build_query(array_filter($_GET, function($k) { return !in_array($k, ['sort', 'order', 'page']); }, ARRAY_FILTER_USE_KEY)) ?>">
                                Price (AUD) <?= $sortBy === 'price_aud' ? ($sortOrder === 'ASC' ? '↑' : '↓') : '' ?>
                            </a>
                        </th>
                        <th class="stock">
                            <a href="?sort=stock_quantity&order=<?= $sortBy === 'stock_quantity' && $sortOrder === 'ASC' ? 'desc' : 'asc' ?>&<?= http_build_query(array_filter($_GET, function($k) { return !in_array($k, ['sort', 'order', 'page']); }, ARRAY_FILTER_USE_KEY)) ?>">
                                Stock <?= $sortBy === 'stock_quantity' ? ($sortOrder === 'ASC' ? '↑' : '↓') : '' ?>
                            </a>
                        </th>
                        <th>Status</th>
                        <th>Date Added</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($results)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px; color: #95a5a6;">
                                No records found matching your criteria.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($results as $row): ?>
                            <tr>
                                <td class="id"><?= h($row['item_id']) ?></td>
                                <td><strong><?= h($row['item_name']) ?></strong></td>
                                <td><?= h($row['brand_name']) ?></td>
                                <td class="category"><?= h($row['category']) ?></td>
                                <td class="price"><?= formatPrice($row['price_aud']) ?></td>
                                <td class="stock"><?= number_format($row['stock_quantity']) ?></td>
                                <td><?= getStockStatus($row['stock_quantity']) ?></td>
                                <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=1<?= http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)) ?>">&laquo; First</a>
                    <a href="?page=<?= $page - 1 ?><?= http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)) ?>">&lsaquo; Prev</a>
                <?php else: ?>
                    <span class="disabled">&laquo; First</span>
                    <span class="disabled">&lsaquo; Prev</span>
                <?php endif; ?>
                
                <?php
                // Sliding window of at most 5 page numbers, clamped to the valid range
                $startPage = max(1, $page - 2);
                $endPage = min($totalPages, $page + 2);
                
                for ($i = $startPage; $i <= $endPage; $i++):
                ?>
                    <?php if ($i === $page): ?>
                        <span class="current"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?page=<?= $i ?><?= http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)) ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?><?= http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)) ?>">Next &rsaquo;</a>
                    <a href="?page=<?= $totalPages ?><?= http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)) ?>">Last &raquo;</a>
                <?php else: ?>
                    <span class="disabled">Next &rsaquo;</span>
                    <span class="disabled">Last &raquo;</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="footer">
            <h3>📝 Code Explanation</h3>
            <p><strong>Key PDO Concepts Demonstrated:</strong></p>
            <ul>
                <li><strong>Prepared Statements:</strong> All queries use <code>prepare()</code> and <code>execute()</code> with bound parameters to prevent SQL injection</li>
                <li><strong>fetchAll():</strong> Retrieves all rows at once as an associative array for table rendering</li>
                <li><strong>Dynamic Query Building:</strong> WHERE clauses built conditionally based on filter inputs</li>
                <li><strong>Input Validation:</strong> Sort column validated against whitelist; pagination uses integer casting</li>
                <li><strong>XSS Prevention:</strong> All output escaped with <code>htmlspecialchars()</code> via helper function <code>h()</code></li>
                <li><strong>Pagination:</strong> LIMIT/OFFSET with total count query for page navigation</li>
                <li><strong>Sortable Columns:</strong> Click column headers to toggle ASC/DESC order</li>
            </ul>
            <p><strong>Australian Brands Featured:</strong> Arnott's, Bega, Cadbury Australia, Bundaberg, Nestlé Australia, Woolworths, Coles, Aldi Australia, Sanitarium, Kellogg's Australia, SunRice, Heinz Australia, MasterFoods, and many more!</p>
        </div>
    </div>
</body>
</html>