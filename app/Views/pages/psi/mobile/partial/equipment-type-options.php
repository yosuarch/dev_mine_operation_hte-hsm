<?php
/**
 * Server-rendered <option> set for #equipType —
 * returned by GET /operator-driver/unique-equipment-type.
 *
 * @var array $types  Rows from view_unique_equipment_type: idx, abrvtn
 */
?>
<option selected value="">Select Equipment Type</option>
<?php foreach ($types as $t): ?>
    <option value="<?= esc($t['idx'], 'attr') ?>"><?= esc($t['abrvtn']) ?></option>
<?php endforeach; ?>
