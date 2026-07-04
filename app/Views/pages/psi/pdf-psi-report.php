<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('pageStyles'); ?>
<!-- still empty, mount at here the necessary style link -->
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>
<!-- the main body -->
<h1>Hi BITCH, you found me</h1>
<?= $this->endSection(); ?>

<?= $this->section('script'); ?>
<!-- additional script in here ( . )  ( . ) -->
<?= $this->endSection(); ?>