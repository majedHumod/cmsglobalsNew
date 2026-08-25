(function () {
  var APP = "https://app.etoscoach.com";
  var MARKETING =
    location.hostname === "etoscoach.com" || location.hostname === "www.etoscoach.com"
      ? location.origin
      : "https://etoscoach.com";
  var LOGIN = APP + "/account/login?redirect=" + encodeURIComponent(MARKETING + "/");
  var LOGOUT = APP + "/account/logout?redirect=" + encodeURIComponent(MARKETING + "/");
  var SUBSCRIBE = APP + "/subscribe";

  function el(html) {
    var wrap = document.createElement("div");
    wrap.innerHTML = html.trim();
    return wrap.firstElementChild;
  }

  function renderGuest(slot) {
    slot.innerHTML = "";
    slot.appendChild(
      el(
        '<a class="nav-login" href="' +
          LOGIN +
          '">تسجيل الدخول</a>'
      )
    );
  }

  function renderUser(slot, data) {
    var name = data.name || data.email || "حسابك";
    var club = data.club ? '<span class="account-club">' + escapeHtml(data.club) + "</span>" : "";
    var dash = data.dashboard_url || SUBSCRIBE;
    var label = data.dashboard_label || "لوحة التحكم";
    slot.innerHTML = "";
    slot.appendChild(
      el(
        '<div class="account-chip">' +
          '<div class="account-meta">' +
          '<strong>' +
          escapeHtml(name) +
          "</strong>" +
          club +
          "</div>" +
          '<a class="cta" href="' +
          dash +
          '">' +
          escapeHtml(label) +
          "</a>" +
          '<a class="nav-login" href="' +
          LOGOUT +
          '">خروج</a>' +
          "</div>"
      )
    );
  }

  function escapeHtml(s) {
    return String(s)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }

  function mount() {
    var slot = document.getElementById("platform-account");
    if (!slot) return;
    renderGuest(slot);
    fetch(APP + "/account/session", {
      credentials: "include",
      headers: { Accept: "application/json" },
    })
      .then(function (res) {
        return res.json();
      })
      .then(function (data) {
        if (data && data.authenticated) {
          renderUser(slot, data);
        }
      })
      .catch(function () {
        return fetch(APP + "/api/platform/session", {
          credentials: "include",
          headers: { Accept: "application/json" },
        }).then(function (res) {
          return res.json();
        });
      })
      .then(function (data) {
        if (data && data.authenticated) {
          renderUser(slot, data);
        }
      });
  }

  function loadPlans() {
    var grid = document.getElementById("pricing-grid");
    if (!grid) return;
    fetch(APP + "/plans", { headers: { Accept: "application/json" } })
      .then(function (res) {
        return res.json();
      })
      .then(function (payload) {
        var plans = payload && payload.plans ? payload.plans : [];
        if (!plans.length) return;
        grid.innerHTML = plans
          .map(function (plan, i) {
            var interval = plan.interval === "year" ? "سنة" : "شهر";
            var price =
              plan.price != null
                ? Number(plan.price) + " " + (plan.currency || "SAR") + "/" + interval
                : "تواصل معنا";
            var features = Array.isArray(plan.features) ? plan.features : [];
            var lis = features
              .slice(0, 4)
              .map(function (f) {
                return "<li>" + escapeHtml(typeof f === "string" ? f : f.label || "") + "</li>";
              })
              .join("");
            var cls = i === 0 ? ' style="border-color: rgba(26,142,154,.25)"' : "";
            var btn = i === 0 ? "btn primary" : "btn";
            return (
              '<div class="price"' +
              cls +
              "><h3>" +
              escapeHtml(plan.name || plan.code) +
              '</h3><div class="value">' +
              escapeHtml(price) +
              "</div><ul>" +
              lis +
              '</ul><div class="hero-actions" style="margin-top:14px"><a class="' +
              btn +
              '" href="' +
              SUBSCRIBE +
              "?plan=" +
              encodeURIComponent(plan.code || "") +
              '">ابدأ الاشتراك</a></div></div>'
            );
          })
          .join("");
      })
      .catch(function () {});
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", function () {
      mount();
      loadPlans();
    });
  } else {
    mount();
    loadPlans();
  }
})();
