@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow-lg">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="fas fa-file-excel"></i> Upload Assessments from Excel</h4>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Upload an Excel file with student assessments. The first column should contain student names, followed by assessment scores.
            </div>

            <form id="uploadForm" enctype="multipart/form-data">
                @csrf
                <div class="row mb-3">

                <div class="col-md-4">
                        <label for="term" class="form-label fw-bold">Term</label>
                        <select class="form-select select2" name="term" id="term" required>
                            <option value="">--Select Term--</option>
                            <option value="1">Term 1</option>
                            <option value="2">Term 2</option>
                            <option value="3">Term 3</option>
                            <option value="4">Term 4</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="class_code" class="form-label fw-bold">Class</label>
                        <select class="form-select select2" name="class_code" id="class_code" required>
                            <option value="">--Select Class--</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->class_code }}">{{ $class->class_desc }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="subcode" class="form-label fw-bold">Subject</label>
                        <select class="form-select select2" name="subcode" id="subcode" required>
                            <option value="">--Select Subject--</option>
                        </select>
                    </div>
                    
                </div>

                <div class="mb-3">
                    <label for="excel_file" class="form-label fw-bold">Excel File</label>
                    <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".xlsx,.xls,.csv" required>
                    <div class="form-text">Upload Excel file with columns: Name, Class Score, SAT 1, SAT 2, etc.</div>
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Upload and Process
                    </button>
                    <a href="{{ route('newassessment.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </form>

            <div id="resultArea" class="mt-4" style="display: none;">
                <h5 class="text-primary">Upload Results:</h5>
                <div class="alert alert-success" id="successMsg"></div>
                
                <div id="errorList" style="display: none;">
                    <h6 class="text-danger">Errors:</h6>
                    <ul class="list-group" id="errorItems"></ul>
                </div>
                
                <h6 class="mt-3">Processed Records:</h6>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="processedTable">
                        <thead class="table-dark">
                            <tr>
                                <th>Student Name</th>
                                <th>Student ID</th>
                                <th>Paper 1</th>
                                <th>Paper 2</th>
                                <th>Total Score</th>
                                <th>Grade</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="processedBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Handle subject dropdown when class changes
        $('#class_code').change(function() {
            let classCode = $(this).val();

            if (classCode) {
                $.ajax({
                    url: "{{ route('getSubjectsByClass') }}",
                    method: "GET",
                    data: { class_code: classCode },
                    success: function(response) {
                        let subjectDropdown = $('#subcode');
                        subjectDropdown.empty();
                        subjectDropdown.append('<option value="">Select Subject</option>');

                        if (response.subjects.length > 0) {
                            response.subjects.forEach(subject => {
                                subjectDropdown.append(
                                    `<option value="${subject.subcode}">${subject.subname}</option>`
                                );
                            });
                        }
                    }
                });
            }
        });

        // Handle form submission
        $('#uploadForm').submit(function(e) {
            e.preventDefault();
            
            let formData = new FormData(this);
            
            // Show loading
            Swal.fire({
                title: 'Processing...',
                text: 'Please wait while we process your file',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            $.ajax({
                url: "{{ route('newassessment.upload.process') }}",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    Swal.close();
                    
                    if (response.ok) {
                        $('#successMsg').text(response.msg);
                        $('#resultArea').show();
                        
                        // Display errors if any
                        if (response.errors && response.errors.length > 0) {
                            $('#errorList').show();
                            $('#errorItems').empty();
                            
                            response.errors.forEach(error => {
                                $('#errorItems').append(`<li class="list-group-item list-group-item-warning">${error}</li>`);
                            });
                        } else {
                            $('#errorList').hide();
                        }
                        
                        // Display processed records
                        $('#processedBody').empty();
                        if (response.processed && response.processed.length > 0) {
                            response.processed.forEach(record => {
                                $('#processedBody').append(`
                                    <tr>
                                        <td>${record.student_name}</td>
                                        <td>${record.student_no}</td>
                                        <td>${record.paper1}</td>
                                        <td>${record.paper2}</td>
                                        <td>${record.total_score}</td>
                                        <td>${record.grade}</td>
                                        <td>${record.action === 'insert' ? 'New' : 'Updated'}</td>
                                    </tr>
                                `);
                            });
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.msg
                        });
                    }
                },
                error: function(xhr) {
                    Swal.close();
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while processing the file'
                    });
                    
                    console.error(xhr.responseText);
                }
            });
        });
    });
</script>
@endsection