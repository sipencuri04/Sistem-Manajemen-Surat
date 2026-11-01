<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
	<div>
		<h1 class="h4 fw-bold text-navy mb-1"><i class="fa-solid fa-bell me-2 text-warning"></i>Pusat Notifikasi</h1>
		<p class="text-muted small mb-0">Pemberitahuan disposisi baru, perubahan tugas, persetujuan surat, dan pembaruan sistem.</p>
	</div>
	<?php if ($unread_count > 0): ?>
		<?= form_open('notifikasi/baca-semua') ?>
			<button type="submit" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-check-double me-1"></i> Tandai Semua Dibaca</button>
		<?= form_close() ?>
	<?php endif; ?>
</div>

<div class="card">
	<div class="card-body p-0">
		<?php if (empty($notifications)): ?>
			<div class="p-5 text-center text-muted">
				<i class="fa-regular fa-bell-slash fa-3x mb-3 text-secondary opacity-50 d-block"></i>
				<h6 class="fw-semibold">Belum ada notifikasi</h6>
				<p class="small mb-0">Anda akan menerima pemberitahuan di sini ketika ada disposisi atau aktivitas surat baru.</p>
			</div>
		<?php else: ?>
			<div class="list-group list-group-flush">
				<?php foreach ($notifications as $n):
					$icon = 'fa-circle-info text-primary';
					if ($n['type'] === 'disposisi') $icon = 'fa-share-nodes text-warning';
					elseif ($n['type'] === 'persetujuan') $icon = 'fa-signature text-success';
					elseif ($n['type'] === 'warning') $icon = 'fa-triangle-exclamation text-danger';
				?>
					<a href="<?= site_url('notifikasi/baca/'.$n['id']) ?>" class="list-group-item list-group-item-action p-3 <?= ! $n['is_read'] ? 'bg-primary-subtle' : '' ?>">
						<div class="d-flex align-items-start gap-3">
							<div class="fs-4 mt-1"><i class="fa-solid <?= $icon ?>"></i></div>
							<div class="flex-fill">
								<div class="d-flex w-100 justify-content-between align-items-center mb-1">
									<h6 class="mb-0 fw-bold text-navy <?= ! $n['is_read'] ? 'text-primary' : '' ?>"><?= e($n['title']) ?></h6>
									<span class="small text-muted" style="font-size:0.75rem;"><?= e(tgl_indo($n['created_at'])) ?> <?= e(substr($n['created_at'], 11, 5)) ?></span>
								</div>
								<p class="mb-1 text-secondary small"><?= nl2br(e($n['message'])) ?></p>
								<?php if ( ! $n['is_read']): ?>
									<span class="badge text-bg-primary" style="font-size:0.65rem;">Baru</span>
								<?php endif; ?>
							</div>
						</div>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
	<?php if ($pagination): ?>
		<div class="card-footer bg-white d-flex align-items-center justify-content-between py-2">
			<div class="small text-muted">Menampilkan <?= count($notifications) ?> dari total <?= number_format($total) ?> notifikasi</div>
			<?= $pagination ?>
		</div>
	<?php endif; ?>
</div>
