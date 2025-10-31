<?php
session_start();
if (isset($_SESSION['email'])) {
  header("Location: front.php");
  exit();
}

$fullname = "";
$shop_name = "";
$email = "";
$password = "";
$confirm_password = "";

$fullname_error = "";
$shop_name_error = "";
$email_error = "";
$password_error = "";
$confirm_password_error = "";

$error = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $fullname = htmlspecialchars($_POST['fullname']);
  $shop_name = htmlspecialchars($_POST['shop_name']);
  $email = htmlspecialchars($_POST['email']);
  $password = htmlspecialchars($_POST['password']);
  $confirm_password = htmlspecialchars($_POST['confirm_password']);

  if (empty($fullname)) {
    $fullname_error = "Full name is required";
    $error = true;
  }

  if (empty($shop_name)) {
    $shop_name_error = "Shop name is required";
    $error = true;
  }

  if (!preg_match('/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/', $email)) {
    $email_error = "Email format is not valid";
    $error = true;
  }

  if (strlen($password) < 6) {
    $password_error = "Password must have at least 6 characters";
    $error = true;
  }

  if ($confirm_password != $password) {
    $confirm_password_error = "Password and Confirm Password do not match";
    $error = true;
  }

  require_once "db.php";

  if (!$error) {
    $stmt = $conn->prepare("SELECT admin_id FROM admin WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
      $email_error = "Email is already in use.";
      $error = true;
    }
    $stmt->close();
  }

  if (!$error) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Note: You may need to add shop_name column to your admin table
    $stmt = $conn->prepare("INSERT INTO admin (fullname, shop_name, email, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $fullname, $shop_name, $email, $hashed_password);
    $stmt->execute();
    $user_id = $stmt->insert_id;
    $stmt->close();
    $_SESSION['admin_id'] = $user_id;
    $_SESSION['fullname'] = $fullname;
    $_SESSION['shop_name'] = $shop_name;
    $_SESSION['email'] = $email;

    header("location: front.php");
    exit();
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Signup Page</title>
  <style>
    body,
    html {
      margin: 0;
      padding: 0;
      font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
      background: #f4f7fb;
      color: #1a2233;
      min-height: 100vh;
    }

    .login-wrapper {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(120deg, #e3ecfc 0%, #f4f7fb 100%);
      padding: 40px 20px;
    }

    .login-card {
      display: flex;
      flex-direction: row;
      background: #fff;
      border-radius: 22px;
      box-shadow: 0 8px 32px rgba(44, 62, 80, 0.13), 0 2px 8px rgba(44, 62, 80, 0.06);
      overflow: hidden;
      max-width: 860px;
      width: 100%;
      border: 1.5px solid #e6eaf3;
    }

    .card-left {
      background: linear-gradient(135deg, #e3ecfc 0%, #b6d0fa 100%);
      color: #2563eb;
      flex: 1.1;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: flex-start;
      padding: 40px 36px;
      position: relative;
      min-width: 260px;
    }

    .card-left::before {
      content: "";
      position: absolute;
      top: 24px;
      right: 24px;
      width: 48px;
      height: 48px;
      background: rgba(37, 99, 235, 0.08);
      border-radius: 50%;
      z-index: 0;
    }

    .card-left h1 {
      font-size: 2.2rem;
      margin-bottom: 14px;
      font-weight: 800;
      letter-spacing: -1px;
      z-index: 1;
    }

    .card-left h3 {
      font-size: 1rem;
      font-weight: 400;
      opacity: 0.85;
      margin-bottom: 0;
      z-index: 1;
    }

    .card-right {
      flex: 1.4;
      padding: 40px 36px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      background: #fff;
    }

    .card-right h2 {
      font-size: 1.8rem;
      font-weight: 700;
      margin-bottom: 8px;
      color: #2563eb;
      letter-spacing: -1px;
    }

    .card-right .subtitle {
      color: #6b7a99;
      font-size: 0.95rem;
      margin-bottom: 24px;
    }

    .card-right form {
      display: flex;
      flex-direction: column;
      gap: 4px;
    }

    .form-group {
      margin-bottom: 6px;
    }

    .card-right form input[type="text"],
    .card-right form input[type="email"],
    .card-right form input[type="password"] {
      width: 100%;
      padding: 12px 16px;
      border: 1.5px solid #dbeafe;
      border-radius: 10px;
      font-size: 1rem;
      background: #f7faff;
      color: #1a2233;
      transition: border 0.2s, box-shadow 0.2s;
      box-shadow: 0 1px 2px rgba(37, 99, 235, 0.03);
      box-sizing: border-box;
    }

    .card-right form input:focus {
      border-color: #2563eb;
      outline: none;
      background: #fff;
      box-shadow: 0 0 0 2px #b6d0fa;
    }

    .error-text {
      color: #dc2626;
      font-size: 0.85rem;
      margin-top: 3px;
      margin-bottom: 0;
      display: block;
      min-height: 18px;
    }

    .card-right button[type="submit"] {
      background: linear-gradient(90deg, #2563eb 60%, #4f8cff 100%);
      color: #fff;
      border: none;
      border-radius: 10px;
      padding: 13px 0;
      font-size: 1.1rem;
      font-weight: 700;
      cursor: pointer;
      margin-top: 8px;
      transition: background 0.18s, box-shadow 0.18s;
      box-shadow: 0 2px 12px rgba(37, 99, 235, 0.10);
      letter-spacing: 0.5px;
    }

    .card-right button[type="submit"]:hover {
      background: linear-gradient(90deg, #1746a2 60%, #2563eb 100%);
      box-shadow: 0 4px 16px rgba(37, 99, 235, 0.13);
    }

    .form-footer {
      margin-top: 16px;
      text-align: center;
    }

    .form-footer a {
      color: #2563eb;
      text-decoration: none;
      font-weight: 600;
      transition: text-decoration 0.15s, color 0.15s;
    }

    .form-footer a:hover {
      text-decoration: underline;
      color: #1746a2;
    }

    @media (max-width: 900px) {
      .login-card {
        flex-direction: column;
        max-width: 98vw;
      }

      .card-left,
      .card-right {
        padding: 36px 16px;
      }

      .card-left {
        align-items: center;
        text-align: center;
        min-width: unset;
      }
    }

    @media (max-width: 600px) {
      .login-card {
        border-radius: 0;
        box-shadow: none;
      }

      .card-left,
      .card-right {
        padding: 22px 6vw;
      }
    }
  </style>
</head>

<body>
  <div class="login-wrapper">
    <div class="login-card">
      <div class="card-left">
        <h1>Join EasyInventory</h1>
        <h3>Create your account to start managing inventory effortlessly.</h3>
      </div>
      <div class="card-right">
        <h2>Sign Up</h2>
        <p class="subtitle">Fill in your details to create an account</p>
        <form method="POST" action="signup.php">
          <div class="form-group">
            <input type="text" name="fullname" placeholder="Full Name" value="<?= htmlspecialchars($fullname) ?>" required />
            <span class="error-text"><?= $fullname_error ?></span>
          </div>
          
          <div class="form-group">
            <input type="text" name="shop_name" placeholder="Shop's Name" value="<?= htmlspecialchars($shop_name) ?>" required />
            <span class="error-text"><?= $shop_name_error ?></span>
          </div>

          <div class="form-group">
            <input type="email" name="email" placeholder="Email Address" value="<?= htmlspecialchars($email) ?>" required />
            <span class="error-text"><?= $email_error ?></span>
          </div>

          <div class="form-group">
            <input type="password" name="password" placeholder="Password" required />
            <span class="error-text"><?= $password_error ?></span>
          </div>

          <div class="form-group">
            <input type="password" name="confirm_password" placeholder="Confirm Password" required />
            <span class="error-text"><?= $confirm_password_error ?></span>
          </div>

          <button type="submit">SIGN UP</button>

          <div class="form-footer">
            <span>Already have an account? <a href="login.php">Login</a></span>
          </div>
        </form>
      </div>
    </div>
  </div>
</body>

</html>

