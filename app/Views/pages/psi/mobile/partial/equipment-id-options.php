<?php
/**
 * Server-rendered <option> set for #equipID —
 * returned by GET /operator-driver/equipment-id.
 *
 * @var array $units  Rows from view_equipment_id_list: idx, equipment_id, where_index
 */
?>
<option value="">Select Equipment ID</option>
<?php foreach ($units as $u): ?>
    <option value="<?= esc($u['idx'], 'attr') ?>"
            data-where-index="<?= esc($u['where_index'], 'attr') ?>"><?= esc($u['equipment_id']) ?></option>
<?php endforeach; ?>
