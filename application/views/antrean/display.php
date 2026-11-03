<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Monitor Antrean &middot; <?= e($school['school_name'] ?: 'SMK Muhammadiyah Bandongan') ?></title>
	<link rel="stylesheet" href="<?= asset_url('assets/vendor/bootstrap/css/bootstrap.min.css') ?>">
	<link rel="stylesheet" href="<?= asset_url('assets/vendor/fontawesome/css/all.min.css') ?>">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@700;800&display=swap" rel="stylesheet">
	<style>
		:root {
			--page-bg: #f0f4f9;
			--header-bg: #093c85;
			--brand-blue: #0d5ecc;
			--text-navy: #0f172a;
			--card-border: #e2e8f0;
		}
		* { box-sizing: border-box; }
		body {
			font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
			background-color: var(--page-bg);
			color: #1e293b;
			min-height: 100vh;
			display: flex;
			flex-direction: column;
			margin: 0;
			padding: 0;
			overflow-x: hidden;
		}

		/* 1. Header Atas */
		.tv-header {
			background: linear-gradient(90deg, #072a63 0%, #0c3e8a 60%, #0f4699 100%);
			padding: 12px 28px;
			color: #ffffff;
			display: flex;
			justify-content: space-between;
			align-items: center;
			box-shadow: 0 4px 15px rgba(7, 42, 99, 0.2);
			position: relative;
			z-index: 10;
		}
		.school-logo-img {
			width: 46px;
			height: 46px;
			object-fit: contain;
			filter: drop-shadow(0 2px 6px rgba(0,0,0,0.25));
		}
		.tv-time-box {
			background: #ffffff;
			border-radius: 14px;
			padding: 6px 18px;
			text-align: center;
			box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
			min-width: 175px;
		}
		.tv-clock-num {
			font-family: 'JetBrains Mono', monospace;
			font-weight: 800;
			font-size: 18.5px;
			color: #0b2548;
			letter-spacing: 0.5px;
			line-height: 1.2;
		}
		.tv-date-sub {
			font-size: 11px;
			color: #64748b;
			font-weight: 600;
		}

		/* 2. Main Content Grid */
		.tv-main-container {
			flex: 1;
			padding: 18px 24px;
			display: flex;
			gap: 20px;
		}
		.tv-col-left {
			flex: 1.25;
			display: flex;
			flex-direction: column;
			gap: 16px;
		}
		.tv-col-right {
			flex: 1;
			display: flex;
			flex-direction: column;
			gap: 12px;
		}

		/* 3. Kartu Panggilan Antrean (Biru Raksasa) */
		.card-call-hero {
			background: linear-gradient(135deg, #0959c8 0%, #1570e6 100%);
			border-radius: 20px;
			padding: 20px 24px 22px 24px;
			box-shadow: 0 10px 25px rgba(13, 94, 204, 0.22);
			color: #ffffff;
			display: flex;
			flex-direction: column;
			justify-content: space-between;
		}
		.call-hero-header {
			display: flex;
			justify-content: space-between;
			align-items: center;
		}
		.call-hero-title {
			font-size: 19px;
			font-weight: 800;
			letter-spacing: 0.2px;
			display: flex;
			align-items: center;
			gap: 10px;
		}
		.call-hero-sub {
			font-size: 13.5px;
			color: rgba(255, 255, 255, 0.9);
			font-weight: 500;
		}

		/* Kotak Putih Nomor Tiket */
		.call-white-box {
			background: #ffffff;
			border-radius: 18px;
			padding: 18px 24px;
			margin: 14px 0 4px 0;
			text-align: center;
			box-shadow: 0 8px 22px rgba(0, 0, 0, 0.08);
			position: relative;
			transition: all 0.35s ease;
		}
		.call-white-box.is-calling-pulse {
			animation: cardPulse 1.1s infinite alternate;
		}
		@keyframes cardPulse {
			0% { transform: scale(1); box-shadow: 0 0 15px rgba(13, 94, 204, 0.3); }
			100% { transform: scale(1.025); box-shadow: 0 0 35px rgba(13, 94, 204, 0.7); }
		}
		.loket-pill-badge {
			display: inline-block;
			background: #dbeafe;
			color: #1e40af;
			font-weight: 800;
			font-size: 17px;
			padding: 5px 36px;
			border-radius: 30px;
			letter-spacing: 1px;
			text-transform: uppercase;
		}
		.ticket-number-giant {
			font-size: 74px;
			font-weight: 900;
			color: #0b2548;
			line-height: 1.1;
			margin: 6px 0 2px 0;
			letter-spacing: 2px;
			font-family: 'JetBrains Mono', -apple-system, sans-serif;
		}
		.service-caption-text {
			font-size: 14px;
			font-weight: 600;
			color: #475569;
		}

		/* 4. Kartu Profil Sekolah (Bawah Kiri) */
		.card-school-profile {
			background: #ffffff;
			border-radius: 18px;
			border: 1px solid var(--card-border);
			box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
			overflow: hidden;
			position: relative;
			padding: 16px 20px;
			display: flex;
			align-items: center;
			justify-content: space-between;
			min-height: 130px;
		}
		.school-profile-left {
			display: flex;
			align-items: center;
			gap: 16px;
			z-index: 2;
			max-width: 72%;
		}
		.school-profile-logo {
			width: 58px;
			height: 58px;
			object-fit: contain;
		}
		.school-profile-divider {
			width: 1.5px;
			height: 48px;
			background-color: #e2e8f0;
		}
		.school-profile-name {
			font-size: 17px;
			font-weight: 800;
			color: #0f172a;
			margin-bottom: 2px;
		}
		.school-profile-addr {
			font-size: 12px;
			color: #64748b;
			margin-bottom: 8px;
			display: flex;
			align-items: center;
			gap: 5px;
		}
		.badge-feature {
			display: inline-flex;
			align-items: center;
			gap: 5px;
			padding: 4px 12px;
			border-radius: 20px;
			font-size: 11px;
			font-weight: 700;
		}
		.badge-feature-blue {
			background: #e0f2fe;
			color: #0284c7;
		}
		.badge-feature-indigo {
			background: #e0e7ff;
			color: #4338ca;
		}
		.school-bg-crop {
			position: absolute;
			right: 0;
			top: 0;
			height: 100%;
			width: 32%;
			object-fit: cover;
			opacity: 0.9;
			mask-image: linear-gradient(to right, transparent 0%, black 40%);
			-webkit-mask-image: linear-gradient(to right, transparent 0%, black 40%);
			z-index: 1;
		}

		/* 5. Kolom Kanan: Status Seluruh Loket */
		.loket-section-header {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-bottom: 2px;
			padding: 0 4px;
		}
		.loket-section-title {
			font-size: 17.5px;
			font-weight: 800;
			color: #0f172a;
			display: flex;
			align-items: center;
			gap: 8px;
		}
		.badge-active-live {
			background: #15803d;
			color: #ffffff;
			border-radius: 30px;
			padding: 5px 14px;
			font-size: 11.5px;
			font-weight: 700;
			display: inline-flex;
			align-items: center;
			gap: 6px;
		}

		/* Card Loket Satuan */
		.loket-card-item {
			background: #ffffff;
			border-radius: 16px;
			padding: 16px 18px;
			border: 1.5px solid var(--card-border);
			box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
			display: flex;
			align-items: center;
			justify-content: space-between;
			transition: all 0.25s ease;
		}
		.loket-card-item.is-active-card {
			border-color: #93c5fd;
			background: #fbfdff;
			box-shadow: 0 6px 16px rgba(37, 99, 235, 0.08);
		}
		.loket-icon-box {
			width: 44px;
			height: 44px;
			border-radius: 12px;
			background: #eff6ff;
			color: #2563eb;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 19px;
			flex-shrink: 0;
			margin-right: 14px;
		}
		.loket-info-title {
			font-size: 14.5px;
			font-weight: 800;
			color: #0f172a;
			line-height: 1.2;
		}
		.loket-info-desc {
			font-size: 13.5px;
			font-weight: 700;
			color: #1e293b;
			margin-top: 2px;
		}
		.loket-info-waiting {
			font-size: 12px;
			color: #64748b;
			margin-top: 3px;
		}
		.loket-right-status {
			display: flex;
			align-items: center;
			gap: 10px;
		}
		.status-serving-badge {
			background: #eff6ff;
			color: #1d4ed8;
			border-radius: 20px;
			padding: 4px 12px;
			font-size: 11.5px;
			font-weight: 700;
			display: inline-flex;
			align-items: center;
			gap: 5px;
			white-space: nowrap;
		}
		.chevron-icon {
			color: #94a3b8;
			font-size: 14px;
			font-weight: 800;
		}

		/* 6. Running Text Footer */
		.tv-footer-bar {
			background: #ffffff;
			border-top: 1px solid var(--card-border);
			height: 48px;
			display: flex;
			align-items: center;
			padding: 0 20px;
			box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.02);
		}
		.marquee-badge-trapezoid {
			background: #0d5ecc;
			color: #ffffff;
			padding: 5px 16px;
			font-weight: 800;
			font-size: 13px;
			text-transform: uppercase;
			border-radius: 6px;
			display: flex;
			align-items: center;
			gap: 6px;
			transform: skewX(-10deg);
			flex-shrink: 0;
		}
		.marquee-badge-trapezoid span {
			transform: skewX(10deg);
		}
		.footer-vertical-line {
			width: 1px;
			height: 22px;
			background-color: #cbd5e1;
			margin: 0 16px;
			flex-shrink: 0;
		}
		.marquee-text-content {
			font-size: 14px;
			font-weight: 600;
			color: #1e293b;
		}

		/* 7. Controls Floating */
		.floating-tv-tools {
			position: fixed;
			bottom: 56px;
			right: 20px;
			display: flex;
			gap: 8px;
			z-index: 999;
			opacity: 0.25;
			transition: opacity 0.3s ease;
		}
		.floating-tv-tools:hover { opacity: 1; }
	</style>
</head>
<body>

<!-- Header Atas -->
<header class="tv-header">
	<div class="d-flex align-items-center gap-3">
		<img src="<?= asset_url('assets/img/logo-sekolah.png') ?>" alt="Logo Sekolah" class="school-logo-img" onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/4/41/Muhammadiyah_Logo.png'">
		<div>
			<div class="fw-black text-white fs-5" style="letter-spacing: 0.3px; line-height: 1.2;">
				<?= e($school['school_name'] ?: 'SMK Muhammadiyah Bandongan') ?>
			</div>
			<div class="text-white text-opacity-75 small" style="font-size: 12.5px;">
				Monitor Antrean Pelayanan Tata Usaha Digital
			</div>
		</div>
	</div>

	<div class="tv-time-box">
		<div class="tv-clock-num" id="liveDisplayClock">
			<i class="fa-regular fa-clock me-1 text-primary"></i> <span id="clockDigits">--:--:--</span>
		</div>
		<div class="tv-date-sub" id="liveDisplayDate"><?= tgl_indo(date('Y-m-d')) ?></div>
	</div>
</header>

<!-- Main Grid -->
<main class="tv-main-container">
	
	<!-- Kolom Kiri: Panggilan Antrean & Profil Sekolah -->
	<div class="tv-col-left">
		
		<!-- Kartu Biru Panggilan Antrean -->
		<div class="card-call-hero">
			<div class="call-hero-header">
				<div class="call-hero-title">
					<i class="fa-solid fa-bullhorn fs-5"></i> Panggilan Antrean
				</div>
				<div class="call-hero-sub" id="callStatusSub">Memanggil Nomor</div>
			</div>

			<!-- Kotak Putih Raksasa -->
			<div class="call-white-box" id="mainCallBox">
				<div>
					<span class="loket-pill-badge" id="mainLoketBadge">
						LOKET <?= !empty($last_called) ? (int) $last_called['loket_number'] : 1 ?>
					</span>
				</div>

				<div class="ticket-number-giant" id="mainTicketNumber">
					<?= !empty($last_called) ? e($last_called['ticket_number']) : 'A-001' ?>
				</div>

				<div class="service-caption-text" id="mainServiceCaption">
					<?= !empty($last_called) ? e($last_called['service_name']) : 'Pelayanan Tata Usaha' ?>
				</div>
			</div>
		</div>

		<!-- Kartu Profil SMK Muhammadiyah Bandongan (Bawah Kiri) -->
		<div class="card-school-profile">
			<div class="school-profile-left">
				<img src="<?= asset_url('assets/img/logo-sekolah.png') ?>" alt="Logo" class="school-profile-logo" onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/4/41/Muhammadiyah_Logo.png'">
				<div class="school-profile-divider"></div>
				<div>
					<div class="school-profile-name"><?= e($school['school_name'] ?: 'SMK Muhammadiyah Bandongan') ?></div>
					<div class="school-profile-addr">
						<i class="fa-solid fa-location-dot text-primary"></i> 
						<span><?= e($school['school_address'] ?: 'Jl. Bandongan – Windusari Km. 1, Kec. Magelang, Kab. Magelang 56152') ?></span>
					</div>
					<div class="d-flex gap-2">
						<span class="badge-feature badge-feature-blue">
							<i class="fa-solid fa-circle-check"></i> Tertib &amp; Transparan
						</span>
						<span class="badge-feature badge-feature-indigo">
							<i class="fa-solid fa-bolt"></i> Layanan Cepat &amp; Akurat
						</span>
					</div>
				</div>
			</div>

			<!-- Background Gedung Sekolah Crop -->
			<img src="<?= asset_url('assets/img/gedung-smk.jpg') ?>" alt="Gedung Sekolah" class="school-bg-crop" onerror="this.style.display='none'">
		</div>

	</div>

	<!-- Kolom Kanan: Status Loket Pelayanan -->
	<div class="tv-col-right">
		<div class="loket-section-header">
			<div class="loket-section-title">
				<i class="fa-solid fa-layer-group text-primary"></i> Status Loket Pelayanan
			</div>
			<div class="badge-active-live">
				<i class="fa-solid fa-circle" style="font-size: 8px;"></i> Pelayanan Berlangsung
			</div>
		</div>

		<!-- List Loket 1, 2, 3 -->
		<div class="d-flex flex-column gap-3 justify-content-between flex-grow-1" id="loketCardsList">
			<?php 
			$service_icons = array(
				1 => 'fa-file-lines',
				2 => 'fa-coins',
				3 => 'fa-user-group',
			);
			foreach ($lokets as $num => $lk): 
				$icon = isset($service_icons[$num]) ? $service_icons[$num] : 'fa-desktop';
				$is_active = ($lk['current_ticket'] !== '-');
			?>
				<div class="loket-card-item <?= $is_active ? 'is-active-card' : '' ?>" id="loketCardBox_<?= $num ?>">
					<div class="d-flex align-items-center">
						<div class="loket-icon-box">
							<i class="fa-solid <?= $icon ?>"></i>
						</div>
						<div>
							<div class="loket-info-title">Loket <?= $num ?> (<?= e($lk['service_code']) ?>)</div>
							<div class="loket-info-desc"><?= e($lk['service_name']) ?></div>
							<div class="loket-info-waiting">
								Sisa Menunggu: <strong id="waitingText_<?= $num ?>"><?= (int) $lk['waiting_count'] ?></strong> Antrean
							</div>
						</div>
					</div>

					<div class="loket-right-status">
						<div class="status-serving-badge" id="servingBadge_<?= $num ?>">
							<i class="fa-solid fa-circle text-primary" style="font-size: 7px;"></i>
							<span><?= $is_active ? e($lk['current_ticket']) : 'Sedang Dilayani' ?></span>
						</div>
						<div class="chevron-icon">&rsaquo;</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

	</div>

</main>

<!-- Footer Running Text -->
<footer class="tv-footer-bar">
	<div class="marquee-badge-trapezoid">
		<span><i class="fa-solid fa-bullhorn me-1"></i> Pengumuman</span>
	</div>
	<div class="footer-vertical-line"></div>
	<div class="flex-grow-1 overflow-hidden" style="white-space: nowrap;">
		<marquee behavior="scroll" direction="left" scrollamount="6" class="marquee-text-content">
			<?= e($school['queue_running_text'] ?: 'Selamat Datang di Layanan Tata Usaha SMK Muhammadiyah Bandongan. Silakan ambil nomor antrean dan menunggu hingga nomor Anda dipanggil.') ?>
		</marquee>
	</div>
</footer>

<!-- Floating Tools -->
<div class="floating-tv-tools">
	<button id="btnAudioToggle" class="btn btn-warning btn-sm rounded-pill px-3 shadow fw-bold" onclick="toggleAudio()">
		<i class="fa-solid fa-volume-high me-1"></i> <span id="audioStatusText">Audio Aktif</span>
	</button>
	<button class="btn btn-dark btn-sm rounded-pill px-3 shadow" onclick="toggleFullScreen()">
		<i class="fa-solid fa-expand me-1"></i> Fullscreen
	</button>
</div>

<script>
// Jam Digital Real-Time
function updateClock() {
	const now = new Date();
	const h = String(now.getHours()).padStart(2, '0');
	const m = String(now.getMinutes()).padStart(2, '0');
	const s = String(now.getSeconds()).padStart(2, '0');
	document.getElementById('clockDigits').textContent = `${h}:${m}:${s}`;
}
setInterval(updateClock, 1000);
updateClock();

// Fullscreen Toggle
function toggleFullScreen() {
	if (!document.fullscreenElement) {
		document.documentElement.requestFullscreen().catch(err => {});
	} else {
		if (document.exitFullscreen) document.exitFullscreen();
	}
}

// --------------------------------------------------------------------------
// AUDIO BELL CHIME & SPEECH SYNTHESIS ENGINE
// --------------------------------------------------------------------------
let audioContext = null;
let audioEnabled = true;

function initAudioContext() {
	if (!audioContext) {
		const AudioCtx = window.AudioContext || window.webkitAudioContext;
		if (AudioCtx) audioContext = new AudioCtx();
	}
	if (audioContext && audioContext.state === 'suspended') {
		audioContext.resume();
	}
}

function playDingDong() {
	if (!audioEnabled) return Promise.resolve();
	initAudioContext();
	if (!audioContext) return Promise.resolve();

	return new Promise((resolve) => {
		const now = audioContext.currentTime;

		// Nada 1: 587.33 Hz (D5)
		const osc1 = audioContext.createOscillator();
		const gain1 = audioContext.createGain();
		osc1.type = 'sine';
		osc1.frequency.setValueAtTime(587.33, now);
		gain1.gain.setValueAtTime(0.5, now);
		gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.6);
		osc1.connect(gain1);
		gain1.connect(audioContext.destination);
		osc1.start(now);
		osc1.stop(now + 0.6);

		// Nada 2: 880 Hz (A5)
		const osc2 = audioContext.createOscillator();
		const gain2 = audioContext.createGain();
		osc2.type = 'sine';
		osc2.frequency.setValueAtTime(880, now + 0.35);
		gain2.gain.setValueAtTime(0.6, now + 0.35);
		gain2.gain.exponentialRampToValueAtTime(0.001, now + 1.2);
		osc2.connect(gain2);
		gain2.connect(audioContext.destination);
		osc2.start(now + 0.35);
		osc2.stop(now + 1.2);

		setTimeout(resolve, 1100);
	});
}

function speakTicket(ticketNumber, loketNumber) {
	if (!audioEnabled || !('speechSynthesis' in window)) return;

	const parts = ticketNumber.split('-');
	const letter = parts[0] || '';
	const digits = parts[1] || '';

	let spellDigits = '';
	for (let i = 0; i < digits.length; i++) {
		const d = digits[i];
		if (d === '0') spellDigits += ' kosong';
		else if (d === '1') spellDigits += ' satu';
		else if (d === '2') spellDigits += ' dua';
		else if (d === '3') spellDigits += ' tiga';
		else if (d === '4') spellDigits += ' empat';
		else if (d === '5') spellDigits += ' lima';
		else if (d === '6') spellDigits += ' enam';
		else if (d === '7') spellDigits += ' tujuh';
		else if (d === '8') spellDigits += ' delapan';
		else if (d === '9') spellDigits += ' sembilan';
	}

	const textToSpeak = `Nomor antrean, ${letter}, ${spellDigits}, silakan menuju ke loket ${loketNumber}`;
	const utterance = new SpeechSynthesisUtterance(textToSpeak);
	utterance.lang = 'id-ID';
	utterance.rate = 0.9;
	utterance.pitch = 1.0;

	const voices = window.speechSynthesis.getVoices();
	const idVoice = voices.find(v => v.lang.includes('id') || v.lang.includes('ID'));
	if (idVoice) utterance.voice = idVoice;

	window.speechSynthesis.speak(utterance);
}

function announceTicket(ticketNumber, loketNumber) {
	playDingDong().then(() => {
		speakTicket(ticketNumber, loketNumber);
	});
}

function toggleAudio() {
	audioEnabled = !audioEnabled;
	initAudioContext();
	const btnText = document.getElementById('audioStatusText');
	const btn = document.getElementById('btnAudioToggle');
	if (audioEnabled) {
		btnText.textContent = 'Audio Aktif';
		btn.className = 'btn btn-warning btn-sm rounded-pill px-3 shadow fw-bold';
		playDingDong();
	} else {
		btnText.textContent = 'Audio Mute';
		btn.className = 'btn btn-secondary btn-sm rounded-pill px-3 shadow fw-bold';
	}
}

document.body.addEventListener('click', function() {
	initAudioContext();
}, { once: true });

// --------------------------------------------------------------------------
// POLLING REAL-TIME AJAX (Setiap 2.5 Detik)
// --------------------------------------------------------------------------
let lastCalledId = null;
let lastCalledTime = null;

function pollDisplayData() {
	fetch('<?= site_url("antrean/api_display_data") ?>')
		.then(res => res.json())
		.then(data => {
			if (!data.success) return;

			if (data.last_called) {
				const call = data.last_called;
				const isNewCall = (call.id !== lastCalledId || call.called_at !== lastCalledTime);

				document.getElementById('mainTicketNumber').textContent = call.ticket_number;
				document.getElementById('mainLoketBadge').textContent = 'LOKET ' + call.loket_number;
				document.getElementById('mainServiceCaption').textContent = call.service_name;

				const callBox = document.getElementById('mainCallBox');

				if (isNewCall && lastCalledTime !== null) {
					callBox.classList.add('is-calling-pulse');
					setTimeout(() => callBox.classList.remove('is-calling-pulse'), 8000);
					announceTicket(call.ticket_number, call.loket_number);
				}

				lastCalledId = call.id;
				lastCalledTime = call.called_at;
			}

			// Perbarui status loket di kolom kanan
			if (data.lokets) {
				for (const [num, lk] of Object.entries(data.lokets)) {
					const badgeEl = document.getElementById('servingBadge_' + num);
					if (badgeEl) {
						if (lk.current_ticket !== '-') {
							badgeEl.innerHTML = `<i class="fa-solid fa-circle text-primary" style="font-size: 7px;"></i> <span>${lk.current_ticket}</span>`;
						} else {
							badgeEl.innerHTML = `<i class="fa-solid fa-circle text-secondary" style="font-size: 7px;"></i> <span>Sedang Dilayani</span>`;
						}
					}

					const waitEl = document.getElementById('waitingText_' + num);
					if (waitEl) waitEl.textContent = lk.waiting_count;

					const cardBox = document.getElementById('loketCardBox_' + num);
					if (cardBox) {
						if (lk.current_ticket !== '-') {
							cardBox.classList.add('is-active-card');
						} else {
							cardBox.classList.remove('is-active-card');
						}
					}
				}
			}
		})
		.catch(err => {});
}

setInterval(pollDisplayData, 2500);
pollDisplayData();
</script>

</body>
</html>
