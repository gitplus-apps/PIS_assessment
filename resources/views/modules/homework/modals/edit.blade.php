<div class="modal fade" id="editHomeworkModal-{{ $homework->transid }}" tabindex="-1" aria-labelledby="editHomeworkModalLabel-{{ $homework->transid }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Homework</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="update-homework-form-{{ $homework->transid }}" action="{{ route('homework.update', $homework->transid) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="homework_title">Title</label>
                        <input type="text" name="homework_title" class="form-control" value="{{ $homework->homework_title }}" required>
                    </div>

                    <div class="form-group">
                        <label for="homework_details">Details</label>
                        <textarea name="homework_details" class="form-control" required>{{ $homework->homework_details }}</textarea>
                    </div>


                    <div class="form-group" id="course_select_{{ $homework->transid }}">
    <label for="subcode">Select Course (If applicable)</label>
    <select name="subcode" class="form-control m-b d-inline select2">
        <option value="">Select Course</option>
        @foreach($courses as $course)
            <option value="{{ $course->subcode }}" {{ isset($homework->subcode) && $homework->subcode == $course->subcode ? 'selected' : '' }}>
                {{ $course->subname }}
            </option>
        @endforeach
    </select>
</div>


                    <div class="form-group">
                        <label for="date_start">Start Date</label>
                        <input type="date" name="date_start" class="form-control" value="{{ $homework->date_start }}" required>
                    </div>

                    <div class="form-group">
                        <label for="date_end">End Date</label>
                        <input type="date" name="date_end" class="form-control" value="{{ $homework->date_end }}" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary update-homework" data-id="{{ $homework->transid }}">Update Homework</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
$(document).ready(function() {
    $('.update-homework').on('click', function(e) {
        e.preventDefault();

        var transid = $(this).data('id');
        var form = $('#update-homework-form-' + transid);
        
        // Validate required fields
        var isValid = true;
        form.find('input[required], textarea[required], select[required]').each(function() {
            if ($(this).val().trim() === '') {
                isValid = false;
                $(this).addClass('is-invalid');
                $(this).focus();
                return false;
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

        // Confirmation modal
        Swal.fire({
            title: 'Are you sure?',
            text: "Are you sure you want to update this homework?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Submit',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    text: "Updating...",
                    showConfirmButton: false,
                    allowEscapeKey: false,
                    allowOutsideClick: false
                });

                var formData = new FormData(form[0]);
                var updateUrl = form.attr('action');

                $.ajax({
                    url: updateUrl,
                    type: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: 'Homework updated successfully!',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Something went wrong!',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr) {
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