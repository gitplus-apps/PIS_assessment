<div class="modal fade" id="editBranchModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Edit Branch</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="update-branch-form" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" id="edit_branch_id">
                    <div class="form-group">
                        <label>Branch Name</label>
                        <input type="text" name="branch_desc" id="edit_branch_desc" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary update-branch">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>
