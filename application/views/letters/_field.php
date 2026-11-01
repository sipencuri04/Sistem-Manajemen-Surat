<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Render satu field dinamis.
 * @var string $name  kunci field
 * @var array  $field konfigurasi field
 * @var mixed  $value nilai saat ini
 * @var string|null $error pesan error
 */
$id     = 'f_'.$name;
$input  = 'f['.$name.']';
$cls    = $error ? ' is-invalid' : '';
$req    = $field['required'] ? 'required' : '';
?>
<div class="col-md-<?= (int) $field['col'] ?>">
	<label class="form-label <?= $req ?>" for="<?= e($id) ?>"><?= e($field['label']) ?></label>

	<?php if ($field['type'] === 'textarea'): ?>
		<textarea class="form-control<?= $cls ?>" id="<?= e($id) ?>" name="<?= e($input) ?>" rows="<?= (int) $field['rows'] ?>"
			maxlength="<?= (int) $field['max'] ?>" placeholder="<?= e($field['placeholder']) ?>" <?= $req ?>><?= e(is_string($value) ? $value : '') ?></textarea>

	<?php elseif ($field['type'] === 'select'): ?>
		<select class="form-select<?= $cls ?>" id="<?= e($id) ?>" name="<?= e($input) ?>" <?= $req ?>>
			<option value="">&mdash; Pilih &mdash;</option>
			<?php foreach ($field['options'] as $opt): ?>
				<option value="<?= e($opt) ?>" <?= (string) $value === (string) $opt ? 'selected' : '' ?>><?= e($opt) ?></option>
			<?php endforeach; ?>
		</select>

	<?php elseif ($field['type'] === 'rows'): ?>
		<?php $rows = is_array($value) && ! empty($value) ? array_values($value) : array(array()); ?>
		<div class="table-responsive">
			<table class="table table-bordered rows-table mb-2<?= $error ? ' border-danger' : '' ?>" data-rows-name="<?= e($name) ?>">
				<thead><tr>
					<th style="width:44px">No</th>
					<?php foreach ($field['columns'] as $col => $col_label): ?><th><?= e($col_label) ?></th><?php endforeach; ?>
					<th style="width:44px"></th>
				</tr></thead>
				<tbody>
				<?php foreach ($rows as $i => $row): ?>
					<tr>
						<td class="text-center align-middle row-no"><?= $i + 1 ?></td>
						<?php foreach ($field['columns'] as $col => $col_label): ?>
							<td><input type="text" class="form-control form-control-sm" maxlength="255" data-col="<?= e($col) ?>"
								name="<?= e($input.'['.$i.']['.$col.']') ?>" value="<?= e(is_array($row) && isset($row[$col]) ? $row[$col] : '') ?>" aria-label="<?= e($col_label) ?>"></td>
						<?php endforeach; ?>
						<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" data-row-remove title="Hapus baris"><i class="fa-solid fa-xmark"></i></button></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<button type="button" class="btn btn-sm btn-outline-primary" data-row-add="<?= e($name) ?>"><i class="fa-solid fa-plus me-1"></i> Tambah baris</button>

	<?php else: ?>
		<?php $html_type = in_array($field['type'], array('date', 'time', 'number'), TRUE) ? $field['type'] : 'text'; ?>
		<input type="<?= $html_type ?>" class="form-control<?= $cls ?>" id="<?= e($id) ?>" name="<?= e($input) ?>"
			value="<?= e(is_string($value) ? $value : '') ?>" <?= $html_type === 'text' ? 'maxlength="'.(int) $field['max'].'"' : '' ?>
			placeholder="<?= e($field['placeholder']) ?>" <?= $req ?>>
	<?php endif; ?>

	<?php if ($error): ?><div class="invalid-feedback d-block"><?= e($error) ?></div><?php endif; ?>
	<?php if ($field['help'] !== ''): ?><div class="form-text"><?= e($field['help']) ?></div><?php endif; ?>
</div>
