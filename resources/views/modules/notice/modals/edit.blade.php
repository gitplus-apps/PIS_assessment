<!-- Edit Notice Modal -->
<div class="modal fade" id="editNoticeModal-{{ $notice->transid }}" tabindex="-1" aria-labelledby="editNoticeModalLabel-{{ $notice->transid }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Notice</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="update-notice-form-{{ $notice->transid }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="notice_title">Title</label>
                        <input type="text" name="notice_title" class="form-control" value="{{ $notice->notice_title }}" required>
                    </div>

                    <div class="form-group">
                        <label for="notice_details">Details</label>
                        <textarea name="notice_details" class="form-control" required>{{ $notice->notice_details }}</textarea>
                    </div>

                    <div class="form-group">
                        <label for="recipient_type">Send Notice To</label>
                        <select name="recipient_type" id="recipient_type_{{ $notice->transid }}" class="form-control" required>
                            <option value="students" {{ $notice->notice_recipient == 'students' ? 'selected' : '' }}>All Students</option>
                            <option value="staff" {{ $notice->notice_recipient == 'staff' ? 'selected' : '' }}>All Staff</option>
                            <option value="course_students" {{ $notice->notice_recipient == 'course_students' ? 'selected' : '' }}>Students in a Course</option>
                            <option value="all" {{ $notice->notice_recipient == 'all' ? 'selected' : '' }}>Everyone (Students & Staff)</option>
                        </select>
                    </div>

                    <div class="form-group" id="course_select_{{ $notice->transid }}" style="display: {{ $notice->notice_recipient == 'course_students' ? 'block' : 'none' }};">
                        <label for="subcode">Select Course (If applicable)</label>
                        <select name="subcode" class="form-control select2">
                            <option value="">Select Course</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->subcode }}" {{ isset($notice->subcode) && $notice->subcode == $course->subcode ? 'selected' : '' }}>
                                    {{ $course->subname }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="date_start">Start Date</label>
                        <input type="datetime-local" name="date_start" class="form-control" value="{{ $notice->date_start }}" required>
                    </div>

                    <div class="form-group">
                        <label for="date_end">End Date</label>
                        <input type="datetime-local" name="date_end" class="form-control" value="{{ $notice->date_end }}" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary update-notice" data-transid="{{ $notice->transid }}">Update Notice</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('recipient_type_{{ $notice->transid }}').addEventListener('change', function() {
        let courseSelect = document.getElementById('course_select_{{ $notice->transid }}');
        if (this.value === 'course_students') {
            courseSelect.style.display = 'block';
        } else {
            courseSelect.style.display = 'none';
        }
    });
</script>

<script>
$(document).ready(function() {
    $('.update-notice').on('click', function(e) {
        e.preventDefault();

        var transid = $(this).data('transid');
        var form = $('#update-notice-form-' + transid);
        
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
            text: "Are you sure you want to update this notice?",
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

                $.ajax({
                    url: "{{ route('notice.update', $notice->transid) }}",
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
                                text: 'Notice updated successfully!',
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
