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
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const btnText = submitBtn.querySelector('.btn-text');
    const originalText = btnText ? btnText.textContent : submitBtn.textContent;
    const originalHTML = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.disabled = true;
    if (btnText) {
        btnText.textContent = 'Sending...';
    } else {
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    }
    
    // EmailJS Configuration - All configured! ✅
    const EMAILJS_PUBLIC_KEY = 'avg4OaHFLEedx-T11';
    const EMAILJS_SERVICE_ID = 'victor_gmail';
    const EMAILJS_TEMPLATE_ID = 'victor_tempID';
    const RECIPIENT_EMAIL = 'www44victor@gmail.com';
    
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
        if (btnText) {
            btnText.textContent = originalText;
        } else {
            submitBtn.innerHTML = originalHTML;
        }
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

// Mobile Menu Toggle
function toggleMobileMenu() {
    const mobileNav = document.getElementById('mobileNav');
    const body = document.body;
    
    if (mobileNav) {
        const isActive = mobileNav.classList.contains('active');
        if (isActive) {
            mobileNav.classList.remove('active');
            body.style.overflow = '';
        } else {
            mobileNav.classList.add('active');
            body.style.overflow = 'hidden';
        }
    }
}

// Sidebar Toggle for Dashboard Pages
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    const body = document.body;
    
    if (sidebar) {
        sidebar.classList.toggle('mobile-open');
        if (backdrop) {
            backdrop.classList.toggle('active');
        }
        if (window.innerWidth <= 1024) {
            if (sidebar.classList.contains('mobile-open')) {
                body.classList.add('sidebar-open');
            } else {
                body.classList.remove('sidebar-open');
            }
        }
    }
}

// Close sidebar when clicking backdrop
function setupSidebarBackdrop() {
    const backdrop = document.getElementById('sidebarBackdrop');
    if (backdrop) {
        backdrop.addEventListener('click', () => {
            const sidebar = document.getElementById('sidebar');
            if (sidebar && sidebar.classList.contains('mobile-open')) {
                toggleSidebar();
            }
        });
    }
}

// Close mobile menu when clicking on a link
function closeMobileMenu() {
    const mobileNav = document.getElementById('mobileNav');
    const body = document.body;
    
    if (mobileNav && mobileNav.classList.contains('active')) {
        mobileNav.classList.remove('active');
        body.style.overflow = '';
    }
}

// Intersection Observer for smooth scroll animations
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('animate');
            // Optional: Unobserve after animation to improve performance
            // observer.unobserve(entry.target);
        }
    });
}, observerOptions);

function setupScrollAnimations() {
    const animateElements = document.querySelectorAll(
        '.service-card, .project-preview-card, .testimonial-card-main, .about-feature, .skill-icon-item, .stat-item, .contact-info-item, .dashboard-card, .service-card-dashboard, .project-card-dashboard, .widget-card, .process-step, .timeline-item, .testimonial-card, .faq-item, .contact-item-dashboard, .contact-form-dashboard, .about-image-frame, .hero-image-wrapper, .skills-image-wrapper, .contact-image-wrapper'
    );
    
    animateElements.forEach(element => {
        element.classList.add('animate-on-load');
        observer.observe(element);
        
        // Check if element is already in viewport on load
        const rect = element.getBoundingClientRect();
        const isInViewport = rect.top < window.innerHeight && rect.bottom > 0;
        if (isInViewport) {
            // Small delay to ensure smooth animation
            setTimeout(() => {
                element.classList.add('animate');
            }, 100);
        }
    });
}

function handleScroll() {
    const scrollPosition = window.scrollY;
    const header = document.querySelector('.main-header');
    
    // Add shadow to header on scroll
    if (header) {
        if (scrollPosition > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    }
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
    
    // Setup scroll animations with Intersection Observer
    setupScrollAnimations();
    
    // Mobile menu toggle - Initialize immediately
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const mobileNavClose = document.querySelector('.mobile-nav-close');
    const mobileNav = document.getElementById('mobileNav');
    const mobileNavLinks = document.querySelectorAll('.mobile-nav .nav-link');
    
    // Toggle button
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleMobileMenu();
        });
    }
    
    // Close button
    if (mobileNavClose) {
        mobileNavClose.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleMobileMenu();
        });
    }
    
    // Close when clicking links
    if (mobileNavLinks.length > 0) {
        mobileNavLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                // If it's an anchor link, close menu after scroll
                if (href && href.startsWith('#')) {
                    e.preventDefault();
                    const targetId = href.substring(1);
                    const targetElement = document.getElementById(targetId);
                    if (targetElement) {
                        toggleMobileMenu();
                        setTimeout(() => {
                            targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }, 300);
                    }
                } else {
                    // For regular links, close menu immediately
                    toggleMobileMenu();
                }
            });
        });
    }
    
    // Close when clicking backdrop (outside menu)
    if (mobileNav) {
        mobileNav.addEventListener('click', function(e) {
            if (e.target === mobileNav) {
                toggleMobileMenu();
            }
        });
    }
    
    // Sidebar toggle for dashboard pages
    const sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }
    
    // Setup sidebar backdrop
    setupSidebarBackdrop();
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

// Scroll events with throttling for better performance
let ticking = false;
function onScroll() {
    if (!ticking) {
        window.requestAnimationFrame(() => {
            handleScroll();
            ticking = false;
        });
        ticking = true;
    }
}
window.addEventListener('scroll', onScroll, { passive: true });

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