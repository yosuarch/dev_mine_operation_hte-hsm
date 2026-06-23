<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('content'); ?>

<?= csrf_field() ?>

<div class="container-fluid py-4">

    <!-- Header -->
    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-0">Notification Recipients</h4>
            <p class="text-muted mb-0 small">Manage who receives P2H submission notifications.</p>
        </div>
        <button type="button" class="btn btn-primary btn-sm fw-bold px-3" data-bs-toggle="modal" data-bs-target="#addRecipientModal">
            <i class="fas fa-plus me-1"></i> Add Recipient
        </button>
    </div>

    <!-- Table card -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="recipientTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Name</th>
                            <th>Channel</th>
                            <th class="d-none d-md-table-cell">Contact</th>
                            <th class="d-none d-lg-table-cell">Notify On</th>
                            <th class="text-center">Active</th>
                            <th class="text-center pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recipients)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                No recipients yet. Add one above.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($recipients as $r): ?>
                        <tr id="row-<?= $r['idx'] ?>">
                            <td class="ps-4 fw-semibold"><?= esc($r['name']) ?></td>
                            <td>
                                <?php if ($r['channel'] === 'email'): ?>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                    <i class="fas fa-envelope me-1" style="font-size:.7rem;"></i>Email
                                </span>
                                <?php else: ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="fab fa-whatsapp me-1" style="font-size:.7rem;"></i>WhatsApp
                                </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted d-none d-md-table-cell" style="font-size:.9rem;"><?= esc($r['contact']) ?></td>
                            <td class="d-none d-lg-table-cell">
                                <?php if ($r['notify_on'] === 'abnormal_only'): ?>
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                    Abnormal only
                                </span>
                                <?php else: ?>
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                    Always
                                </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="form-check form-switch d-flex justify-content-center mb-0">
                                    <input class="form-check-input toggle-active" type="checkbox" role="switch"
                                           data-idx="<?= $r['idx'] ?>"
                                           <?= $r['active'] ? 'checked' : '' ?>>
                                </div>
                            </td>
                            <td class="text-center pe-4">
                                <button type="button" class="btn btn-sm btn-outline-secondary btn-edit me-1"
                                        data-idx="<?= $r['idx'] ?>"
                                        data-name="<?= esc($r['name']) ?>"
                                        data-channel="<?= esc($r['channel']) ?>"
                                        data-contact="<?= esc($r['contact']) ?>"
                                        data-notify-on="<?= esc($r['notify_on']) ?>">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-delete"
                                        data-idx="<?= $r['idx'] ?>" data-name="<?= esc($r['name']) ?>">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Add Recipient Modal -->
<div class="modal fade" id="addRecipientModal" tabindex="-1" aria-labelledby="addRecipientModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="addRecipientModalLabel">Add Recipient</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0">

                <div class="mb-3">
                    <label for="inputName" class="form-label fw-semibold">Name</label>
                    <input type="text" id="inputName" class="form-control" placeholder="e.g. Foreman Budi">
                    <div class="invalid-feedback">Name is required.</div>
                </div>

                <div class="mb-3">
                    <label for="inputChannel" class="form-label fw-semibold">Channel</label>
                    <select id="inputChannel" class="form-select">
                        <option value="email">Email</option>
                        <option value="whatsapp">WhatsApp</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="inputContact" class="form-label fw-semibold">Contact</label>
                    <input type="text" id="inputContact" class="form-control" placeholder="email@example.com">
                    <div class="form-text" id="contactHint">Enter the email address.</div>
                    <div class="invalid-feedback">Contact is required.</div>
                </div>

                <div class="mb-1">
                    <label for="inputNotifyOn" class="form-label fw-semibold">Notify On</label>
                    <select id="inputNotifyOn" class="form-select">
                        <option value="always">Always — every P2H submission</option>
                        <option value="abnormal_only">Abnormal only — when not-normal items exist</option>
                    </select>
                </div>

            </div>
            <div class="modal-footer border-0 pt-0 gap-2">
                <div id="addError" class="text-danger small me-auto d-none"></div>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="btnAddConfirm" class="btn btn-primary fw-bold px-4">Add</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Recipient Modal -->
<div class="modal fade" id="editRecipientModal" tabindex="-1" aria-labelledby="editRecipientModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="editRecipientModalLabel">Edit Recipient</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0">

                <input type="hidden" id="editIdx">

                <div class="mb-3">
                    <label for="editName" class="form-label fw-semibold">Name</label>
                    <input type="text" id="editName" class="form-control">
                    <div class="invalid-feedback">Name is required.</div>
                </div>

                <div class="mb-3">
                    <label for="editChannel" class="form-label fw-semibold">Channel</label>
                    <select id="editChannel" class="form-select">
                        <option value="email">Email</option>
                        <option value="whatsapp">WhatsApp</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="editContact" class="form-label fw-semibold">Contact</label>
                    <input type="text" id="editContact" class="form-control">
                    <div class="form-text" id="editContactHint">Enter the email address.</div>
                    <div class="invalid-feedback">Contact is required.</div>
                </div>

                <div class="mb-1">
                    <label for="editNotifyOn" class="form-label fw-semibold">Notify On</label>
                    <select id="editNotifyOn" class="form-select">
                        <option value="always">Always — every P2H submission</option>
                        <option value="abnormal_only">Abnormal only — when not-normal items exist</option>
                    </select>
                </div>

            </div>
            <div class="modal-footer border-0 pt-0 gap-2">
                <div id="editError" class="text-danger small me-auto d-none"></div>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="btnEditConfirm" class="btn btn-primary fw-bold px-4">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirm Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <i class="fas fa-triangle-exclamation text-warning mb-3" style="font-size:2rem;"></i>
                <p class="fw-semibold mb-1">Remove recipient?</p>
                <p class="text-muted small mb-0" id="deleteTargetName"></p>
            </div>
            <div class="modal-footer border-0 pt-0 gap-2">
                <button type="button" class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="btnDeleteConfirm" class="btn btn-danger flex-fill fw-bold">Remove</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('main-js'); ?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<?= $this->endSection(); ?>

<?= $this->section('script'); ?>
<script>
$(document).ready(function () {

    const csrfToken = () => $('[name="csrf_test_name"]').val();

    function refreshCsrf(hash) {
        if (hash) $('[name="csrf_test_name"]').val(hash);
    }

    function channelHint(channel) {
        return channel === 'email'
            ? { placeholder: 'email@example.com', hint: 'Enter the email address.' }
            : { placeholder: '628123456789',      hint: 'International format without +. e.g. 628123456789' };
    }

    function channelBadgeHtml(channel) {
        return channel === 'email'
            ? '<span class="badge bg-primary-subtle text-primary border border-primary-subtle"><i class="fas fa-envelope me-1" style="font-size:.7rem;"></i>Email</span>'
            : '<span class="badge bg-success-subtle text-success border border-success-subtle"><i class="fab fa-whatsapp me-1" style="font-size:.7rem;"></i>WhatsApp</span>';
    }

    function notifyBadgeHtml(notifyOn) {
        return notifyOn === 'abnormal_only'
            ? '<span class="badge bg-warning-subtle text-warning border border-warning-subtle">Abnormal only</span>'
            : '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Always</span>';
    }

    // ── Channel → contact placeholder (Add modal) ─────────────────
    $('#inputChannel').on('change', function () {
        const h = channelHint($(this).val());
        $('#inputContact').attr('placeholder', h.placeholder);
        $('#contactHint').text(h.hint);
    });

    // ── Channel → contact placeholder (Edit modal) ────────────────
    $('#editChannel').on('change', function () {
        const h = channelHint($(this).val());
        $('#editContact').attr('placeholder', h.placeholder);
        $('#editContactHint').text(h.hint);
    });

    // ── Add recipient ─────────────────────────────────────────────
    $('#btnAddConfirm').on('click', function () {
        const $btn  = $(this).prop('disabled', true).text('Adding...');
        const name  = $('#inputName').val().trim();
        const ch    = $('#inputChannel').val();
        const cont  = $('#inputContact').val().trim();
        const notif = $('#inputNotifyOn').val();

        $('#inputName, #inputContact').removeClass('is-invalid');
        $('#addError').addClass('d-none');

        let valid = true;
        if (!name)  { $('#inputName').addClass('is-invalid');    valid = false; }
        if (!cont)  { $('#inputContact').addClass('is-invalid'); valid = false; }
        if (!valid) { $btn.prop('disabled', false).text('Add'); return; }

        $.ajax({
            url: '<?= base_url('/notification-target/store') ?>',
            method: 'POST',
            contentType: 'application/json',
            headers: { 'X-CSRF-TOKEN': csrfToken() },
            data: JSON.stringify({ name, channel: ch, contact: cont, notify_on: notif }),
            success: function (res) {
                refreshCsrf(res.csrf_hash);
                bootstrap.Modal.getInstance(document.getElementById('addRecipientModal')).hide();

                const $emptyRow = $('#recipientTable tbody tr td[colspan]').closest('tr');
                if ($emptyRow.length) $emptyRow.remove();

                const safeName = $('<span>').text(name).html();
                const safeCont = $('<span>').text(cont).html();

                $('#recipientTable tbody').append(`
                    <tr id="row-${res.idx}">
                        <td class="ps-4 fw-semibold">${safeName}</td>
                        <td>${channelBadgeHtml(ch)}</td>
                        <td class="text-muted" style="font-size:.9rem;">${safeCont}</td>
                        <td>${notifyBadgeHtml(notif)}</td>
                        <td class="text-center">
                            <div class="form-check form-switch d-flex justify-content-center mb-0">
                                <input class="form-check-input toggle-active" type="checkbox" role="switch"
                                       data-idx="${res.idx}" checked>
                            </div>
                        </td>
                        <td class="text-center pe-4">
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-edit me-1"
                                    data-idx="${res.idx}"
                                    data-name="${safeName}"
                                    data-channel="${ch}"
                                    data-contact="${safeCont}"
                                    data-notify-on="${notif}">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete"
                                    data-idx="${res.idx}" data-name="${safeName}">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>`);

                $('#inputName, #inputContact').val('');
                $('#inputChannel').val('email').trigger('change');
                $('#inputNotifyOn').val('always');
            },
            error: function (xhr) {
                refreshCsrf((JSON.parse(xhr.responseText || '{}')).csrf_hash);
                const msg = (JSON.parse(xhr.responseText || '{}')).message || 'Failed to add recipient.';
                $('#addError').text(msg).removeClass('d-none');
            },
            complete: function () { $btn.prop('disabled', false).text('Add'); }
        });
    });

    $('#addRecipientModal').on('hidden.bs.modal', function () {
        $('#inputName, #inputContact').val('').removeClass('is-invalid');
        $('#inputChannel').val('email').trigger('change');
        $('#inputNotifyOn').val('always');
        $('#addError').addClass('d-none');
    });

    // ── Open edit modal ───────────────────────────────────────────
    $(document).on('click', '.btn-edit', function () {
        const $btn = $(this);
        $('#editIdx').val($btn.data('idx'));
        $('#editName').val($btn.data('name')).removeClass('is-invalid');
        $('#editChannel').val($btn.data('channel')).trigger('change');
        $('#editContact').val($btn.data('contact')).removeClass('is-invalid');
        $('#editNotifyOn').val($btn.data('notify-on'));
        $('#editError').addClass('d-none');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('editRecipientModal')).show();
    });

    // ── Save edit ─────────────────────────────────────────────────
    $('#btnEditConfirm').on('click', function () {
        const $btn    = $(this).prop('disabled', true).text('Saving...');
        const idx     = $('#editIdx').val();
        const name    = $('#editName').val().trim();
        const ch      = $('#editChannel').val();
        const cont    = $('#editContact').val().trim();
        const notif   = $('#editNotifyOn').val();

        $('#editName, #editContact').removeClass('is-invalid');
        $('#editError').addClass('d-none');

        let valid = true;
        if (!name)  { $('#editName').addClass('is-invalid');    valid = false; }
        if (!cont)  { $('#editContact').addClass('is-invalid'); valid = false; }
        if (!valid) { $btn.prop('disabled', false).text('Save'); return; }

        $.ajax({
            url: `<?= base_url('/notification-target/update/') ?>${idx}`,
            method: 'POST',
            contentType: 'application/json',
            headers: { 'X-CSRF-TOKEN': csrfToken() },
            data: JSON.stringify({ name, channel: ch, contact: cont, notify_on: notif }),
            success: function (res) {
                refreshCsrf(res.csrf_hash);
                bootstrap.Modal.getInstance(document.getElementById('editRecipientModal')).hide();

                const safeName = $('<span>').text(name).html();
                const safeCont = $('<span>').text(cont).html();
                const $row     = $(`#row-${idx}`);

                $row.find('td').eq(0).html(safeName);
                $row.find('td').eq(1).html(channelBadgeHtml(ch));
                $row.find('td').eq(2).html(safeCont);
                $row.find('td').eq(3).html(notifyBadgeHtml(notif));

                // Refresh data-* on the edit button so re-opening shows current values
                $row.find('.btn-edit')
                    .data('name', safeName)
                    .data('channel', ch)
                    .data('contact', safeCont)
                    .data('notify-on', notif)
                    .attr('data-name', safeName)
                    .attr('data-channel', ch)
                    .attr('data-contact', safeCont)
                    .attr('data-notify-on', notif);

                $row.find('.btn-delete').attr('data-name', safeName);
            },
            error: function (xhr) {
                refreshCsrf((JSON.parse(xhr.responseText || '{}')).csrf_hash);
                const msg = (JSON.parse(xhr.responseText || '{}')).message || 'Failed to save changes.';
                $('#editError').text(msg).removeClass('d-none');
            },
            complete: function () { $btn.prop('disabled', false).text('Save'); }
        });
    });

    // ── Toggle active ─────────────────────────────────────────────
    $(document).on('change', '.toggle-active', function () {
        const $cb = $(this);
        const idx = $cb.data('idx');

        $.ajax({
            url: `<?= base_url('/notification-target/toggle/') ?>${idx}`,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken() },
            success: function (res) {
                refreshCsrf(res.csrf_hash);
                $cb.prop('checked', res.active === 1);
            },
            error: function () { $cb.prop('checked', !$cb.prop('checked')); }
        });
    });

    // ── Delete ────────────────────────────────────────────────────
    let pendingDeleteIdx = null;

    $(document).on('click', '.btn-delete', function () {
        pendingDeleteIdx = $(this).data('idx');
        $('#deleteTargetName').text($(this).data('name'));
        bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteModal')).show();
    });

    $('#btnDeleteConfirm').on('click', function () {
        if (!pendingDeleteIdx) return;
        const $btn = $(this).prop('disabled', true);

        $.ajax({
            url: `<?= base_url('/notification-target/delete/') ?>${pendingDeleteIdx}`,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken() },
            success: function (res) {
                refreshCsrf(res.csrf_hash);
                $(`#row-${pendingDeleteIdx}`).fadeOut(300, function () {
                    $(this).remove();
                    if ($('#recipientTable tbody tr').length === 0) {
                        $('#recipientTable tbody').append(
                            '<tr><td colspan="6" class="text-center text-muted py-5">No recipients yet. Add one above.</td></tr>'
                        );
                    }
                });
                bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
            },
            error: function () {},
            complete: function () { $btn.prop('disabled', false); }
        });
    });

    $('#deleteModal').on('hidden.bs.modal', function () { pendingDeleteIdx = null; });

});
</script>
<?= $this->endSection(); ?>
