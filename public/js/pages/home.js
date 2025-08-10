// Home page reveal animations (IntersectionObserver)
(function(){
  const supportsReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (supportsReducedMotion) return;

  const items = document.querySelectorAll('[data-anim="reveal"]');
  if (!items.length) return;

  const obs = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
        obs.unobserve(entry.target);
      }
    });
  }, { threshold: 0.2, rootMargin: '0px 0px -10% 0px' });

  items.forEach(el => obs.observe(el));
})();


