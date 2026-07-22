<?php
require_once 'config.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('index.php');
}

$product_id = (int) $_GET['id'];

$stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    redirect('index.php');
}

$product = $result->fetch_assoc();
$stmt->close();

$display_price = $product['discount_price'] > 0 ? $product['discount_price'] : $product['price'];
$old_price = $product['discount_price'] > 0 ? $product['price'] : null;

include 'includes/header.php';
?>

<main>
    <div class="product-detail">
        <!-- Product Image -->
        <div>
            <?php if ($product['image_url']): ?>
                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-detail__image">
            <?php else: ?>
                <div style="width:100%;min-height:300px;background:#111;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#555;border:2px dashed #333;">No Image Available</div>
            <?php endif; ?>
        </div>
        
        <!-- Product Info -->
        <div class="product-detail__info">
            <span style="color:#888;font-size:0.9rem;text-transform:uppercase;letter-spacing:1px;"><?php echo htmlspecialchars($product['category_name']); ?></span>
            
            <h1 class="product-detail__name"><?php echo htmlspecialchars($product['name']); ?></h1>
            
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <span class="product-detail__price"><?php echo formatPrice($display_price); ?></span>
                <?php if ($old_price): ?>
                    <span class="product-detail__old-price"><?php echo formatPrice($old_price); ?></span>
                    <?php
                    $discount_pct = round((($old_price - $display_price) / $old_price) * 100);
                    ?>
                    <span style="background:#D4AF37;color:#000;padding:5px 12px;border-radius:4px;font-weight:700;font-size:0.9rem;">-<?php echo $discount_pct; ?>%</span>
                <?php endif; ?>
            </div>
            
            <p class="product-detail__description"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            
            <!-- Qty Selector -->
            <div class="qty-selector">
                <button type="button" onclick="changeQty(-1)">-</button>
                <input type="number" id="qty" value="1" min="1" max="99" readonly>
                <button type="button" onclick="changeQty(1)">+</button>
            </div>
            
            <!-- Action Buttons -->
            <div style="display:flex;flex-direction:column;gap:12px;margin-top:10px;">
                <button class="btn-primary" onclick="addToCart(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name'], ENT_QUOTES); ?>', <?php echo $display_price; ?>, '<?php echo htmlspecialchars($product['image_url'], ENT_QUOTES); ?>')">
                    Add to Cart
                </button>
                
                <a href="#" onclick="orderWhatsApp('<?php echo htmlspecialchars($product['name'], ENT_QUOTES); ?>', <?php echo $display_price; ?>); return false;" 
                   class="whatsapp-order-btn" target="_blank" rel="noopener">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.363z"/>
                    </svg>
                    WhatsApp Order
                </a>
                
                <a href="tel:<?php echo CALLS; ?>" class="call-btn" target="_blank" rel="noopener">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M20 15.5c-1.25 0-2.45-.2-3.57-.57a1.02 1.02 0 00-1.02.24l-2.2 2.2a15.045 15.045 0 01-6.59-6.59l2.2-2.21a.96.96 0 00.25-1A11.36 11.36 0 018.5 4c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1a11.36 11.36 0 00.36 5.16 14.939 14.939 0 005.57 5.57 14.939 14.939 0 005.57-5.57 11.36 11.36 0 00.36-5.16c0-.55-.45-1-1-1h-3.5c-.55 0-1 .45-1 1s.45 1 1 1c.53 0 1.04.08 1.52.23a1.02 1.02 0 00.98.24l2.2-2.2a14.939 14.939 0 015.57 5.57 14.939 14.939 0 01-5.57 5.57 14.939 14.939 0 01-5.57-5.57l-2.2 2.2a.96.96 0 00-.25 1 11.36 11.36 0 00-.36 5.16c0 .55.45 1 1 1h3.5c.55 0 1-.45 1-1s-.45-1-1-1c-.53 0-1.04-.08-1.52-.23a1.02 1.02 0 00-.98-.24l-2.2 2.2a15.045 15.045 0 01-6.59-6.59l2.2-2.21c.07-.07.15-.14.24-.23a1.02 1.02 0 00.24-1.02A11.36 11.36 0 014.5 4c0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1s-.45 1-1 1c-.53 0-1.04.08-1.52.23a1.02 1.02 0 00-.98.24l-2.2 2.2a14.939 14.939 0 01-5.57 5.57 14.939 14.939 0 01-5.57-5.57 14.939 14.939 0 015.57-5.57l2.2-2.21c.07-.07.15-.14.24-.23a1.02 1.02 0 00.24-1.02A11.36 11.36 0 0112.05 0c6.554 0 11.89 5.335 11.893 11.893a11.821 11.821 0 01-3.48 8.363L24 24l-4-4z"/>
                    </svg>
                    Call Now: <?php echo CALLS; ?>
                </a>
            </div>
        </div>
    </div>
</main>

<script>
let currentQty = 1;

function changeQty(delta) {
    const input = document.getElementById('qty');
    let val = parseInt(input.value) || 1;
    val += delta;
    if (val < 1) val = 1;
    if (val > 99) val = 99;
    currentQty = val;
    input.value = val;
}

function addToCart(id, name, price, image) {
    const qty = parseInt(document.getElementById('qty').value) || 1;
    
    let cart = JSON.parse(localStorage.getItem('pk_cart') || '[]');
    
    const existing = cart.find(item => item.id === id);
    if (existing) {
        existing.qty += qty;
        if (existing.qty > 99) existing.qty = 99;
    } else {
        cart.push({
            id: id,
            name: name,
            price: parseFloat(price),
            image: image || '',
            qty: qty
        });
    }
    
    localStorage.setItem('pk_cart', JSON.stringify(cart));
    updateCartCount();
    
    alert('Added to cart!');
}

function updateCartCount() {
    const cart = JSON.parse(localStorage.getItem('pk_cart') || '[]');
    const count = cart.reduce((sum, item) => sum + item.qty, 0);
    const el = document.getElementById('cart-count');
    if (el) {
        el.textContent = count;
        el.style.display = count > 0 ? 'inline-block' : 'none';
    }
}

function orderWhatsApp(name, price) {
    const qty = parseInt(document.getElementById('qty').value) || 1;
    const total = (price * qty).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    const text = 'Hi PK Premium, I want to order: ' + qty + 'x ' + name + ' - ' + total + ' UGX';
    window.open('https://wa.me/<?php echo WHATSAPP; ?>?text=' + encodeURIComponent(text), '_blank');
}

document.addEventListener('DOMContentLoaded', updateCartCount);
</script>

<?php $conn->close(); ?>
<?php include 'includes/footer.php'; ?>
