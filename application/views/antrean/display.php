<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Layar Monitor Antrean TU &middot; <?= e($school['school_name'] ?: 'SMK Muhammadiyah Bandongan') ?></title>
	<link rel="stylesheet" href="<?= asset_url('assets/vendor/bootstrap/css/bootstrap.min.css') ?>">
	<link rel="stylesheet" href="<?= asset_url('assets/vendor/fontawesome/css/all.min.css') ?>">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@700;800;900&display=swap" rel="stylesheet">
	<style>
		:root {
			--tv-bg: #070d19;
			--tv-surface: #0e172a;
			--tv-border: rgba(255, 255, 255, 0.1);
			--tv-primary: #3b82f6;
			--tv-gold: #f59e0b;
			--tv-green: #10b981;
		}
		* { box-sizing: border-box; }
		body {
			font-family: 'Plus Jakarta Sans', -apple-system, sans-serif;
			background-color: var(--tv-bg);
			background-image: radial-gradient(at 10% 20%, rgba(30, 58, 138, 0.4) 0px, transparent 50%),
			                  radial-gradient(at 90% 80%, rgba(29, 78, 216, 0.3) 0px, transparent 50%);
			color: #ffffff;
			min-height: 100vh;
			display: flex;
			flex-direction: column;
			overflow-x: hidden;
			margin: 0;
			padding: 0;
		}

		/* Header Styling */
		.tv-header {
			background: rgba(14, 23, 42, 0.85);
			backdrop-filter: blur(12px);
			border-bottom: 2px solid var(--tv-border);
			padding: 16px 32px;
		}
		.tv-logo-box {
			width: 60px;
			height: 60px;
			background: #ffffff;
			border-radius: 14px;
			display: flex;
			align-items: center;
			justify-content: center;
			box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
		}
		.tv-clock {
			font-family: 'JetBrains Mono', monospace;
			font-size: 32px;
			font-weight: 800;
			color: #38bdf8;
			letter-spacing: 1px;
			text-shadow: 0 0 15px rgba(56, 189, 248, 0.5);
		}

		/* Main Content Layout */
		.tv-main {
			flex: 1;
			padding: 24px 32px;
			display: flex;
			gap: 24px;
		}
		.tv-left-col {
			flex: 1.1;
			display: flex;
			flex-direction: column;
			gap: 24px;
		}
		.tv-right-col {
			flex: 0.9;
			display: flex;
			flex-direction: column;
			gap: 16px;
		}

		/* Kartu Panggilan Utama */
		.main-call-card {
			background: linear-gradient(145deg, #132247 0%, #0b152d 100%);
			border: 2px solid rgba(59, 130, 246, 0.4);
			border-radius: 28px;
			padding: 30px 24px;
			text-align: center;
			box-shadow: 0 20px 45px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.1);
			position: relative;
			overflow: hidden;
			transition: all 0.4s ease;
		}
		.main-call-card.calling-pulse {
			animation: tvPulse 1.2s infinite alternate;
			border-color: #38bdf8 !important;
		}
		@keyframes tvPulse {
			0% { box-shadow: 0 0 20px rgba(56, 189, 248, 0.4), inset 0 0 15px rgba(56, 189, 248, 0.2); }
			100% { box-shadow: 0 0 50px rgba(56, 189, 248, 0.8), inset 0 0 30px rgba(56, 189, 248, 0.5); }
		}
		.main-ticket-num {
			font-family: 'JetBrains Mono', monospace;
			font-size: 88px;
			font-weight: 900;
			line-height: 1;
			margin: 14px 0;
			color: #ffffff;
			text-shadow: 0 0 25px rgba(255, 255, 255, 0.4);
			letter-spacing: 3px;
		}
		.loket-badge {
			display: inline-block;
			background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
			color: #000000;
			font-weight: 900;
			font-size: 32px;
			padding: 8px 36px;
			border-radius: 40px;
			letter-spacing: 1.5px;
			box-shadow: 0 8px 20px rgba(245, 158, 11, 0.4);
		}

		/* Video / Info Box */
		.video-box {
			background: var(--tv-surface);
			border: 1px solid var(--tv-border);
			border-radius: 22px;
			overflow: hidden;
			flex: 1;
			min-height: 220px;
			display: flex;
			align-items: center;
			justify-content: center;
			position: relative;
		}
		.video-box iframe {
			width: 100%;
			height: 100%;
			border: none;
			position: absolute;
			top: 0;
			left: 0;
		}

		/* Kartu Daftar Loket */
		.loket-card {
			background: var(--tv-surface);
			border: 1px solid var(--tv-border);
			border-radius: 20px;
			padding: 20px 24px;
			display: flex;
			align-items: center;
			justify-content: space-between;
			box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
			transition: all 0.3s ease;
		}
		.loket-card.active-loket {
			border-color: rgba(59, 130, 246, 0.5);
			background: linear-gradient(145deg, #142245 0%, #0d172e 100%);
		}
		.loket-header {
			font-size: 20px;
			font-weight: 800;
			letter-spacing: 0.5px;
		}
		.loket-ticket {
			font-family: 'JetBrains Mono', monospace;
			font-size: 46px;
			font-weight: 900;
			color: #38bdf8;
		}

		/* Running Text Marquee */
		.tv-footer-marquee {
			background: #090e1c;
			border-top: 2px solid var(--tv-border);
			padding: 12px 24px;
			display: flex;
			align-items: center;
			font-size: 17px;
			font-weight: 600;
			color: #cbd5e1;
		}
		.marquee-label {
			background: #2563eb;
			color: #fff;
			font-weight: 800;
			font-size: 13px;
			padding: 4px 14px;
			border-radius: 8px;
			text-transform: uppercase;
			letter-spacing: 0.5px;
			margin-right: 18px;
			white-space: nowrap;
		}

		/* Floating Controls */
		.tv-controls {
			position: fixed;
			bottom: 60px;
			right: 32px;
			display: flex;
			gap: 10px;
			z-index: 999;
			opacity: 0.3;
			transition: opacity 0.3s ease;
		}
		.tv-controls:hover { opacity: 1; }
	</style>
</head>
<body>

<!-- Header -->
<header class="tv-header">
	<div class="d-flex justify-content-between align-items-center">
		<div class="d-flex align-items-center gap-3">
			<div class="tv-logo-box">
				<i class="fa-solid fa-school text-primary fs-2"></i>
			</div>
			<div>
				<h3 class="fw-black mb-0 text-white text-uppercase" style="letter-spacing: 1px;">
					<?= e($school['school_name'] ?: 'SMK MUHAMMADIYAH BANDONGAN') ?>
				</h3>
				<div class="text-info fw-semibold small" style="letter-spacing: 1.5px; text-transform: uppercase;">
					<i class="fa-solid fa-bullhorn me-1"></i> MONITOR ANTREAN PELAYANAN TATA USAHA DIGITAL
				</div>
			</div>
		</div>

		<div class="text-end">
			<div class="tv-clock" id="displayClock">--:--:--</div>
			<div class="text-white-50 small fw-bold" id="displayDate"><?= tgl_indo(date('Y-m-d')) ?></div>
		</div>
	</div>
</header>

<!-- Main Grid -->
<main class="tv-main">
	
	<!-- Kolom Kiri: Panggilan Utama & Video Profil -->
	<section class="tv-left-col">
		
		<!-- Kartu Panggilan Terbesar -->
		<div class="main-call-card" id="mainCallCard">
			<div class="d-flex justify-content-between align-items-center mb-2 px-3">
				<span class="badge bg-primary bg-opacity-25 text-info border border-info border-opacity-25 px-3 py-2 rounded-pill fs-6 text-uppercase fw-bold">
					<i class="fa-solid fa-volume-high me-1"></i> PANGGILAN ANTREAN
				</span>
				<span class="text-white-50 small fw-semibold" id="callStatusBadge">Menunggu Panggilan</span>
			</div>

			<div class="main-ticket-num" id="mainTicketNum">
				<?= !empty($last_called) ? e($last_called['ticket_number']) : '---' ?>
			</div>

			<div class="mb-3">
				<div class="loket-badge" id="mainLoketBadge">
					LOKET <?= !empty($last_called) ? (int) $last_called['loket_number'] : 1 ?>
				</div>
			</div>

			<div class="fs-5 fw-bold text-white-50 text-uppercase" id="mainServiceName">
				<?= !empty($last_called) ? e($last_called['service_name']) : 'Pelayanan Tata Usaha' ?>
			</div>
		</div>

		<!-- Kotak Media / Video Profil Sekolah -->
		<div class="video-box" id="mediaBox">
			<?php 
			$video_url = !empty($school['queue_video_url']) ? $school['queue_video_url'] : '';
			if (!empty($video_url) && (strpos($video_url, 'youtube.com') !== FALSE || strpos($video_url, 'youtu.be') !== FALSE)): 
				// Format URL embed YouTube
				preg_match('/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=))([\w\-]+)/', $video_url, $matches);
				$yt_id = isset($matches[1]) ? $matches[1] : '';
			?>
				<iframe src="https://www.youtube.com/embed/<?= $yt_id ?>?autoplay=1&mute=1&loop=1&playlist=<?= $yt_id ?>&controls=0" allow="autoplay; encrypted-media"></iframe>
			<?php else: ?>
				<!-- Fallback Display Banner Modern -->
				<div class="p-4 text-center w-100">
					<div class="d-inline-flex p-3 rounded-circle bg-primary bg-opacity-10 text-primary mb-3">
						<i class="fa-solid fa-graduation-cap fs-1"></i>
					</div>
					<h4 class="fw-bold text-white mb-2"><?= e($school['school_name'] ?: 'SMK Muhammadiyah Bandongan') ?></h4>
					<p class="text-white-50 small mb-3" style="max-width: 480px; margin: 0 auto;">
						<?= e($school['school_address'] ?: 'Jl. Bandongan - Windusari Km. 1, Kab. Magelang (56152)') ?>
					</p>
					<div class="d-flex justify-content-center gap-3">
						<span class="badge bg-white bg-opacity-10 px-3 py-2 rounded-pill"><i class="fa-solid fa-award text-warning me-1"></i> Terakreditasi B</span>
						<span class="badge bg-white bg-opacity-10 px-3 py-2 rounded-pill"><i class="fa-solid fa-shield-halved text-success me-1"></i> Layanan Cepat &amp; Akurat</span>
					</div>
				</div>
			<?php endif; ?>
		</div>

	</section>

	<!-- Kolom Kanan: Status Seluruh Loket -->
	<section class="tv-right-col">
		<div class="d-flex justify-content-between align-items-center mb-1">
			<h5 class="fw-bold text-white mb-0 text-uppercase tracking-wider">
				<i class="fa-solid fa-desktop text-primary me-2"></i> STATUS LOKET PELAYANAN
			</h5>
			<span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-25 px-2 py-1 rounded-pill small">
				<i class="fa-solid fa-circle me-1" style="font-size: 8px;"></i> Real-Time Live
			</span>
		</div>

		<div id="loketListContainer" class="d-flex flex-column gap-3 flex-grow-1 justify-content-around">
			<?php foreach ($lokets as $num => $loket): ?>
				<div class="loket-card <?= ($loket['current_ticket'] !== '-') ? 'active-loket' : '' ?>" id="loketCard_<?= $num ?>">
					<div>
						<div class="d-flex align-items-center gap-2 mb-1">
							<span class="badge bg-primary px-3 py-1 rounded-pill fw-bold fs-6">LOKET <?= $num ?></span>
							<span class="text-white-50 small fw-bold text-uppercase">(<?= e($loket['service_code']) ?>)</span>
						</div>
						<div class="loket-header text-white"><?= e($loket['service_name']) ?></div>
						<div class="small text-white-50 mt-1">
							Sisa Menunggu: <strong class="text-warning"><?= (int) $loket['waiting_count'] ?></strong> Orang
						</div>
					</div>
					<div class="text-end">
						<div class="small text-white-50 text-uppercase fw-semibold">Sedang Dilayani</div>
						<div class="loket-ticket" id="loketNum_<?= $num ?>"><?= e($loket['current_ticket']) ?></div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</section>

</main>

<!-- Running Text Footer -->
<footer class="tv-footer-marquee">
	<div class="marquee-label">
		<i class="fa-solid fa-bullhorn me-1"></i> PENGUMUMAN
	</div>
	<div class="flex-grow-1 overflow-hidden position-relative" style="white-space: nowrap;">
		<marquee behavior="scroll" direction="left" scrollamount="6">
			<?= e($school['queue_running_text'] ?: 'Selamat Datang di Pelayanan Tata Usaha SMK Muhammadiyah Bandongan. Silakan ambil tiket antrean dan perhatikan nomor panggilan monitor TV. Jam Operasional: Senin - Jumat 07.00 - 15.30 WIB.') ?>
		</marquee>
	</div>
</footer>

<!-- Floating Controls -->
<div class="tv-controls">
	<button id="btnAudioToggle" class="btn btn-warning btn-sm rounded-pill px-3 shadow fw-bold" onclick="toggleAudio()">
		<i class="fa-solid fa-volume-high me-1"></i> <span id="audioStatusText">Audio Aktif</span>
	</button>
	<button class="btn btn-outline-light btn-sm rounded-pill px-3 shadow" onclick="toggleFullScreen()">
		<i class="fa-solid fa-expand me-1"></i> Fullscreen
	</button>
</div>

<script>
// Jam Digital Display
function updateDisplayClock() {
	const now = new Date();
	const h = String(now.getHours()).padStart(2, '0');
	const m = String(now.getMinutes()).padStart(2, '0');
	const s = String(now.getSeconds()).padStart(2, '0');
	document.getElementById('displayClock').textContent = `${h}:${m}:${s}`;
}
setInterval(updateDisplayClock, 1000);
updateDisplayClock();

// Fullscreen Toggle
function toggleFullScreen() {
	if (!document.fullscreenElement) {
		document.documentElement.requestFullscreen().catch(err => {});
	} else {
		if (document.exitFullscreen) document.exitFullscreen();
	}
}

// --------------------------------------------------------------------------
// AUDIO CHIME & SPEECH SYNTHESIS ENGINE
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

// Buat Nada Bel Ding-Dong 2-Tone Airport/Bank Chime Menggunakan Web Audio API
function playChime() {
	if (!audioEnabled) return Promise.resolve();
	initAudioContext();
	if (!audioContext) return Promise.resolve();

	return new Promise((resolve) => {
		const now = audioContext.currentTime;

		// Tone 1: 587.33 Hz (D5)
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

		// Tone 2: 880 Hz (A5) dimulai setelah 0.35 detik
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

// Suara Pemanggil Manusia Bahasa Indonesia (Web Speech API)
function speakQueue(ticketNumber, loketNumber) {
	if (!audioEnabled || !('speechSynthesis' in window)) return;

	// Eja nomor tiket: misal A-001 menjadi "A... kosong... kosong... satu"
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

	// Cek pilihan suara bahasa Indonesia jika ada di sistem
	const voices = window.speechSynthesis.getVoices();
	const idVoice = voices.find(v => v.lang.includes('id') || v.lang.includes('ID'));
	if (idVoice) utterance.voice = idVoice;

	window.speechSynthesis.speak(utterance);
}

function announceTicket(ticketNumber, loketNumber) {
	// 1. Bunyikan nada bel ding-dong
	playChime().then(() => {
		// 2. Lanjutkan suara ucapan
		speakQueue(ticketNumber, loketNumber);
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
		playChime();
	} else {
		btnText.textContent = 'Audio Mute';
		btn.className = 'btn btn-secondary btn-sm rounded-pill px-3 shadow fw-bold';
	}
}

// Inisialisasi audio saat ada klik pertama kali di halaman
document.body.addEventListener('click', function() {
	initAudioContext();
}, { once: true });

// --------------------------------------------------------------------------
// POLLING DATA REAL-TIME AJAX (Setiap 2.5 Detik)
// --------------------------------------------------------------------------
let lastCalledId = null;
let lastCalledTime = null;

function pollDisplayData() {
	fetch('<?= site_url("antrean/api_display_data") ?>')
		.then(res => res.json())
		.then(data => {
			if (!data.success) return;

			// Periksa pemanggilan antrean baru atau panggil ulang
			if (data.last_called) {
				const call = data.last_called;
				const isNewCall = (call.id !== lastCalledId || call.called_at !== lastCalledTime);

				document.getElementById('mainTicketNum').textContent = call.ticket_number;
				document.getElementById('mainLoketBadge').textContent = 'LOKET ' + call.loket_number;
				document.getElementById('mainServiceName').textContent = call.service_name;
				document.getElementById('callStatusBadge').textContent = 'Sedang Dipanggil';

				const mainCard = document.getElementById('mainCallCard');

				if (isNewCall && lastCalledTime !== null) {
					// Efek pulse kedipan bersinar
					mainCard.classList.add('calling-pulse');
					setTimeout(() => mainCard.classList.remove('calling-pulse'), 8000);

					// Bunyikan suara
					announceTicket(call.ticket_number, call.loket_number);
				}

				lastCalledId = call.id;
				lastCalledTime = call.called_at;
			}

			// Perbarui status tiap loket
			if (data.lokets) {
				for (const [num, lk] of Object.entries(data.lokets)) {
					const numEl = document.getElementById('loketNum_' + num);
					if (numEl) numEl.textContent = lk.current_ticket;

					const cardEl = document.getElementById('loketCard_' + num);
					if (cardEl) {
						if (lk.current_ticket !== '-') {
							cardEl.classList.add('active-loket');
						} else {
							cardEl.classList.remove('active-loket');
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

