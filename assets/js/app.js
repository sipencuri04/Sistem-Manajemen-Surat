/* Sistem Manajemen Persuratan SMK - interaksi antarmuka (tanpa framework) */
(function () {
	'use strict';

	function $(sel, ctx) { return (ctx || document).querySelector(sel); }
	function $all(sel, ctx) { return Array.prototype.slice.call((ctx || document).querySelectorAll(sel)); }

	/* ---------- Sidebar mobile & Responsivitas ---------- */
	$all('[data-sidebar-toggle]').forEach(function (btn) {
		btn.addEventListener('click', function () { $('#sidebar').classList.toggle('show'); });
	});
	$all('[data-sidebar-close]').forEach(function (el) {
		el.addEventListener('click', function () { $('#sidebar').classList.remove('show'); });
	});
	// Tutup sidebar saat klik nav-link di perangkat mobile
	$all('.sidebar-nav .nav-link').forEach(function (link) {
		link.addEventListener('click', function () {
			if (window.innerWidth < 992) {
				var sb = $('#sidebar');
				if (sb) sb.classList.remove('show');
			}
		});
	});
	// Tutup sidebar jika tekan tombol Escape
	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') {
			var sb = $('#sidebar');
			if (sb && sb.classList.contains('show')) sb.classList.remove('show');
		}
	});

	// Deteksi formulir dengan sticky-actions agar navigasi bawah mobile tidak bentrok
	if ($('.sticky-actions')) {
		document.body.classList.add('has-sticky-actions');
	}

	/* ---------- Tampilkan/sembunyikan password ---------- */
	$all('[data-toggle-password]').forEach(function (btn) {
		btn.addEventListener('click', function () {
			var input = $(btn.getAttribute('data-toggle-password'));
			if (!input) return;
			input.type = input.type === 'password' ? 'text' : 'password';
			var icon = btn.querySelector('i');
			if (icon) { icon.classList.toggle('fa-eye'); icon.classList.toggle('fa-eye-slash'); }
		});
	});

	/* ---------- Konfirmasi sebelum submit (hapus, dll) ---------- */
	var pendingForm = null;
	document.addEventListener('submit', function (ev) {
		var form = ev.target;
		if (!form.hasAttribute('data-confirm') || form.dataset.confirmed === '1') return;
		ev.preventDefault();
		var modalEl = $('#confirmModal');
		if (!modalEl || typeof bootstrap === 'undefined') {
			if (window.confirm(form.getAttribute('data-confirm'))) { form.dataset.confirmed = '1'; form.submit(); }
			return;
		}
		$('#confirmMessage').textContent = form.getAttribute('data-confirm');
		pendingForm = form;
		bootstrap.Modal.getOrCreateInstance(modalEl).show();
	}, true);
	var yes = $('#confirmYes');
	if (yes) {
		yes.addEventListener('click', function () {
			if (!pendingForm) return;
			pendingForm.dataset.confirmed = '1';
			var f = pendingForm; pendingForm = null;
			bootstrap.Modal.getInstance($('#confirmModal')).hide();
			f.submit();
		});
	}

	/* ---------- Tabel baris dinamis (field type "rows") ---------- */
	function renumber(table) {
		var name = table.getAttribute('data-rows-name');
		$all('tbody tr', table).forEach(function (tr, i) {
			var no = tr.querySelector('.row-no');
			if (no) no.textContent = i + 1;
			$all('input', tr).forEach(function (input) {
				var col = input.getAttribute('data-col');
				input.name = 'f[' + name + '][' + i + '][' + col + ']';
			});
		});
	}
	$all('[data-rows-name]').forEach(function (table) {
		table.addEventListener('click', function (ev) {
			var del = ev.target.closest('[data-row-remove]');
			if (!del) return;
			var rows = $all('tbody tr', table);
			if (rows.length > 1) { del.closest('tr').remove(); }
			else { $all('input', rows[0]).forEach(function (i) { i.value = ''; }); }
			renumber(table);
		});
		var addBtn = $('[data-row-add="' + table.getAttribute('data-rows-name') + '"]');
		if (addBtn) {
			addBtn.addEventListener('click', function () {
				var tbody = table.querySelector('tbody');
				var last = tbody.querySelector('tr:last-child');
				var clone = last.cloneNode(true);
				$all('input', clone).forEach(function (i) { i.value = ''; i.classList.remove('is-invalid'); });
				tbody.appendChild(clone);
				renumber(table);
				var first = clone.querySelector('input');
				if (first) first.focus();
			});
		}
	});

	/* ---------- Mode penomoran (otomatis/manual) ---------- */
	var modeInputs = $all('input[name="number_mode"]');
	function syncMode() {
		var checked = $('input[name="number_mode"]:checked');
		var manual = checked && checked.value === 'manual';
		var box = $('#manualNumberBox'), auto = $('#autoNumberBox');
		if (box) box.classList.toggle('d-none', !manual);
		if (auto) auto.classList.toggle('d-none', manual);
	}
	modeInputs.forEach(function (i) { i.addEventListener('change', syncMode); });
	if (modeInputs.length) syncMode();

	/* ---------- Perkiraan nomor berikutnya (AJAX GET) ---------- */
	var nextBox = $('#nextNumber');
	var dateInput = $('#letter_date');
	function refreshNext() {
		if (!nextBox || !dateInput || !dateInput.value) return;
		var url = nextBox.getAttribute('data-url') + '?date=' + encodeURIComponent(dateInput.value);
		fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
			.then(function (r) { return r.json(); })
			.then(function (j) { if (j && j.number) nextBox.textContent = j.number; })
			.catch(function () {});
	}
	if (nextBox && dateInput) { dateInput.addEventListener('change', refreshNext); }

	/* ---------- Pratinjau sebelum simpan (form dikirim ke tab baru) ---------- */
	$all('[data-preview-action]').forEach(function (btn) {
		btn.addEventListener('click', function () {
			var form = btn.closest('form');
			var oldAction = form.action, oldTarget = form.target;
			form.action = btn.getAttribute('data-preview-action');
			form.target = '_blank';
			form.dataset.confirmed = '1';
			form.submit();
			form.action = oldAction; form.target = oldTarget;
			delete form.dataset.confirmed;
		});
	});

	/* ---------- Token penomoran: klik untuk menyisipkan ---------- */
	$all('[data-token]').forEach(function (el) {
		el.addEventListener('click', function () {
			var input = $('#number_format');
			if (!input) return;
			var start = input.selectionStart || input.value.length;
			input.value = input.value.slice(0, start) + el.getAttribute('data-token') + input.value.slice(input.selectionEnd || start);
			input.focus();
		});
	});

	/* ---------- Pratinjau gambar upload ---------- */
	$all('input[type="file"][data-preview]').forEach(function (input) {
		input.addEventListener('change', function () {
			var img = $(input.getAttribute('data-preview'));
			if (!img || !input.files || !input.files[0]) return;
			img.src = URL.createObjectURL(input.files[0]);
			img.classList.remove('d-none');
		});
	});

	/* ---------- Grafik dashboard ---------- */
	var chartEl = $('#monthlyChart');
	if (chartEl && typeof Chart !== 'undefined') {
		var payload = JSON.parse(chartEl.getAttribute('data-chart') || '{}');
		new Chart(chartEl, {
			type: 'bar',
			data: {
				labels: payload.labels || [],
				datasets: [{
					label: 'Jumlah surat',
					data: payload.values || [],
					backgroundColor: 'rgba(29,111,224,.85)',
					hoverBackgroundColor: '#0b2a5b',
					borderRadius: 6,
					maxBarThickness: 36
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				plugins: { legend: { display: false } },
				scales: {
					y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#eef2f7' } },
					x: { grid: { display: false } }
				}
			}
		});
	}

	/* ---------- Pop-up kegunaan jenis surat (tombol ! pada kartu) ---------- */
	var guideModal = $('#guideModal');
	if (guideModal) {
		document.addEventListener('click', function (ev) {
			var btn = ev.target.closest('[data-guide]');
			if (!btn) return;
			ev.preventDefault();
			var g = {};
			try { g = JSON.parse(btn.getAttribute('data-guide')); } catch (e) { return; }
			$('#guideTitle').textContent = g.name || '';
			$('#guideCode').textContent = g.code || '';
			$('#guidePurpose').textContent = g.purpose || 'Belum ada keterangan untuk jenis surat ini.';
			$('#guideLayout').textContent = g.layout || '';
			var ul = $('#guideWhen'); ul.innerHTML = '';
			(g.when || []).forEach(function (w) { var li = document.createElement('li'); li.textContent = w; ul.appendChild(li); });
			$('#guideWhenBox').classList.toggle('d-none', !(g.when || []).length);
			var req = $('#guideRequired'); req.innerHTML = '';
			(g.required || []).forEach(function (r) { var b = document.createElement('span'); b.className = 'badge text-bg-light border'; b.textContent = r; req.appendChild(b); });
			$('#guideReqBox').classList.toggle('d-none', !(g.required || []).length);
			$('#guideTip').textContent = g.tip || '';
			$('#guideTipBox').classList.toggle('d-none', !g.tip);
			var create = $('#guideCreate');
			create.classList.toggle('d-none', !g.url);
			if (g.url) create.setAttribute('href', g.url);
			bootstrap.Modal.getOrCreateInstance(guideModal).show();
		});
	}

	/* ---------- Salin teks (kode permohonan) ---------- */
	$all('[data-copy]').forEach(function (btn) {
		btn.addEventListener('click', function () {
			var el = $(btn.getAttribute('data-copy'));
			if (!el) return;
			var text = el.textContent.trim();
			var done = function () { btn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Tersalin'; };
			if (navigator.clipboard && window.isSecureContext) { navigator.clipboard.writeText(text).then(done); }
			else { var t = document.createElement('textarea'); t.value = text; document.body.appendChild(t); t.select(); try { document.execCommand('copy'); done(); } catch (e) {} t.remove(); }
		});
	});

	/* ---------- Filter otomatis saat pilihan berubah ---------- */
	$all('[data-autosubmit]').forEach(function (el) {
		el.addEventListener('change', function () { el.form.submit(); });
	});

	/* ---------- Instant Navigation & Hover Prefetcher (LCP Sub-100ms) ---------- */
	(function initInstantNav() {
		// Jangan lakukan prefetch jika mode hemat kuota (Data Saver) atau koneksi lambat 2G
		if (navigator.connection && (navigator.connection.saveData || /2g/.test(navigator.connection.effectiveType))) {
			return;
		}

		var prefetchedUrls = new Set();
		var hoverTimer = null;

		function isSafeLink(a) {
			if (!a || a.tagName !== 'A') return false;
			var href = a.getAttribute('href');
			if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:')) return false;
			if (a.hasAttribute('download') || a.hasAttribute('data-no-instant') || a.target === '_blank') return false;

			try {
				var url = new URL(href, window.location.href);
				if (url.origin !== window.location.origin) return false;
				var path = url.pathname.toLowerCase();
				// Hindari prefetch aksi destruktif atau file binary berat
				if (/logout|hapus|delete|batal|cetak|download/.test(path)) return false;
				return url.href;
			} catch (e) {
				return false;
			}
		}

		function prefetchUrl(url) {
			if (prefetchedUrls.has(url)) return;
			prefetchedUrls.add(url);

			var link = document.createElement('link');
			link.rel = 'prefetch';
			link.href = url;
			link.as = 'document';
			document.head.appendChild(link);
		}

		// Hover listener dengan jeda 65ms
		document.addEventListener('mouseover', function (e) {
			var a = e.target.closest('a');
			var validUrl = isSafeLink(a);
			if (!validUrl) return;

			clearTimeout(hoverTimer);
			hoverTimer = setTimeout(function () {
				prefetchUrl(validUrl);
			}, 65);
		}, { passive: true });

		document.addEventListener('mouseout', function (e) {
			clearTimeout(hoverTimer);
		}, { passive: true });

		// Touchstart untuk smartphone / tablet
		document.addEventListener('touchstart', function (e) {
			var a = e.target.closest('a');
			var validUrl = isSafeLink(a);
			if (validUrl) {
				prefetchUrl(validUrl);
			}
		}, { passive: true });
	})();
})();
