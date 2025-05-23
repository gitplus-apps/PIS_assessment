<div class="modal fade" id="dept-list-modal" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="min-width: 45px;" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Department Staff List</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
              <div class="row">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-striped table-hover dataTable js-exportable"
                        width='100%' id="department-list-table">
                        <thead>
                            <tr>
                                <th>Picture</th>
                                <th>Staff</th>
                                <th>Department</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Data is fetched here using ajax --}}
                        </tbody>
                    </table>
                </div>
              </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
