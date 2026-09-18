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

  // Pricing monthly / yearly toggle — updates all three plans in place
  const pricingSection = document.querySelector('.pricing-section');
  const billingToggle = document.querySelector('.pricing-billing-toggle');
  const billingLabels = document.querySelectorAll('[data-cycle-label]');
  function formatAmount(value) {
    const number = Number(value);
    if (Number.isNaN(number)) return String(value || '');
    return number.toLocaleString('en-US');
  }
  function setBillingCycle(cycle) {
    const yearly = cycle === 'yearly';
    pricingSection?.classList.toggle('is-yearly', yearly);
    billingToggle?.setAttribute('aria-pressed', yearly ? 'true' : 'false');
    billingLabels.forEach((label) => {
      const active = label.getAttribute('data-cycle-label') === cycle;
      label.classList.toggle('is-active', active);
    });
    document.querySelectorAll('.pricing-plan[data-monthly-amount]').forEach((card) => {
      const prefix = yearly ? 'yearly' : 'monthly';
      const amount = card.getAttribute(`data-${prefix}-amount`);
      const list = card.getAttribute(`data-${prefix}-list`);
      const href = card.getAttribute(`data-${prefix}-href`);
      const note = card.getAttribute(`data-${prefix}-note`);
      const amountEl = card.querySelector('.pricing-price-amount');
      const listEl = card.querySelector('.pricing-list-price');
      const noteEl = card.querySelector('.pricing-bill-note');
      const btn = card.querySelector('.pricing-btn');
      if (amountEl && amount) amountEl.textContent = formatAmount(amount);
      if (listEl && list) listEl.textContent = formatAmount(list) + ' ر.س';
      if (noteEl && note) noteEl.textContent = note;
      if (btn && href) btn.setAttribute('href', href);
    });
  }
  billingToggle?.addEventListener('click', () => {
    const yearly = pricingSection?.classList.contains('is-yearly');
    setBillingCycle(yearly ? 'monthly' : 'yearly');
  });
  billingLabels.forEach((label) => {
    label.addEventListener('click', () => {
      setBillingCycle(label.getAttribute('data-cycle-label') || 'monthly');
    });
  });
  setBillingCycle('monthly');

  // Story details modal
  const storyModal = document.getElementById('story-modal');
  const storyTitle = document.getElementById('story-modal-title');
  const storyContent = document.getElementById('story-modal-content');
  const storyImage = document.getElementById('story-modal-image');
  function closeStory() {
    if (!storyModal) return;
    storyModal.hidden = true;
    document.body.classList.remove('story-open');
  }
  function openStory(card) {
    if (!storyModal || !storyTitle || !storyContent) return;
    const key = card.getAttribute('data-story');
    const template = document.getElementById('story-' + key);
    const title = card.querySelector('.title')?.textContent || '';
    const img = card.querySelector('.cover img');
    if (!template) return;
    storyTitle.textContent = title;
    storyContent.innerHTML = template.innerHTML;
    if (storyImage && img) {
      storyImage.src = img.getAttribute('src') || '';
      storyImage.alt = img.getAttribute('alt') || title;
    }
    storyModal.hidden = false;
    document.body.classList.add('story-open');
  }
  document.querySelectorAll('[data-story]').forEach((card) => {
    card.addEventListener('click', () => openStory(card));
    card.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        openStory(card);
      }
    });
  });
  document.querySelectorAll('[data-story-close]').forEach((el) => {
    el.addEventListener('click', closeStory);
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeStory();
  });
});

