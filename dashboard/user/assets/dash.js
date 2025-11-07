// dash.js - improved + fully fixed sidebar toggle + correct class names + preserves all your AJAX logic
document.addEventListener("DOMContentLoaded", function() {

  const content = document.getElementById('pvContent');
  const links = document.querySelectorAll('a[data-ajax]');
  const sidebar = document.getElementById('sidebar');
  const sidebarToggle = document.getElementById('sidebarToggle');
  const themeToggle = document.getElementById('themeToggle');
  const main = document.querySelector('.pv-main');

  // ============================
  // AJAX PAGE LOADING (your original logic preserved)
  // ============================
  links.forEach(a => {
    a.addEventListener('click', function(e) {
      e.preventDefault();
      const href = this.getAttribute('href');

      // Change URL without reload
      history.pushState({}, '', href);

      // Remove old active + set new active
      document.querySelectorAll('.pv-nav a').forEach(x => x.classList.remove('active'));
      this.classList.add('active');

      // load via AJAX
      loadSection(href);
    });
  });

  // Browser Back / Forward buttons
  window.addEventListener('popstate', () => {
    const url = new URL(location.href);
    const page = url.searchParams.get('page') || 'overview';
    loadSection('?page=' + page);
  });


  // ============================
  // MAIN AJAX FETCH HANDLER
  // ============================
  async function loadSection(href) {
    const url = new URL(href, location.origin);
    const page = url.searchParams.get('page') || 'overview';
    const fetchUrl = `sections/${page}.php`;

    if (content) {
      content.innerHTML = '<div class="glass-card">Loading...</div>';
    }

    try {
      const res = await fetch(fetchUrl, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });

      if (!res.ok) {
        content.innerHTML = '<div class="glass-card">Section not found. Refresh page.</div>';
        return;
      }

      const html = await res.text();
      content.innerHTML = html;

      // reinitialize charts, etc
      runAfterLoad();

    } catch (err) {
      content.innerHTML = '<div class="glass-card">Error loading section.</div>';
      console.error('Load section error:', err);
    }
  }


  // ============================
  // RUN AFTER LOAD (CHART INIT)
  // ============================
  function runAfterLoad() {
    const ctx = document.getElementById('oilChart');

    if (ctx && typeof Chart !== 'undefined') {
      new Chart(ctx, {
        type: 'line',
        data: {
          labels: ['Mon','Tue','Wed','Thu','Fri'],
          datasets: [{
            label: 'Market Price ₦',
            data: [45000, 45200, 45500, 46000, 47200],
            borderColor: '#4caf50',
            backgroundColor: 'rgba(76,175,80,0.12)',
            borderWidth: 2,
            tension: 0.35
          }]
        },
        options: {
          responsive: true,
          plugins: { legend: { display: false } },
          scales: {
            x: { ticks: { color: '#fff' } },
            y: { ticks: { color: '#fff' } }
          }
        }
      });
    }
  }


  // ============================
  // ✅ FIXED SIDEBAR TOGGLE (THE REAL PROBLEM)
  // ============================
  // Your CSS uses: .pv-sidebar.hide AND .pv-main.full
  // But your JS was toggling .expanded (wrong class)
  // This fixes everything:
  // ============================

  if (sidebarToggle) {
    sidebarToggle.addEventListener('click', () => {
      sidebar.classList.toggle('hide');
      main.classList.toggle('full');
    });
  }


  // ============================
  // THEME TOGGLE (unchanged)
  // ============================
  if (themeToggle) {
    const saved = localStorage.getItem('pv-theme');

    if (saved === 'dark') document.body.classList.add('pv-dark');

    themeToggle.addEventListener('click', () => {
      document.body.classList.toggle('pv-dark');

      localStorage.setItem(
        'pv-theme',
        document.body.classList.contains('pv-dark') ? 'dark' : 'light'
      );
    });
  }

  // Run chart init on first page load
  runAfterLoad();

});
