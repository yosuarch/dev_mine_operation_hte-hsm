<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Your application description">
    <title><?= isset($pageTitle) ? esc($pageTitle) : 'Dashboard' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <style>
        /* On desktop: sidebar shows inline at fixed width */
        @media (min-width: 992px) {
            #mobileSidebar {
                width: 250px;
                flex-shrink: 0;
                border-right: 1px solid #dee2e6;
                min-height: calc(100vh - 56px);
            }
        }
    </style>
    <?= $this->renderSection('pageStyles'); ?>
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

        <!-- Sidebar: offcanvas drawer on mobile, inline panel on desktop -->
        <?= view('components/sidebar') ?>

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

    <!-- modal section -->
    <?= $this->renderSection('modal'); ?>

    <!-- Bootstrap JS (required for offcanvas toggle) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <!-- script section -->
    <?= $this->renderSection('main-js'); ?>
    <?= $this->renderSection('script'); ?>
</body>

</html>
