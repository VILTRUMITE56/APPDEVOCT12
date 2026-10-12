<?php
// Shared nav bar; included at the top of every page so any page can reach any other
?>
<style>
    .navbar { background: #2c3e50; padding: 10px 16px; font-family: Arial, Helvetica, sans-serif; }
    .navbar a { color: #ffffff; text-decoration: none; margin-right: 14px; font-size: 14px; }
    .navbar a:hover { color: #27ae60; }
    .navbar a.brand { font-weight: bold; margin-right: 24px; }
</style>
<div class="navbar">
    <a href="index.php" class="brand">Home</a>
    <a href="fetch.php">fetch()</a>
    <a href="fetch_all.php">fetchAll()</a>
    <a href="insert.php">Insert</a>
    <a href="update.php">Update</a>
    <a href="delete.php">Delete</a>
    <a href="display_table_raw.php">Raw Table</a>
</div>
