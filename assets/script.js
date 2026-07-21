(function() {
    'use strict';

    const API_URL = window.API_URL || 'https://your-infinityfree-domain.rf.gd/api.php';
    const WHATSAPP_NUMBER = window.WHATSAPP_NUMBER || '2567XXXXXXXX';
    const IMAGEKIT_URL = window.IMAGEKIT_URL || 'https://ik.imagekit.io/your_imagekit_id';

    let currentProduct = null;
    let currentQty = 1;

    function getCart() {
        try {
            return JSON.parse(localStorage.getItem('pk_cart') || '[]');
        } catch (e) {
            return [];
        }
    }

    function saveCart(cart) {
        localStorage.setItem('pk_cart', JSON.stringify(cart));
    }

    function updateCartCount() {
        const cart = getCart();
        const count = cart.reduce((sum, item) => sum + item.qty, 0);
        document.querySelectorAll('#cartCount').forEach(el => {
            el.textContent = count;
        });
    }

    function addToCart(id, name, price, image, qty) {
        const cart = getCart();
        const existing = cart.find(item => item.id === id);
        if (existing) {
            existing.qty += qty;
        } else {
            cart.push({ id, name, price, image, qty });
        }
        saveCart(cart);
        updateCartCount();
        alert('Added to cart!');
    }

    function updateQty(id, newQty) {
        const cart = getCart();
        const item = cart.find(i => i.id === id);
        if (item) {
            item.qty = Math.max(1, Math.min(99, newQty));
            saveCart(cart);
            renderCart();
            updateCartCount();
        }
    }

    function removeFromCart(id) {
        const cart = getCart().filter(i => i.id !== id);
        saveCart(cart);
        renderCart();
        updateCartCount();
    }

    function showLockdown() {
        document.querySelectorAll('#lockdownOverlay').forEach(el => el.style.display = 'flex');
        document.querySelectorAll('#mainContent').forEach(el => el.style.display = 'none');
        document.querySelectorAll('footer').forEach(el => el.style.display = 'none');
        document.querySelectorAll('header').forEach(el => el.style.display = 'none');
    }

    function showMain() {
        document.querySelectorAll('#lockdownOverlay').forEach(el => el.style.display = 'none');
        document.querySelectorAll('#mainContent').forEach(el => el.style.display = 'block');
        document.querySelectorAll('footer').forEach(el => el.style.display = 'block');
        document.querySelectorAll('header').forEach(el => el.style.display = 'block');
    }

    async function checkLicense() {
        try {
            const res = await fetch(`${API_URL}?action=check_license&t=${Date.now()}`);
            const data = await res.json();
            if (data.license.status === 'expired') {
                showLockdown();
                return false;
            }
            showMain();
            return true;
        } catch (e) {
            console.error('License check failed', e);
            showLockdown();
            return false;
        }
    }

    async function fetchJSON(action) {
        const res = await fetch(`${API_URL}?action=${action}&t=${Date.now()}`);
        if (!res.ok) throw new Error('API error');
        return res.json();
    }

    function getImageUrl(filename) {
        if (!filename) return 'https://via.placeholder.com/400?text=No+Image';
        if (filename.startsWith('http')) return filename;
        return `${IMAGEKIT_URL}/${filename}?tr=w-400,q-80,f-webp`;
    }

    function formatPrice(price) {
        return `UGX ${Number(price).toLocaleString()}`;
    }

    async function loadCategories() {
        try {
            const response = await fetchJSON('get_categories');
            const categories = response.categories || [];
            const grid = document.getElementById('categoriesGrid');
            if (!grid) return;
            grid.innerHTML = categories.map(cat => `
                <div class="category-card" onclick="window.filterByCategory(${cat.id})">
                    <img src="${getImageUrl(cat.image)}" alt="${cat.name}" loading="lazy">
                    <h3>${cat.name}</h3>
                </div>
            `).join('');
        } catch (e) {
            console.error('Failed to load categories', e);
        }
    }

    async function loadProducts(categoryId = null) {
        try {
            const url = categoryId ? `${API_URL}?action=get_products&category_id=${categoryId}&t=${Date.now()}` : `${API_URL}?action=get_products&t=${Date.now()}`;
            const res = await fetch(url);
            const response = await res.json();
            const products = response.products || [];
            const grid = document.getElementById('productsGrid');
            if (!grid) return;
            grid.innerHTML = products.map(p => `
                <div class="product-card">
                    ${p.is_offer ? '<span class="offer-badge">OFFER</span>' : ''}
                    <a href="product.html?id=${p.id}" style="text-decoration:none;color:inherit;">
                        <img src="${getImageUrl(p.image)}" alt="${p.name}" loading="lazy">
                        <div class="product-info">
                            <h3>${p.name}</h3>
                            <div class="price">${formatPrice(p.price)}</div>
                            <button class="btn" onclick="event.preventDefault(); window.location.href='product.html?id=${p.id}'">View Product</button>
                        </div>
                    </a>
                </div>
            `).join('');
        } catch (e) {
            console.error('Failed to load products', e);
        }
    }

    async function loadOffers() {
        try {
            const response = await fetchJSON('get_offers');
            const products = response.offers || [];
            const track = document.getElementById('offersTrack');
            if (!track || !products.length) {
                const section = document.getElementById('offersSection');
                if (section) section.style.display = 'none';
                return;
            }
            track.innerHTML = products.map(p => `
                <div class="offer-card">
                    <img src="${getImageUrl(p.image)}" alt="${p.name}" loading="lazy">
                    <div class="offer-info">
                        <span class="offer-badge">OFFER</span>
                        <h4>${p.name}</h4>
                        <div class="offer-price">${formatPrice(p.price)}</div>
                        <a href="product.html?id=${p.id}" class="btn">View Offer</a>
                    </div>
                </div>
            `).join('');
        } catch (e) {
            console.error('Failed to load offers', e);
        }
    }

    function renderCart() {
        const container = document.getElementById('cartContent');
        if (!container) return;
        const cart = getCart();
        if (!cart.length) {
            container.innerHTML = '<p style="text-align:center;padding:40px;color:#888;">Your cart is empty.</p>';
            return;
        }
        let total = 0;
        container.innerHTML = cart.map(item => {
            const subtotal = item.price * item.qty;
            total += subtotal;
            return `
                <div class="cart-item">
                    <img src="${getImageUrl(item.image)}" alt="${item.name}">
                    <div class="item-info">
                        <h4>${item.name}</h4>
                        <p class="item-price">${formatPrice(item.price)}</p>
                        <div class="qty-controls">
                            <button onclick="window.updateCartQty(${item.id}, ${item.qty - 1})">-</button>
                            <span>${item.qty}</span>
                            <button onclick="window.updateCartQty(${item.id}, ${item.qty + 1})">+</button>
                        </div>
                        <p class="item-subtotal">Subtotal: ${formatPrice(subtotal)}</p>
                    </div>
                    <button class="remove-btn" onclick="window.removeCartItem(${item.id})">Remove</button>
                </div>
            `;
        }).join('') + `
            <div class="cart-summary">
                <h3>Total: ${formatPrice(total)}</h3>
                <div class="action-buttons">
                    <a href="https://wa.me/${WHATSAPP_NUMBER}?text=${encodeURIComponent(generateCartWhatsApp())}" class="btn-order" target="_blank">Order on WhatsApp</a>
                    <a href="index.html" class="btn-continue">Continue Shopping</a>
                </div>
            </div>
        `;
    }

    function generateCartWhatsApp() {
        const cart = getCart();
        if (!cart.length) return '';
        let msg = 'Hello PK Premium, I would like to order:\n';
        let total = 0;
        cart.forEach(item => {
            const sub = item.price * item.qty;
            total += sub;
            msg += `- ${item.name} x${item.qty} = ${formatPrice(sub)}\n`;
        });
        msg += `\nTotal: ${formatPrice(total)}`;
        return msg;
    }

    function renderProductDetail() {
        const params = new URLSearchParams(window.location.search);
        const id = parseInt(params.get('id'));
        if (!id) return;
        const container = document.getElementById('productDetail');
        if (!container) return;

        fetch(`${API_URL}?action=get_product&id=${id}&t=${Date.now()}`)
            .then(res => res.json())
            .then(response => {
                const product = response.product;
                if (!product) {
                    container.innerHTML = '<p>Product not found.</p>';
                    return;
                }
                currentProduct = product;
                currentQty = 1;
                container.innerHTML = `
                    <img src="${getImageUrl(product.image)}" alt="${product.name}" class="main-image">
                    <div class="info">
                        <span class="offer-badge">${product.is_offer ? 'OFFER' : (product.category_name || '')}</span>
                        <h2>${product.name}</h2>
                        <div class="price">${formatPrice(product.price)}</div>
                        <p class="description">${product.description || ''}</p>
                        <div class="qty-selector">
                            <button onclick="window.changeQty(-1)">-</button>
                            <span id="qtyDisplay">1</span>
                            <button onclick="window.changeQty(1)">+</button>
                        </div>
                    </div>
                `;
            })
            .catch(err => {
                container.innerHTML = '<p>Failed to load product.</p>';
                console.error(err);
            });
    }

    function changeQty(delta) {
        currentQty = Math.max(1, Math.min(99, currentQty + delta));
        const display = document.getElementById('qtyDisplay');
        if (display) display.textContent = currentQty;
    }

    function initPage() {
        const path = window.location.pathname;
        if (path.includes('product.html')) {
            renderProductDetail();
            document.getElementById('addToCartBtn')?.addEventListener('click', () => {
                if (!currentProduct) return;
                addToCart(currentProduct.id, currentProduct.name, currentProduct.price, currentProduct.image, currentQty);
            });
            document.getElementById('whatsappBtn')?.addEventListener('click', () => {
                if (!currentProduct) return;
                const msg = `Hello PK Premium, I would like to order:\n- ${currentProduct.name} x${currentQty} = ${formatPrice(currentProduct.price * currentQty)}\nTotal: ${formatPrice(currentProduct.price * currentQty)}`;
                window.open(`https://wa.me/${WHATSAPP_NUMBER}?text=${encodeURIComponent(msg)}`, '_blank');
            });
            document.getElementById('callBtn')?.addEventListener('click', () => {
                window.location.href = `tel:+256703504504`;
            });
        } else if (path.includes('cart.html')) {
            renderCart();
        }
    }

    window.updateCartQty = function(id, qty) {
        if (qty < 1) {
            removeFromCart(id);
        } else {
            updateQty(id, qty);
        }
    };

    window.removeCartItem = function(id) {
        removeFromCart(id);
    };

    window.filterByCategory = function(categoryId) {
        loadProducts(categoryId);
        document.getElementById('productsSection')?.scrollIntoView({ behavior: 'smooth' });
    };

    window.changeQty = changeQty;

    document.addEventListener('DOMContentLoaded', async () => {
        const active = await checkLicense();
        if (!active) return;
        updateCartCount();
        if (document.getElementById('categoriesGrid')) {
            loadCategories();
        }
        if (document.getElementById('productsGrid')) {
            loadProducts();
        }
        if (document.getElementById('offersTrack')) {
            loadOffers();
        }
        initPage();
    });
})();
