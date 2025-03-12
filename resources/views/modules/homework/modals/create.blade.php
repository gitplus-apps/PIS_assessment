<div class="modal fade" id="createHomeworkModal" tabindex="-1" aria-labelledby="createHomeworkModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Assignment</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form  id="add-homework-form" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="homework_title">Title</label>
                        <input type="text" name="homework_title" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="homework_details">Details</label>
                        <textarea name="homework_details" class="form-control" required></textarea>
                    </div>
                    
                    <div class="form-group" hidden>
                        <label for="recipient_type"></label>
                        <input name="recipient_type" value="course_students" id="recipient_type" class="form-control" required>
                    </div>

                    <div class="form-group" id="course_select">
                        <label for="subcode">Select Course</label>
                        <select name="subcode" class="form-control m-b d-inline select2" required>
                            <option value="">Select Course</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->subcode }}">{{ $course->subcode }}-{{ $course->subname }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="file" class="form-label">Upload File</label>
                        <input type="file" class="form-control" id="file" name="file" required>
                    </div>

                    <div class="form-group">
                        <label for="date_start">Start Date</label>
                        <input type="datetime-local" name="date_start" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="date_end">End Date</label>
                        <input type="datetime-local" name="date_end" class="form-control" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="send-homework">Send</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
$(document).ready(function() {
    $('#send-homework').on('click', function(e) {
        e.preventDefault();

        // Validate required fields
        var isValid = true;
        $('#add-homework-form').find('input[required], textarea[required], select[required]').each(function() {
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
            text: "Are you sure you want to send this assignment?",
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

                var formData = new FormData($('#add-homework-form')[0]);

                $.ajax({
                    url: "{{ route('homework.store') }}",
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
                                text: 'Homework created successfully!',
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