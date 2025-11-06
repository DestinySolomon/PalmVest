<?php
require_once "./config/db.php";

// Detect referral if passed in URL
$ref = "";
$refType = "";  // "user" or "admin"

if (isset($_GET['ref']) && trim($_GET['ref']) !== "") {
    $ref = htmlspecialchars(trim($_GET['ref']));

    // Check if referral is from a user
    $checkUserRef = $conn->prepare("SELECT id FROM users WHERE referral_code = ?");
    $checkUserRef->bind_param("s", $ref);
    $checkUserRef->execute();
    $userRefResult = $checkUserRef->get_result();

    if ($userRefResult->num_rows > 0) {
        $refType = "user";
    } else {
        // Check if admin-generated
        $checkAdminRef = $conn->prepare("SELECT id FROM referral_codes WHERE code = ?");
        $checkAdminRef->bind_param("s", $ref);
        $checkAdminRef->execute();
        $adminRefResult = $checkAdminRef->get_result();

        if ($adminRefResult->num_rows > 0) {
            $refType = "admin";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PalmVest - Create Account</title>
 <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Charm:wght@400;700&family=Monsieur+La+Doulaise&display=swap" rel="stylesheet">
  <!-- Bootstrap & Icons -->
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
 <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Bootstrap Icons -->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css"
    />
<link rel="stylesheet" href="./assets/style.css">
   
</head>

<body>

  <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white">
      <div class="container">
        <!-- Logo on the left -->
        <a class="navbar-brand" href="index.html">PalmVest</a>

        <button
          class="navbar-toggler"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#navbarNav"
        >
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
          <!-- Menu items in the center -->
          <ul class="navbar-nav mx-auto">
            <li class="nav-item">
              <a class="nav-link" href="index.html">Home</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#">Services</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#">About</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#">Portfolio</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#">Contact</a>
            </li>
          </ul>

          <!-- User icon on the right -->
          <div class="d-flex">
            <a
              href="#"
              class="user-icon"
              data-bs-toggle="modal"
              data-bs-target="#authModal"
            >
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

    <!-- LEFT -->
    <div class="left-box">
        <div class="logo">PalmVest</div>

        <h2 class="left-title">Welcome to PalmVest</h2>
        <p class="left-text">
            PalmVest is a referral-driven investment platform.  
            You need a valid referral to join and start earning returns.
        </p>
    </div>

    
    <!-- RIGHT -->
    <div class="right-box">

        <h2 class="signup-title">Create Your Account</h2>
        <p class="text-muted">Referral Required ✅</p>

        <!-- REFERRAL NOTICES -->
        <?php if ($ref !== "" && $refType === "user"): ?>
            <div class="ref-box ref-user" id="refNotice">
                ✅ Referral Detected — Joined via an investor: <strong><?php echo $ref; ?></strong>
            </div>
        <?php elseif ($ref !== "" && $refType === "admin"): ?>
            <div class="ref-box ref-admin" id="refNotice">
                ✅ Admin Invite Code Accepted: <strong><?php echo $ref; ?></strong>
            </div>
        <?php endif; ?>

        <form action="actions/register_action.php" method="POST" id="signupForm">

            <!-- NAME -->
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <!-- EMAIL -->
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <!-- PHONE -->
            <div class="mb-3">
                <label class="form-label">Phone Number</label>
                <input type="tel" name="phone" class="form-control" required>
            </div>

            <!-- PASSWORD -->
            <div class="mb-2">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="password" name="password" required>
                    <span class="input-group-text" onclick="togglePassword('password', this)">
                        <i class="bi bi-eye"></i>
                    </span>
                </div>
                <!-- you deleted strength-bar here -->
                <div class="mt-2">
                  <span id="passwordStrength"></span>
               </div>

            </div>

            <!-- CONFIRM PASSWORD -->
            <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <div class="input-group">
                    <input type="password" id="confirm_password" class="form-control" required>
                    <span class="input-group-text" onclick="togglePassword('confirm_password', this)">
                        <i class="bi bi-eye"></i>
                    </span>
                </div>
                <small id="matchText" class="text-danger"></small>
            </div>

            <!-- REFERRAL (REQUIRED ✅) -->
            <div class="mb-3">
                <label class="form-label">Referral Code *</label>
                <input type="text" name="referral_code" id="referral_code" class="form-control"
                       value="<?php echo $ref; ?>" required>
            </div>

            <button type="submit" class="signup-btn">Create Account</button>

            <div class="login-link text-center mt-3">
                Already have an account? <a href="login.php">Login</a>
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
