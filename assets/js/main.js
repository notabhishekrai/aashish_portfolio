(function () {
  var REVEAL_DIST = 32;
  var REVEAL_DUR = '0.9';

  document.querySelectorAll('[data-reveal="fade"]').forEach(function (el) {
    el.style.opacity = '0';
    el.style.transform = 'translateY(' + REVEAL_DIST + 'px)';
    el.style.transition = 'opacity ' + REVEAL_DUR + 's cubic-bezier(.22,1,.36,1), transform ' + REVEAL_DUR + 's cubic-bezier(.22,1,.36,1)';
  });

  function revealEl(el) {
    if (el.dataset.reveal === 'clip') {
      el.style.clipPath = 'inset(0 0 0% 0)';
    } else {
      el.style.opacity = '1';
      el.style.transform = 'translateY(0)';
    }
  }

  var revealTargets = Array.from(document.querySelectorAll('[data-reveal]'));
  var revealObserver = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (!entry.isIntersecting) return;
      entry.target.dataset.revealed = '1';
      revealEl(entry.target);
      revealObserver.unobserve(entry.target);
    });
  }, { threshold: 0.15 });
  revealTargets.forEach(function (el) { revealObserver.observe(el); });

  function checkRevealFallback() {
    revealTargets.forEach(function (el) {
      if (el.dataset.revealed) return;
      var rect = el.getBoundingClientRect();
      if (rect.top < window.innerHeight * 1.1 && rect.bottom > -200) {
        el.dataset.revealed = '1';
        revealEl(el);
      }
    });
  }

  // Stat counters
  var slotEls = document.querySelectorAll('[data-slot-count]');
  if (slotEls.length) {
    var spin = function (el) {
      var target = parseInt(el.dataset.slotCount, 10) || 0;
      var duration = 1100;
      var start = performance.now();
      var tick = function (now) {
        var p = Math.min((now - start) / duration, 1);
        if (p < 1) {
          var jitter = Math.floor(Math.random() * (target + 20));
          el.textContent = jitter + '+';
          requestAnimationFrame(tick);
        } else {
          el.textContent = target + '+';
        }
      };
      requestAnimationFrame(tick);
    };
    var slotObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        spin(entry.target);
        slotObserver.unobserve(entry.target);
      });
    }, { threshold: 0.6 });
    slotEls.forEach(function (el) { slotObserver.observe(el); });
  }

  // Hero title stagger-in
  document.querySelectorAll('.hero-word').forEach(function (el, i) {
    setTimeout(function () {
      el.style.transition = 'opacity .9s cubic-bezier(.22,1,.36,1), transform .9s cubic-bezier(.22,1,.36,1)';
      el.style.opacity = '1';
      el.style.transform = 'translateY(0)';
    }, 250 + i * 140);
  });

  // Scroll progress bar + nav background + parallax
  var nav = document.getElementById('site-nav');
  var bar = document.getElementById('scroll-progress');

  var parallaxEls = Array.from(document.querySelectorAll('[data-parallax]')).map(function (el) {
    var rect = el.getBoundingClientRect();
    return { el: el, speed: parseFloat(el.dataset.parallax) || 0.12, docTop: rect.top + window.scrollY, height: rect.height };
  });

  function recalcParallax() {
    parallaxEls.forEach(function (p) {
      p.el.style.transform = 'none';
      var rect = p.el.getBoundingClientRect();
      p.docTop = rect.top + window.scrollY;
      p.height = rect.height;
    });
  }
  window.addEventListener('resize', recalcParallax, { passive: true });
  // The Archivo web font loads async and can reflow the layout after these
  // rects were first measured, leaving parallax offsets stale until resize.
  if (document.fonts && document.fonts.ready) {
    document.fonts.ready.then(recalcParallax);
  }

  var ticking = false;
  function onScroll() {
    if (ticking) return;
    ticking = true;
    requestAnimationFrame(function () {
      var y = window.scrollY;
      var max = document.documentElement.scrollHeight - window.innerHeight;
      if (bar) bar.style.width = (max > 0 ? (y / max) * 100 : 0) + '%';
      if (nav) {
        if (y > 40) {
          nav.style.background = 'rgba(255,255,255,.9)';
          nav.style.borderBottomColor = 'var(--color-divider)';
          nav.style.backdropFilter = 'blur(8px)';
        } else {
          nav.style.background = 'transparent';
          nav.style.borderBottomColor = 'transparent';
          nav.style.backdropFilter = 'none';
        }
      }
      var viewportCenter = y + window.innerHeight / 2;
      parallaxEls.forEach(function (p) {
        var elCenter = p.docTop + p.height / 2;
        p.el.style.transform = 'translateY(' + ((viewportCenter - elCenter) * p.speed) + 'px)';
      });
      ticking = false;
    });
  }
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
  checkRevealFallback();
  window.addEventListener('scroll', checkRevealFallback, { passive: true });

  // Nav scrollspy
  var navLinks = document.querySelectorAll('[data-nav-link]');
  if (navLinks.length) {
    navLinks.forEach(function (el) { el.style.transition = 'color .2s ease'; });
    var spyMap = Array.from(navLinks)
      .map(function (el) { return { el: el, section: document.getElementById(el.dataset.navLink) }; })
      .filter(function (x) { return x.section; });

    function setActive(id) {
      navLinks.forEach(function (el) {
        var active = el.dataset.navLink === id;
        el.style.color = active ? 'var(--color-accent)' : '';
        el.style.fontWeight = active ? '700' : '';
      });
    }

    var hero = document.getElementById('hero');
    function updateActive() {
      if (hero) {
        var heroRect = hero.getBoundingClientRect();
        if (heroRect.bottom > window.innerHeight * 0.4) { setActive(null); return; }
      }
      var line = window.innerHeight * 0.4;
      var current = null;
      spyMap.forEach(function (item) {
        var rect = item.section.getBoundingClientRect();
        if (rect.top <= line && rect.bottom > line) current = item.section.id;
      });
      if (!current) {
        var best = null, bestDist = Infinity;
        spyMap.forEach(function (item) {
          var rect = item.section.getBoundingClientRect();
          var dist = Math.abs(rect.top - line);
          if (dist < bestDist) { bestDist = dist; best = item.section.id; }
        });
        current = best;
      }
      if (current) setActive(current);
    }
    window.addEventListener('scroll', updateActive, { passive: true });
    updateActive();
  }
})();
