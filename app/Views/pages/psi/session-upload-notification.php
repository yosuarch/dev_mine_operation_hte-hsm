<?php if (session()->has('inserted')): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <strong>Import is Done!</strong>
        <ul>
            <li>Successfull Insert: <?= session('inserted') ?></li>
            <li>Skipped data: <?= session('skipped') ?></li>
        </ul>

        <?php if (!empty(session('errors'))): ?>
            <hr>
            <p class="mb-0">Detail Error:</p>
            <div style="max-height: 200px; overflow-y: auto;">
                <ul class="list-unstyled">
                    <?php foreach (session('errors') as $error): ?>
                        <li><small><?= esc($error) ?></small></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>