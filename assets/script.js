// =========================
// HOME PAGE TEXTAREA COUNTER (SAFE)
// =========================
const textarea = document.querySelector("textarea");
const charCount = document.querySelector(".char-count");

if (textarea && charCount) {
  textarea.addEventListener("input", function () {
    const length = this.value.length;
    charCount.textContent = `${length}/300 characters`;
  });
}

// =================================
// NAVBAR + BACK TO TOP (SAFE)
// =================================
document.addEventListener("DOMContentLoaded", function () {
  const navbar = document.querySelector(".navbar");
  const backToTopButton = document.getElementById("backToTop");

  if (navbar && backToTopButton) {
    function handleScroll() {
      if (window.scrollY > 100) navbar.classList.add("scrolled");
      else navbar.classList.remove("scrolled");

      if (window.scrollY > 300) backToTopButton.classList.add("show");
      else backToTopButton.classList.remove("show");
    }

    window.addEventListener("scroll", handleScroll);
    handleScroll();

    backToTopButton.addEventListener("click", () => {
      window.scrollTo({ top: 0, behavior: "smooth" });
    });
  }

  // Smooth scrolling for anchor links
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      const target = document.querySelector(this.getAttribute("href"));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: "smooth", block: "start" });
      }
    });
  });

  // Investment form validation (SAFE)
  const investmentForm = document.querySelector(".invest-section form");
  if (investmentForm) {
    investmentForm.addEventListener("submit", function (e) {
      e.preventDefault();

      const formData = new FormData(this);
      const name = formData.get("name");
      const email = formData.get("email");

      if (!name || !email) {
        alert("Please fill in all required fields.");
        return;
      }

      alert("Thank you! We will contact you shortly.");
      this.reset();

      if (charCount) charCount.textContent = "0/300 characters";
    });
  }

  // Investment card loading animation (SAFE)
  const investmentCards = document.querySelectorAll(".investment-card");
  if (investmentCards.length > 0) {
    investmentCards.forEach((card) => {
      card.addEventListener("click", function () {
        const button = this.querySelector(".invest-btn");
        const original = button.textContent;

        button.textContent = "Processing...";
        button.disabled = true;

        setTimeout(() => {
          button.textContent = original;
          button.disabled = false;
          alert("Investment process started!");
        }, 1500);
      });
    });
  }
});

// =====================================
// REGISTER PAGE JS
// =====================================

// Auto-hide referral notice
const refNotice = document.getElementById("refNotice");
if (refNotice) {
  setTimeout(() => {
    refNotice.style.transition = "opacity 1s";
    refNotice.style.opacity = "0";
    setTimeout(() => refNotice.remove(), 1000);
  }, 4000);
}

// Show/Hide password
function togglePassword(id, iconElement) {
  const field = document.getElementById(id);
  const icon = iconElement.querySelector("i");

  if (!field || !icon) return;

  if (field.type === "password") {
    field.type = "text";
    icon.classList.replace("bi-eye", "bi-eye-slash");
  } else {
    field.type = "password";
    icon.classList.replace("bi-eye-slash", "bi-eye");
  }
}

// Password strength
const passwordField = document.getElementById("password");
const strengthText = document.getElementById("passwordStrength");

if (passwordField && strengthText) {
  passwordField.addEventListener("input", function () {
    const pass = passwordField.value;

    if (!pass) {
      strengthText.textContent = "";
      strengthText.style.color = "";
      return;
    }

    if (pass.length < 6) {
      strengthText.textContent = "Weak";
      strengthText.style.color = "red";
    } else if (
      pass.match(/[0-9]/) &&
      pass.match(/[A-Z]/) &&
      pass.match(/[a-z]/) &&
      pass.length >= 8
    ) {
      strengthText.textContent = "Strong";
      strengthText.style.color = "green";
    } else {
      strengthText.textContent = "Medium";
      strengthText.style.color = "orange";
    }
  });
}

// Confirm password
const confirmPasswordField = document.getElementById("confirm_password");
const matchText = document.getElementById("matchText");
const signupForm = document.getElementById("signupForm");

if (confirmPasswordField && matchText) {
  confirmPasswordField.addEventListener("input", () => {
    matchText.textContent =
      confirmPasswordField.value !== passwordField.value
        ? "Passwords do not match"
        : "";
  });
}

if (signupForm && confirmPasswordField && passwordField) {
  signupForm.addEventListener("submit", function (e) {
    if (confirmPasswordField.value !== passwordField.value) {
      e.preventDefault();
      matchText.textContent = "Passwords do not match";
      confirmPasswordField.focus();
    }
  });
}

// error message on buy modal 
// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert.auto-hide');
    
    alerts.forEach(function(alert) {
        const delay = alert.getAttribute('data-bs-delay') || 5000;
        
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, delay);
    });
});