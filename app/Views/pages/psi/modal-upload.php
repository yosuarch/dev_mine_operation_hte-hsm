<!-- upload model -->
<div class="modal fade" id="uploadPSIRecord" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="uploadPSIRecordLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="uploadPSIRecordLabel">Pre-Start Inspection Import</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="psiForm" method="post" action="/upload-psi-record" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="mb-3">
                                <label for="formFile" class="form-label">Select the Pre-Start Inspection (P2H) File</label>
                                <input class="form-control" type="file" id="formFile" name="psiRecording">
                            </div>
                            <p><small>only excel file less than 5mb can be upload</small></p>
                            <button type="button" id="previewPSIFile" class="btn btn-outline-info">Preview</button>
                        </div>
                        <div class="col-lg-8">
                            <p>Lorem ipsum dolor sit amet.</p>
                        </div>
                        <div class="col-lg-12">
                            <!-- preview table -->
                            <table id="filePreview" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Equipment ID</th>
                                        <th>Date</th>
                                        <th>Shift</th>
                                        <th>Operator Name</th>
                                        <th>HM Start</th>
                                        <th>HM End</th>
                                        <th>Checking Item</th>
                                        <th>Checking Note</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- body from json -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-warning">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>