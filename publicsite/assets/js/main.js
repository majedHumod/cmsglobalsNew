// Mobile nav toggle with overlay and ESC close
document.addEventListener('DOMContentLoaded', function () {
  const nav = document.querySelector('.nav');
  const toggle = document.querySelector('.menu-toggle');
  let backdrop = null;

  function openMenu() {
    if (!nav) return;
    nav.classList.add('open');
    document.body.classList.add('menu-open');
    toggle?.setAttribute('aria-expanded', 'true');
    // toggle icons
    const iconMenu = toggle?.querySelector('.icon-menu');
    const iconClose = toggle?.querySelector('.icon-close');
    if (iconMenu && iconClose) { iconMenu.style.display = 'none'; iconClose.style.display = 'block'; }
    // create backdrop
    if (!backdrop) {
      backdrop = document.createElement('div');
      backdrop.className = 'nav-backdrop';
      document.body.appendChild(backdrop);
      backdrop.addEventListener('click', closeMenu);
    }
    backdrop.style.display = 'block';
  }
  function closeMenu() {
    if (!nav) return;
    nav.classList.remove('open');
    document.body.classList.remove('menu-open');
    toggle?.setAttribute('aria-expanded', 'false');
    const iconMenu = toggle?.querySelector('.icon-menu');
    const iconClose = toggle?.querySelector('.icon-close');
    if (iconMenu && iconClose) { iconMenu.style.display = 'block'; iconClose.style.display = 'none'; }
    if (backdrop) backdrop.style.display = 'none';
  }
  function toggleMenu() {
    if (nav?.classList.contains('open')) closeMenu(); else openMenu();
  }
  if (toggle) toggle.addEventListener('click', toggleMenu);
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeMenu();
  });

  // FAQ accordion
  document.querySelectorAll('.faq-item').forEach((item) => {
    const q = item.querySelector('.faq-q');
    if (q) {
      q.addEventListener('click', () => item.classList.toggle('open'));
    }
  });

  // Smooth scroll for same-page anchors in older browsers
  document.querySelectorAll('a[href^="#"]').forEach((a) => {
    a.addEventListener('click', (e) => {
      const id = a.getAttribute('href').slice(1);
      const el = document.getElementById(id);
      if (el) {
        e.preventDefault();
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });

  // FAQ show more
  const moreBtn = document.getElementById('faq-more-toggle');
  const moreBox = document.getElementById('faq-more');
  if (moreBtn && moreBox) {
    moreBtn.addEventListener('click', (e) => {
      e.preventDefault();
      const isHidden = moreBox.style.display === 'none' || !moreBox.style.display;
      moreBox.style.display = isHidden ? 'block' : 'none';
      moreBtn.textContent = isHidden ? 'إخفاء الأسئلة' : 'عرض المزيد من الأسئلة';
    });
  }
});

