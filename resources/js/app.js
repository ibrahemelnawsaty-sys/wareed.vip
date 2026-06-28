// ظهور تدريجي للأقسام عند التمرير
const revealObserver = new IntersectionObserver(
    (entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                revealObserver.unobserve(entry.target);
            }
        });
    },
    { threshold: 0.12 }
);

function initReveal() {
    document.querySelectorAll('[data-reveal]').forEach((el) => revealObserver.observe(el));
}

// قائمة الموبايل
function initMobileNav() {
    const btn = document.querySelector('[data-nav-toggle]');
    const menu = document.querySelector('[data-nav-menu]');
    if (!btn || !menu) return;
    btn.addEventListener('click', () => menu.classList.toggle('hidden'));
}

// عدّادات الأرقام المتحركة
function initCounters() {
    const counters = document.querySelectorAll('[data-counter]');
    if (!counters.length) return;
    const obs = new IntersectionObserver((entries) => {
        entries.forEach((e) => {
            if (!e.isIntersecting) return;
            const el = e.target;
            const target = parseFloat(el.dataset.counter);
            const suffix = el.dataset.suffix || '';
            const nfLocale = 'en-US'; // أرقام إنجليزية دائماً
            let cur = 0;
            const step = target / 60;
            const tick = () => {
                cur += step;
                if (cur >= target) {
                    el.textContent = target.toLocaleString(nfLocale) + suffix;
                } else {
                    el.textContent = Math.floor(cur).toLocaleString(nfLocale) + suffix;
                    requestAnimationFrame(tick);
                }
            };
            tick();
            obs.unobserve(el);
        });
    }, { threshold: 0.5 });
    counters.forEach((c) => obs.observe(c));
}

// تأثير الهيدر عند التمرير
function initHeaderScroll() {
    const header = document.querySelector('[data-header]');
    if (!header) return;
    const onScroll = () => {
        if (window.scrollY > 20) header.classList.add('is-scrolled');
        else header.classList.remove('is-scrolled');
    };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
}

document.addEventListener('DOMContentLoaded', () => {
    initReveal();
    initMobileNav();
    initCounters();
    initHeaderScroll();
});
