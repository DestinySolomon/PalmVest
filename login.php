<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>PalmVest - Login</title>

 <!-- Google Fonts -->
 <link rel="preconnect" href="https://fonts.googleapis.com">
 <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
 <link href="https://fonts.googleapis.com/css2?family=Charm:wght@400;700&display=swap" rel="stylesheet">

 <!-- Bootstrap -->
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

 <!-- Icons -->
 <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

 <!-- Custom CSS -->
 <link rel="stylesheet" href="./assets/style.css">
</head>

<body>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-light bg-white">
  <div class="container">
    <a class="navbar-brand" href="index.html">PalmVest</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav mx-auto">
        <li class="nav-item"><a class="nav-link" href="index.html">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Services</a></li>
        <li class="nav-item"><a class="nav-link" href="#">About</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Portfolio</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Contact</a></li>
      </ul>

      <div class="d-flex">
        <a href="#" class="user-icon" data-bs-toggle="modal" data-bs-target="#authModal">
          <i class="bi bi-person"></i>
        </a>
      </div>
    </div>
  </div>
</nav>


  <!-- Authentication Modal -->
    <div
      class="modal fade"
      id="authModal"
      tabindex="-1"
      aria-labelledby="authModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
              aria-label="Close"
            ></button>
          </div>
          <div class="modal-body text-center">
            <div class="welcome-icon">
              <i class="bi bi-person-circle"></i>
            </div>
            <h3 class="welcome-title">Welcome to PalmVest</h3>
            <p class="welcome-text">
              Join our community of agricultural investors and start growing
              your wealth sustainably. Sign up to begin your investment journey
              or log in to access your account.
            </p>

            <div class="auth-buttons">
              <a href="login.html" class="btn login-btn auth-btn btn-lg"
                >Login</a
              >
              <a href="signup.html" class="btn signup-btn auth-btn btn-lg"
                >Sign Up</a
              >
            </div>
          </div>
        </div>
      </div>
    </div>

<div class="wrapper">

    <!-- LEFT COLUMN -->
    <div class="left-box">
        <div class="logo">PalmVest</div>
        <h2 class="left-title">Welcome Back</h2>
        <p class="left-text">
            Sign in to continue your investment journey and access your dashboard.
        </p>
    </div>

    
    <!-- RIGHT COLUMN -->
    <div class="right-box">

        <h2 class="signup-title">Login to Your Account</h2>

        <!-- SHOW ERROR IF WRONG LOGIN -->
        <?php if(isset($_SESSION['login_error'])): ?>
          <div class="alert alert-danger">
            <?php 
              echo $_SESSION['login_error']; 
              unset($_SESSION['login_error']);
            ?>
          </div>
        <?php endif; ?>

        <!-- SHOW SUCCESS AFTER REGISTER -->
        <?php if(isset($_SESSION['success'])): ?>
          <div class="alert alert-success">
            <?php 
              echo $_SESSION['success']; 
              unset($_SESSION['success']);
            ?>
          </div>
        <?php endif; ?>


        <form action="actions/login_action.php" method="POST" id="loginForm">

            <!-- EMAIL -->
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <!-- PASSWORD -->
            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" name="password" id="login_password" class="form-control" required>
                    <span class="input-group-text" onclick="togglePassword('login_password', this)">
                        <i class="bi bi-eye"></i>
                    </span>
                </div>
            </div>

            <button type="submit" class="signup-btn">Login</button>

            <div class="login-link text-center mt-3">
                Don't have an account? <a href="register.php">Create Account</a>
            </div>
        </form>

    </div>
</div>


<!-- Footer Section -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-main">
                <!-- Brand Section -->
                <div class="footer-brand">
                    <div class="footer-logo">PalmVest</div>
                    <p class="footer-description">
                        Professional investment management and wealth planning services designed to help you achieve your financial goals through proven strategies and expert guidance.
                    </p>
                </div>
                
                <!-- Services Section -->
                <div class="footer-section">
                    <h3>Services</h3>
                    <ul class="footer-links">
                        <li><a href="#">Portfolio Management</a></li>
                        <li><a href="#">Wealth Planning</a></li>
                        <li><a href="#">Market Research</a></li>
                        <li><a href="#">Risk Management</a></li>
                        <li><a href="#">Retirement Planning</a></li>
                    </ul>
                </div>
                
                <!-- Company Section -->
                <div class="footer-section">
                    <h3>Company</h3>
                    <ul class="footer-links">
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Our Team</a></li>
                        <li><a href="#">Careers</a></li>
                        <li><a href="#">News & Insights</a></li>
                        <li><a href="#">Contact Us</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Footer Bottom -->
            <div class="footer-bottom">
                <div class="copyright">
                    © 2025 PalmVest. All rights reserved. Website Builder
                </div>
                <div class="footer-legal">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                    <a href="#">Cookie Policy</a>
                </div>
            </div>
        </div>
    </footer>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="./assets/script.js"></script>
</body>
</html>
