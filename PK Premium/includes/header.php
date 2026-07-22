<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PK PREMIUM STYLES AND SCENTS - Your premium destination for clothes, shoes, jewellery, deodorants, and body sprays in Uganda.">
    <meta name="theme-color" content="#000000">
    <title>PK PREMIUM STYLES AND SCENTS</title>
    
    <!-- Favicon from poster -->
    <link rel="icon" type="image/jpeg" href="https://ik.imagekit.io/pkstores/IMG-20260722-WA0345.jpg">
    
    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="style.css">
    
    <!-- WhatsApp Order Button Styles -->
    <style>
        .whatsapp-order-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: #25D366;
            color: #fff;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 30px;
            font-weight: 500;
            transition: transform 0.2s, box-shadow 0.2s;
            font-family: 'Poppins', sans-serif;
        }
        .whatsapp-order-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 211, 102, 0.4);
        }
        .call-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: #D4AF37;
            color: #000;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 30px;
            font-weight: 500;
            transition: transform 0.2s, box-shadow 0.2s;
            font-family: 'Poppins', sans-serif;
        }
        .call-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(212, 175, 55, 0.4);
        }
    </style>
</head>
<body>
    <!-- Subscription Warning Banner -->
    <?php if (isset($_SESSION['sub_warning']) && !empty($_SESSION['sub_warning'])): ?>
        <div style="background:#D4AF37;color:#000;text-align:center;padding:10px;font-family:Poppins,sans-serif;font-weight:500;">
            <?php echo htmlspecialchars($_SESSION['sub_warning']); ?>
        </div>
        <?php unset($_SESSION['sub_warning']); ?>
    <?php endif; ?>

    <!-- Navigation Header -->
    <header style="background:#000;color:#D4AF37;position:sticky;top:0;z-index:1000;box-shadow:0 2px 10px rgba(212,175,55,0.3);">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:15px 20px;max-width:1200px;margin:0 auto;flex-wrap:wrap;gap:10px;">
            
            <!-- Logo -->
            <a href="index.php" style="text-decoration:none;display:flex;align-items:center;gap:10px;">
                <img src="https://ik.imagekit.io/pkstores/IMG-20260722-WA0345.jpg" alt="PK Premium Logo" style="height:50px;width:50px;object-fit:cover;border-radius:50%;border:2px solid #D4AF37;">
                <span style="font-family:Poppins,sans-serif;font-weight:700;font-size:1.2rem;color:#D4AF37;text-transform:uppercase;letter-spacing:1px;">
                    PK PREMIUM
                </span>
            </a>
            
            <!-- Navigation -->
            <nav style="display:flex;align-items:center;gap:15px;flex-wrap:wrap;">
                <a href="index.php" style="color:#D4AF37;text-decoration:none;font-family:Poppins,sans-serif;font-weight:500;font-size:0.9rem;">Home</a>
                <a href="cart.php" style="color:#D4AF37;text-decoration:none;font-family:Poppins,sans-serif;font-weight:500;font-size:0.9rem;position:relative;display:flex;align-items:center;gap:5px;">
                    Cart
                    <span id="cart-count" style="background:#D4AF37;color:#000;border-radius:50%;padding:2px 8px;font-size:0.75rem;font-weight:700;">0</span>
                </a>
                <?php if (isset($_SESSION['admin'])): ?>
                    <a href="admin/dashboard.php" style="color:#D4AF37;text-decoration:none;font-family:Poppins,sans-serif;font-weight:500;font-size:0.9rem;">Admin</a>
                    <a href="admin/logout.php" style="color:#fff;text-decoration:none;font-family:Poppins,sans-serif;font-weight:500;font-size:0.9rem;background:#333;padding:5px 12px;border-radius:4px;">Logout</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    
    <script>
    function updateCartCount() {
        const cart = JSON.parse(localStorage.getItem('pk_cart') || '[]');
        const count = cart.reduce((sum, item) => sum + item.qty, 0);
        const el = document.getElementById('cart-count');
        if (el) {
            el.textContent = count;
            el.style.display = count > 0 ? 'inline-block' : 'none';
        }
    }
    document.addEventListener('DOMContentLoaded', updateCartCount);
    </script>