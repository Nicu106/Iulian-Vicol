import './bootstrap';
import '../css/app.css';

// Simple Animations
class SimpleAnimations {
    constructor() {
        this.observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        this.init();
    }
    
    init() {
        this.setupPreloader();
        this.setupBannerAnimations();
        this.setupScrollObservers();
        this.setupWowEffects();
        this.setupNavEffects();
    }
    
    setupPreloader() {
        // Add loading class to body
        document.body.classList.add('tech-loading');
        
        // Remove loading class when page is loaded
        window.addEventListener('load', () => {
            setTimeout(() => {
                document.body.classList.remove('tech-loading');
                document.body.classList.add('loaded');
                this.triggerBannerAnimations();
            }, 500);
        });
    }
    
    setupBannerAnimations() {
        // Banner animations will be triggered after page load
        this.bannerElements = {
            content: document.querySelector('.tech-hero-content'),
            title: document.querySelector('.tech-text'),
            subtitle: document.querySelector('.tech-subtitle'),
            button: document.querySelector('.tech-button')
        };
    }
    
    triggerBannerAnimations() {
        if (this.bannerElements.content) {
            setTimeout(() => {
                this.bannerElements.content.classList.add('animated');
            }, 300);
        }
        
        if (this.bannerElements.title) {
            setTimeout(() => {
                this.bannerElements.title.classList.add('animated');
            }, 600);
        }
        
        if (this.bannerElements.subtitle) {
            setTimeout(() => {
                this.bannerElements.subtitle.classList.add('animated');
            }, 900);
        }
        
        if (this.bannerElements.button) {
            setTimeout(() => {
                this.bannerElements.button.classList.add('animated');
            }, 1200);
        }
    }
    
    setupScrollObservers() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, this.observerOptions);
        
        // Observe all animation elements
        document.querySelectorAll('.wow-observer, .wow-fade-in, .wow-slide-up, .wow-scale, .wow-rotate, .tech-section-title, .tech-section-subtitle').forEach(el => {
            observer.observe(el);
        });
    }
    
    setupWowEffects() {
        // Magnetic effects
        const magneticElements = document.querySelectorAll('.wow-magnetic');
        
        magneticElements.forEach(element => {
            element.addEventListener('mousemove', (e) => {
                const rect = element.getBoundingClientRect();
                const x = e.clientX - rect.left - rect.width / 2;
                const y = e.clientY - rect.top - rect.height / 2;
                
                element.style.transform = `translate(${x * 0.1}px, ${y * 0.1}px) scale(1.05)`;
            });
            
            element.addEventListener('mouseleave', () => {
                element.style.transform = 'translate(0px, 0px) scale(1)';
            });
        });
        
        // 3D effects for cards
        const cards3D = document.querySelectorAll('.wow-3d, .tech-card');
        
        cards3D.forEach(card => {
            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                
                const rotateX = (y - centerY) / 15;
                const rotateY = (centerX - x) / 15;
                
                card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateZ(20px)`;
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) translateZ(0px)';
            });
        });
    }
    
    setupNavEffects() {
        // Nav scroll effect
        const nav = document.querySelector('.tech-nav');
        if (nav) {
            window.addEventListener('scroll', () => {
                if (window.scrollY > 50) {
                    nav.classList.add('scrolled');
                } else {
                    nav.classList.remove('scrolled');
                }
            });
        }
        
        // Nav link hover effects
        const navLinks = document.querySelectorAll('.tech-nav-link');
        
        navLinks.forEach(link => {
            link.addEventListener('mouseenter', () => {
                link.style.color = '#3b82f6';
            });
            
            link.addEventListener('mouseleave', () => {
                link.style.color = '#1a1a1a';
            });
        });
    }
}

// Initialize animations when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new SimpleAnimations();
});

// Add some effects on window load
window.addEventListener('load', () => {
    // Stagger animation for wow elements
    const wowElements = document.querySelectorAll('.wow-observer');
    
    wowElements.forEach((el, index) => {
        el.style.animationDelay = `${index * 0.1}s`;
    });
    
    // Add hover effects to tech buttons
    const techButtons = document.querySelectorAll('.tech-button');
    
    techButtons.forEach(button => {
        button.addEventListener('mouseenter', () => {
            button.style.transform = 'translateY(-2px)';
        });
        
        button.addEventListener('mouseleave', () => {
            button.style.transform = 'translateY(0)';
        });
    });
    
    // Add pulse effect to tech cards
    const techCards = document.querySelectorAll('.tech-card');
    
    techCards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.boxShadow = '0 12px 30px rgba(26, 26, 26, 0.1)';
        });
        
        card.addEventListener('mouseleave', () => {
            card.style.boxShadow = 'none';
        });
    });
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});
