// DOM Elements
const body = document.body;
const toggleSwitch = document.querySelector('.theme-switch input[type="checkbox"]');
const themeIcon = document.querySelector('.theme-switch-wrapper em i');
const nav = document.querySelector('nav');
const burger = document.querySelector('.burger');
const navLinks = document.querySelector('.nav-links');
const navLinksItems = document.querySelectorAll('.nav-links li');
const contactForm = document.getElementById('contact-form');
const filterBtns = document.querySelectorAll('.filter-btn');
const projectCards = document.querySelectorAll('.project-card');

// Check for saved theme in localStorage
const currentTheme = localStorage.getItem('theme');

// Functions
function setTheme(isDark) {
    if (isDark) {
        body.setAttribute('data-theme', 'dark');
        toggleSwitch.checked = true;
        if (themeIcon) {
            themeIcon.classList.remove('fa-moon');
            themeIcon.classList.add('fa-sun');
        }
    } else {
        body.removeAttribute('data-theme');
        toggleSwitch.checked = false;
        if (themeIcon) {
            themeIcon.classList.remove('fa-sun');
            themeIcon.classList.add('fa-moon');
        }
    }
}

function toggleNav() {
    // Toggle navigation menu
    navLinks.classList.toggle('nav-active');
    burger.classList.toggle('toggle');
    body.classList.toggle('menu-open');
    
    // Animate Links with staggered delay
    navLinksItems.forEach((link, index) => {
        if (link.style.animation) {
            link.style.animation = '';
            link.style.opacity = '0';
            link.style.transform = 'translateY(20px)';
        } else {
            link.style.animation = `navLinkFade 0.5s ease forwards ${index / 7 + 0.3}s`;
            link.style.opacity = '1';
            link.style.transform = 'translateY(0)';
        }
    });
    
    // Prevent scrolling when menu is open
    if (body.classList.contains('menu-open')) {
        document.documentElement.style.overflow = 'hidden';
    } else {
        document.documentElement.style.overflow = '';
    }
}

function filterProjects(category) {
    projectCards.forEach(card => {
        const cardCategory = card.getAttribute('data-category');
        
        if (category === 'all' || cardCategory === category) {
            card.style.display = 'block';
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 100);
        } else {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            setTimeout(() => {
                card.style.display = 'none';
            }, 300);
        }
    });
}

async function handleContactFormSubmit(e) {
    e.preventDefault();
    
    // Get form values
    const name = document.getElementById('name').value;
    const email = document.getElementById('email').value;
    const subject = document.getElementById('subject').value;
    const message = document.getElementById('message').value;
    
    // Get submit button to show loading state
    const submitBtn = e.target.querySelector('.submit-btn');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnIcon = submitBtn.querySelector('.btn-icon');
    const originalText = btnText.textContent;
    
    // Show loading state
    submitBtn.disabled = true;
    btnText.textContent = 'Sending...';
    btnIcon.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    // EmailJS Configuration
    // IMPORTANT: You need to replace these values with your EmailJS credentials
    // See EMAILJS_SETUP.md file for detailed setup instructions
    // 1. Sign up at https://www.emailjs.com/ (free account)
    // 2. Create an email service (Gmail, Outlook, etc.)
    // 3. Create an email template
    // 4. Get your Public Key, Service ID, and Template ID from EmailJS dashboard
    // 5. Replace the values below:
    
    const EMAILJS_PUBLIC_KEY = 'YOUR_PUBLIC_KEY'; // Replace with your EmailJS Public Key
    const EMAILJS_SERVICE_ID = 'YOUR_SERVICE_ID'; // Replace with your EmailJS Service ID
    const EMAILJS_TEMPLATE_ID = 'YOUR_TEMPLATE_ID'; // Replace with your EmailJS Template ID
    const RECIPIENT_EMAIL = 'www44victor@gmail.com'; // Your email address
    
    // Check if EmailJS is configured
    if (EMAILJS_PUBLIC_KEY === 'YOUR_PUBLIC_KEY' || 
        EMAILJS_SERVICE_ID === 'YOUR_SERVICE_ID' || 
        EMAILJS_TEMPLATE_ID === 'YOUR_TEMPLATE_ID') {
        submitBtn.disabled = false;
        btnText.textContent = originalText;
        btnIcon.innerHTML = '<i class="fas fa-paper-plane"></i>';
        showFormMessage('error', 'EmailJS is not configured yet. Please check EMAILJS_SETUP.md for setup instructions.');
        return;
    }
    
    try {
        // Initialize EmailJS with your Public Key
        if (typeof emailjs !== 'undefined') {
            emailjs.init(EMAILJS_PUBLIC_KEY);
        } else {
            throw new Error('EmailJS library not loaded. Please check your internet connection.');
        }
        
        // Send email using EmailJS
        const response = await emailjs.send(
            EMAILJS_SERVICE_ID,
            EMAILJS_TEMPLATE_ID,
            {
                to_email: RECIPIENT_EMAIL,
                from_name: name,
                from_email: email,
                subject: subject,
                message: message,
                reply_to: email
            }
        );
        
        // Success - show success message
        showFormMessage('success', 'Thank you for your message! I will get back to you soon.');
        
        // Reset form
        e.target.reset();
        
    } catch (error) {
        // Error - show error message
        console.error('EmailJS Error:', error);
        showFormMessage('error', 'Sorry, there was an error sending your message. Please try again or contact me directly at www44victor@gmail.com');
    } finally {
        // Reset button state
        submitBtn.disabled = false;
        btnText.textContent = originalText;
        btnIcon.innerHTML = '<i class="fas fa-paper-plane"></i>';
    }
}

// Function to show success/error messages
function showFormMessage(type, message) {
    // Remove any existing messages
    const existingMessage = document.querySelector('.form-message');
    if (existingMessage) {
        existingMessage.remove();
    }
    
    // Create message element
    const messageDiv = document.createElement('div');
    messageDiv.className = `form-message form-message-${type}`;
    messageDiv.textContent = message;
    
    // Insert message before the form
    const form = document.getElementById('contact-form');
    form.parentNode.insertBefore(messageDiv, form);
    
    // Auto-remove message after 5 seconds
    setTimeout(() => {
        messageDiv.style.opacity = '0';
        messageDiv.style.transform = 'translateY(-10px)';
        setTimeout(() => {
            messageDiv.remove();
        }, 300);
    }, 5000);
}

function animateSkillBars() {
    const skillLevels = document.querySelectorAll('.skill-level');
    
    skillLevels.forEach(skill => {
        const width = skill.style.width;
        skill.style.width = '0';
        
        setTimeout(() => {
            skill.style.width = width;
        }, 300);
    });
}

function handleScroll() {
    const scrollPosition = window.scrollY;
    
    // Add shadow to header on scroll
    if (scrollPosition > 50) {
        nav.parentElement.classList.add('scrolled');
    } else {
        nav.parentElement.classList.remove('scrolled');
    }
    
    // Animate elements when they come into view
    const animateElements = document.querySelectorAll('.service-card, .project-card, .contact-item');
    
    animateElements.forEach(element => {
        const elementPosition = element.getBoundingClientRect().top;
        const screenPosition = window.innerHeight / 1.3;
        
        if (elementPosition < screenPosition) {
            element.classList.add('animate');
        }
    });
}

// Event Listeners
document.addEventListener('DOMContentLoaded', () => {
    // Apply saved theme or default to light
    if (currentTheme === 'dark') {
        setTheme(true);
    } else {
        // Ensure icon is set correctly for default light theme
        if (themeIcon) {
            themeIcon.classList.remove('fa-sun');
            themeIcon.classList.add('fa-moon');
        }
    }
    
    // Animate skill bars if they exist on the page
    if (document.querySelector('.skill-level')) {
        animateSkillBars();
    }
    
    // Add animation class to elements that should animate on page load
    document.querySelectorAll('.service-card, .project-card, .contact-item').forEach(element => {
        element.classList.add('animate-on-load');
    });
});

// Theme switch event
if (toggleSwitch) {
    toggleSwitch.addEventListener('change', function() {
        if (this.checked) {
            localStorage.setItem('theme', 'dark');
            setTheme(true);
        } else {
            localStorage.setItem('theme', 'light');
            setTheme(false);
        }
    });
}

// Mobile navigation event
if (burger) {
    burger.addEventListener('click', toggleNav);
    
    // Close mobile menu when clicking on backdrop
    const navBackdrop = document.querySelector('.nav-backdrop');
    if (navBackdrop) {
        navBackdrop.addEventListener('click', () => {
            if (navLinks.classList.contains('nav-active')) {
                toggleNav();
            }
        });
    }
    
    // Close mobile menu when clicking on a nav link
    navLinksItems.forEach(item => {
        item.addEventListener('click', () => {
            if (navLinks.classList.contains('nav-active')) {
                toggleNav();
            }
        });
    });
}

// Project filter events
if (filterBtns.length > 0) {
    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            // Remove active class from all buttons
            filterBtns.forEach(b => b.classList.remove('active'));
            
            // Add active class to clicked button
            btn.classList.add('active');
            
            // Filter projects
            const category = btn.getAttribute('data-filter');
            filterProjects(category);
        });
    });
}

// Contact form submission
if (contactForm) {
    contactForm.addEventListener('submit', handleContactFormSubmit);
}

// Scroll events
window.addEventListener('scroll', handleScroll);

// Add CSS animation class
document.body.classList.add('css-animations-enabled');

// Add keyframe animation for nav links (used in mobile menu)
const style = document.createElement('style');
style.textContent = `
    @keyframes navLinkFade {
        from {
            opacity: 0;
            transform: translateX(50px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    .animate-on-load {
        opacity: 0;
        transform: translateY(20px);
        transition: opacity 0.5s ease, transform 0.5s ease;
    }
    
    .animate, .animate-on-load {
        opacity: 1;
        transform: translateY(0);
    }
    
    .scrolled {
        box-shadow: 0 5px 15px var(--shadow-color);
    }
`;
document.head.appendChild(style);