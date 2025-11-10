<?php
session_start();
require_once "./config/db.php";

$message = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    
    if (empty($email)) {
        $error = "Please enter your email address.";
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT id, name FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Generate reset token
            $token = bin2hex(random_bytes(50));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Store token in database
            $tokenStmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
            $tokenStmt->bind_param("sss", $email, $token, $expires);
            
          if ($tokenStmt->execute()) {
    // Get the correct base URL
          $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
          $host = $_SERVER['HTTP_HOST'];
          $script_path = dirname($_SERVER['SCRIPT_NAME']); // Gets the directory of current script
    
         // Build the correct reset link
       $reset_link = $protocol . "://" . $host . $script_path . "/reset_password.php?token=" . $token;
       $message = "Password reset link generated: <a href='$reset_link' target='_blank'>Click here to reset password</a><br><br>
               <strong>Full link:</strong> $reset_link<br><br>
               <strong>Note:</strong> In production, this link would be sent to your email.";
      }else {
                $error = "Error generating reset token. Please try again.";
            }
        } else {
            $error = "No account found with that email address.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PalmVest - Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="./assets/style.css">
</head>
<body>
    <!-- Navigation (same as your register.php) -->


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

<!--  Login Modal-->
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
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <h2 class="text-center mb-4">Forgot Password</h2>
                        <p class="text-muted text-center">Enter your email address and we'll send you a link to reset your password.</p>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($message): ?>
                            <div class="alert alert-info"><?php echo $message; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="forgot_password.php">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required 
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Send Reset Link</button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-3">
                            <a href="login.php" class="text-decoration-none">Back to Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>