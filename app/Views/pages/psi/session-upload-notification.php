<?php if (session()->has('message')): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <strong>Processing Complete!</strong>
        <p class="mb-1"><?= session('message') ?></p>

        <?php if (!empty(session('errors'))): ?>
            <hr>
            <p class="mb-1"><strong>Errors found (skipped rows):</strong></p>
            <div style="max-height: 200px; overflow-y: auto; background: rgba(0,0,0,0.05); padding: 5px; border-radius: 4px;">
                <ul class="list-unstyled mb-0">
                    <?php foreach (session('errors') as $error): ?>
                        <li><small><?= esc($error) ?></small></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>