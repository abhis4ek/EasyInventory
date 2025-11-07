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
  <title>EasyInventory - Inventory Management for Small Businesses</title>
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
      padding: 8px 18px;
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
    }

    .feature-card:hover .feature-icon {
      color: #ffdd57;
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
      font-size: 16px;
      color: #555;
      line-height: 1.5;
    }

    /* Footer */
    footer {
      background: #1a2332;
      color: #bbb;
      padding: 50px 20px 30px;
      margin-top: 40px;
    }

    .footer-content {
      max-width: 1100px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 40px;
      margin-bottom: 30px;
    }

    .footer-section h3 {
      color: #fff;
      font-size: 18px;
      margin-bottom: 15px;
    }

    .footer-section p {
      line-height: 1.8;
      font-size: 14px;
    }

    .footer-section ul {
      list-style: none;
      padding: 0;
    }

    .footer-section ul li {
      margin-bottom: 10px;
    }

    .footer-section ul li a {
      color: #bbb;
      text-decoration: none;
      transition: color 0.3s ease;
    }

    .footer-section ul li a:hover {
      color: #0073e6;
    }

    .footer-bottom {
      border-top: 1px solid #333;
      padding-top: 20px;
      text-align: center;
      font-size: 14px;
    }

    .social-links {
      display: flex;
      gap: 15px;
      margin-top: 15px;
    }

    .social-links a {
      width: 40px;
      height: 40px;
      background: #2a3544;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #bbb;
      text-decoration: none;
      transition: background 0.3s ease, color 0.3s ease;
    }

    .social-links a:hover {
      background: #0073e6;
      color: #fff;
    }

    @media (max-width: 768px) {
      .features-grid {
        grid-template-columns: 1fr;
      }

      .hero h1 {
        font-size: 32px;
      }

      nav {
        padding: 15px 20px;
      }

      .nav-links {
        gap: 10px;
      }
    }
  </style>
</head>

<body>

  <!-- Navbar -->
  <nav>
    <div class="logo">Easy<span>Inventory</span></div>
    <div class="nav-links">
      <a href="#home">Home</a>
      <a href="#features">Features</a>
      <a href="#contact">Contact</a>
      <button class="login-btn" onclick="location.href='login.php'">Login</button>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="hero" id="home">
    <h1>Inventory Management Made Simple</h1>
    <p>Manage inventory, track sales and purchases, monitor stock levels, and generate reports - all in one easy-to-use platform designed for small businesses.</p>
    <a href="signup.php" class="btn">Get Started Free</a>
  </section>

  <!-- Features Section -->
  <section class="features-section" id="features">
    <h2>Everything You Need to Manage Your Inventory</h2>
    <p>Powerful features designed specifically for small business owners</p>

    <div class="features-grid">
      <div class="feature-card">
        <div class="feature-icon">📦</div>
        <h3>Product Management</h3>
        <p>Add, edit, and organize products with categories, pricing (including MRP support), stock levels, and supplier information.</p>
      </div>

      <div class="feature-card">
        <div class="feature-icon">📊</div>
        <h3>Stock Tracking</h3>
        <p>Real-time inventory tracking with automatic stock updates. Get instant visibility into what's in stock, low stock, or out of stock.</p>
      </div>

      <div class="feature-card">
        <div class="feature-icon">🔔</div>
        <h3>Low Stock Alerts</h3>
        <p>Automated notifications when inventory drops below safe levels. Never run out of stock unexpectedly again.</p>
      </div>

      <div class="feature-card">
        <div class="feature-icon">💰</div>
        <h3>Sales & Purchases</h3>
        <p>Record sales and purchase transactions, generate invoices, and automatically update inventory levels with each transaction.</p>
      </div>

      <div class="feature-card">
        <div class="feature-icon">👥</div>
        <h3>Customer & Supplier Management</h3>
        <p>Maintain detailed records of customers and suppliers with complete contact information and transaction history.</p>
      </div>

      <div class="feature-card">
        <div class="feature-icon">📈</div>
        <h3>Reports & Analytics</h3>
        <p>Generate profit/loss reports, track monthly performance, identify top-selling products, and make data-driven decisions.</p>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer id="contact">
    <div class="footer-content">
      <div class="footer-section">
        <h3>About EasyInventory</h3>
        <p>EasyInventory is a simple yet powerful inventory management system designed specifically for small businesses. Manage your inventory, track sales, and grow your business with ease.</p>
        <div class="social-links">
          <a href="#" title="Facebook">f</a>
          <a href="#" title="Twitter">𝕏</a>
          <a href="#" title="LinkedIn">in</a>
        </div>
      </div>

      <div class="footer-section">
        <h3>Quick Links</h3>
        <ul>
          <li><a href="#home">Home</a></li>
          <li><a href="#features">Features</a></li>
          <li><a href="signup.php">Sign Up</a></li>
          <li><a href="login.php">Login</a></li>
        </ul>
      </div>

      <div class="footer-section">
        <h3>Support</h3>
        <ul>
          <li><a href="mailto:support@easyinventory.com">Contact Support</a></li>
          <li><a href="#">Documentation</a></li>
          <li><a href="#">Privacy Policy</a></li>
          <li><a href="#">Terms of Service</a></li>
        </ul>
      </div>

      <div class="footer-section">
        <h3>Contact Info</h3>
        <p><strong>Email:</strong><br>support@easyinventory.com</p>
        <p><strong>Location:</strong><br>Shillong, Meghalaya, India</p>
      </div>
    </div>

    <div class="footer-bottom">
      <p>&copy; 2025 EasyInventory. All rights reserved. Built with ❤️ for small businesses.</p>
    </div>
  </footer>

</body>

</html>