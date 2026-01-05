// DOM Elements
const body = document.body;
const themeToggleBtn = document.getElementById('themeToggle');
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
        if (themeToggleBtn) {
            const icon = themeToggleBtn.querySelector('i');
            if (icon) {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
            }
        }
    } else {
        body.removeAttribute('data-theme');
        if (themeToggleBtn) {
            const icon = themeToggleBtn.querySelector('i');
            if (icon) {
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
            }
        }
    }
}

function toggleTheme() {
    const isDark = body.getAttribute('data-theme') === 'dark';
    const newTheme = isDark ? 'light' : 'dark';
    localStorage.setItem('theme', newTheme);
    setTheme(!isDark);
}

function toggleNav() {
    // Toggle navigation menu
    if (!navLinks || !burger) return;
    
    navLinks.classList.toggle('nav-active');
    burger.classList.toggle('toggle');
    body.classList.toggle('menu-open');
    
    // Animate Links with staggered delay
    if (navLinksItems && navLinksItems.length > 0) {
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
    }
    
    // Prevent scrolling when menu is open
    if (body.classList.contains('menu-open')) {
        document.documentElement.style.overflow = 'hidden';
    } else {
        document.documentElement.style.overflow = '';
    }
}

function filterProjects(category) {
    if (!projectCards || projectCards.length === 0) return;
    
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
            body.classList.remove('menu-open');
        } else {
            mobileNav.classList.add('active');
            body.style.overflow = 'hidden';
            body.classList.add('menu-open');
        }
    }
}

// Sidebar Toggle for Dashboard Pages
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    const body = document.body;
    const sidebarToggle = document.getElementById('sidebarToggle');
    
    if (sidebar) {
        const isOpening = !sidebar.classList.contains('mobile-open');
        sidebar.classList.toggle('mobile-open');
        
        if (backdrop) {
            backdrop.classList.toggle('active');
        }
        
        if (window.innerWidth <= 1024) {
            if (isOpening) {
                body.classList.add('sidebar-open');
                body.style.overflow = 'hidden';
    } else {
                body.classList.remove('sidebar-open');
                body.style.overflow = '';
            }
        }
        
        // Update toggle button icon/animation
        if (sidebarToggle) {
            const icon = sidebarToggle.querySelector('i');
            if (icon) {
                if (isOpening) {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-times');
                } else {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
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
        // Also remove any backdrop or overlay
        document.body.classList.remove('menu-open');
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
    // Enhanced observer with better options
    const enhancedObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                // Add delay based on index for staggered effect
                setTimeout(() => {
                    entry.target.classList.add('animate');
                }, index * 50);
                // Unobserve after animation for performance
                enhancedObserver.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -100px 0px'
    });
    
    const animateElements = document.querySelectorAll(
        '.service-card, .project-preview-card, .testimonial-card-main, .about-feature, .skill-icon-item, .stat-item, .contact-info-item, .dashboard-card, .service-card-dashboard, .project-card-dashboard, .widget-card, .process-step, .timeline-item, .testimonial-card, .faq-item, .contact-item-dashboard, .contact-form-dashboard, .about-image-frame, .hero-image-wrapper, .skills-image-wrapper, .contact-image-wrapper, .welcome-banner, .cta-card, .dashboard-header, .projects-grid-dashboard, .dashboard-grid, .contact-info-dashboard, .header-nav, .search-box, .notification-btn, .profile-pic'
    );
    
    animateElements.forEach((element, index) => {
        element.classList.add('animate-on-load');
        enhancedObserver.observe(element);
        
        // Check if element is already in viewport on load
        const rect = element.getBoundingClientRect();
        const isInViewport = rect.top < window.innerHeight && rect.bottom > 0;
        if (isInViewport) {
            // Small delay to ensure smooth animation
            setTimeout(() => {
            element.classList.add('animate');
            }, 100 + (index * 50));
        }
    });
    
    // Also observe project image wrappers
    const projectImageWrappers = document.querySelectorAll('.project-image-wrapper');
    projectImageWrappers.forEach(wrapper => {
        wrapper.classList.add('animate-on-load');
        enhancedObserver.observe(wrapper);
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
        setTheme(false);
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
                if (!href) return;
                
                // If it's an anchor link on the same page, close menu and scroll
                if (href.startsWith('#')) {
                    e.preventDefault();
                    const targetId = href.substring(1);
                    const targetElement = document.getElementById(targetId);
                    if (targetElement) {
                        // Close menu first
                        toggleMobileMenu();
                        // Then scroll after a short delay to allow menu to close
                        setTimeout(() => {
                            targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }, 300);
                    } else {
                        // If target not found, just close menu
                        toggleMobileMenu();
                    }
                } else {
                    // For regular page links (like projects.html, contact.html)
                    // Close menu immediately - navigation will happen via href
                    toggleMobileMenu();
                    // Don't prevent default - let the browser navigate
                    // The menu will be closed before navigation happens
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
    
    // Sidebar toggle for dashboard pages (projects.html, contact.html)
    const sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        // Remove any existing listeners by cloning
        const newToggle = sidebarToggle.cloneNode(true);
        sidebarToggle.parentNode.replaceChild(newToggle, sidebarToggle);
        
        // Add fresh event listener
        newToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            toggleSidebar();
        });
        
        // Also add touch event for better mobile support
        newToggle.addEventListener('touchend', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleSidebar();
        });
    }
    
    // Setup sidebar backdrop
    setupSidebarBackdrop();
    
    // Setup sidebar icon links - ensure they work properly
    const sidebarIcons = document.querySelectorAll('.sidebar-icon');
    if (sidebarIcons.length > 0) {
        sidebarIcons.forEach(icon => {
            icon.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (!href) return;
                
                // Close sidebar on mobile before navigation
                const isMobile = window.innerWidth <= 1024;
                const sidebar = document.getElementById('sidebar');
                
                if (isMobile && sidebar && sidebar.classList.contains('mobile-open')) {
                    // Close sidebar immediately
                    toggleSidebar();
                }
                
                // If it's an anchor link, handle scrolling
                if (href.includes('#')) {
                    const parts = href.split('#');
                    const pagePath = parts[0];
                    const targetId = parts[1];
                    
                    // Check if we need to navigate to a different page
                    const currentPage = window.location.pathname.split('/').pop() || 'index.html';
                    const targetPage = pagePath || currentPage;
                    
                    if (targetPage !== currentPage && pagePath) {
                        // Different page - let browser navigate, then scroll after load
                        // Don't prevent default - let navigation happen
                        // Store target ID to scroll after page load
                        if (targetId) {
                            sessionStorage.setItem('scrollToId', targetId);
                        }
                    } else if (targetId) {
                        // Same page - prevent default and scroll
                        e.preventDefault();
                        setTimeout(() => {
                            const targetElement = document.getElementById(targetId);
                            if (targetElement) {
                                targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
                            }
                        }, isMobile ? 400 : 100);
                    }
                } else {
                    // Regular page link - let browser navigate naturally
                    // Sidebar already closed on mobile
                }
            });
        });
    }
    
    // Handle scroll to target after page load (for cross-page anchor links)
    const scrollToId = sessionStorage.getItem('scrollToId');
    if (scrollToId) {
        sessionStorage.removeItem('scrollToId');
        setTimeout(() => {
            const targetElement = document.getElementById(scrollToId);
            if (targetElement) {
                targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }, 300);
    }
    
    // Ensure "View Project" buttons are clickable
    const viewProjectButtons = document.querySelectorAll('.btn-primary[href]');
    viewProjectButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href && href !== '#' && href !== 'contact.html') {
                // Allow navigation to external links
                // Don't prevent default
            }
        });
    });
    
    // Ensure header nav links work
    const headerNavLinks = document.querySelectorAll('.header-nav .nav-item');
    headerNavLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (!href) return;
            
            if (href.includes('#')) {
                const parts = href.split('#');
                const pagePath = parts[0];
                const targetId = parts[1];
                const currentPage = window.location.pathname.split('/').pop() || 'index.html';
                
                // If it's a link to a different page with anchor, let browser navigate
                if (pagePath && pagePath !== currentPage) {
                    // Store target ID for scroll after navigation
                    if (targetId) {
                        sessionStorage.setItem('scrollToId', targetId);
                    }
                    // Let browser navigate naturally
                } else if (targetId) {
                    // Same page anchor link - prevent default and scroll
                    e.preventDefault();
                    const targetElement = document.getElementById(targetId);
                    if (targetElement) {
                        targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }
            }
            // For regular page links without anchors, let browser navigate naturally
        });
    });
    
    // Testimonials Carousel
    setupTestimonialsCarousel();
    
    // Load project cover images
    loadProjectCoverImages();
});

// Function to load project cover images automatically
async function loadProjectCoverImages() {
    const projectCards = document.querySelectorAll('.project-card-dashboard');
    
    // Manual image mapping - you can add your project images here
    // You can use screenshot services, upload images, or use og:image from the sites
    const projectImageMap = {
        'https://securehrorg.onrender.com/': null, // Add image URL here or leave null to auto-fetch
        'https://tradepalorg.netlify.app': null, // Add image URL here or leave null to auto-fetch
        'https://moviesgo-lfu1.onrender.com/': null, // Add image URL here or leave null to auto-fetch
    };
    
    // Process each card
    for (const card of projectCards) {
        const viewProjectLink = card.querySelector('.btn-primary[href]');
        if (!viewProjectLink) continue;
        
        const projectUrl = viewProjectLink.getAttribute('href');
        if (!projectUrl || projectUrl === '#' || projectUrl === 'contact.html') continue;
        
        const imageWrapper = card.querySelector('.project-image-wrapper');
        if (!imageWrapper) continue;
        
        // Check if image already exists
        if (imageWrapper.querySelector('img')) continue;
        
        // Check manual mapping first
        let imageUrl = projectImageMap[projectUrl];
        
        // If no manual mapping, try to fetch og:image
        if (!imageUrl) {
            try {
                imageUrl = await fetchOgImage(projectUrl);
            } catch (error) {
                console.log('Could not fetch og:image for:', projectUrl);
            }
        }
        
        // If we have an image URL, load it
        if (imageUrl) {
            loadProjectImage(imageWrapper, imageUrl, card);
            // Add small delay between requests to avoid rate limiting
            await new Promise(resolve => setTimeout(resolve, 500));
        }
    }
}

// Function to load and display project image
function loadProjectImage(imageWrapper, imageUrl, card) {
    const placeholder = imageWrapper.querySelector('.project-image-placeholder');
    if (!placeholder) return;
    
    const img = document.createElement('img');
    img.src = imageUrl;
    img.alt = card.querySelector('h3')?.textContent || 'Project Preview';
    img.classList.add('loading');
    
    img.onerror = () => {
        // If image fails to load, keep placeholder
        img.remove();
        console.log('Failed to load image:', imageUrl);
    };
    
    img.onload = () => {
        img.classList.remove('loading');
        img.classList.add('loaded');
        // Fade out placeholder
        setTimeout(() => {
            placeholder.classList.add('fade-out');
        }, 300);
    };
    
    // Insert image before placeholder
    imageWrapper.insertBefore(img, placeholder);
}

// Function to fetch og:image from a URL using CORS proxy
async function fetchOgImage(url) {
    try {
        // Use a CORS proxy to fetch the page HTML
        const proxyUrl = `https://api.allorigins.win/get?url=${encodeURIComponent(url)}`;
        const response = await fetch(proxyUrl, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        });
        
        if (!response.ok) throw new Error('Failed to fetch');
        
        const data = await response.json();
        
        if (data.contents) {
            const parser = new DOMParser();
            const doc = parser.parseFromString(data.contents, 'text/html');
            
            // Try to find og:image
            const ogImage = doc.querySelector('meta[property="og:image"]');
            if (ogImage) {
                let imageUrl = ogImage.getAttribute('content');
                // Handle relative URLs
                if (imageUrl && !imageUrl.startsWith('http')) {
                    try {
                        const urlObj = new URL(url);
                        imageUrl = new URL(imageUrl, urlObj.origin).href;
                    } catch (e) {
                        console.log('Error parsing image URL');
                    }
                }
                return imageUrl;
            }
        }
    } catch (error) {
        console.log('Error fetching og:image:', error);
    }
    
    return null;
}

// Ensure sidebar toggle works on page load for dashboard pages
window.addEventListener('load', () => {
    const sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle && !sidebarToggle.hasAttribute('data-initialized')) {
        sidebarToggle.setAttribute('data-initialized', 'true');
        sidebarToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleSidebar();
        });
    }
});

// Testimonials Carousel Functionality
function setupTestimonialsCarousel() {
    const carousel = document.getElementById('testimonialsCarousel');
    const prevBtn = document.getElementById('prevTestimonial');
    const nextBtn = document.getElementById('nextTestimonial');
    const dots = document.querySelectorAll('.carousel-dots .dot');
    const slides = document.querySelectorAll('.testimonial-slide');
    
    if (!carousel || slides.length === 0) return;
    
    let currentSlide = 0;
    let autoSlideInterval;
    
    function showSlide(index) {
        // Remove active class from all slides and dots
        slides.forEach(slide => slide.classList.remove('active'));
        dots.forEach(dot => dot.classList.remove('active'));
        
        // Add active class to current slide and dot
        if (slides[index]) {
            slides[index].classList.add('active');
        }
        if (dots[index]) {
            dots[index].classList.add('active');
        }
        
        currentSlide = index;
    }
    
    function nextSlide() {
        const next = (currentSlide + 1) % slides.length;
        showSlide(next);
    }
    
    function prevSlide() {
        const prev = (currentSlide - 1 + slides.length) % slides.length;
        showSlide(prev);
    }
    
    function startAutoSlide() {
        autoSlideInterval = setInterval(nextSlide, 5000); // Auto-slide every 5 seconds
    }
    
    function stopAutoSlide() {
        if (autoSlideInterval) {
            clearInterval(autoSlideInterval);
        }
    }
    
    // Event listeners
    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            stopAutoSlide();
            nextSlide();
            startAutoSlide();
        });
    }
    
    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            stopAutoSlide();
            prevSlide();
            startAutoSlide();
        });
    }
    
    // Dot navigation
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            stopAutoSlide();
            showSlide(index);
            startAutoSlide();
        });
    });
    
    // Start auto-slide
    startAutoSlide();
    
    // Pause on hover
    if (carousel) {
        carousel.addEventListener('mouseenter', stopAutoSlide);
        carousel.addEventListener('mouseleave', startAutoSlide);
    }
}

// Theme toggle event
if (themeToggleBtn) {
    themeToggleBtn.addEventListener('click', toggleTheme);
}

// Mobile navigation event
if (burger) {
    burger.addEventListener('click', toggleNav);
    
    // Close mobile menu when clicking on backdrop
    const navBackdrop = document.querySelector('.nav-backdrop');
    if (navBackdrop && navLinks) {
        navBackdrop.addEventListener('click', () => {
            if (navLinks.classList.contains('nav-active')) {
                toggleNav();
            }
        });
    }
    
    // Close mobile menu when clicking on a nav link
    if (navLinksItems && navLinksItems.length > 0 && navLinks) {
    navLinksItems.forEach(item => {
        item.addEventListener('click', () => {
            if (navLinks.classList.contains('nav-active')) {
                toggleNav();
            }
        });
    });
    }
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