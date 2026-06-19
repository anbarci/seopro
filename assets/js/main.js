(function () {
	'use strict';

	var root = document.documentElement;
	var STORAGE_KEY = 'seopro-theme';

	function currentTheme() {
		return root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
	}

	function applyTheme(mode) {
		root.setAttribute('data-theme', mode);
		try {
			localStorage.setItem(STORAGE_KEY, mode);
		} catch (e) {}
		document.querySelectorAll('[data-seopro-theme-toggle]').forEach(function (btn) {
			btn.setAttribute('aria-pressed', mode === 'dark' ? 'true' : 'false');
		});
	}

	function openNav() {
		var nav = document.getElementById('seopro-mobile-nav');
		if (!nav) return;
		nav.hidden = false;
		document.body.classList.add('seopro-nav-open');
		document.querySelectorAll('[data-seopro-nav-toggle]').forEach(function (b) {
			b.setAttribute('aria-expanded', 'true');
		});
		var close = nav.querySelector('[data-seopro-nav-close]');
		if (close) close.focus();
	}

	function closeNav() {
		var nav = document.getElementById('seopro-mobile-nav');
		if (!nav) return;
		nav.hidden = true;
		document.body.classList.remove('seopro-nav-open');
		document.querySelectorAll('[data-seopro-nav-toggle]').forEach(function (b) {
			b.setAttribute('aria-expanded', 'false');
		});
	}

	function toggleSearch(open) {
		var ov = document.querySelector('[data-seopro-search]');
		if (!ov) return;
		ov.hidden = !open;
		if (open) {
			var input = ov.querySelector('input[type="search"]');
			if (input) input.focus();
		}
	}

	function closeModals() {
		var any = false;
		document.querySelectorAll('[data-seopro-modal]:not([hidden])').forEach(function (m) {
			m.hidden = true;
			any = true;
		});
		if (any) document.body.classList.remove('seopro-nav-open');
	}

	function showToast(msg) {
		var t = document.createElement('div');
		t.className = 'seopro-toast';
		t.setAttribute('role', 'status');
		t.textContent = msg;
		document.body.appendChild(t);
		requestAnimationFrame(function () { t.classList.add('is-on'); });
		setTimeout(function () {
			t.classList.remove('is-on');
			setTimeout(function () { if (t.parentNode) { t.parentNode.removeChild(t); } }, 320);
		}, 3200);
	}

	document.addEventListener('click', function (event) {
		if (event.target.closest('[data-seopro-theme-toggle]')) {
			event.preventDefault();
			applyTheme(currentTheme() === 'dark' ? 'light' : 'dark');
			return;
		}
		if (event.target.closest('[data-seopro-totop]')) {
			event.preventDefault();
			var smooth = !window.matchMedia('(prefers-reduced-motion: reduce)').matches;
			window.scrollTo({ top: 0, behavior: smooth ? 'smooth' : 'auto' });
			return;
		}
		var fontBtn = event.target.closest('[data-seopro-font]');
		if (fontBtn) {
			event.preventDefault();
			var dir = fontBtn.getAttribute('data-seopro-font') === 'up' ? 1 : -1;
			var cur = 1;
			try { cur = parseFloat(localStorage.getItem('seopro-reading-scale')) || 1; } catch (e) {}
			var next = Math.min(1.5, Math.max(0.85, Math.round((cur + dir * 0.1) * 100) / 100));
			document.documentElement.style.setProperty('--reading-scale', next);
			try { localStorage.setItem('seopro-reading-scale', next); } catch (e) {}
			return;
		}
		if (event.target.closest('[data-seopro-search-toggle]')) {
			event.preventDefault();
			toggleSearch(true);
			return;
		}
		if (event.target.closest('[data-seopro-search-close]')) {
			event.preventDefault();
			toggleSearch(false);
			return;
		}
		var searchOv = document.querySelector('[data-seopro-search]');
		if (searchOv && !searchOv.hidden && event.target === searchOv) {
			toggleSearch(false);
			return;
		}
		var modalOpen = event.target.closest('[data-seopro-modal-open]');
		if (modalOpen) {
			event.preventDefault();
			var modal = document.querySelector('[data-seopro-modal="' + modalOpen.getAttribute('data-seopro-modal-open') + '"]');
			if (modal) {
				modal.hidden = false;
				document.body.classList.add('seopro-nav-open');
				var mc = modal.querySelector('[data-seopro-modal-close]');
				if (mc) mc.focus();
			}
			return;
		}
		if (event.target.closest('[data-seopro-modal-close]')) {
			event.preventDefault();
			closeModals();
			return;
		}
		var openModalEl = event.target.closest('[data-seopro-modal]');
		if (openModalEl && event.target === openModalEl) {
			closeModals();
			return;
		}
		if (event.target.closest('[data-seopro-nav-toggle]')) {
			event.preventDefault();
			openNav();
			return;
		}
		if (event.target.closest('[data-seopro-nav-close]')) {
			event.preventDefault();
			closeNav();
			return;
		}
		var copyBtn = event.target.closest('[data-seopro-copy]');
		if (copyBtn) {
			event.preventDefault();
			var url = copyBtn.getAttribute('data-seopro-copy');
			var done = function () {
				copyBtn.classList.add('is-copied');
				setTimeout(function () { copyBtn.classList.remove('is-copied'); }, 1800);
			};
			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(url).then(done).catch(done);
			} else {
				var t = document.createElement('textarea');
				t.value = url; document.body.appendChild(t); t.select();
				try { document.execCommand('copy'); } catch (e) {}
				document.body.removeChild(t); done();
			}
			return;
		}
		var aiCopy = event.target.closest('[data-seopro-ai-copy]');
		if (aiCopy) {
			// Bu servisler bağlantıdan prompt almıyor: soruyu panoya kopyala,
			// link normal şekilde yeni sekmede açılsın (preventDefault YOK).
			var ask = aiCopy.getAttribute('data-seopro-ai-copy') || '';
			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(ask).catch(function () {});
			} else {
				var ta = document.createElement('textarea');
				ta.value = ask; ta.style.position = 'fixed'; ta.style.opacity = '0';
				document.body.appendChild(ta); ta.select();
				try { document.execCommand('copy'); } catch (e) {}
				document.body.removeChild(ta);
			}
			showToast('Soru panoya kopyalandı — açılan sohbete yapıştırın (Ctrl/⌘+V)');
			return;
		}
		var feedBtn = event.target.closest('[data-seopro-feed-prev], [data-seopro-feed-next]');
		if (feedBtn) {
			event.preventDefault();
			var section = feedBtn.closest('section');
			var track = section && section.querySelector('[data-seopro-feed-track]');
			if (track) {
				var dir = feedBtn.hasAttribute('data-seopro-feed-next') ? 1 : -1;
				track.scrollBy({ left: dir * Math.round(track.clientWidth * 0.8), behavior: 'smooth' });
			}
			return;
		}
		var embedBtn = event.target.closest('[data-seopro-embed] .seopro-embed__play');
		if (embedBtn) {
			event.preventDefault();
			var embed = embedBtn.closest('[data-seopro-embed]');
			var id = embed.getAttribute('data-id');
			if (id) {
				var iframe = document.createElement('iframe');
				iframe.src = 'https://www.youtube-nocookie.com/embed/' + id + '?autoplay=1';
				iframe.setAttribute('allow', 'accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture');
				iframe.setAttribute('allowfullscreen', '');
				iframe.setAttribute('title', 'YouTube');
				embed.innerHTML = '';
				embed.appendChild(iframe);
			}
			return;
		}
		var nav = document.getElementById('seopro-mobile-nav');
		if (nav && !nav.hidden && event.target === nav) {
			closeNav();
		}
	});

	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape') {
			closeNav();
			toggleSearch(false);
			closeModals();
		}
	});

	function initProgress() {
		var bar = document.querySelector('[data-seopro-progress]');
		if (!bar) return;
		var ticking = false;
		var update = function () {
			ticking = false;
			var h = document.documentElement;
			var max = h.scrollHeight - h.clientHeight;
			var pct = max > 0 ? (h.scrollTop / max) * 100 : 0;
			bar.style.width = pct + '%';
		};
		window.addEventListener('scroll', function () {
			if (!ticking) {
				ticking = true;
				window.requestAnimationFrame(update);
			}
		}, { passive: true });
		update();
	}

	function initToTop() {
		var btn = document.querySelector('[data-seopro-totop]');
		if (!btn) return;
		var ticking = false;
		var update = function () {
			ticking = false;
			btn.classList.toggle('is-visible', window.scrollY > 600);
		};
		window.addEventListener('scroll', function () {
			if (!ticking) {
				ticking = true;
				window.requestAnimationFrame(update);
			}
		}, { passive: true });
		update();
	}

	function initReveal() {
		if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
		if (!('IntersectionObserver' in window)) return;
		var els = document.querySelectorAll('.seopro-card, .seopro-hero__main, .seopro-hero__item, .seopro-block, .seopro-newsletter');
		if (!els.length) return;
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (e) {
				if (e.isIntersecting) { e.target.classList.add('seopro-in'); io.unobserve(e.target); }
			});
		}, { rootMargin: '0px 0px -8% 0px', threshold: 0.06 });
		els.forEach(function (el, i) {
			el.classList.add('seopro-reveal');
			el.style.transitionDelay = (Math.min(i % 6, 5) * 60) + 'ms';
			io.observe(el);
		});
	}

	function initFontSize() {
		try {
			var s = parseFloat(localStorage.getItem('seopro-reading-scale'));
			if (s >= 0.85 && s <= 1.5) {
				document.documentElement.style.setProperty('--reading-scale', s);
			}
		} catch (e) {}
	}

	function initTabs() {
		var widgets = document.querySelectorAll('[data-seopro-tabs]');
		if (!widgets.length) return;
		[].forEach.call(widgets, function (w) {
			var tabs = [].slice.call(w.querySelectorAll('[role="tab"]'));
			var panels = [].slice.call(w.querySelectorAll('[role="tabpanel"]'));
			if (tabs.length < 2) return;
			function select(idx, focus) {
				tabs.forEach(function (t, i) {
					var on = i === idx;
					t.setAttribute('aria-selected', on ? 'true' : 'false');
					t.tabIndex = on ? 0 : -1;
					t.classList.toggle('is-active', on);
				});
				panels.forEach(function (p, i) {
					var on = i === idx;
					p.classList.toggle('is-active', on);
					if (on) { p.removeAttribute('hidden'); } else { p.setAttribute('hidden', ''); }
				});
				if (focus && tabs[idx]) tabs[idx].focus();
			}
			tabs.forEach(function (t, i) {
				t.addEventListener('click', function () { select(i, false); });
				t.addEventListener('keydown', function (e) {
					var n = null;
					if (e.key === 'ArrowRight' || e.key === 'ArrowDown') { n = (i + 1) % tabs.length; }
					else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') { n = (i - 1 + tabs.length) % tabs.length; }
					else if (e.key === 'Home') { n = 0; }
					else if (e.key === 'End') { n = tabs.length - 1; }
					if (n !== null) { e.preventDefault(); select(n, true); }
				});
			});
		});
	}

	function initFlipbar() {
		var bar = document.querySelector('[data-seopro-flip]');
		if (!bar) return;
		var line = bar.querySelector('.seopro-flipbar__line');
		var dataEl = bar.querySelector('.seopro-flipbar__data');
		if (!line || !dataEl) return;
		var items;
		try { items = JSON.parse(dataEl.textContent); } catch (e) { return; }
		if (!items || items.length < 2) return;
		// Otomatik değişen içerik motion'dır → reduced-motion'da ilk başlık sabit kalır.
		if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
		var i = 0;
		var paused = false;
		var interval = parseInt(bar.getAttribute('data-interval'), 10) || 4000;
		// WCAG 2.2.2: otomatik dönen içerik duraklatılabilir olmalı (fare üstü + klavye odağı).
		bar.addEventListener('mouseenter', function () { paused = true; });
		bar.addEventListener('mouseleave', function () { paused = false; });
		bar.addEventListener('focusin', function () { paused = true; });
		bar.addEventListener('focusout', function () { paused = false; });
		window.setInterval(function () {
			if (paused) return;
			i = (i + 1) % items.length;
			var it = items[i];
			line.classList.add('is-flipping');
			// Kenar-üstü (görünmez) anında metni değiştir: kart dönerken yenisi gelir.
			window.setTimeout(function () { line.textContent = it.t; line.setAttribute('href', it.u); }, 290);
			window.setTimeout(function () { line.classList.remove('is-flipping'); }, 620);
		}, Math.max(2500, interval));
	}

	function initLazyAds() {
		var boxes = [].slice.call(document.querySelectorAll('[data-seopro-ad-lazy]'));
		if (!boxes.length) return;
		var armed = false, io = null, libLoaded = false;

		function activate(box) {
			if (!box.hasAttribute('data-seopro-ad-lazy')) return;
			box.removeAttribute('data-seopro-ad-lazy');
			var tpl = box.querySelector('template[data-seopro-ad-code]');
			if (!tpl) return;
			var frag = tpl.content.cloneNode(true);
			// Inject ederken <script> innerHTML ile ÇALIŞMAZ → yeniden oluştur.
			// adsbygoogle kütüphanesi yalnızca BİR kez yüklensin (tekrar = render-blocking).
			[].forEach.call(frag.querySelectorAll('script'), function (old) {
				var src = old.getAttribute('src') || '';
				var isLib = /adsbygoogle\.js/.test(src);
				if (isLib && libLoaded) { if (old.parentNode) old.parentNode.removeChild(old); return; }
				if (isLib) { libLoaded = true; }
				var s = document.createElement('script');
				for (var i = 0; i < old.attributes.length; i++) {
					s.setAttribute(old.attributes[i].name, old.attributes[i].value);
				}
				s.text = old.textContent;
				old.parentNode.replaceChild(s, old);
			});
			box.appendChild(frag);
			if (tpl.parentNode) tpl.parentNode.removeChild(tpl);
			box.classList.add('is-loaded');
		}

		function arm() {
			if (armed) return;
			armed = true;
			if ('IntersectionObserver' in window) {
				io = new IntersectionObserver(function (entries) {
					entries.forEach(function (e) {
						if (e.isIntersecting) { activate(e.target); io.unobserve(e.target); }
					});
				}, { rootMargin: '400px 0px' });
				boxes.forEach(function (b) { io.observe(b); });
			} else {
				boxes.forEach(activate);
			}
		}

		var evs = ['scroll', 'pointermove', 'touchstart', 'keydown', 'wheel', 'click'];
		function onFirst() {
			evs.forEach(function (ev) { window.removeEventListener(ev, onFirst); });
			arm();
		}
		evs.forEach(function (ev) { window.addEventListener(ev, onFirst, { passive: true }); });
		// Etkileşim olmasa bile ağırlık taşımayan bir idle yedeği.
		window.setTimeout(arm, 3500);
	}

	function initAdAnchor() {
		var anchor = document.querySelector('[data-seopro-anchor]');
		if (!anchor) return;
		try { if (sessionStorage.getItem('seopro-anchor-closed') === '1') return; } catch (e) {}
		anchor.hidden = false;
		var close = anchor.querySelector('[data-seopro-anchor-close]');
		if (close) {
			close.addEventListener('click', function () {
				anchor.hidden = true;
				try { sessionStorage.setItem('seopro-anchor-closed', '1'); } catch (e) {}
			});
		}
	}

	document.addEventListener('DOMContentLoaded', function () {
		applyTheme(currentTheme());
		initProgress();
		initReveal();
		initToTop();
		initFontSize();
		initTabs();
		initFlipbar();
		initLazyAds();
		initAdAnchor();
	});
})();
