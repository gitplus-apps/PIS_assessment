<!-- Notice Creation Modal -->
<div class="modal fade" id="createNoticeModal" tabindex="-1" aria-labelledby="createNoticeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Notice</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="send-notice-form" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="notice_title">Title</label>
                        <input type="text" name="notice_title" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="notice_details">Details</label>
                        <textarea name="notice_details" class="form-control" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="recipient_type">Send Notice To</label>
                        <select name="recipient_type" id="recipient_type" class="form-control" required>
                            <option value="students">All Students</option>
                            <option value="staff">All Staff</option>
                            <option value="course_students">Students in a Course</option>
                            <option value="all">Everyone (Students & Staff)</option>
                        </select>
                    </div>

                    <div class="form-group" id="course_select" style="display: none;">
                        <label for="subcode">Select Course (If applicable)</label>
                        <select name="subcode" class="form-control select2">
                            <option value="">Select Course</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->subcode }}">{{ $course->subname }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="date_start">Start Date</label>
                        <input type="datetime-local" name="date_start" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="date_end">End Date</label>
                        <input type="datetime-local" name="date_end" class="form-control">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="send-notice">Send Notice</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('recipient_type').addEventListener('change', function() {
        let courseSelect = document.getElementById('course_select');
        if (this.value === 'course_students') {
            courseSelect.style.display = 'block';
        } else {
            courseSelect.style.display = 'none';
        }
    });
</script>

<script>
$(document).ready(function() {
    $('#send-notice').on('click', function(e) {
        e.preventDefault();

        // Validate required fields
        var isValid = true;
        $('#send-notice-form').find('input[required], textarea[required], select[required]').each(function() {
            if ($(this).val().trim() === '') {
                isValid = false;
                $(this).addClass('is-invalid'); // Add Bootstrap's invalid class for styling
                $(this).focus();
                return false; // Break the loop
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        if (!isValid) {
            Swal.fire({
                icon: 'error',
                title: 'Required Field Missing',
                text: 'Please fill out all required fields before submitting.',
                confirmButtonText: 'OK'
            });
            return;
        }

        // Show confirmation modal if all fields are filled
        Swal.fire({
            title: 'Are you sure?',
            text: "Are you sure you want to send this notice?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Submit',
            cancelButtonText: 'Cancel',
            reverseButtons: false
        }).then((result) => {
            if (result.value) {
                // If confirmed, submit the form using Ajax
                Swal.fire({
                    text: "Sending...",
                    showConfirmButton: false,
                    allowEscapeKey: false,
                    allowOutsideClick: false
                });

                var formData = new FormData($('#send-notice-form')[0]);

                $.ajax({
                    url: "{{ route('notice.store') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            // Success message
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: 'Notice sent successfully!',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                // Reload the page or close the modal
                                location.reload();
                            });
                        } else {
                            // Handle error if necessary
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Something went wrong!',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr) {
                        // Handle error
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Something went wrong! Please try again.',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        });
    });
});

</script>