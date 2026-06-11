const HEADER_OFFSET = 96;

const activeClasses = ['bg-white', 'text-[#AB1E23]', 'shadow-sm', 'ring-1', 'ring-[#E6C280]/50'];
const inactiveClasses = ['text-[#080D21]'];

function setActiveNav(sectionId) {
    document.querySelectorAll('[data-nav-section]').forEach((link) => {
        const isActive = link.dataset.navSection === sectionId;

        link.classList.remove(...activeClasses);
        if (isActive) {
            link.classList.add(...activeClasses);
        }
    });
}

function scrollToSection(hash) {
    const target = document.querySelector(hash);
    if (!target) {
        return false;
    }

    const top = target.getBoundingClientRect().top + window.scrollY - HEADER_OFFSET;

    window.scrollTo({ top, behavior: 'smooth' });
    history.pushState(null, '', hash);

    const sectionId = target.dataset.section ?? hash.slice(1);
    setActiveNav(sectionId);

    return true;
}

document.querySelectorAll('a[href^="#"]').forEach((link) => {
    link.addEventListener('click', (event) => {
        const hash = link.getAttribute('href');
        if (!hash || hash === '#') {
            return;
        }

        if (scrollToSection(hash)) {
            event.preventDefault();
        }
    });
});

const sections = document.querySelectorAll('[data-section]');

if (sections.length > 0) {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    setActiveNav(entry.target.dataset.section);
                }
            });
        },
        { rootMargin: '-30% 0px -55% 0px', threshold: 0 }
    );

    sections.forEach((section) => observer.observe(section));

    const initialHash = window.location.hash;
    if (initialHash) {
        setTimeout(() => scrollToSection(initialHash), 100);
    } else {
        setActiveNav(sections[0].dataset.section);
    }
}
