<?php
$ref = isset($_GET['ref']) ? htmlspecialchars($_GET['ref']) : "";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PalmVest - Create Account</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #2e7d32;
            --secondary-color: #4caf50;
            --dark-color: #1b5e20;
            --light-color: #e8f5e9;
        }

        body {
            background: var(--light-color);
            font-family: "Poppins", sans-serif;
        }

        .wrapper {
            max-width: 1000px;
            margin: 60px auto;
            background: #fff;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            display: flex;
            flex-wrap: wrap;
        }

        /* LEFT */
        .left-box {
            flex: 1;
            min-width: 350px;
            padding: 50px;
            background: var(--primary-color);
            color: #fff;
            position: relative;
        }

        .logo {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 25px;
            display: block;
        }

        .left-title {
            font-size: 2.4rem;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .left-text {
            font-size: 1.1rem;
            opacity: 0.9;
            line-height: 1.6;
        }

        /* RIGHT */
        .right-box {
            flex: 1;
            min-width: 350px;
            padding: 50px;
        }

        .signup-title {
            font-size: 1.8rem;
            color: var(--primary-color);
            font-weight: 700;
        }

        .form-label {
            font-weight: 500;
            color: var(--dark-color);
        }

        .form-control {
            height: 48px;
            border-radius: 10px;
        }

        .input-group-text {
            background: #fff;
            border-left: none;
            cursor: pointer;
            border-radius: 0 10px 10px 0;
        }

        .strength-bar {
            height: 8px;
            width: 100%;
            background: #ddd;
            border-radius: 5px;
            margin-top: 5px;
            transition: .3s;
        }

        .strength-bar span {
            display: block;
            height: 100%;
            width: 0%;
            border-radius: 5px;
            transition: .3s;
        }

        .signup-btn {
            background: var(--primary-color);
            color: #fff;
            width: 100%;
            height: 50px;
            font-size: 1.1rem;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            transition: 0.3s;
        }

        .signup-btn:hover {
            background: var(--dark-color);
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
        }

        .login-link a {
            color: var(--primary-color);
            font-weight: 600;
            text-decoration: none;
        }

        .ref-box {
            background: var(--light-color);
            padding: 10px;
            border-left: 4px solid var(--secondary-color);
            color: var(--dark-color);
            border-radius: 5px;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>

<div class="wrapper">

    <!-- LEFT SIDE -->
    <div class="left-box">
        <div class="logo">PalmVest</div>

        <h2 class="left-title">Welcome to PalmVest</h2>
        <p class="left-text">
            Start your investment journey with confidence.  
            Stake virtual palm oil and earn returns in 30 days.  
            Simple, secure & profitable.
        </p>
    </div>

    <!-- RIGHT SIDE -->
    <div class="right-box">

        <h2 class="signup-title">Create Your Account</h2>
        <p class="text-muted mb-3">Join thousands already earning with PalmVest.</p>

        <?php if($ref != ""): ?>
            <div class="ref-box">
                ✅ Referral detected: <strong><?php echo $ref; ?></strong>
            </div>
        <?php endif; ?>

        <form action="actions/register_action.php" method="POST" id="signupForm">

            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" class="form-control" name="name" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" class="form-control" name="email" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Phone Number</label>
                <input type="tel" class="form-control" name="phone" required>
            </div>

            <!-- PASSWORD FIELD -->
            <div class="mb-2">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="password" name="password" required>
                    <span class="input-group-text" onclick="togglePassword('password', this)">
                        <i class="bi bi-eye"></i>
                    </span>
                </div>

                <!-- Strength Indicator -->
                <div class="strength-bar mt-2">
                    <span id="strengthIndicator"></span>
                </div>
            </div>

            <!-- CONFIRM PASSWORD -->
            <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="confirm_password" required>
                    <span class="input-group-text" onclick="togglePassword('confirm_password', this)">
                        <i class="bi bi-eye"></i>
                    </span>
                </div>
                <small id="matchText" class="text-danger"></small>
            </div>

            <input type="hidden" name="referral_code" value="<?php echo $ref; ?>">

            <button type="submit" class="signup-btn">Create Account</button>

            <div class="login-link">
                Already have an account? <a href="login.php">Login</a>
            </div>
        </form>

    </div>
</div>


<!-- BOOTSTRAP JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>



</body>
</html>
