(() => {
  document.addEventListener('click', (e) => {
    const themeBtn = e.target.closest('[data-theme]');
    if (themeBtn) { document.body.className = document.body.className.replace(/theme-[^\s]+/g,'').trim(); document.body.classList.add(themeBtn.dataset.theme); }
    if (e.target.closest('[data-toggle-sidebar]')) document.body.classList.toggle('sidebar-open');
    const openModal = e.target.closest('[data-open-modal]'); if (openModal) document.querySelector(openModal.dataset.openModal)?.classList.add('is-open');
    if (e.target.matches('[data-close-modal], .eco-modal')) e.target.closest('.eco-modal')?.classList.remove('is-open');
    const tab = e.target.closest('.eco-tab'); if (tab){ const wrap=tab.closest('[data-tabs]'); wrap.querySelectorAll('.eco-tab').forEach(t=>t.classList.remove('is-active')); tab.classList.add('is-active'); wrap.querySelectorAll('[data-tab-panel]').forEach(p=>p.hidden=true); wrap.querySelector(`#${tab.dataset.tab}`)?.removeAttribute('hidden');}
    const acc = e.target.closest('.eco-accordion-btn'); if (acc){ const p=acc.nextElementSibling; p.hidden=!p.hidden; }
  });
})();
