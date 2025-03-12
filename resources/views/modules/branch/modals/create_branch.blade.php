<div class="modal fade" id="createBranchModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Create Branch</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="add-branch-form" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label>Branch Name</label>
                        <input type="text" name="branch_desc" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary" id="add-branch">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>
