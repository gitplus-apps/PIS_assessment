@extends('layouts.app')

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="">
        <div class="" style="display: flex; flex-direction: column; align-items: center;">
            <form id="filterForm" style="display:flex; flex-direction:row; width:100%; align-items:end">
                <div class="col-md-3">
                    <label for="class_code">Class</label>
                    <select class="form-control select2" id="report_class_code" name="class_code">
                        <option value="">Select Class</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->class_code }}">{{ $class->class_desc }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="term">Term</label>
                    <select class="form-control select2" id="report_term" name="term">
                        <option value="">Select Term</option>
                        <option value="1">Term 1</option>
                        <option value="2">Term 2</option>
                        <option value="3">Term 3</option>
                        <option value="4">Term 4</option>
                    </select>
                </div>
                <div class="col-md-2 text-center">
                    <button type="button" class="btn btn-primary" onclick="fetchAssessments()">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
            </form>

            <div class="container mt-4">
                <h5 style="text-align: center; margin-bottom: 1rem;">Subjects and General Comments</h5>
                <table class="table table-bordered table-striped text-center"class="table table-bordered text-center"
                    id="assessmentTable">
                    <thead class="thead-dark">
                        <tr>
                            <th>Subjects</th>
                            <th>Student</th>
                            <th>Grade</th>
                            <th>Comments</th>
                        </tr>
                    </thead>
                    <tbody id="assessmentData">
                        <tr>
                            <td colspan="7">No data available</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Comment Modal -->
    <div class="modal fade" id="commentModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Comment</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="commentForm">
                        <input type="hidden" id="comment_student_no">
                        <input type="hidden" id="comment_class_code">
                        <input type="hidden" id="comment_term">

                        <div class="form-group">
                            <label>Student:</label>
                            <p id="comment_student_name"></p>
                        </div>
                        <div class="form-group">
                            <label>Class:</label>
                            <p id="comment_class"></p>
                        </div>
                        <div class="form-group">
                            <label>Term:</label>
                            <p id="comment_term_text"></p>
                        </div>
                        <div class="form-group">
                            <label for="comment_text">Comment</label>
                            <textarea class="form-control" id="comment_text" rows="3" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="submitComment()">Submit</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Edit Comment Modal -->
    <div class="modal fade" id="editCommentModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Comment</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="editCommentForm">
                        <input type="hidden" id="edit_comment_id">

                        <div class="form-group">
                            <label for="edit_comment_text">Edit Comment</label>
                            <textarea class="form-control" id="edit_comment_text" rows="3" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="updateComment()">Update</button>
                </div>
            </div>
        </div>
    </div>


    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function fetchAssessments() {
            let class_code = document.getElementById('report_class_code').value.trim();
            let term = document.getElementById('report_term').value.trim();
            let student_no = document.getElementById('student_no').value.trim();
            let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch('{{ route('comment.fetchComment') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        class_code,
                        term,
                    })
                })
                .then(response => response.json())
                .then(data => {
                    let tbody = document.getElementById('assessmentData');
                    tbody.innerHTML = '';

                    if (data.assessments.length > 0) {
                        data.assessments.forEach(assess => {
                            tbody.innerHTML += `<tr>
                    <td>${assess.subname}</td>
                    <td>${assess.paper1 ?? 'N/A'}</td>
                    <td>${assess.paper2 ?? 'N/A'}</td>
                    <td>${assess.total_score ?? 'N/A'}</td>
                    <td>${assess.grade}</td>
                    <td>${assess.t_remarks}</td>

                </tr>
                <h6>Comment</h6>
             <div class="d-flex flex-column gap-2">
    <span class="fw-semibold text-secondary text-wrap">${assess.comment}</span>
    <div class="d-flex gap-2">
        <button class="btn btn-sm btn-outline-primary"
            onclick="openCommentModal('${data.student.student_no}', '${class_code}', '${term}', '${data.student.student_name}', '${assess.class_name}')">
            <i class="fas fa-comment"></i> Comment
        </button>
        <button class="btn btn-sm btn-outline-success"
            onclick="openEditCommentModal('${assess.transid}', '${assess.comment}')">
            <i class="fas fa-edit"></i> Edit
        </button>
        <button class="btn btn-sm btn-outline-danger delete-comment-btn" data-id="${assess.transid}">
            <i class="fas fa-trash-alt"></i> Delete
        </button>
    </div>
</div>


                `;
                        });
                    } else {
                        tbody.innerHTML = '<tr><td colspan="7">No data available</td></tr>';
                    }
                })
                .catch(error => console.error("Error fetching assessments:", error));
        }


        function openCommentModal(student_no, class_code, term, student_name, class_desc) {
            console.log("Student No:", student_no);
            console.log("Class Code:", class_code);
            console.log("Term:", term);
            console.log("Student Name:", student_name);
            console.log("Class Desc:", class_desc); // Debugging line

            document.getElementById('comment_student_no').value = student_no;
            document.getElementById('comment_class_code').value = class_code;
            document.getElementById('comment_term').value = term;
            document.getElementById('comment_student_name').textContent = student_name;
            document.getElementById('comment_class').textContent = class_desc; // Assigning class_desc
            document.getElementById('comment_term_text').textContent = 'Term ' + term;
            document.getElementById('comment_text').value = '';
            $('#commentModal').modal('show');
        }


        function submitComment() {
            let student_no = document.getElementById('comment_student_no').value;
            let class_code = document.getElementById('comment_class_code').value;
            let term = document.getElementById('comment_term').value;
            let comment_text = document.getElementById('comment_text').value;
            let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            if (!comment_text.trim()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Warning',
                    text: 'Please enter a comment before submitting.',
                    confirmButtonText: 'OK'
                });
                return;
            }

            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to submit this comment?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Submit',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        text: "Submitting...",
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Use FormData for proper submission
                    let formData = new FormData();
                    formData.append('_token', csrfToken);
                    formData.append('student_no', student_no);
                    formData.append('class_code', class_code);
                    formData.append('term', term);
                    formData.append('comment', comment_text);

                    fetch('{{ route('comment.store') }}', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            Swal.close();
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: 'Comment submitted successfully!',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    $('#commentModal').modal('hide');
                                    document.getElementById('comment_text').value = ''; // Clear input
                                    // location.reload();
                                    $("#filterForm").submit();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: data.message || 'An error occurred. Please try again.',
                                    confirmButtonText: 'OK'
                                });
                            }
                        })
                        .catch(error => {
                            Swal.close();
                            console.error("Error submitting comment:", error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Something went wrong! Please try again.',
                                confirmButtonText: 'OK'
                            });
                        });
                }
            });

        }

        function openEditCommentModal(transid, comment) {
            document.getElementById('edit_comment_id').value = transid;
            document.getElementById('edit_comment_text').value = comment;
            $('#editCommentModal').modal('show');
        }


        function updateComment() {
            let transid = document.getElementById('edit_comment_id').value;
            let ct_remarks = document.getElementById('edit_comment_text').value;
            let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            if (!transid || !ct_remarks) {
                Swal.fire("Warning!", "Please enter a comment before updating.", "warning");
                return;
            }

            Swal.fire({
                title: "Are you sure?",
                text: "Do you want to update this comment?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Update!"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('comment.update') }}",
                        method: "POST",
                        data: {
                            transid: transid,
                            ct_remarks: ct_remarks,
                            _token: csrfToken
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire("Success!", "Comment updated successfully.", "success")
                                    .then(() => location.reload());
                            } else {
                                Swal.fire("Error!", response.message, "error");
                            }
                        },
                        error: function() {
                            Swal.fire("Error!", "Something went wrong. Try again.", "error");
                        }
                    });
                }
            });
        }
    </script>

    <script>
        $(document).ready(function() {

            $(document).on("click", ".delete-comment-btn", function() {
                let transid = $(this).data("id");

                Swal.fire({
                    title: "Are you sure?",
                    text: "This action will delete the assessment permanently!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Delete!",
                    cancelButtonText: "Cancel"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('comment.delete') }}",
                            method: "POST",
                            data: {
                                transid: transid,
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                Swal.fire("Deleted!", "Assessment has been deleted.",
                                    "success");
                            },
                            error: function() {
                                Swal.fire("Error!", "Something went wrong. Try again.",
                                    "error");
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
