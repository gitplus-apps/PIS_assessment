<div class="modal fade" id="edit-assess-sat1-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Edit Student SAT1 Assessment</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="edit-student-sat1-form-admin">
                    @csrf
                    <input type="hidden" name="school_code" id="edit-ass-sat1-school_code">
                    <input type="hidden" name="assessment_id" id="edit-ass-sat1-code">
                    <input type="hidden" name="student_no" id="edit-ass-sat1-student-id">
                    <input type="hidden" name="class_code" id="edit-ass-sat1-class-id">


                    <input type="hidden" name="class_score" id="edit-ass-sat1-class_score">
                    <input type="hidden" name="sat1" id="edit-ass-sat1-sat1">
                    <input type="hidden" name="sat2" id="edit-ass-sat1-sat2">
                    <input type="hidden" name="exam" id="edit-ass-sat1-exam">
                    
                    <input type="hidden" name="sat2_paper1" id="edit-ass-sat2_paper1">
                    <input type="hidden" name="sat2_paper2" id="edit-ass-sat2_paper2">

                    <input type="hidden" name="t_comment" id="edit-ass-sat1-t_comment">

                    <div class="form-group">
                        <label>Student</label>
                        <input type="text" id="edit-ass-sat1-student-display" class="form-control" readonly>
                    </div>

                    <div class="form-group">
                        <label>Subject</label>
                        <input type="text" name="subcode" id="edit-ass-sat1-course" class="form-control" readonly>
                    </div>

                    <div class="form-group">
                        <label for="edit-ass-term">Term</label>
                        <input type="number" name="term" id="edit-ass-sat1-term" class="form-control" readonly>
                    </div>


                    <div class="form-group">
                        <label for="edit-ass-sat1">Paper 1</label>
                        <input type="number" id="edit-ass-sat1_paper1" name="sat1_paper1"
                            class="form-control form-control-sm" required>
                    </div>

                    <div class="form-group">
                        <label for="edit-ass-sat2">Paper 2</label>
                        <input type="number" name="sat1_paper2" id="edit-ass-sat1_paper2"
                            class="form-control form-control-sm" required>
                    </div>

                </form>
                <div class="modal-footer">
                    <button class="btn btn-light btn-sm" form="edit-student-sat1-form-admin" type="reset">Reset</button>
                    <button class="btn btn-primary btn-sm" form="edit-student-sat1-form-admin" type="submit">Submit</button>
                </div>
            </div>
        </div>
    </div>
</div>

