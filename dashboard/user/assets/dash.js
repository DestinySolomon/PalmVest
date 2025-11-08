// dash.js - Fully fixed version with Buy Oil modal fixes
document.addEventListener("DOMContentLoaded", function () {
  const content = document.getElementById("pvContent");
  const links = document.querySelectorAll("a[data-ajax]");
  const sidebar = document.getElementById("sidebar");
  const sidebarToggle = document.getElementById("sidebarToggle");
  const themeToggle = document.getElementById("themeToggle");
  const main = document.querySelector(".pv-main");

  // ============================
  // AJAX PAGE LOADING
  // ============================
  links.forEach((a) => {
    a.addEventListener("click", function (e) {
      e.preventDefault();
      const href = this.getAttribute("href");

      // Change URL without reload
      history.pushState({}, "", href);

      // Remove old active + set new active
      document.querySelectorAll(".pv-nav a").forEach((x) => x.classList.remove("active"));
      this.classList.add("active");

      // load via AJAX
      loadSection(href);
    });
  });

  window.addEventListener("popstate", () => {
    const url = new URL(location.href);
    const page = url.searchParams.get("page") || "overview";
    loadSection("?page=" + page);
  });

  async function loadSection(href) {
    const url = new URL(href, location.origin);
    const page = url.searchParams.get("page") || "overview";
    const fetchUrl = `sections/${page}.php`;

    if (content) content.innerHTML = '<div class="glass-card">Loading...</div>';

    try {
      const res = await fetch(fetchUrl, { headers: { "X-Requested-With": "XMLHttpRequest" } });
      if (!res.ok) {
        content.innerHTML = '<div class="glass-card">Section not found. Refresh page.</div>';
        return;
      }
      const html = await res.text();
      content.innerHTML = html;
      runAfterLoad();
    } catch (err) {
      content.innerHTML = '<div class="glass-card">Error loading section.</div>';
      console.error("Load section error:", err);
    }
  }

  // ============================
  // RUN AFTER LOAD
  // ============================
  function runAfterLoad() {
    const ctx = document.getElementById("oilChart");
    if (ctx && typeof Chart !== "undefined") {
      new Chart(ctx, {
        type: "line",
        data: {
          labels: ["Mon", "Tue", "Wed", "Thu", "Fri"],
          datasets: [{
            label: "Market Price ₦",
            data: [45000, 45200, 45500, 46000, 47200],
            borderColor: "#4caf50",
            backgroundColor: "rgba(76,175,80,0.12)",
            borderWidth: 2,
            tension: 0.35,
          }],
        },
        options: {
          responsive: true,
          plugins: { legend: { display: false } },
          scales: { x: { ticks: { color: "#fff" } }, y: { ticks: { color: "#fff" } } },
        },
      });
    }
  }

  // ============================
  // SIDEBAR TOGGLE
  // ============================
  if (sidebarToggle) {
    sidebarToggle.addEventListener("click", () => {
      sidebar.classList.toggle("hide");
      main.classList.toggle("full");
    });
  }

  // ============================
  // THEME TOGGLE
  // ============================
  if (themeToggle) {
    const saved = localStorage.getItem("pv-theme");
    if (saved === "dark") document.body.classList.add("pv-dark");

    themeToggle.addEventListener("click", () => {
      document.body.classList.toggle("pv-dark");
      localStorage.setItem(
        "pv-theme",
        document.body.classList.contains("pv-dark") ? "dark" : "light"
      );
    });
  }

  runAfterLoad();

  // ============================
  // BUY OIL MODAL HANDLER
  // ============================
  const buyModalEl = document.getElementById("buyModal");
  const buyModal = buyModalEl ? new bootstrap.Modal(buyModalEl) : null;

  const qtyInput = document.getElementById("buy_qty");
  const totalBox = document.getElementById("totalPrice");
  const listingIdEl = document.getElementById("listing_id");
  const unitPriceEl = document.getElementById("unit_price");
  const sellerNameEl = document.getElementById("sellerName");
  const unitPriceText = document.getElementById("unitPrice");
  const availText = document.getElementById("availableQty");
  const confirmBtn = document.getElementById("confirmBtn");
  const buyForm = document.getElementById("buyForm");

  document.querySelectorAll(".buy-btn").forEach((btn) => {
    btn.addEventListener("click", () => {
      const seller = btn.dataset.seller;
      const price = parseFloat(btn.dataset.price);
      const avail = parseInt(btn.dataset.available);
      const listingId = btn.dataset.listing;

      // populate modal
      sellerNameEl.textContent = seller;
      unitPriceText.textContent = `₦${price.toLocaleString()}`;
      availText.textContent = avail;
      listingIdEl.value = listingId;
      unitPriceEl.value = price;
      qtyInput.value = 1;
      totalBox.textContent = `₦${price.toLocaleString()}`;
      confirmBtn.disabled = false;

      // show modal
      if (buyModal) buyModal.show();

      // update total and validate input
      qtyInput.oninput = () => {
        let q = parseInt(qtyInput.value);
        if (isNaN(q) || q < 1) q = 1;
        if (q > avail) q = avail;
        qtyInput.value = q;
        totalBox.textContent = `₦${(q * price).toLocaleString()}`;
        confirmBtn.disabled = q < 1 || q > avail;
      };
    });
  });

  // final validation on submit
  if (buyForm) {
    buyForm.addEventListener("submit", (e) => {
      const qty = parseInt(qtyInput.value);
      const availQty = parseInt(availText.textContent);

      if (qty < 1 || qty > availQty) {
        e.preventDefault();
        alert(`Please enter a quantity between 1 and ${availQty}`);
        return false;
      }
    });
  }

  // ============================
  // Remove modal-backdrop on close (fix unclickable layer issue)
  // ============================
  if (buyModalEl) {
    buyModalEl.addEventListener("hidden.bs.modal", () => {
      const backdrops = document.querySelectorAll(".modal-backdrop");
      backdrops.forEach((b) => b.remove());
    });
  }
});



//WALLET SECTION Quick Amount Buttons Script -->

document.addEventListener('DOMContentLoaded', function() {
    const fundAmountInput = document.getElementById('fundAmount');
    const withdrawAmountInput = document.getElementById('withdrawAmount');
    
    // Quick amount buttons for funding
    const quickAmounts = [1000, 5000, 10000, 20000, 50000];
    
    // Create quick amount buttons for funding
    const quickAmountsContainer = document.createElement('div');
    quickAmountsContainer.className = 'mb-3';
    quickAmountsContainer.innerHTML = '<label class="form-label">Quick Amounts</label><div class="d-flex gap-2 flex-wrap" id="quickAmounts"></div>';
    
    fundAmountInput.parentNode.parentNode.after(quickAmountsContainer);
    
    const quickAmountsDiv = document.getElementById('quickAmounts');
    quickAmounts.forEach(amount => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn btn-outline-primary btn-sm';
        button.textContent = '₦' + amount.toLocaleString();
        button.addEventListener('click', function() {
            fundAmountInput.value = amount;
        });
        quickAmountsDiv.appendChild(button);
    });

    // Quick amount buttons for withdrawal
    const withdrawQuickAmounts = [1000, 5000, 10000, 20000, 50000];
    const withdrawQuickContainer = document.createElement('div');
    withdrawQuickContainer.className = 'mb-3';
    withdrawQuickContainer.innerHTML = '<label class="form-label">Quick Amounts</label><div class="d-flex gap-2 flex-wrap" id="withdrawQuickAmounts"></div>';
    
    withdrawAmountInput.parentNode.parentNode.after(withdrawQuickContainer);
    
    const withdrawQuickAmountsDiv = document.getElementById('withdrawQuickAmounts');
    withdrawQuickAmounts.forEach(amount => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn btn-outline-success btn-sm';
        button.textContent = '₦' + amount.toLocaleString();
        button.addEventListener('click', function() {
            if (amount <=  $wallet_balance ) {
                withdrawAmountInput.value = amount;
            } else {
                alert('Amount exceeds available balance');
            }
        });
        withdrawQuickAmountsDiv.appendChild(button);
    });

    // Auto-hide alerts
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert.auto-hide');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
});


