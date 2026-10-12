<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grocery Items - PDO Methods Homepage</title>
</head>
<body>

    <h1>Australian Grocery Items - PDO Methods</h1>

    <?php require_once 'navbar.php'; ?>

    <hr>

    <h2>Pages</h2>
    <ul>
        <li><a href="fetch.php"><strong>fetch.php</strong></a> - demonstrates <code>fetch()</code> one row at a time, plus FETCH_ASSOC, FETCH_NUM, FETCH_OBJ, FETCH_BOTH and <code>fetchColumn()</code></li>
        <li><a href="fetch_all.php"><strong>fetch_all.php</strong></a> - demonstrates <code>fetchAll()</code> with FETCH_COLUMN, FETCH_KEY_PAIR and FETCH_CLASS</li>
        <li><a href="insert.php"><strong>insert.php</strong></a> - named and positional placeholders, a multi-row transaction, and error handling</li>
        <li><a href="update.php"><strong>update.php</strong></a> - single and bulk updates, CASE expressions, subqueries, RETURNING clause and a safe update pattern</li>
        <li><a href="delete.php"><strong>delete.php</strong></a> - single and conditional deletes, a transaction rollback demo and a safe delete pattern</li>
        <li><a href="display_table.php"><strong>display_table.php</strong></a> - the full styled table with search, category filter, price range, sorting and pagination</li>
        <li><a href="display_table_raw.php"><strong>display_table_raw.php</strong></a> - the same data as a plain HTML table, useful for checking the raw query output</li>
        <li><a href="schema.sql"><strong>schema.sql</strong></a> - creates <code>grocery_db</code>, the <code>grocery_items</code> table and the seed data; run this in phpMyAdmin first</li>
    </ul>



</body>
</html>
