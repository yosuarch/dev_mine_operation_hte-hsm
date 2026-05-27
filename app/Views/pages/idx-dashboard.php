<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('content') ?>
<h1>Dashboard</h1>
<p>Welcome, <?= isset($name) ? esc($name) : 'Dashboard' ?>. <?= isset($message) ? esc($message) : 'Dashboard' ?></p>
<?= $this->endSection() ?>