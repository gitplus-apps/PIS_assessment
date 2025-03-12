<div class="modal fade" id="homeworkModal{{ $homework->transid }}" tabindex="-1" aria-labelledby="homeworkModalLabel{{ $homework->transid }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 shadow-lg border-0">
            
            <!-- Modal Header -->
            <div class="modal-header bg-primary text-white rounded-top-4">
                <h5 class="modal-title fw-bold">{{ $homework->homework_title }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4">
                <p class="text-dark fs-6">{!! nl2br(e($homework->homework_details)) !!}</p>

                <!-- File Download Button -->
                @if($homework->file_path)
                <div class="text-center my-3">
                    <a href="{{ asset('storage/' . $homework->file_path) }}" class="btn btn-outline-primary d-flex align-items-center justify-content-center w-100" download>
                        <i class="fas fa-download me-2"></i> Download Assignment
                    </a>
                </div>
                @endif

                <!-- Homework Submission Form -->
                <form action="{{ route('studenthomeworks.store') }}" method="POST" enctype="multipart/form-data" class="mt-3">
                    @csrf
                    <div class="form-group" hidden>
                        <label for="homework_title">Title</label>
                        <input type="text" name="homework_title" value="{{ $homework->homework_title ?? '' }}" class="form-control" required>
                    </div>

                    <div class="form-group" hidden>
                        <label for="subcode">Details</label>
                        <input name="subcode" value="{{ $homework->course_recipient ?? '' }}" class="form-control" required></input>
                    </div>

                    <div class="form-group" hidden>
                        <label for="submit_to">Posted By</label>
                        <input name="submit_to" value="{{ $homework->posted_by ?? '' }}" class="form-control" required></input>
                    </div>

                    <div class="mb-3">
                        <label for="file" class="form-label fw-semibold">Upload Completed Assignment</label>
                        <input type="file" class="form-control" id="file" name="file" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-upload"></i> Submit Assignment
                        </button>
                    </div>
                </form>

                <!-- Date Posted -->
                <div class="mt-4 text-muted small text-center">
                    <i class="far fa-clock"></i> Posted {{ \Carbon\Carbon::parse($homework->date_posted)->diffForHumans() }}
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-light rounded-bottom-4 border-0 d-flex justify-content-center">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>

        </div>
    </div>
</div> 
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<style>
    .custom-modal-width {
        max-width: 500px; /* Adjust as needed */
    }
    .blink {
    animation: blink-animation 1s steps(5, start) infinite;
}

@keyframes blink-animation {
    to {
        visibility: hidden;
    }
}
</style>

<script>
$(document).ready(function() {
    $('.submit-homework').on('click', function(e) {
        e.preventDefault();
        
        var homeworkId = $(this).data('homework-id');
        var form = $('#submit-homework-form-' + homeworkId);
        
        var isValid = true;
        form.find('input[required]').each(function() {
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

        Swal.fire({
            title: 'Are you sure?',
            text: "Are you sure you want to submit this assignment?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Submit',
            cancelButtonText: 'Cancel',
            reverseButtons: false
        }).then((result) => {
            if (result.value) {
                Swal.fire({
                    text: "Sending...",
                    showConfirmButton: false,
                    allowEscapeKey: false,
                    allowOutsideClick: false
                });

                var formData = new FormData(form[0]);

                $.ajax({
                    url: "{{ route('studenthomeworks.store') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Submitted',
                                text: 'Assignment submitted successfully!',
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