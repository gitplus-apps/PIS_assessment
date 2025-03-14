@extends('layouts.app')

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="">
        <div class="">
            <form id="filterForm" class="mb-4">
                <div class="row">
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
                    <div class="col-md-3">
                        <label for="student_no">Student</label>
                        <select class="form-control select2" id="student_no" name="student_no">
                            <option value="">Select Student</option>
                            @foreach ($students as $student)
                                <option value="{{ $student->student_no }}">{{ $student->student_no }}-{{ $student->fname }} {{ $student->mname }} {{ $student->lname }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button type="button" class="btn btn-primary mt-3" onclick="fetchAssessments()">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </form>

            <div class="container mt-4">
                <h5 style="text-align: center;">Subjects and Assessment Scores</h5>
                <table class="table table-bordered text-center" id="assessmentTable">
                    <thead class="thead-dark">
                        <tr>
                            <th>Subjects</th>
                            <th>Paper 1 (50%)</th>
                            <th>Paper 2 (50%)</th>
                            <th>Final Score (100%)</th>
                            <th>Grade</th>
                            <th>Remarks</th>
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

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // function fetchAssessments() {
        //     let class_code = document.getElementById('report_class_code').value.trim();
        //     let term = document.getElementById('report_term').value.trim();
        //     let student_no = document.getElementById('student_no').value.trim();
        //     let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        //     fetch('{{ route('comment.fetchComment') }}', {
        //         method: 'POST',
        //         headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        //         body: JSON.stringify({ class_code, term, student_no })
        //     })
        //     .then(response => response.json())
        //     .then(data => {
        //         let tbody = document.getElementById('assessmentData');
        //         tbody.innerHTML = '';
        //         if (data.length > 0) {
        //             data.forEach(assess => {
        //                 tbody.innerHTML += `<tr>
        //                     <td>${assess.subname}</td>
        //                     <td>${assess.paper1 ?? 'N/A'}</td>
        //                     <td>${assess.paper2 ?? 'N/A'}</td>
        //                     <td>${assess.total_score ?? 'N/A'}</td>
        //                     <td>${assess.grade}</td>
        //                     <td>${assess.t_remarks}</td>
        //                     <td><button class="btn btn-sm btn-primary" onclick="openCommentModal('${assess.student_no}', '${assess.class_code}', '${assess.term}', '${assess.student_name}', '${assess.class_desc}')">Comment</button></td>
        //                 </tr>`;
        //             });
        //         } else {
        //             tbody.innerHTML = '<tr><td colspan="7">No data available</td></tr>';
        //         }
        //     })
        //     .catch(error => console.error("Error fetching assessments:", error));
        // }

        function fetchAssessments() {
    let class_code = document.getElementById('report_class_code').value.trim();
    let term = document.getElementById('report_term').value.trim();
    let student_no = document.getElementById('student_no').value.trim();
    let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch('{{ route('comment.fetchComment') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ class_code, term, student_no })
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
                <button class="btn btn-sm btn-primary" onclick="openCommentModal('${data.student.student_no}', '${class_code}', '${term}', '${data.student.student_name}', '${assess.class_name}')">
                            Comment
                        </button>

                        <button class="btn btn-sm btn-danger" onclick="deleteComment('${assess.transid}')">
                            Delete
                        </button>
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

        // function submitComment() {
        //     let student_no = document.getElementById('comment_student_no').value;
        //     let class_code = document.getElementById('comment_class_code').value;
        //     let term = document.getElementById('comment_term').value;
        //     let comment_text = document.getElementById('comment_text').value;
        //     let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        //     fetch('{{ route('comment.store') }}', {
        //         method: 'POST',
        //         headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        //         body: JSON.stringify({ student_no, class_code, term, comment: comment_text })
        //     })
        //     .then(() => $('#commentModal').modal('hide'))
        //     .catch(error => console.error("Error submitting comment:", error));
        // }



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
                        location.reload(); // Refresh page or update UI dynamically
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

    function deleteComment(transid) {
    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to undo this action!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Yes, delete it!"
    }).then((result) => {
        if (result.isConfirmed) {
            let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch('{{ route('comment.destroy') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ transid })
            })
            .then(response => response.json())
            .then(data => {
                if (data.ok) {
                    Swal.fire("Deleted!", data.message, "success");
                    fetchAssessments(); // Refresh data
                } else {
                    Swal.fire("Error!", data.error, "error");
                }
            })
            .catch(error => {
                Swal.fire("Error!", "An error occurred while deleting.", "error");
                console.error("Error deleting comment:", error);
            });
        }
    });
}

}


    </script>
@endsection
