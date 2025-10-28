<?php
session_start();
if (isset($_SESSION["email"])) {
  header("Location: front.php");
  exit();
}


$user_email = "";
$error = "";
if ($_SERVER['REQUEST_METHOD'] == "POST") {
  $user_email = htmlspecialchars($_POST['email']);
  $password = htmlspecialchars($_POST['password']);
  require_once "db.php";
  $stmt = $conn->prepare("SELECT admin_id,fullname,email,password FROM admin WHERE email=?");
  $stmt->bind_param("s", $user_email);
  $stmt->execute();
  $stmt->bind_result($admin_id, $fullname, $email, $hashed_password);
  if ($stmt->fetch()) {
    if (password_verify($password, $hashed_password)) {
      session_start();
      $_SESSION['admin_id'] = $admin_id;
      $_SESSION['fullname'] = $fullname;
      $_SESSION['email'] = $email;
      header("Location: front.php");
      exit();
    }
  }
  $stmt->close();
  $error = "Invalid email or password";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login Page</title>
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
      padding: 54px 44px;
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
      font-size: 2.6rem;
      margin-bottom: 18px;
      font-weight: 800;
      letter-spacing: -1px;
      z-index: 1;
    }

    .card-left h3 {
      font-size: 1.15rem;
      font-weight: 400;
      opacity: 0.85;
      margin-bottom: 0;
      z-index: 1;
    }

    .card-right {
      flex: 1.4;
      padding: 54px 44px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      background: #fff;
    }

    .card-right h2 {
      font-size: 2.1rem;
      font-weight: 700;
      margin-bottom: 10px;
      color: #2563eb;
      letter-spacing: -1px;
    }

    .card-right .subtitle {
      color: #6b7a99;
      font-size: 1.05rem;
      margin-bottom: 32px;
    }

    .card-right form {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .card-right form input[type="text"],
    .card-right form input[type="email"],
    .card-right form input[type="password"] {
      width: 100%;
      padding: 15px 18px;
      margin-bottom: 14px;
      border: 1.5px solid #dbeafe;
      border-radius: 10px;
      font-size: 1.05rem;
      background: #f7faff;
      color: #1a2233;
      transition: border 0.2s, box-shadow 0.2s;
      box-shadow: 0 1px 2px rgba(37, 99, 235, 0.03);
    }

    .card-right form input:focus {
      border-color: #2563eb;
      outline: none;
      background: #fff;
      box-shadow: 0 0 0 2px #b6d0fa;
    }

    .card-right .remember {
      display: flex;
      align-items: center;
      margin-bottom: 10px;
      gap: 8px;
    }

    .card-right .remember input[type="checkbox"] {
      accent-color: #2563eb;
      width: 18px;
      height: 18px;
    }

    .card-right button[type="submit"] {
      background: linear-gradient(90deg, #2563eb 60%, #4f8cff 100%);
      color: #fff;
      border: none;
      border-radius: 10px;
      padding: 15px 0;
      font-size: 1.15rem;
      font-weight: 700;
      cursor: pointer;
      margin-top: 10px;
      transition: background 0.18s, box-shadow 0.18s;
      box-shadow: 0 2px 12px rgba(37, 99, 235, 0.10);
      letter-spacing: 0.5px;
    }

    .card-right button[type="submit"]:hover {
      background: linear-gradient(90deg, #1746a2 60%, #2563eb 100%);
      box-shadow: 0 4px 16px rgba(37, 99, 235, 0.13);
    }

    .form-footer {
      margin-top: 22px;
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
        <h1>Welcome back to EasyInventory</h1>
        <h3>An Inventory Management Software to run all your inventory operations.</h3>
      </div>
      <div class="card-right">
        <h2>Login</h2>
        <p class="subtitle">Login to access your account</p>
        <?php if ($error) { ?>
          <div class="error_message_login">
            <p class="p_login" style="text-align: center;color: red;"><strong><?= $error ?></strong></p>
          </div>
        <?php } ?>
        <form action="login.php" method="POST">
          <input type="text" name="email" placeholder="Email" required />
          <input type="password" name="password" placeholder="Password" required />
          <div class="remember">
            <input type="checkbox" id="remember" name="remember" />
            <label for="remember">Remember me</label>
          </div>
          <button type="submit">LOGIN</button>
          <div class="form-footer">
            <span>New User? <a href="signup.php">Signup</a></span>
          </div>
        </form>
      </div>
    </div>
  </div>
</body>

</html>
