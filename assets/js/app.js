// assets/js/app.js — MedRDV JavaScript

document.addEventListener('DOMContentLoaded', () => {

  // ── Auto-dismiss alerts ──
  document.querySelectorAll('.alert').forEach(alert => {
    setTimeout(() => {
      alert.style.transition = 'opacity .5s, transform .5s';
      alert.style.opacity = '0';
      alert.style.transform = 'translateY(-8px)';
      setTimeout(() => alert.remove(), 500);
    }, 4500);
  });

  // ── Close modal on overlay click ──
  document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', e => {
      if (e.target === modal) modal.classList.add('hidden');
    });
  });

  // ── Close modal on Escape key ──
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      document.querySelectorAll('.modal:not(.hidden)').forEach(m => m.classList.add('hidden'));
    }
  });

  // ── Confirm delete buttons ──
  document.querySelectorAll('[data-confirm]').forEach(btn => {
    btn.addEventListener('click', e => {
      if (!confirm(btn.dataset.confirm)) e.preventDefault();
    });
  });

  // ── Animate stat cards on scroll ──
  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.animation = 'fadeInUp .4s ease forwards';
      }
    });
  }, { threshold: 0.1 });

  document.querySelectorAll('.stat-card, .medecin-card, .rdv-card, .step-card').forEach(el => {
    observer.observe(el);
  });

  // ── Active nav link ──
  const currentPath = window.location.pathname;
  document.querySelectorAll('.nav-link').forEach(link => {
    if (link.getAttribute('href') && currentPath.includes(link.getAttribute('href').split('/').pop())) {
      link.style.color = 'var(--color-primary)';
      link.style.borderBottomColor = 'var(--color-accent)';
    }
  });

  // ── Password strength indicator ──
  const pwInput = document.getElementById('password');
  if (pwInput) {
    pwInput.addEventListener('input', () => {
      const val = pwInput.value;
      let strength = 0;
      if (val.length >= 6)  strength++;
      if (val.length >= 10) strength++;
      if (/[A-Z]/.test(val)) strength++;
      if (/[0-9]/.test(val)) strength++;
      if (/[^A-Za-z0-9]/.test(val)) strength++;

      let existing = document.getElementById('pw-strength');
      if (!existing) {
        existing = document.createElement('div');
        existing.id = 'pw-strength';
        existing.style.cssText = 'height:4px;border-radius:99px;margin-top:.35rem;transition:all .3s';
        pwInput.parentElement.appendChild(existing);
      }
      const colors = ['#fee2e2','#fca5a5','#fb923c','#4ade80','#16a34a'];
      const widths = ['20%','40%','60%','80%','100%'];
      existing.style.width = widths[Math.max(0, strength - 1)] || '0%';
      existing.style.background = colors[Math.max(0, strength - 1)] || 'transparent';
    });
  }

});

// ── CSS animation ──
const style = document.createElement('style');
style.textContent = `
  @keyframes fadeInUp {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: translateY(0); }
  }
`;
document.head.appendChild(style);