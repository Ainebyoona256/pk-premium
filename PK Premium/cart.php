<?php require_once 'config.php'; ?>
<?php include 'includes/header.php'; ?>

<main>
    <div class="container" style="padding:30px 15px;">
        <h1 style="text-align:center;margin-bottom:30px;">Shopping Cart</h1>
        
        <div id="cart-content">
            <!-- Will be populated by JavaScript -->
        </div>
        
        <div id="cart-summary" class="cart-summary" style="display:none;">
            <h3>Order Summary</h3>
            <div class="cart-total">Total: <span id="cart-total-amount">0</span> UGX</div>
            <div style="display:flex;flex-direction:column;gap:10px;margin-top:20px;">
                <a href="https://wa.me/<?php echo WHATSAPP; ?>?text=" id="whatsapp-checkout" class="whatsapp-order-btn" target="_blank" rel="noopener" style="justify-content:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.363z"/>
                    </svg>
                    Order via WhatsApp
                </a>
                <a href="tel:<?php echo CALLS; ?>" class="call-btn" target="_blank" rel="noopener" style="justify-content:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M20 15.5c-1.25 0-2.45-.2-3.57-.57a1.02 1.02 0 00-1.02.24l-2.2 2.2a15.045 15.045 0 01-6.59-6.59l2.2-2.21a.96.96 0 00.25-1A11.36 11.36 0 018.5 4c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1a11.36 11.36 0 00.36 5.16 14.939 14.939 0 005.57 5.57 14.939 14.939 0 005.57-5.57 11.36 11.36 0 00.36-5.16c0-.55-.45-1-1-1h-3.5c-.55 0-1 .45-1 1s.45 1 1 1c.53 0 1.04.08 1.52.23a1.02 1.02 0 00.98.24l2.2-2.2a14.939 14.939 0 015.57 5.57 14.939 14.939 0 01-5.57 5.57 14.939 14.939 0 01-5.57-5.57l-2.2 2.2a.96.96 0 00-.25 1 11.36 11.36 0 00-.36 5.16c0 .55.45 1 1 1h3.5c.55 0 1-.45 1-1s-.45-1-1-1c-.53 0-1.04-.08-1.52-.23a1.02 1.02 0 00-.98-.24l-2.2 2.2a14.939 14.939 0 01-5.57 5.57 14.939 14.939 0 01-5.57-5.57 14.939 14.939 0 015.57-5.57l2.2-2.21c.07-.07.15-.14.24-.23a1.02 1.02 0 00.24-1.02A11.36 11.36 0 0112.05 0c6.554 0 11.89 5.335 11.893 11.893a11.821 11.821 0 01-3.48 8.363L24 24l-4-4z"/>
                    </svg>
                    Call Us: <?php echo CALLS; ?>
                </a>
                <button onclick="clearCart()" class="btn-danger" style="padding:12px;border-radius:30px;font-weight:600;">Clear Cart</button>
            </div>
        </div>
    </div>
</main>

<script>
function renderCart() {
    const cart = JSON.parse(localStorage.getItem('pk_cart') || '[]');
    const container = document.getElementById('cart-content');
    const summary = document.getElementById('cart-summary');
    const totalEl = document.getElementById('cart-total-amount');
    const waLink = document.getElementById('whatsapp-checkout');
    
    if (cart.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"></path>
                </svg>
                <h3>Your cart is empty</h3>
                <p>Looks like you haven\'t added anything yet.</p>
                <a href="index.php" class="btn-primary" style="margin-top:20px;">Continue Shopping</a>
            </div>
        `;
        summary.style.display = 'none';
        return;
    }
    
    summary.style.display = 'block';
    
    let html = '<table class="cart-table"><thead><tr><th>Item</th><th>Price</th><th>Qty</th><th>Subtotal</th><th></th></tr></thead><tbody>';
    
    let total = 0;
    let waText = 'Hi PK Premium, I want to order:';
    
    cart.forEach((item, index) => {
        const subtotal = item.price * item.qty;
        total += subtotal;
        waText += `%0A${item.qty}x ${item.name} - ${formatPrice(item.price * item.qty)}`;
    });
    
    cart.forEach((item, index) => {
        const subtotal = item.price * item.qty;
        html += `
            <tr>
                <td style="display:flex;align-items:center;gap:15px;flex-wrap:wrap;">
                    ${item.image ? `<img src="${item.image}" alt="${escapeHtml(item.name)}" class="cart-item-image">` : ''}
                    <span style="font-weight:600;">${escapeHtml(item.name)}</span>
                </td>
                <td>${formatPrice(item.price)}</td>
                <td>
                    <div class="cart-item-qty">
                        <button onclick="updateQty(${index}, -1)">-</button>
                        <input type="number" value="${item.qty}" min="1" max="99" onchange="setQty(${index}, this.value)" style="width:50px;">
                        <button onclick="updateQty(${index}, 1)">+</button>
                    </div>
                </td>
                <td style="color:#D4AF37;font-weight:600;">${formatPrice(subtotal)}</td>
                <td><button onclick="removeItem(${index})" class="btn-danger btn-small">Remove</button></td>
            </tr>
        `;
    });
    
    html += '</tbody></table>';
    container.innerHTML = html;
    totalEl.textContent = formatPrice(total);
    waLink.href = `https://wa.me/${WHATSAPP}?text=${waText}`;
}

function escapeHtml(text) {
    const map = {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'};
    return text.replace(/[&<>"']/g, m => map[m]);
}

function formatPrice(amount) {
    return Number(amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

function updateQty(index, delta) {
    const cart = JSON.parse(localStorage.getItem('pk_cart') || '[]');
    if (!cart[index]) return;
    
    cart[index].qty += delta;
    if (cart[index].qty < 1) cart[index].qty = 1;
    if (cart[index].qty > 99) cart[index].qty = 99;
    
    localStorage.setItem('pk_cart', JSON.stringify(cart));
    renderCart();
    updateCartCount();
}

function setQty(index, value) {
    const cart = JSON.parse(localStorage.getItem('pk_cart') || '[]');
    if (!cart[index]) return;
    
    let val = parseInt(value) || 1;
    if (val < 1) val = 1;
    if (val > 99) val = 99;
    cart[index].qty = val;
    
    localStorage.setItem('pk_cart', JSON.stringify(cart));
    renderCart();
    updateCartCount();
}

function removeItem(index) {
    const cart = JSON.parse(localStorage.getItem('pk_cart') || '[]');
    cart.splice(index, 1);
    localStorage.setItem('pk_cart', JSON.stringify(cart));
    renderCart();
    updateCartCount();
}

function clearCart() {
    if (confirm('Are you sure you want to clear your cart?')) {
        localStorage.removeItem('pk_cart');
        renderCart();
        updateCartCount();
    }
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

document.addEventListener('DOMContentLoaded', function() {
    renderCart();
    updateCartCount();
});
</script>

<?php $conn->close(); ?>
<?php include 'includes/footer.php'; ?>
