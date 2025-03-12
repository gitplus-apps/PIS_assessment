@extends('layouts.app')
@section('page-name', 'Branch')
@section('content')
<div class="page-header">
        <div class="row">
            <div class="col">
                <h3 class="page-title">@yield('page-name')
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/">Home</a></li>
                        <li class="breadcrumb-item active">Branch</li>
                    </ul>
            </div>
        </div>
    </div>
<div class="card shadow-lg rounded">
        <div class="card-body">
        <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#createBranchModal">Add Branch</button>
            <table class="table table-hover table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Branch Code</th>
                        <th>Branch Name</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($branches as $branch)
                        <tr>
                            <td><strong>{{ $branch->branch_code }}</strong></td>
                            <td>{{ $branch->branch_desc }}</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-info edit-branch" 
                                    data-id="{{ $branch->transid }}" 
                                    data-desc="{{ $branch->branch_desc }}" 
                                    data-url="{{ route('branch.update', $branch->transid) }}" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editBranchModal">
                                    <i class="fas fa-edit"></i> Edit
                                </button>

                                <form action="{{ route('branch.delete', $branch->transid) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-danger delete-branch" data-id="{{ $branch->transid }}">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('modules.branch.modals.create_branch')
@include('modules.branch.modals.edit_branch')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    $('#add-branch').on('click', function(e) {
        e.preventDefault();

        // Validate required fields
        var isValid = true;
        $('#add-branch-form').find('input[required], textarea[required], select[required]').each(function() {
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
            text: "Are you sure you want to add this branch?",
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

                var formData = new FormData($('#add-branch-form')[0]);

                $.ajax({
                    url: "{{ route('branch.store') }}",
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
                                text: 'Branch added successfully!',
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


$(document).ready(function() {
    $('.edit-branch').on('click', function() {
        var transid = $(this).data('id');
        var branchDesc = $(this).data('desc');
        var updateUrl = $(this).data('url');

        $('#edit_branch_id').val(transid);
        $('#edit_branch_desc').val(branchDesc);
        $('#update-branch-form').attr('action', updateUrl);
    });

    $('#update-branch-form').on('submit', function(e) {
        e.preventDefault();

        var form = $(this);
        var formData = new FormData(form[0]);
        var updateUrl = form.attr('action');

        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to update this branch.",
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
                                text: 'Branch updated successfully!',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: response.message || 'Something went wrong!',
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



$(document).ready(function() {
    // Handle Delete Button Click
    $('.delete-branch').on('click', function(e) {
        e.preventDefault();

        // Get homework ID from data attribute
        var branchId = $(this).data('id');
        var deleteUrl = "{{ route('branch.delete', ':id') }}".replace(':id', branchId);

        Swal.fire({
            title: 'Delete assignment!',
            text: "Are you sure you want to delete assignment!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete',
            cancelButtonText: 'Cancel',
            reverseButtons: false
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading indicator
                Swal.fire({
                    text: "Deleting...",
                    showConfirmButton: false,
                    allowEscapeKey: false,
                    allowOutsideClick: false
                });

                // Send AJAX Request for Deletion
                $.ajax({
                    //url: '/branch/delete/' + $(this).data('id'),
                    url: deleteUrl,
                    type: "POST",
                    data: {
                        _method: 'DELETE',
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success) {
                            // Success message
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Assignment deleted successfully!',
                                confirmButtonText: 'OK'
                            }).then(() => {
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

@endsection
