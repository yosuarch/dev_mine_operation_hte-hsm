<?php if ($sum = session('summary')): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <strong>Import Finished!</strong>
        <ul class="mt-2">
            <li>Total rows handled: <strong><?= $sum['total'] ?></strong></li>
            <li>New Records Inserted: <strong><?= $sum['inserted'] ?></strong></li>
            <li>Existing Records Updated: <strong><?= $sum['updated'] ?></strong></li>
            <li>Errors: <strong><?= count($sum['errors']) ?></strong></li>
        </ul>

        <?php if (!empty($sum['errors'])): ?>
            <div class="table-responsive mt-3" style="max-height: 200px;">
                <table class="table table-sm table-bordered bg-white">
                    <thead class="table-danger">
                        <tr>
                            <th>Row</th>
                            <th>Cause</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sum['errors'] as $err): ?>
                            <tr>
                                <td><?= $err['row'] ?></td>
                                <td><?= $err['reason'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>