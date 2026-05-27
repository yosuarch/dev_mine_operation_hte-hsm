<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Your application description">
    <title><?= isset($pageTitle) ? esc($pageTitle) : 'Dashboard' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <!-- DataTables Core JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <!-- DataTables Bootstrap5 CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <!-- DataTables Bootstrap5 JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
</head>

<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container-fluid">
            <?= view('components/navbar') ?>
        </div>
    </nav>

    <!-- Main Layout Container -->
    <div class="d-flex p-0 m-0" style="min-height: calc(100vh - 56px);">
        <!-- Sidebar -->
        <aside class="bg-light" style="width: 250px; flex-shrink: 0; border-right: 1px solid #dee2e6;">
            <?= view('components/sidebar') ?>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-grow-1 p-4" style="overflow-y: auto;">
            <div class="container-fluid">
                <?= $this->renderSection('content'); ?>
            </div>
        </main>
    </div>

    <!-- Footer -->
    <footer class="bg-light border-top mt-5">
        <div class="container-fluid p-0 m-0">
            <?= view('components/footer') ?>
        </div>
    </footer>

    <?= $this->renderSection('modal'); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <?= $this->renderSection('script'); ?>
</body>

</html>