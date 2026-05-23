/**
 * Campaign countdown — counts down to a target date and renders
 * D / H / M / S into a band on the page. Drop a wrapper with
 * data-countdown="YYYY-MM-DDTHH:MM:SS+09:00" containing four .seg
 * elements with the data-cd attributes "d|h|m|s".
 */
(function () {
  function pad(n, w) { n = String(n); return n.length >= w ? n : "0".repeat(w - n.length) + n; }
  function update(el) {
    var target = new Date(el.getAttribute('data-countdown')).getTime();
    var now = Date.now();
    var diff = target - now;
    var ended = diff <= 0;
    if (ended) diff = 0;
    var d = Math.floor(diff / 86400000);
    var h = Math.floor((diff / 3600000) % 24);
    var m = Math.floor((diff / 60000) % 60);
    var s = Math.floor((diff / 1000) % 60);
    function set(k, v, w) {
      var n = el.querySelector('[data-cd="' + k + '"]');
      if (n) n.textContent = pad(v, w);
    }
    set('d', d, 2); set('h', h, 2); set('m', m, 2); set('s', s, 2);
    if (ended) el.closest('.countdown-band, .top-banner')?.classList.add('ended');
  }
  function init() {
    document.querySelectorAll('[data-countdown]').forEach(function (el) {
      update(el);
      setInterval(function () { update(el); }, 1000);
    });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
