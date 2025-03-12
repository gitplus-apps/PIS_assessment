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
                        <label for="edit-ass-paper1">Paper 1</label>
                        <input type="number" id="edit-ass-paper1" name="paper1"
                            class="form-control form-control-sm" required>
                    </div>

                    <div class="form-group">
                        <label for="edit-ass-paper2">Paper 2</label>
                        <input type="number" name="paper2" id="edit-ass-paper2"
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

<!-- <script>
$(document).ready(function () {
    let studentTable = $('#studentTable tbody');

    function showNoDataMessage() {
        studentTable.html(`
            <tr>
                <td colspan="5" class="text-center text-muted">No data available in table</td>
            </tr>
        `);
    }

    showNoDataMessage();

    $('#filterForm').on('submit', function (e) {
        e.preventDefault();
        let classCode = $('#class_code').val();
        let subcode = $('#subcode').val();
        let term = $('#term').val();

        $.ajax({
            url: "{{ route('newassessment.filter') }}",
            method: "GET",
            data: { class_code: classCode, subcode: subcode, term: term },
            success: function (response) {
                studentTable.empty();
                if (response.students.length > 0) {
                    response.students.forEach(function (student) {
                        studentTable.append(`
                            <tr>
                                <td>${student.student_no}</td>
                                <td>${student.fname} ${student.lname}</td>
                                <td>${student.current_class}</td>
                                <td>${student.total_score}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-assessment-btn" 
                                            data-id="${student.transid}" 
                                            data-student="${student.student_no}" 
                                            data-paper1="${student.paper1}" 
                                            data-paper2="${student.paper2}" 
                                            data-subcode="${student.subcode}" 
                                            data-class="${student.class_code}" 
                                            data-term="${student.term}">
                                        Edit
                                    </button>
                                </td>
                            </tr>
                        `);
                    });
                } else {
                    showNoDataMessage();
                }
            },
            error: function () {
                showNoDataMessage();
            }
        });
    });

    $("#edit-student-assess-form-admin").submit(function (e) {
        e.preventDefault();
        let formData = $(this).serialize();

        $.ajax({
            url: "{{ route('newassessment.store') }}",
            type: "POST",
            data: formData,
            dataType: "json",
            success: function (response) {
                if (response.ok) {
                    alert(response.msg);
                    $("#edit-assess-modal").modal("hide");
                    location.reload();
                } else {
                    alert(response.msg);
                }
            },
            error: function (xhr) {
                console.log(xhr.responseText);
                alert("An error occurred while processing the request.");
            },
        });
    });

    $(document).on("click", ".edit-assessment-btn", function () {
        let assessmentId = $(this).data("id");

        if (!assessmentId) {
            alert("Assessment ID is missing!");
            return;
        }

        $.ajax({
            url: "{{ route('newassessment.getAssessment', '') }}/" + assessmentId,
            type: "GET",
            dataType: "json",
            success: function (data) {
                if (!data) {
                    alert("Error: Assessment not found.");
                    return;
                }

                $("#edit-ass-code").val(data.transid);
                $("#edit-ass-student-id").val(data.student_no);
                $("#edit-ass-student-display").val(data.student_name);
                $("#edit-ass-course-id").val(data.class_code);
                $("#edit-ass-course").val(data.course_name);
                $("#edit-ass-paper1").val(data.paper1);
                $("#edit-ass-paper2").val(data.paper2);
                $("#edit-ass-term").val(data.term);
                
                $("#edit-assess-modal").modal("show");
            },
            error: function (xhr) {
                console.log(xhr.responseText);
                alert("Error fetching assessment details.");
            },
        });
    });
});
</script> -->
