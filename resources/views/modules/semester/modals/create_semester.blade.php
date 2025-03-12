<div class="modal fade" id="createSemesterModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Create Semester</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="add-semester-form" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label>Semester</label>
                        <input type="text" name="sem_desc" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary" id="add-semester">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>
