/* Pedeset extras: izbornik za dijeljenje, položaj klizača u linku (?p=), forma za prijedloge */
(function () {
	'use strict';
	var X = window.PEDESET_X || {};
	function $(s, c) { return (c || document).querySelector(s); }

	var range = $('.place-cmp .cmp-range');

	/* ?r=NN postavlja klizač (0–100) */
	try {
		var p = parseFloat(new URLSearchParams(location.search).get('r'));
		if (range && isFinite(p) && p >= 0 && p <= 100) {
			range.value = p;
			range.dispatchEvent(new Event('input', { bubbles: true }));
		}
	} catch (e) {}

	function toast(msg) {
		var t = document.createElement('div');
		t.className = 'toast';
		t.textContent = msg;
		document.body.appendChild(t);
		setTimeout(function () { t.remove(); }, 2200);
	}
	function shareUrl() {
		var u = location.origin + location.pathname;
		var pos = range ? Math.round(parseFloat(range.value)) : 50;
		if (isFinite(pos) && Math.abs(pos - 50) > 2) { u += '?r=' + pos; }
		return u;
	}

	/* Izbornik za dijeljenje */
	var btn = $('[data-share]');
	var menu;
	function closeMenu() { if (menu) { menu.remove(); menu = null; } }
	function openMenu() {
		closeMenu();
		var url = shareUrl();
		var title = document.title;
		var text = encodeURIComponent(title + ' ' + url);
		var eu = encodeURIComponent(url);
		var items = [
			['WhatsApp', 'https://wa.me/?text=' + text],
			['Facebook', 'https://www.facebook.com/sharer/sharer.php?u=' + eu],
			['X', 'https://twitter.com/intent/tweet?text=' + encodeURIComponent(title) + '&url=' + eu],
			['E-mail', 'mailto:?subject=' + encodeURIComponent(title) + '&body=' + encodeURIComponent(url)]
		];
		menu = document.createElement('div');
		menu.className = 'share-menu';
		menu.setAttribute('role', 'menu');
		items.forEach(function (it) {
			var a = document.createElement('a');
			a.href = it[1]; a.textContent = it[0]; a.setAttribute('role', 'menuitem');
			if (it[0] !== 'E-mail') { a.target = '_blank'; a.rel = 'noopener'; }
			menu.appendChild(a);
		});
		var c = document.createElement('button');
		c.type = 'button'; c.textContent = 'Kopiraj link'; c.setAttribute('role', 'menuitem');
		c.addEventListener('click', function () {
			var done = function () { toast('Link kopiran'); closeMenu(); };
			if (navigator.clipboard) { navigator.clipboard.writeText(url).then(done, done); } else { done(); }
		});
		menu.appendChild(c);
		if (navigator.share) {
			var n = document.createElement('button');
			n.type = 'button'; n.textContent = 'Više opcija…'; n.setAttribute('role', 'menuitem');
			n.addEventListener('click', function () { navigator.share({ title: title, url: url }).catch(function () {}); closeMenu(); });
			menu.appendChild(n);
		}
		btn.parentNode.style.position = 'relative';
		btn.parentNode.appendChild(menu);
	}
	if (btn) {
		document.addEventListener('click', function (e) {
			if (e.target === btn || btn.contains(e.target)) {
				e.stopImmediatePropagation();
				e.preventDefault();
				if (menu) { closeMenu(); } else { openMenu(); }
			} else if (menu && !menu.contains(e.target)) {
				closeMenu();
			}
		}, true);
		document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { closeMenu(); } });
	}

	/* Forma za prijedloge */
	var f = $('[data-suggest]');
	if (f) {
		var loaded = Date.now();
		f.elements.ts.value = loaded;
		var msg = $('.suggest-msg', f);
		f.addEventListener('submit', function (e) {
			e.preventDefault();
			msg.className = 'suggest-msg';
			if (f.elements.message.value.trim().length < 10) {
				msg.textContent = 'Napiši barem nekoliko riječi.';
				msg.classList.add('err');
				return;
			}
			var fd = new FormData(f);
			fd.append('action', 'g50_suggest');
			fd.append('place', X.place);
			var b = $('button[type=submit]', f);
			b.disabled = true;
			fetch(X.ajax, { method: 'POST', body: fd, credentials: 'same-origin' })
				.then(function (r) { return r.json(); })
				.then(function (j) {
					var m = j && j.data && j.data.message;
					msg.textContent = m || (j.success ? 'Hvala!' : 'Nešto je pošlo po zlu.');
					if (j.success) { f.reset(); msg.classList.add('ok'); } else { msg.classList.add('err'); b.disabled = false; }
				})
				.catch(function () { msg.textContent = 'Nema veze, pokušaj ponovno.'; msg.classList.add('err'); b.disabled = false; });
		});
	}
})();
