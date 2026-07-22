<?php
// admin/login.php
require_once '../config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT * FROM admin WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            if (password_verify($password, $admin['password'])) {
                $_SESSION['admin'] = $admin['id'];
                redirect('dashboard.php');
            } else {
                $error = 'Invalid credentials';
            }
        } else {
            $error = 'Invalid credentials';
        }
        $stmt->close();
    } else {
        $error = 'Please fill in all fields';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - PK Premium</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: #000;
            color: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: #111;
            padding: 40px;
            border-radius: 16px;
            border: 1px solid #333;
            width: 100%;
            max-width: 400px;
            margin: 20px;
        }
        .login-card h2 {
            text-align: center;
            color: #D4AF37;
            margin-bottom: 25px;
        }
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #D4AF37;
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #333;
            background: #000;
            color: #fff;
            font-size: 0.95rem;
        }
        .btn-primary {
            width: 100%;
            background: #D4AF37;
            color: #000;
            padding: 14px;
            border: none;
            border-radius: 30px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            background: #b8942e;
        }
        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            background: rgba(220,53,69,0.1);
            color: #dc3545;
            border: 1px solid #dc3545;
        }
        .brand {
            text-align: center;
            margin-bottom: 20px;
        }
        .brand img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid #D4AF37;
            margin: 0 auto 10px;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="brand">
            <img src="https://ik.imagekit.io/pkstores/IMG-20260722-WA0345.jpg" alt="PK Premium">
            <h2>PK PREMIUM</h2>
        </div>
        
        <?php if ($error): ?>
            <div class="alert"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required autofocus>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn-primary">Login</button>
        </form>
    </div>
</body>
</html>
