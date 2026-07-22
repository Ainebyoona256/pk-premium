<?php
// admin/dashboard.php
require_once '../config.php';

if (!isset($_SESSION['admin'])) {
    redirect('login.php');
}

$message = '';
$current_tab = isset($_GET['tab']) ? sanitize($_GET['tab']) : 'dashboard';

if (!$message && isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// Handle category image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category_image'])) {
    $category_id = (int) $_POST['category_id'];
    if (isset($_FILES['category_image_file']) && $_FILES['category_image_file']['tmp_name']) {
        $url = uploadToImageKit($_FILES['category_image_file'], IMAGEKIT_FOLDER);
        if ($url) {
            $stmt = $conn->prepare("UPDATE categories SET image_url = ? WHERE id = ?");
            $stmt->bind_param("si", $url, $category_id);
            if ($stmt->execute()) {
                $message = "Category image updated successfully";
            } else {
                $message = "Error updating image";
            }
            $stmt->close();
        } else {
            $message = "Image upload failed";
        }
    }
}

// Handle product add/update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_product'])) {
    $product_id = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
    $category_id = (int) $_POST['category_id'];
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $price = (float) $_POST['price'];
    $discount_price = (float) $_POST['discount_price'];
    $quantity_remaining = isset($_POST['quantity_remaining']) ? (int) $_POST['quantity_remaining'] : 0;
    $image_url = sanitize($_POST['image_url']);
    
    if ($product_id > 0) {
        $stmt = $conn->prepare("UPDATE products SET category_id=?, name=?, description=?, price=?, discount_price=?, quantity_remaining=?, image_url=? WHERE id=?");
        $stmt->bind_param("issddisi", $category_id, $name, $description, $price, $discount_price, $quantity_remaining, $image_url, $product_id);
        if ($stmt->execute()) {
            $message = "Product updated successfully";
        }
        $stmt->close();
    } else {
        $stmt = $conn->prepare("INSERT INTO products (category_id, name, description, price, discount_price, quantity_remaining, image_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issddis", $category_id, $name, $description, $price, $discount_price, $quantity_remaining, $image_url);
        if ($stmt->execute()) {
            $message = "Product added successfully";
        }
        $stmt->close();
    }
}

// Handle product delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $product_id = (int) $_POST['product_id'];
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    if ($stmt->execute()) {
        $message = "Product deleted";
    }
    $stmt->close();
}

// Handle offer add/update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_offer'])) {
    $offer_id = isset($_POST['offer_id']) ? (int) $_POST['offer_id'] : 0;
    $title = sanitize($_POST['title']);
    $discount_percent = (int) $_POST['discount_percent'];
    $start_date = sanitize($_POST['start_date']);
    $end_date = sanitize($_POST['end_date']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if ($offer_id > 0) {
        $stmt = $conn->prepare("UPDATE offers SET title=?, discount_percent=?, start_date=?, end_date=?, is_active=? WHERE id=?");
        $stmt->bind_param("sisssi", $title, $discount_percent, $start_date, $end_date, $is_active, $offer_id);
        if ($stmt->execute()) {
            $message = "Offer updated successfully";
        }
        $stmt->close();
    } else {
        $stmt = $conn->prepare("INSERT INTO offers (title, discount_percent, start_date, end_date, is_active) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sisss", $title, $discount_percent, $start_date, $end_date, $is_active);
        if ($stmt->execute()) {
            $message = "Offer added successfully";
        }
        $stmt->close();
    }
}

// Handle offer delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_offer'])) {
    $offer_id = (int) $_POST['offer_id'];
    $stmt = $conn->prepare("DELETE FROM offers WHERE id = ?");
    $stmt->bind_param("i", $offer_id);
    if ($stmt->execute()) {
        $message = "Offer deleted";
    }
    $stmt->close();
}

// Fetch subscription info
$sub_days = 'N/A';
try {
    $res = $conn->query("SELECT subscription_expiry FROM settings WHERE id=1");
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $sub_days = floor((strtotime($row['subscription_expiry']) - time()) / 86400);
    }
} catch (Exception $e) { $sub_days = 'Error'; }

// Fetch categories
$categories = [];
$cat_res = $conn->query("SELECT * FROM categories");
if ($cat_res) {
    while ($row = $cat_res->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Fetch products
$products = [];
$prod_res = $conn->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
if ($prod_res) {
    while ($row = $prod_res->fetch_assoc()) {
        $products[] = $row;
    }
}

// Fetch offers
$offers = [];
$offer_res = $conn->query("SELECT * FROM offers ORDER BY id DESC");
if ($offer_res) {
    while ($row = $offer_res->fetch_assoc()) {
        $offers[] = $row;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PK Premium</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        .admin-layout { display: flex; min-height: 100vh; }
        .admin-sidebar {
            width: 260px;
            background: #000;
            border-right: 1px solid #333;
            padding: 20px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        .admin-sidebar h3 {
            color: #D4AF37;
            margin-bottom: 20px;
            font-size: 1.1rem;
        }
        .admin-sidebar a {
            display: block;
            padding: 12px 15px;
            color: #fff;
            margin-bottom: 5px;
            border-radius: 8px;
            transition: all 0.2s;
            font-weight: 500;
        }
        .admin-sidebar a:hover, .admin-sidebar a.active {
            background: #D4AF37;
            color: #000;
        }
        .admin-main { flex: 1; margin-left: 260px; padding: 30px; }
        @media (max-width: 768px) {
            .admin-sidebar { width: 100%; position: relative; height: auto; border-right: none; border-bottom: 1px solid #333; }
            .admin-main { margin-left: 0; padding: 20px; }
            .admin-layout { flex-direction: column; }
        }
        .image-preview { max-width: 150px; margin-top: 10px; border-radius: 8px; border: 1px solid #333; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <h3>PK PREMIUM ADMIN</h3>
            <a href="?tab=dashboard" class="<?php echo $current_tab == 'dashboard' ? 'active' : ''; ?>">Dashboard</a>
            <a href="?tab=categories" class="<?php echo $current_tab == 'categories' ? 'active' : ''; ?>">Categories</a>
            <a href="?tab=products" class="<?php echo $current_tab == 'products' ? 'active' : ''; ?>">Products</a>
            <a href="?tab=offers" class="<?php echo $current_tab == 'offers' ? 'active' : ''; ?>">Offers</a>
            <a href="../" target="_blank" style="margin-top:20px;border-top:1px solid #333;padding-top:20px;">Visit Store</a>
            <a href="logout.php">Logout</a>
        </aside>
        
        <main class="admin-main">
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <!-- Dashboard Tab -->
            <?php if ($current_tab == 'dashboard'): ?>
                <h1>Dashboard</h1>
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3><?php echo count($products); ?></h3>
                        <p>Total Products</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo count($categories); ?></h3>
                        <p>Categories</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo count($offers); ?></h3>
                        <p>Active Offers</p>
                    </div>
                    <div class="stat-card">
                        <h3 style="color:<?php echo $sub_days <= 7 ? '#dc3545' : '#D4AF37'; ?>">
                            <?php echo $sub_days > 0 ? $sub_days . ' days' : ($sub_days == 0 ? 'Expires today' : 'Expired'); ?>
                        </h3>
                        <p>Subscription Remaining</p>
                    </div>
                </div>
                
                <div class="admin-form" style="max-width:100%;">
                    <h3>Business Information</h3>
                    <p><strong>WhatsApp:</strong> <?php echo WHATSAPP; ?></p>
                    <p><strong>Calls:</strong> <?php echo CALLS; ?></p>
                    <p><strong>Renewal Contact:</strong> <?php echo RENEWAL_CONTACT; ?></p>
                    <p><strong>Location:</strong> <?php echo LOCATION; ?></p>
                    <p><strong>Subscription Expiry:</strong> <?php echo SUBSCRIPTION_EXPIRY; ?></p>
                </div>
            <?php endif; ?>
            
            <!-- Categories Tab -->
            <?php if ($current_tab == 'categories'): ?>
                <h1>Categories</h1>
                <div style="overflow-x:auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Image</th>
                                <th>Upload Image</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td><?php echo $cat['id']; ?></td>
                                <td><?php echo htmlspecialchars($cat['name']); ?></td>
                                <td>
                                    <?php if ($cat['image_url']): ?>
                                        <img src="<?php echo htmlspecialchars($cat['image_url']); ?>" style="height:50px;border-radius:4px;" alt="">
                                    <?php else: ?>
                                        <span style="color:#888;">None</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" enctype="multipart/form-data" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                                        <input type="hidden" name="category_image" value="1">
                                        <input type="hidden" name="category_id" value="<?php echo $cat['id']; ?>">
                                        <input type="file" name="category_image_file" accept="image/*" onchange="this.form.submit()" style="font-size:0.85rem;">
                                        <button type="submit" class="btn-primary btn-small">Upload</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
            
            <!-- Products Tab -->
            <?php if ($current_tab == 'products'): ?>
                <h1>Products</h1>
                
                <!-- Add/Edit Product Form -->
                <div class="admin-form" style="max-width:100%;">
                    <h3 style="margin-bottom:20px;" id="form-title">Add New Product</h3>
                    <form method="POST" id="product-form">
                        <input type="hidden" name="product_id" id="product-id" value="">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="name" id="p-name" required>
                        </div>
                        <div class="form-group">
                            <label>Category</label>
                            <select name="category_id" id="p-category" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" id="p-desc"></textarea>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
                            <div class="form-group">
                                <label>Price (UGX)</label>
                                <input type="number" name="price" id="p-price" step="0.01" required>
                            </div>
                            <div class="form-group">
                                <label>Discount Price (UGX)</label>
                                <input type="number" name="discount_price" id="p-discount" step="0.01" value="0">
                            </div>
                            <div class="form-group">
                                <label>Quantity Remaining (Stock)</label>
                                <input type="number" name="quantity_remaining" id="p-qty" step="1" min="0" value="0">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Image URL</label>
                            <input type="text" name="image_url" id="p-image" placeholder="Auto-filled after upload">
                            <input type="file" id="p-image-upload" accept="image/*" style="margin-top:10px;">
                            <img id="p-image-preview" class="image-preview" style="display:none;">
                            <button type="button" class="btn-secondary btn-small" style="margin-top:10px;" onclick="uploadProductImage()">Upload Image</button>
                        </div>
                        <div style="display:flex;gap:10px;">
                            <button type="submit" name="save_product" class="btn-primary">Save Product</button>
                            <button type="button" class="btn-secondary" onclick="resetProductForm()">Reset</button>
                        </div>
                    </form>
                </div>
                
                <!-- Products List -->
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Discount</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $prod): ?>
                        <tr>
                            <td><?php echo $prod['id']; ?></td>
                            <td><?php echo htmlspecialchars($prod['name']); ?></td>
                            <td><?php echo htmlspecialchars($prod['category_name']); ?></td>
                            <td><?php echo formatPrice($prod['price']); ?></td>
                            <td>
                                <?php if ($prod['discount_price'] > 0): ?>
                                    <?php echo formatPrice($prod['discount_price']); ?>
                                <?php else: ?>
                                    <span style="color:#888;">None</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span style="color:<?php echo $prod['quantity_remaining'] > 0 ? '#25D366' : '#dc3545'; ?>">
                                    <?php echo $prod['quantity_remaining']; ?>
                                </span>
                            </td>
                            <td>
                                <button onclick="editProduct(<?php echo htmlspecialchars(json_encode($prod), ENT_QUOTES); ?>)" class="btn-secondary btn-small">Edit</button>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this product?')">
                                    <input type="hidden" name="product_id" value="<?php echo $prod['id']; ?>">
                                    <button type="submit" name="delete_product" class="btn-danger btn-small">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            
            <!-- Offers Tab -->
            <?php if ($current_tab == 'offers'): ?>
                <h1>Offers</h1>
                
                <div class="admin-form" style="max-width:100%;">
                    <h3 style="margin-bottom:20px;"><?php echo isset($_GET['edit_offer']) ? 'Edit' : 'Add New'; ?> Offer</h3>
                    <?php
                    $edit_offer = null;
                    if (isset($_GET['edit_offer'])) {
                        $oid = (int) $_GET['edit_offer'];
                        $stmt = $conn->prepare("SELECT * FROM offers WHERE id = ?");
                        $stmt->bind_param("i", $oid);
                        $stmt->execute();
                        $res = $stmt->get_result();
                        if ($res && $res->num_rows > 0) {
                            $edit_offer = $res->fetch_assoc();
                        }
                        $stmt->close();
                    }
                    ?>
                    <form method="POST">
                        <?php if ($edit_offer): ?>
                            <input type="hidden" name="offer_id" value="<?php echo $edit_offer['id']; ?>">
                        <?php endif; ?>
                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" name="title" value="<?php echo $edit_offer ? htmlspecialchars($edit_offer['title']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Discount Percent</label>
                            <input type="number" name="discount_percent" value="<?php echo $edit_offer ? $edit_offer['discount_percent'] : ''; ?>" required min="1" max="99">
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
                            <div class="form-group">
                                <label>Start Date</label>
                                <input type="date" name="start_date" value="<?php echo $edit_offer ? $edit_offer['start_date'] : date('Y-m-d'); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>End Date</label>
                                <input type="date" name="end_date" value="<?php echo $edit_offer ? $edit_offer['end_date'] : ''; ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="is_active" value="1" <?php echo (!$edit_offer || $edit_offer['is_active']) ? 'checked' : ''; ?>>
                                Active
                            </label>
                        </div>
                        <button type="submit" name="save_offer" class="btn-primary"><?php echo $edit_offer ? 'Update' : 'Add'; ?> Offer</button>
                        <?php if ($edit_offer): ?>
                            <a href="?tab=offers" class="btn-secondary" style="display:inline-block;margin-left:10px;">Cancel</a>
                        <?php endif; ?>
                    </form>
                </div>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Discount</th>
                            <th>Dates</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($offers as $offer): ?>
                        <tr>
                            <td><?php echo $offer['id']; ?></td>
                            <td><?php echo htmlspecialchars($offer['title']); ?></td>
                            <td><?php echo $offer['discount_percent']; ?>%</td>
                            <td><?php echo $offer['start_date']; ?> to <?php echo $offer['end_date']; ?></td>
                            <td><?php echo $offer['is_active'] ? '<span style="color:#25D366;">Active</span>' : '<span style="color:#888;">Inactive</span>'; ?></td>
                            <td>
                                <a href="?tab=offers&edit_offer=<?php echo $offer['id']; ?>" class="btn-secondary btn-small">Edit</a>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this offer?')">
                                    <input type="hidden" name="offer_id" value="<?php echo $offer['id']; ?>">
                                    <button type="submit" name="delete_offer" class="btn-danger btn-small">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </main>
    </div>

    <script>
    // Product image upload via ImageKit
    function uploadProductImage() {
        const input = document.getElementById('p-image-upload');
        if (!input.files || !input.files[0]) {
            alert('Please select an image file');
            return;
        }
        
        const formData = new FormData();
        formData.append('file', input.files[0]);
        formData.append('folder', '<?php echo IMAGEKIT_FOLDER; ?>');
        
        fetch('../upload.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('p-image').value = data.url;
                const preview = document.getElementById('p-image-preview');
                preview.src = data.url;
                preview.style.display = 'block';
            } else {
                alert('Upload failed: ' + data.message);
            }
        })
        .catch(err => {
            alert('Upload error: ' + err.message);
        });
    }
    
    // Edit product from list
    function editProduct(data) {
        document.getElementById('form-title').textContent = 'Edit Product';
        document.getElementById('product-id').value = data.id;
        document.getElementById('p-name').value = data.name;
        document.getElementById('p-category').value = data.category_id;
        document.getElementById('p-desc').value = data.description;
        document.getElementById('p-price').value = data.price;
        document.getElementById('p-discount').value = data.discount_price || 0;
        document.getElementById('p-qty').value = data.quantity_remaining || 0;
        document.getElementById('p-image').value = data.image_url || '';
        if (data.image_url) {
            const preview = document.getElementById('p-image-preview');
            preview.src = data.image_url;
            preview.style.display = 'block';
        }
        window.scrollTo({top:0, behavior:'smooth'});
    }
    
    function resetProductForm() {
        document.getElementById('product-form').reset();
        document.getElementById('product-id').value = '';
        document.getElementById('form-title').textContent = 'Add New Product';
        document.getElementById('p-image-preview').style.display = 'none';
    }
    </script>
    
    <?php $conn->close(); ?>
    
    <footer style="background:#000;color:#D4AF37;text-align:center;padding:20px;margin-top:40px;font-family:Poppins,sans-serif;border-top:1px solid #D4AF37;">
        <div style="max-width:1200px;margin:0 auto;">
            <p style="margin:5px 0;font-weight:500;">&copy; 2026 Pk Premium Styles and Scents. All Rights Reserved.</p>
            <p style="margin:5px 0;font-weight:600;color:#D4AF37;">This website was built by Calvin Kellerman Technologies.</p>
        </div>
    </footer>
</body>
</html>
