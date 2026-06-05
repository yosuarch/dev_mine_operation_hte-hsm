<?php if ($resultId = session('result_id')): ?>
    <?php
    $db = \Config\Database::connect();
    $res = $db->table('psi_record_upload_results')->where('id', $resultId)->get()->getRow();
    $sum = json_decode($res->summary_json, true);
    ?>

    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <strong>Import Finished!</strong>
        <p>Total rows handled: <strong><?= $sum['total'] ?></strong></p>
    </div>
<?php endif; ?>