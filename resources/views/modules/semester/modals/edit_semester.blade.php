<div class="modal fade" id="editSemesterModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Edit Semester</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="update-semester-form" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" id="edit_semester_id">
                    <div class="form-group">
                        <label>Semester</label>
                        <input type="text" name="sem_desc" id="edit_semester_desc" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary update-semester">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>
