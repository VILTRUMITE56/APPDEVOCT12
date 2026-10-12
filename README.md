# PDO Methods Activity - Australian Grocery Items

## Overview
This project demonstrates PDO (PHP Data Objects) methods for database operations using an Australian grocery items database with 180+ real products from Australian brands.

## Country: Australia 🇦🇺
Chosen for its unique grocery brands including:
- **Arnott's** - Iconic biscuits (Tim Tam, Shapes, Mint Slice)
- **Bega** - Vegemite, cheese, peanut butter
- **Cadbury Australia** - Dairy Milk, Cherry Ripe, Crunchie
- **Bundaberg** - Ginger Beer, craft sodas
- **Nestlé Australia** - Milo, Maggi, KitKat
- **Sanitarium** - Weet-Bix, So Good
- **Kellogg's Australia** - Corn Flakes, Nutri-Grain
- **Heinz Australia** - Baked beans, soups
- **MasterFoods** - Sauces, condiments
- **SunRice** - Rice products
- **Golden Circle** - Canned fruits
- **Four'n Twenty** - Meat pies
- **Mrs Mac's** - Pies and sausage rolls
- **Bird's Eye Australia** - Frozen vegetables
- **McCain Australia** - Frozen fries and wedges

## Database Schema
```sql
CREATE TABLE grocery_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(100) NOT NULL,
    brand_name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    price_aud DECIMAL(10,2) NOT NULL,
    stock_quantity INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```
- **5+ columns** with snake_case naming
- **180 records** (exceeds 100 requirement)
- **Categories**: Biscuits, Spreads, Chocolate, Beverages, Dairy, Frozen, Pantry, Snacks

## Files

| File | Description |
|------|-------------|
| `index.php` | Homepage with a nav bar linking every page |
| `navbar.php` | Shared nav bar included at the top of all pages |
| `schema.sql` | Database schema + INSERT statements |
| `dbconfig.php` | PDO connection with error handling *(gitignored)* |
| `dbconfig.example.php` | Template for `dbconfig.php` with placeholder values |
| `fetch_all.php` | Demonstrates `fetchAll()` with `print_r()` and `<pre>` |
| `fetch.php` | Demonstrates `fetch()` with `print_r()` and `<pre>` |
| `insert.php` | INSERT with prepared statements (single, batch, transaction) |
| `delete.php` | DELETE with prepared statements (single, multi, transaction) |
| `update.php` | UPDATE with prepared statements (single, bulk, conditional) |
| `display_table.php` | HTML table with filtering, sorting, pagination |
| `display_table_raw.php` | The same data as a plain HTML table |

## Setup Instructions

1. **Start XAMPP** - Ensure Apache and MySQL are running

2. **Import the database**:
   ```bash
   mysql -u root < schema.sql
   ```
   Or use phpMyAdmin to import `schema.sql`

3. **Create `dbconfig.php` from the template** - it is gitignored so it is not in the repo:
   ```bash
   cp dbconfig.example.php dbconfig.php
   ```
   Then edit `dbconfig.php` and fill in your database user and password. For a default local XAMPP install these are `root` and an empty password.

4. **Access** - Place the folder in `htdocs` and open `http://localhost/Ybanez/`

## PDO Methods Demonstrated

### fetchAll()
- `fetchAll()` - All rows as associative array
- `fetchAll(PDO::FETCH_COLUMN)` - Single column
- `fetchAll(PDO::FETCH_KEY_PAIR)` - Key-value pairs
- `fetchAll(PDO::FETCH_CLASS)` - Object mapping

### fetch()
- `fetch()` in while loop - Row by row
- `fetch(PDO::FETCH_ASSOC)` - Associative array
- `fetch(PDO::FETCH_NUM)` - Numeric array
- `fetch(PDO::FETCH_OBJ)` - stdClass object
- `fetch(PDO::FETCH_BOTH)` - Both associative and numeric
- `fetchColumn()` - Single column from next row

### Insert
- Named placeholders (`:name`)
- Positional placeholders (`?`)
- Transactions with `beginTransaction()`, `commit()`, `rollBack()`
- `lastInsertId()` for auto-increment IDs

### Delete
- Single record by ID
- Multiple records with condition
- Transaction with rollback
- Safe delete pattern with verification

### Update
- Single record by ID
- Bulk update with condition
- CASE statement for conditional updates
- Subquery-based updates
- Safe update with field whitelist
- Batch updates in transaction

### Display Table
- Prepared statements with dynamic WHERE
- Filtering (search, category, price range)
- Sorting (clickable column headers)
- Pagination (LIMIT/OFFSET)
- XSS prevention with `htmlspecialchars()`
- Stock status badges
- Responsive design

## Requirements Met

✅ 5+ columns with snake_case names  
✅ 100+ records (180 actual) with Australian brand names  
✅ dbconfig.php with comments  
✅ fetch_all.php with fetchAll() + print_r + <pre>  
✅ fetch.php with fetch() + print_r + <pre>  
✅ insert.php with prepared statements  
✅ delete.php with prepared statements  
✅ update.php with prepared statements  
✅ display_table.php renders HTML table  
✅ GitHub repository ready  

## GitHub Repository
https://github.com/VILTRUMITE56/APPDEVOCT12

## Author
Student - PDO Methods Activity

## License
Educational use only
