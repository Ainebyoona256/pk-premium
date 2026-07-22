<?php require_once 'config.php'; ?>
<?php include 'includes/header.php'; ?>

<main>
    <!-- Hero Banner -->
    <section class="hero-banner">
        <div class="container">
            <h1>Welcome to PK Premium</h1>
            <p>Discover the finest styles and scents. Premium clothes, shoes, jewellery, deodorants, and body sprays.</p>
        </div>
    </section>

    <?php
    // Check if there's an active sale/discount
    $active_discount = getActiveDiscount($conn);
    $show_sale_popup = $active_discount > 0;
    ?>

    <!-- Category Rows -->
    <?php
    // Order: Shoes (2), Clothes (1), Jewellery (3), Deodorants (4), Body Sprays (5)
    $category_order = [2, 1, 3, 4, 5];
    
    foreach ($category_order as $cat_id) {
        $cat_stmt = $conn->prepare("SELECT id, name, image_url FROM categories WHERE id = ?");
        $cat_stmt->bind_param("i", $cat_id);
        $cat_stmt->execute();
        $cat_result = $cat_stmt->get_result();
        
        if ($cat_result && $cat_result->num_rows > 0) {
            $category = $cat_result->fetch_assoc();
    ?>
    
    <section class="category-section">
        <div class="section-header">
            <h2><?php echo htmlspecialchars($category['name']); ?></h2>
        </div>
        
        <div class="product-grid">
            <?php
            $prod_stmt = $conn->prepare("SELECT id, name, price, discount_price, image_url FROM products WHERE category_id = ? ORDER BY id DESC LIMIT 4");
            $prod_stmt->bind_param("i", $cat_id);
            $prod_stmt->execute();
            $products = $prod_stmt->get_result();
            
            if ($products && $products->num_rows > 0):
                while ($product = $products->fetch_assoc()):
                    $display_price = $product['discount_price'] > 0 ? $product['discount_price'] : $product['price'];
                    $old_price = $product['discount_price'] > 0 ? $product['price'] : null;
                    $discount_percent = $old_price ? round((($old_price - $display_price) / $old_price) * 100) : 0;
            ?>
            <div class="product-card">
                <a href="product.php?id=<?php echo $product['id']; ?>" style="text-decoration:none;">
                    <?php if ($product['image_url']): ?>
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-card__image" loading="lazy">
                    <?php else: ?>
                        <div class="product-card__image" style="display:flex;align-items:center;justify-content:center;color:#555;font-size:0.8rem;">No Image</div>
                    <?php endif; ?>
                    
                    <div class="product-card__body">
                        <h3 class="product-card__name"><?php echo htmlspecialchars($product['name']); ?></h3>
                        
                        <?php if ($discount_percent > 0): ?>
                            <span class="product-card__discount-badge">-<?php echo $discount_percent; ?>%</span>
                        <?php endif; ?>
                        
                        <div class="product-card__price"><?php echo formatPrice($display_price); ?></div>
                        <?php if ($old_price): ?>
                            <div class="product-card__old-price"><?php echo formatPrice($old_price); ?></div>
                        <?php endif; ?>
                    </div>
                </a>
            </div>
            <?php
                endwhile;
            else:
            ?>
            <p style="grid-column:1/-1;text-align:center;color:#888;padding:20px;">No products available in this category yet.</p>
            <?php endif; ?>
        </div>
    </section>
    
    <?php
    }
    $cat_stmt->close();
    $conn->close();
    ?>
</main>

<!-- Sale Popup -->
<?php if ($show_sale_popup): ?>
<div class="sale-popup-overlay" id="salePopup">
    <div class="sale-popup">
        <button class="close-popup" onclick="closeSalePopup()">&times;</button>
        <h2>SALE!</h2>
        <div class="discount-percent"><?php echo $active_discount; ?>%</div>
        <p>OFF THIS</p>
        <?php if (isWeekendOrHoliday()): ?>
            <p style="color:#D4AF37;font-weight:600;">WEEKEND / HOLIDAY</p>
        <?php else: ?>
            <p style="color:#D4AF37;font-weight:600;">LIMITED TIME OFFER</p>
        <?php endif; ?>
        <p style="margin-top:20px;color:#ccc;">Hurry! Limited stock available.</p>
        <a href="#" onclick="closeSalePopup(); return false;" class="btn-primary" style="margin-top:20px;display:inline-block;">Shop Now</a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        document.getElementById('salePopup').style.display = 'flex';
    }, 500);
});

function closeSalePopup() {
    document.getElementById('salePopup').style.display = 'none';
}
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
