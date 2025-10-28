<?php
session_start();
if (isset($_SESSION['email'])) {
  header("Location: front.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EasyInventory</title>
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: #fff;
      color: #333;
    }

    /* Navbar */
    nav {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 50px;
      background: #fff;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      position: sticky;
      top: 0;
      z-index: 1000;
    }

    .logo {
      font-weight: bold;
      font-size: 26px;
    }

    .logo span {
      color: #0073e6;
    }

    .nav-links {
      display: flex;
      gap: 20px;
      align-items: center;
    }

    .nav-links a {
      text-decoration: none;
      color: #333;
      font-weight: 500;
      transition: color 0.3s ease;
    }

    .nav-links a:hover {
      color: #0073e6;
    }

    .login-btn {
      background: #0073e6;
      color: white;
      border: none;
      padding: 6px 15px;
      border-radius: 5px;
      cursor: pointer;
      font-size: 14px;
      transition: transform 0.2s ease, background 0.3s ease;
    }

    .login-btn:hover {
      background: #005bb5;
      transform: scale(1.05);
    }

    /* Hero Section */
    .hero {
      padding: 100px 20px;
      text-align: center;
      background: linear-gradient(135deg, #f9fbff, #eef6ff);
    }

    .hero h1 {
      font-size: 40px;
      font-weight: bold;
      margin-bottom: 15px;
    }

    .hero p {
      max-width: 600px;
      margin: 0 auto 20px auto;
      font-size: 19px;
      line-height: 1.5;
      color: #555;
    }

    .hero .btn {
      display: inline-block;
      background: #0073e6;
      color: white;
      text-decoration: none;
      padding: 12px 25px;
      border-radius: 6px;
      font-weight: bold;
      transition: background 0.3s ease, transform 0.2s ease;
    }

    .hero .btn:hover {
      background: #005bb5;
      transform: translateY(-3px);
    }

    /* Features Section */
    .features-section {
      padding: 60px 20px;
      text-align: center;
      background: #f9fafb;
    }

    .features-section h2 {
      font-size: 28px;
      font-weight: bold;
      margin-bottom: 10px;
    }

    .features-section p {
      color: #666;
      font-size: 16px;
      margin-bottom: 40px;
    }

    .features-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 30px;
      max-width: 1100px;
      margin: 0 auto;
    }

    .feature-card {
      background: #fff;
      border-radius: 12px;
      padding: 25px;
      text-align: left;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .feature-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
      background: #e8f2f8;
      /* background turns blue */

    }

    .feature-card:hover .feature-icon {
      color: #ffdd57;
      /* icon changes to yellow for contrast */
    }

    .feature-icon {
      font-size: 28px;
      color: #0073e6;
      margin-bottom: 10px;
      display: inline-block;
    }

    .feature-card h3 {
      font-size: 22px;
      margin-bottom: 8px;
      font-weight: bold;
    }

    .feature-card p {
      font-size: 19px;
      color: #555;
      line-height: 1.5;
    }

    /* Footer */
    footer {
      background: #111;
      color: #bbb;
      padding: 30px 20px;
      text-align: center;
      margin-top: 40px;
    }

    footer a {
      color: #bbb;
      margin: 0 10px;
      text-decoration: none;
      transition: color 0.3s ease;
    }

    footer a:hover {
      color: #fff;
    }

    footer p {
      margin-top: 10px;
      font-size: 14px;
    }
  </style>
</head>

<body>

  <!-- Navbar -->
  <nav>
    <div class="logo">Easy<span>Inventory</span></div>
    <div class="nav-links">
      <a href="#">Home</a>
      <a href="#features">About</a>
      <a href="#contact">Contact</a>
      <button class="login-btn" onclick="location.href='login.php'">Login</button>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="hero">
    <h1>Inventory management software designed for small businesses</h1>
    <p>Manage orders. Track inventory. Handle billing.
      One inventory management software to run all your inventory operations.</p>
    <a href="signup.php" class="btn">Get Started</a>

  </section>

  <!-- Features Section -->
  <section class="features-section" id="features">
    <h2>Powerful Features to Streamline Your Inventory</h2>
    <p>Designed for small businesses, our software offers everything you need</p>

    <div class="features-grid">
      <div class="feature-card">
        <div class="feature-icon">🔐</div>
        <h3>User Authentication</h3>
        <p>Secure login, role-based access control, and data protection for all users.</p>
      </div>

      <div class="feature-card">
        <div class="feature-icon">📦</div>
        <h3>Product Management</h3>
        <p>Add, edit, and categorize products, SKUs, and detailed descriptions.</p>
      </div>

      <div class="feature-card">
        <div class="feature-icon">📊</div>
        <h3>Stock Tracking</h3>
        <p>Real-time updates on stock quantity adjustments, and warehouse transfers.</p>
      </div>

      <div class="feature-card">
        <div class="feature-icon">🔎</div>
        <h3>Search & Filter</h3>
        <p>Quickly find products using keyword, suppliers, or custom tags.</p>
      </div>

      <div class="feature-card">
        <div class="feature-icon">📑</div>
        <h3>Reports Generation</h3>
        <p>Generate sales reports, stock movement history and custom analytics.</p>
      </div>

      <div class="feature-card">
        <div class="feature-icon">🔔</div>
        <h3>Low Stock Alerts</h3>
        <p>Receive automated notifications when product quantities drop below set thresholds.</p>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer id="contact">
    <div>
      <a href="#">Privacy Policy</a> |
      <a href="#">Terms of Service</a> |
      <a href="#contact">Contact</a>
    </div>
    <p>© 2025 EasyInventory. All rights reserved.</p>
  </footer>

</body>

</html>
