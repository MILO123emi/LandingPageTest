// Public landings: header on scroll, section reveal and a light hero parallax (PLAN §5).
// Everything is skipped when the visitor prefers reduced motion.
const header = document.querySelector<HTMLElement>('.site-header');
const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const heroImg = document.querySelector<HTMLElement>('.hero-media img');
// Inner pages (catalog, product) have no hero behind the header, so it stays solid.
const solid = document.body.classList.contains('page-solid');

const onScroll = (): void => {
    const y = window.scrollY;

    header?.classList.toggle('scrolled', solid || y > 40);

    if (heroImg && !reduce && y < window.innerHeight) {
        heroImg.style.transform = `translateY(${y * -0.08}px)`;
    }
};

window.addEventListener('scroll', onScroll, { passive: true });
onScroll();

const items = document.querySelectorAll<HTMLElement>('.reveal');

if (reduce || !('IntersectionObserver' in window)) {
    items.forEach((el) => el.classList.add('in'));
} else {
    const io = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('in');
                    io.unobserve(entry.target);
                }
            });
        },
        { rootMargin: '0px 0px -8% 0px' },
    );

    items.forEach((el) => io.observe(el));
}
