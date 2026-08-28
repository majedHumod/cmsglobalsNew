(function () {
  var APP = "https://app.etoscoach.com";
  var MARKETING =
    location.hostname === "etoscoach.com" || location.hostname === "www.etoscoach.com"
      ? location.origin
      : "https://etoscoach.com";
  var redirectTarget =
    location.pathname.indexOf("/subscribe") === 0 || location.pathname.indexOf("/billing/") === 0
      ? APP + location.pathname + location.search
      : MARKETING + "/";
  var LOGIN = APP + "/account/login?redirect=" + encodeURIComponent(redirectTarget);
  var LOGOUT = APP + "/account/logout?redirect=" + encodeURIComponent(redirectTarget.indexOf(APP) === 0 ? redirectTarget : MARKETING + "/");
  var SUBSCRIBE = APP + "/subscribe";

  function el(html) {
    var wrap = document.createElement("div");
    wrap.innerHTML = html.trim();
    return wrap.firstElementChild;
  }

  function setSessionState(state) {
    var root = document.documentElement;
    root.classList.remove("platform-session-pending", "platform-session-ready", "platform-guest", "platform-authenticated");
    root.classList.add("platform-session-ready", state === "authenticated" ? "platform-authenticated" : "platform-guest");
  }

  function renderGuest(slot) {
    slot.innerHTML = "";
    slot.appendChild(el('<a class="nav-login" href="' + LOGIN + '">تسجيل الدخول</a>'));
    slot.appendChild(el('<a class="cta js-start-cta" href="' + SUBSCRIBE + '">ابدأ الآن</a>'));
    setSessionState("guest");
  }

  function renderUser(slot, data) {
    var name = data.name || data.email || "حسابك";
    var club = data.club ? '<span class="account-club">' + escapeHtml(data.club) + "</span>" : "";
    var hint = data.status_hint
      ? '<span class="account-club" style="color:#b45309">' + escapeHtml(data.status_hint) + "</span>"
      : "";
    var primaryUrl = data.dashboard_url || SUBSCRIBE;
    var primaryLabel = data.dashboard_label || "لوحة التحكم";
    var secondary =
      data.secondary_url && data.secondary_label
        ? '<a class="btn" href="' + data.secondary_url + '">' + escapeHtml(data.secondary_label) + "</a>"
        : "";

    slot.innerHTML = "";
    slot.appendChild(
      el(
        '<div class="account-chip">' +
          '<div class="account-meta">' +
          '<strong>' +
          escapeHtml(name) +
          "</strong>" +
          club +
          hint +
          "</div>" +
          '<a class="cta" href="' +
          primaryUrl +
          '">' +
          escapeHtml(primaryLabel) +
          "</a>" +
          secondary +
          '<a class="nav-login" href="' +
          LOGOUT +
          '">خروج</a>' +
          "</div>"
      )
    );
    setSessionState("authenticated");
  }

  function escapeHtml(s) {
    return String(s)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }

  function fetchSession() {
    var endpoints = [APP + "/account/session", APP + "/api/platform/session"];

    function tryEndpoint(index) {
      if (index >= endpoints.length) {
        return Promise.resolve({ authenticated: false });
      }

      return fetch(endpoints[index], {
        credentials: "include",
        headers: { Accept: "application/json" },
      })
        .then(function (res) {
          if (!res.ok) {
            throw new Error("session " + res.status);
          }
          return res.json();
        })
        .catch(function () {
          return tryEndpoint(index + 1);
        });
    }

    return tryEndpoint(0);
  }

  function mount() {
    var slot = document.getElementById("platform-account");
    if (!slot) {
      document.documentElement.classList.remove("platform-session-pending");
      return;
    }

    fetchSession()
      .then(function (data) {
        if (data && data.authenticated) {
          renderUser(slot, data);
        } else {
          renderGuest(slot);
        }
      })
      .catch(function () {
        renderGuest(slot);
      });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", mount);
  } else {
    mount();
  }
})();
