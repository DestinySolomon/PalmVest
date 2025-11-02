  // Character counter for textarea at the home page
        const textarea = document.querySelector('textarea');
        const charCount = document.querySelector('.char-count');
        
        textarea.addEventListener('input', function() {
            const length = this.value.length;
            charCount.textContent = `${length}/300 characters`;
        });