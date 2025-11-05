  // Character counter for textarea at the home page
        const textarea = document.querySelector('textarea');
        const charCount = document.querySelector('.char-count');
        
        textarea.addEventListener('input', function() {
            const length = this.value.length;
            charCount.textContent = `${length}/300 characters`;
        });




     // Scroll event listener
document.addEventListener('DOMContentLoaded', function() {
    // Navbar scroll effect
    const navbar = document.querySelector('.navbar');
    const backToTopButton = document.getElementById('backToTop');

    // Navbar scroll effect
    function handleScroll() {
        // Navbar background change on scroll
        if (window.scrollY > 100) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }

        // Back to top button visibility
        if (window.scrollY > 300) {
            backToTopButton.classList.add('show');
        } else {
            backToTopButton.classList.remove('show');
        }
    }

    // Back to top functionality
    function scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    // Event listeners
    window.addEventListener('scroll', handleScroll);
    backToTopButton.addEventListener('click', scrollToTop);

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Character counter for investment form
    const textarea = document.querySelector('textarea');
    const charCount = document.querySelector('.char-count');
    
    if (textarea && charCount) {
        textarea.addEventListener('input', function() {
            const length = this.value.length;
            charCount.textContent = `${length}/300 characters`;
        });
    }

    // Investment form submission
    const investmentForm = document.querySelector('.invest-section form');
    if (investmentForm) {
        investmentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const formData = new FormData(this);
            const name = formData.get('name') || document.querySelector('input[type="text"]').value;
            const email = formData.get('email') || document.querySelector('input[type="email"]').value;
            
            // Simple validation
            if (!name || !email) {
                alert('Please fill in all required fields.');
                return;
            }

            // Simulate form submission
            alert('Thank you for your interest! We will contact you shortly with investment information.');
            this.reset();
            
            // Reset character count
            if (charCount) {
                charCount.textContent = '0/300 characters';
            }
        });
    }

    // Add loading animation to investment cards
    const investmentCards = document.querySelectorAll('.investment-card');
    investmentCards.forEach(card => {
        card.addEventListener('click', function() {
            const button = this.querySelector('.invest-btn');
            const originalText = button.textContent;
            
            button.textContent = 'Processing...';
            button.disabled = true;
            
            setTimeout(() => {
                button.textContent = originalText;
                button.disabled = false;
                alert('Investment process started! You will be redirected to the payment page.');
            }, 1500);
        });
    });

    // Initialize with scroll check
    handleScroll();
});

// Register Form 

    // ✅ Show/Hide Password
    function togglePassword(id, icon) {
        const field = document.getElementById(id);
        const eye = icon.querySelector("i");

        if (field.type === "password") {
            field.type = "text";
            eye.classList.remove("bi-eye");
            eye.classList.add("bi-eye-slash");
        } else {
            field.type = "password";
            eye.classList.remove("bi-eye-slash");
            eye.classList.add("bi-eye");
        }
    }

    // ✅ Password Strength Checker
    const password = document.getElementById("password");
    const bar = document.getElementById("strengthIndicator");

    password.addEventListener("input", () => {
        const val = password.value;
        let strength = 0;

        if (val.length >= 6) strength++;
        if (/[A-Z]/.test(val)) strength++;
        if (/[0-9]/.test(val)) strength++;
        if (/[^A-Za-z0-9]/.test(val)) strength++;

        if (strength === 0) { bar.style.width = "0%"; }
        if (strength === 1) { bar.style.width = "25%"; bar.style.background = "red"; }
        if (strength === 2) { bar.style.width = "50%"; bar.style.background = "orange"; }
        if (strength === 3) { bar.style.width = "75%"; bar.style.background = "gold"; }
        if (strength === 4) { bar.style.width = "100%"; bar.style.background = "green"; }
    });

    // ✅ Confirm Password Match
    const confirmPassword = document.getElementById("confirm_password");
    const matchText = document.getElementById("matchText");

    confirmPassword.addEventListener("input", () => {
        if (confirmPassword.value !== password.value) {
            matchText.textContent = "Passwords do not match";
        } else {
            matchText.textContent = "";
        }
    });
