<div class="modal fade" id="edit-assess-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Edit Student Assessment</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="edit-student-assess-form-admin">
                    @csrf
                    <input type="hidden" name="school_code" id="edit-ass-school_code">
                    <input type="hidden" name="assessment_id" id="edit-ass-code">
                    <input type="hidden" name="student_no" id="edit-ass-student-id">
                    <input type="hidden" name="class_code" id="edit-ass-class-id">
                    

                    <div class="form-group">
                        <label>Student</label>
                        <input type="text" id="edit-ass-student-display" class="form-control" readonly>
                    </div>

                    <div class="form-group">
                        <label>Subject</label>
                        <input type="text" name="subcode" id="edit-ass-course" class="form-control" readonly>
                    </div>

                    <div class="form-group">
                        <label for="edit-ass-term">Term</label>
                        <input type="number" name="term" id="edit-ass-term" class="form-control" readonly>
                    </div>

                    <div class="form-group">
                        <label for="edit-ass-class_score">Class Score</label>
                        <input type="number" id="edit-ass-class_score" name="class_score"
                            class="form-control form-control-sm" required>
                    </div> 

                    <div class="form-group">
                        <label for="edit-ass-paper1">SAT 1</label>
                        <input type="number" id="edit-ass-paper1" name="paper1"
                            class="form-control form-control-sm" required>
                    </div>

                    <div class="form-group">
                        <label for="edit-ass-paper2">SAT 2</label>
                        <input type="number" name="paper2" id="edit-ass-paper2"
                            class="form-control form-control-sm" required>
                    </div>

                    <div class="form-group">
                        <label for="edit-ass-exam">Exams</label>
                        <input type="number" name="exam" id="edit-ass-exam"
                            class="form-control form-control-sm" required>
                    </div>

                </form>
                <div class="modal-footer">
                    <button class="btn btn-light btn-sm" form="edit-student-assess-form-admin" type="reset">Reset</button>
                    <button class="btn btn-primary btn-sm" form="edit-student-assess-form-admin" type="submit">Submit</button>
                </div>
            </div>
        </div>
    </div>
</div>

