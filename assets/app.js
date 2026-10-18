/* Pedeset — 50godina front-end */
(function () {
	'use strict';
	var D = window.PEDESET || { places: [] };
	var places = D.places || [];

	function $(s, c) { return (c || document).querySelector(s); }
	function $$(s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); }
	function clamp(v, a, b) { return Math.max(a, Math.min(b, v)); }
	function norm(s) { return (s || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/đ/g, 'd'); }

	/* ---------- Usporedni klizač (s uvećanjem) ---------- */
	function Compare(el) {
		var stage = $('[data-stage]', el);
		var old = $('.cmp-old', el);
		var range = $('.cmp-range', el);
		var st = { pos: 50, s: 1, x: 0, y: 0 };
		var pointers = {};
		var mode = null; // 'slide' | 'pan' | 'pinch'
		var start = null;

		function size() { return el.getBoundingClientRect(); }
		function bound() {
			var r = size();
			var minX = r.width - r.width * st.s, minY = r.height - r.height * st.s;
			st.x = clamp(st.x, minX, 0);
			st.y = clamp(st.y, minY, 0);
		}
		function render() {
			var r = size();
			el.style.setProperty('--pos', st.pos + '%');
			stage.style.transform = st.s === 1 ? '' : 'translate(' + st.x + 'px,' + st.y + 'px) scale(' + st.s + ')';
			// Rez u koordinatama pozornice
			var screenX = r.width * st.pos / 100;
			var stageX = (screenX - st.x) / st.s;
			var right = Math.max(0, r.width - stageX);
			old.style.clipPath = 'inset(0 ' + right + 'px 0 0)';
			el.classList.toggle('is-zoomed', st.s > 1.001);
			range.value = st.pos;
		}
		function setPos(clientX) {
			var r = size();
			st.pos = clamp((clientX - r.left) / r.width * 100, 0, 100);
			render();
		}
		function zoomAt(factor, cx, cy) {
			var r = size();
			var px = (cx === undefined ? r.width / 2 : cx - r.left);
			var py = (cy === undefined ? r.height / 2 : cy - r.top);
			var ns = clamp(st.s * factor, 1, 6);
			var k = ns / st.s;
			st.x = px - (px - st.x) * k;
			st.y = py - (py - st.y) * k;
			st.s = ns;
			if (st.s === 1) { st.x = 0; st.y = 0; }
			bound();
			render();
		}
		this.zoom = function (dir) {
			if (dir === 'reset') { st.s = 1; st.x = 0; st.y = 0; render(); return; }
			zoomAt(dir === 'in' ? 1.6 : 1 / 1.6);
		};

		range.addEventListener('input', function () { st.pos = +range.value; render(); });

		el.addEventListener('pointerdown', function (e) {
			pointers[e.pointerId] = { x: e.clientX, y: e.clientY };
			var ids = Object.keys(pointers);
			if (ids.length === 2) {
				var a = pointers[ids[0]], b = pointers[ids[1]];
				mode = 'pinch';
				start = { d: Math.hypot(a.x - b.x, a.y - b.y), s: st.s };
				return;
			}
			var r = size();
			var divX = r.left + r.width * st.pos / 100;
			if (st.s > 1.001 && Math.abs(e.clientX - divX) > 28) {
				mode = 'pan';
				start = { x: e.clientX, y: e.clientY, tx: st.x, ty: st.y };
			} else {
				mode = 'slide';
				if (e.pointerType === 'mouse') { setPos(e.clientX); }
				start = { x: e.clientX, y: e.clientY, decided: e.pointerType === 'mouse' };
			}
			try { el.setPointerCapture(e.pointerId); } catch (err) {}
		});
		el.addEventListener('pointermove', function (e) {
			if (!pointers[e.pointerId]) { return; }
			pointers[e.pointerId] = { x: e.clientX, y: e.clientY };
			if (mode === 'pinch') {
				var ids = Object.keys(pointers);
				if (ids.length < 2) { return; }
				var a = pointers[ids[0]], b = pointers[ids[1]];
				var d = Math.hypot(a.x - b.x, a.y - b.y);
				var target = clamp(start.s * d / start.d, 1, 6);
				zoomAt(target / st.s, (a.x + b.x) / 2, (a.y + b.y) / 2);
				e.preventDefault();
			} else if (mode === 'pan') {
				st.x = start.tx + (e.clientX - start.x);
				st.y = start.ty + (e.clientY - start.y);
				bound();
				render();
				e.preventDefault();
			} else if (mode === 'slide') {
				if (!start.decided) {
					var dx = Math.abs(e.clientX - start.x), dy = Math.abs(e.clientY - start.y);
					if (dx < 4 && dy < 4) { return; }
					if (dy > dx && st.s === 1) { mode = null; return; } // okomito = skrolanje
					start.decided = true;
				}
				setPos(e.clientX);
			}
		});
		function end(e) {
			delete pointers[e.pointerId];
			if (Object.keys(pointers).length === 0) { mode = null; }
		}
		el.addEventListener('pointerup', end);
		el.addEventListener('pointercancel', end);
		el.addEventListener('wheel', function (e) {
			if (!el.hasAttribute('data-zoomable')) { return; }
			if (!e.ctrlKey && st.s === 1) { return; }
			e.preventDefault();
			zoomAt(e.deltaY < 0 ? 1.15 : 1 / 1.15, e.clientX, e.clientY);
		}, { passive: false });
		el.addEventListener('dblclick', function (e) {
			if (!el.hasAttribute('data-zoomable')) { return; }
			if (st.s > 1.001) { st.s = 1; st.x = 0; st.y = 0; render(); } else { zoomAt(2.5, e.clientX, e.clientY); }
		});
		window.addEventListener('resize', function () { bound(); render(); });
		render();
	}

	var compares = $$('[data-cmp]').map(function (el) { return new Compare(el); });
	var placeCmp = $('.place-cmp [data-cmp]');
	if (placeCmp) {
		placeCmp.setAttribute('data-zoomable', '');
		var ctl = compares[$$('[data-cmp]').indexOf(placeCmp)];
		$$('[data-zoom]').forEach(function (b) {
			b.addEventListener('click', function () { ctl.zoom(b.getAttribute('data-zoom')); });
		});
	}

	/* ---------- Dijeljenje ---------- */
	function toast(msg) {
		var t = document.createElement('div');
		t.className = 'toast';
		t.textContent = msg;
		document.body.appendChild(t);
		setTimeout(function () { t.remove(); }, 2200);
	}
	$$('[data-share]').forEach(function (b) {
		b.addEventListener('click', function () {
			var data = { title: document.title, url: location.href.split('#')[0] };
			if (navigator.share) {
				navigator.share(data).catch(function () {});
			} else if (navigator.clipboard) {
				navigator.clipboard.writeText(data.url).then(function () { toast('Link kopiran'); });
			}
		});
	});

	/* ---------- Tražilica i nasumično ---------- */
	function findPlace(q) {
		q = norm(q).trim();
		if (!q) { return null; }
		var exact = places.filter(function (p) { return norm(p.t) === q; })[0];
		if (exact) { return exact; }
		return places.filter(function (p) { return norm(p.t).indexOf(q) === 0; })[0] ||
			places.filter(function (p) { return norm(p.t).indexOf(q) > -1; })[0] || null;
	}
	var finder = $('.finder');
	if (finder) {
		var fq = $('#finder-q');
		var go = function () {
			var p = findPlace(fq.value);
			if (p) { location.href = p.u; } else { toast('Nema tog mjesta — pogledaj popis'); location.hash = 'mjesta'; }
		};
		finder.addEventListener('submit', function (e) { e.preventDefault(); go(); });
		fq.addEventListener('change', function () { var p = findPlace(fq.value); if (p && norm(p.t) === norm(fq.value)) { location.href = p.u; } });
	}
	$$('[data-random]').forEach(function (b) {
		b.addEventListener('click', function () {
			if (places.length) { location.href = places[Math.floor(Math.random() * places.length)].u; }
		});
	});

	/* ---------- Filtar popisa ---------- */
	var grid = $('[data-grid]');
	if (grid) {
		var items = $$('li', grid);
		var kind = 'all';
		var gq = $('[data-grid-q]');
		var count = $('[data-count]');
		var apply = function () {
			var q = norm(gq ? gq.value : '');
			var n = 0;
			items.forEach(function (li) {
				var ok = (kind === 'all' || li.getAttribute('data-k') === kind) && (!q || norm(li.getAttribute('data-t')).indexOf(q) > -1);
				li.hidden = !ok;
				if (ok) { n++; }
			});
			if (count) { count.textContent = n; }
		};
		$$('[data-filter]').forEach(function (b) {
			b.addEventListener('click', function () {
				kind = b.getAttribute('data-filter');
				$$('[data-filter]').forEach(function (x) { x.classList.toggle('is-on', x === b); });
				apply();
			});
		});
		if (gq) { gq.addEventListener('input', apply); }
	}

	/* ---------- Karta ---------- */
	var homeMap = $('#home-map');
	if (homeMap) {
		var initMap = function () {
			var L = window.L;
			if (!L) { return; }
			var mobile = window.matchMedia('(pointer:coarse)').matches;
			var map = L.map(homeMap, {
				center: [44.6, 16.4], zoom: 7, minZoom: 6, maxZoom: 16,
				scrollWheelZoom: false, dragging: !mobile, tap: false,
				maxBounds: [[41.5, 12.0], [47.5, 20.6]]
			});
			L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
				maxZoom: 19,
				attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
			}).addTo(map);
			map.on('click focus', function () { map.scrollWheelZoom.enable(); });
			homeMap.addEventListener('mouseleave', function () { map.scrollWheelZoom.disable(); });
			var pts = [];
			places.forEach(function (p) {
				if (!p.lat) { return; }
				var icon = L.divIcon({ className: '', html: '<span class="pin' + (p.k === 'mjesto' ? ' is-mjesto' : '') + '"></span>', iconSize: [14, 14], iconAnchor: [7, 7] });
				var m = L.marker([p.lat, p.lng], { icon: icon, title: p.t, keyboard: true }).addTo(map);
				var html = '<div class="pop"><a href="' + p.u + '"><img src="' + p.a + '" alt="" loading="lazy">' +
					p.t.replace(/</g, '&lt;') + '<small>Usporedi 1968. i 2018. →</small></a></div>';
				m.bindPopup(html, { maxWidth: 240, autoPanPadding: [20, 60] });
				pts.push([p.lat, p.lng]);
			});
			if (pts.length) { map.fitBounds(pts, { padding: [24, 24], maxZoom: 8 }); }
		};
		var started = false;
		var start = function () {
			if (started) { return; }
			started = true;
			if (window.L) { initMap(); } else { window.addEventListener('load', initMap); }
		};
		if ('IntersectionObserver' in window) {
			var io = new IntersectionObserver(function (en) {
				if (en[0].isIntersecting) { io.disconnect(); start(); }
			}, { rootMargin: '300px' });
			io.observe(homeMap);
		} else {
			start();
		}
	}
})();
