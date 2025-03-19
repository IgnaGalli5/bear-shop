document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const menuToggle = document.getElementById('menuToggle');
    const menu = document.querySelector('.menu');
    
    if (menuToggle && menu) {
        menuToggle.addEventListener('click', function() {
            menu.classList.toggle('active');
        });
    }
    
    // Close menu when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.menu') && !event.target.closest('#menuToggle') && menu.classList.contains('active')) {
            menu.classList.remove('active');
        }
    });
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 80,
                    behavior: 'smooth'
                });
                
                // Close mobile menu if open
                if (menu.classList.contains('active')) {
                    menu.classList.remove('active');
                }
            }
        });
    });
    
    // Add to cart functionality
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productName = this.parentElement.querySelector('h3').textContent;
            
            // Create and show notification
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.innerHTML = `<p>${productName} agregado al carrito</p>`;
            document.body.appendChild(notification);
            
            // Remove notification after 3 seconds
            setTimeout(() => {
                notification.classList.add('fade-out');
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 500);
            }, 3000);
        });
    });
    
    // Add notification styles
    const style = document.createElement('style');
    style.textContent = `
        .notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: rgb(148, 90, 66);
            color: white;
            padding: 15px 25px;
            border-radius: 4px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            animation: slideIn 0.5s ease;
        }
        
        .notification.fade-out {
            animation: fadeOut 0.5s ease forwards;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes fadeOut {
            from {
                opacity: 1;
            }
            to {
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
    
    // Form submission
    const contactForm = document.querySelector('.contact-form form');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const nameInput = this.querySelector('#name');
            const emailInput = this.querySelector('#email');
            const messageInput = this.querySelector('#message');
            
            // Simple validation
            if (nameInput.value && emailInput.value && messageInput.value) {
                // Show success message
                const formSuccess = document.createElement('div');
                formSuccess.className = 'form-success';
                formSuccess.innerHTML = '<p>¡Mensaje enviado con éxito! Nos pondremos en contacto contigo pronto.</p>';
                
                // Replace form with success message
                this.innerHTML = '';
                this.appendChild(formSuccess);
            } else {
                // Show error message for empty fields
                const inputs = [nameInput, emailInput, messageInput];
                inputs.forEach(input => {
                    if (!input.value) {
                        input.classList.add('error');
                    } else {
                        input.classList.remove('error');
                    }
                });
            }
        });
        
        // Remove error class on input
        const formInputs = contactForm.querySelectorAll('input, textarea');
        formInputs.forEach(input => {
            input.addEventListener('input', function() {
                if (this.value) {
                    this.classList.remove('error');
                }
            });
        });
    }
    
    // Newsletter subscription
    const newsletterForm = document.querySelector('.footer-newsletter form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const emailInput = this.querySelector('input[type="email"]');
            if (emailInput && emailInput.value) {
                // Show success message
                const successMessage = document.createElement('p');
                successMessage.textContent = '¡Gracias por suscribirte!';
                successMessage.style.color = 'rgb(238, 200, 163)';
                
                this.innerHTML = '';
                this.appendChild(successMessage);
            }
        });
    }
    
    // Add styles for form validation
    const validationStyle = document.createElement('style');
    validationStyle.textContent = `
        .error {
            border: 2px solid #ff3860 !important;
            background-color: rgba(255, 56, 96, 0.1);
        }
        
        .form-success {
            background-color: rgba(238, 200, 163, 0.2);
            border: 1px solid rgb(148, 90, 66);
            padding: 20px;
            border-radius: 4px;
            text-align: center;
            color: rgb(148, 90, 66);
        }
    `;
    document.head.appendChild(validationStyle);
});