@extends('layouts.app')

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="container mt-4">
        <div class="">
            <ul class="nav nav-tabs" id="assessmentTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="filter-tab2" data-bs-toggle="tab" data-bs-target="#filterStudents2"
                        type="button" role="tab" aria-controls="filterStudents2" aria-selected="true">
                        <i class="fas fa-filter"></i> SAT1 Assessment
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="filter-tab3" data-bs-toggle="tab" data-bs-target="#filterStudents3"
                        type="button" role="tab" aria-controls="filterStudents3" aria-selected="false">
                        <i class="fas fa-filter"></i> SAT2 Assessment
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="filter-tab" data-bs-toggle="tab" data-bs-target="#filterStudents"
                        type="button" role="tab" aria-controls="filterStudents" aria-selected="false">
                        <i class="fas fa-filter"></i> END OF TERM Assessment
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="report-tab" data-bs-toggle="tab" data-bs-target="#report" type="button"
                        role="tab" aria-controls="report" aria-selected="false">
                        <i class="fas fa-chart-bar"></i> Reports
                    </button>
                </li>
            </ul>
        </div>
        </br>

        <div class="tab-content mt-3" id="assessmentTabsContent">


            <!-- SAT1 Assessment -->
            <div class="tab-pane fade show active" id="filterStudents2" role="tabpanel" aria-labelledby="filter-tab2">
                {{-- <h4 class="mb-3 text-center text-primary">Students Assessment</h4> --}}

                <div class="d-flex justify-content-between align-items-center">
                    <div class="col-md-4">
                        <label for="searchStudent2" class="form-label fw-bold">Search Student</label>
                        <input type="text" class="form-control" id="searchStudent2"
                            placeholder="Enter student name or number">
                    </div>
                    <a href="{{ route('newassessment.upload') }}" class="btn btn-primary">Upload Excel</a>
                </div>

                <br />
                <br />
                <br />

                <form id="filterForm2" class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label for="term" class="form-label fw-bold">Term</label>
                        <select class="form-select select2" name="term" id="term2">
                            <option value="">--Select Term--</option>
                            <option value="1">Term 1</option>
                            <option value="2">Term 2</option>
                            <option value="3">Term 3</option>
                            <option value="4">Term 4</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="class_code" class="form-label fw-bold">Class</label>
                        <select class="form-select select2" name="class_code" id="class_code2">
                            <option value="">--Select Class--</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->class_code }}">{{ $class->class_desc }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="subcode" class="form-label fw-bold">Subject</label>
                        <select class="form-control select2" id="subcode2" name="subcode">
                            <option value="">Select Subject</option>
                        </select>
                    </div>



                    <div class="col-md-2 text-center">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Apply Filter
                        </button>
                    </div>
                </form>

                <div class="card shadow-lg mt-4">
                    <div class="card-body">
                        <h5 class="card-title text-center text-secondary">SAT1 Assessment</h5>
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered mt-3" id="studentTable2">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="font-size: 14px; padding: 15px 10px;">Student No</th>
                                        <th style="font-size: 14px; padding: 15px 10px;">Name</th>
                                        <th style="font-size: 14px; padding: 15px 10px;">Class</th>
                                        {{-- <th style="font-size: 14px; padding: 15px 10px;">Class Score</th> --}}
                                        <th style="font-size: 14px; padding: 15px 10px;">Paper 1</th>
                                        <th style="font-size: 14px; padding: 15px 10px;">Paper 2</th>
                                        {{-- <th style="font-size: 14px; padding: 15px 10px;">Total class Score(30%)</th> --}}
                                        {{-- <th style="font-size: 14px; padding: 15px 10px;">Exams</th> --}}
                                        {{-- <th style="font-size: 14px; padding: 15px 10px;">Exams(70%)</th> --}}
                                        <th style="font-size: 14px; padding: 15px 10px;">Total</th>
                                        <th style="font-size: 14px; padding: 15px 10px;">Grade</th>
                                        <th style="font-size: 14px; padding: 15px 10px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Filtered students will be displayed here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>


            <!-- SAT2 Assessment -->
            <div class="tab-pane fade show" id="filterStudents3" role="tabpanel" aria-labelledby="filter-tab3">
                {{-- <h4 class="mb-3 text-center text-primary">Students Assessment</h4> --}}

                <div class="d-flex justify-content-between align-items-center">
                    <div class="col-md-4">
                        <label for="searchStudent3" class="form-label fw-bold">Search Student</label>
                        <input type="text" class="form-control" id="searchStudent3"
                            placeholder="Enter student name or number">
                    </div>
                    <a href="{{ route('newassessment.upload') }}" class="btn btn-primary">Upload Excel</a>
                </div>

                <br />
                <br />
                <br />

                <form id="filterForm3" class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label for="term" class="form-label fw-bold">Term</label>
                        <select class="form-select select2" name="term" id="term3">
                            <option value="">--Select Term--</option>
                            <option value="1">Term 1</option>
                            <option value="2">Term 2</option>
                            <option value="3">Term 3</option>
                            <option value="4">Term 4</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="class_code" class="form-label fw-bold">Class</label>
                        <select class="form-select select2" name="class_code" id="class_code3">
                            <option value="">--Select Class--</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->class_code }}">{{ $class->class_desc }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="subcode" class="form-label fw-bold">Subject</label>
                        <select class="form-control select2" id="subcode3" name="subcode">
                            <option value="">Select Subject</option>
                        </select>
                    </div>



                    <div class="col-md-2 text-center">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Apply Filter
                        </button>
                    </div>
                </form>

                <div class="card shadow-lg mt-4">
                    <div class="card-body">
                        <h5 class="card-title text-center text-secondary">SAT2 Assessment</h5>
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered mt-3" id="studentTable3">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="font-size: 14px; padding: 15px 10px;">Student No</th>
                                        <th style="font-size: 14px; padding: 15px 10px;">Name</th>
                                        <th style="font-size: 14px; padding: 15px 10px;">Class</th>
                                        {{-- <th style="font-size: 14px; padding: 15px 10px;">Class Score</th> --}}
                                        <th style="font-size: 14px; padding: 15px 10px;">Paper 1</th>
                                        <th style="font-size: 14px; padding: 15px 10px;">Paper 2</th>
                                        {{-- <th style="font-size: 14px; padding: 15px 10px;">Total class Score(30%)</th> --}}
                                        {{-- <th style="font-size: 14px; padding: 15px 10px;">Exams</th> --}}
                                        {{-- <th style="font-size: 14px; padding: 15px 10px;">Exams(70%)</th> --}}
                                        <th style="font-size: 14px; padding: 15px 10px;">Total</th>
                                        <th style="font-size: 14px; padding: 15px 10px;">Grade</th>
                                        <th style="font-size: 14px; padding: 15px 10px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Filtered students will be displayed here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>


            <!-- End Of term tab-->
            <div class="tab-pane fade" id="filterStudents" role="tabpanel" aria-labelledby="filter-tab">
                {{-- <h4 class="mb-3 text-center text-primary">Students Assessment</h4> --}}

                <div class="d-flex justify-content-between align-items-center">
                    <div class="col-md-4">
                        <label for="searchStudent" class="form-label fw-bold">Search Student</label>
                        <input type="text" class="form-control" id="searchStudent"
                            placeholder="Enter student name or number">
                    </div>
                    <a href="{{ route('newassessment.upload') }}" class="btn btn-primary">Upload Excel</a>
                </div>

                <br />
                <br />
                <br />

                <form id="filterForm" class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label for="term" class="form-label fw-bold">Term</label>
                        <select class="form-select select2" name="term" id="term">
                            <option value="">--Select Term--</option>
                            <option value="1">Term 1</option>
                            <option value="2">Term 2</option>
                            <option value="3">Term 3</option>
                            <option value="4">Term 4</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="class_code" class="form-label fw-bold">Class</label>
                        <select class="form-select select2" name="class_code" id="class_code">
                            <option value="">--Select Class--</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->class_code }}">{{ $class->class_desc }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="subcode" class="form-label fw-bold">Subject</label>
                        <select class="form-control select2" id="subcode" name="subcode">
                            <option value="">Select Subject</option>
                        </select>
                    </div>



                    <div class="col-md-2 text-center">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Apply Filter
                        </button>
                    </div>
                </form>

                <div class="card shadow-lg mt-4">
                    <div class="card-body">
                        <h5 class="card-title text-center text-secondary">Student List</h5>
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered mt-3" id="studentTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="font-size: 14px; padding: 15px 10px;">Student No</th>
                                        <th style="font-size: 14px; padding: 15px 10px;">Name</th>
                                        <th style="font-size: 14px; padding: 15px 10px;">Class</th>
                                        <th style="font-size: 14px; padding: 15px 10px;">Class Score</th>
                                        <th style="font-size: 14px; padding: 15px 10px;">SAT 1</th>
                                        <th style="font-size: 14px; padding: 15px 10px;">SAT 2</th>
                                        <th style="font-size: 14px; padding: 15px 10px;">Total class Score(30%)</th>
                                        <th style="font-size: 14px; padding: 15px 10px;">Exams</th>
                                        <th style="font-size: 14px; padding: 15px 10px;">Exams(70%)</th>
                                        <th style="font-size: 14px; padding: 15px 10px;">Total Grade</th>
                                        <th style="font-size: 14px; padding: 15px 10px;">Grade</th>
                                        <th style="font-size: 14px; padding: 15px 10px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Filtered students will be displayed here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>




            <!-- Report Tab -->
            <div class="tab-pane fade" id="report" role="tabpanel" aria-labelledby="report-tab">
                <form id="filterForm" class="row g-3 align-items-end">
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
                                <option value="{{ $student->student_no }}">
                                    {{ $student->student_no }}-{{ $student->fname }} {{ $student->mname }}
                                    {{ $student->lname }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-primary mt-3" onclick="fetchAssessments()"><i
                                class="fas fa-filter"></i> Filter
                        </button>
                    </div>

                </form>

                <div class="container mt-4">


                    <div id="printSection"
                        style="border: 1px solid #ddd; box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.1); padding: 20px; width: 90%; margin: auto; font-family: Arial, sans-serif;">
                        <h3 style="text-align: center; color: #007bff;">PIS – MODEL MONTESSORI SCHOOL</h3>
                        <h5 style="text-align: center;">CAMBRIDGE ASSESSMENT INTERNATIONAL EDUCATION</h5>
                        <h6 style="text-align: center; font-weight: 600;">2024/2025 ACADEMIC YEAR TERM 2</h6>
                        <h4 style="text-align: center; margin-top: 20px;">ASSESSMENT REPORT</h4>

                        <br><br>
                        <div id="student_info" style="display: flex; justify-content: space-around;"></div>
                        <br>

                        <h5 style="text-align: center;">Subjects and Assessment Scores</h5>
                        <table style="width: 100%; border-collapse: collapse; text-align: center; margin-top: 20px;">
                            <thead style="background-color: #343a40; color: #fff;">
                                <tr>
                                    <th
                                        style="font-size: 13px; padding: 10px 5px; border: 1px solid #444444; color: #acacac;">
                                        Subjects</th>
                                    <th
                                        style="font-size: 13px; padding: 10px 5px; border: 1px solid #444444; color: #acacac;">
                                        Class Score</th>
                                    <th
                                        style="font-size: 13px; padding: 10px 5px; border: 1px solid #444444; color: #acacac;">
                                        SAT 1</th>
                                    <th
                                        style="font-size: 13px; padding: 10px 5px; border: 1px solid #444444; color: #acacac;">
                                        SAT 2</th>
                                    <th
                                        style="font-size: 13px; padding: 10px 5px; border: 1px solid #444444; color: #acacac;">
                                        Total Class Score(30%)</th>
                                    <th
                                        style="font-size: 13px; padding: 10px 5px; border: 1px solid #444444; color: #acacac;">
                                        Exams</th>
                                    <th
                                        style="font-size: 13px; padding: 10px 5px; border: 1px solid #444444; color: #acacac;">
                                        Exams(70%)</th>
                                    <th
                                        style="font-size: 13px; padding: 10px 5px; border: 1px solid #444444; color: #acacac;">
                                        Total Grade(100%)</th>
                                    <th
                                        style="font-size: 13px; padding: 10px 5px; border: 1px solid #444444; color: #acacac;">
                                        Grade</th>
                                    <th
                                        style="font-size: 13px; padding: 10px 5px; border: 1px solid #444444; color: #acacac;">
                                        Remarks</th>
                                </tr>
                            </thead>
                            <tbody id="assessmentData">
                                <tr>
                                    <td colspan="10" style="padding: 10px; border: 1px solid #ddd; color: #6c757d;">No
                                        data available</td>
                                </tr>
                            </tbody>
                        </table>

                        <br>
                        <div style="margin-top: 20px;">
                            <h5>Class Teacher's Comments:</h5>
                            <div id="comment"></div>
                        </div>

                        <br>
                        <div style="margin-top: 20px; display: flex; justify-content: space-between;">
                            <div>
                                <p><strong>Sign:</strong> ____________________</p>
                                <p>Academic Coordinator</p>
                            </div>
                            <div>
                                <p><strong>Sign:</strong> ____________________</p>
                                <p>Class Teacher</p>
                            </div>
                        </div>

                        <p><strong>Resumption Date:</strong> 1/11/2024</p>
                        <p><strong>Midterm Date:</strong> 6/11/2024</p>
                    </div>



                    <div class="mt-3 text-center">
                        <button class="btn btn-success" onclick="printReport()"><i class="fas fa-print"></i>
                            Print</button>
                        <button class="btn btn-danger" onclick="downloadPDF()"><i class="fas fa-file-pdf"></i>
                            Download
                            PDF</button>
                    </div>
                </div>
            </div>

        </div>

        <!-- Bootstrap JavaScript (Make sure you include this in your layout if not already added) -->



        @include('modules.newassessment.modals.edit_student_endofterm')
        @include('modules.newassessment.modals.edit_student_sat1')
        @include('modules.newassessment.modals.edit_student_sat2')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.5.0/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script> --}}


        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

        <script>
            function printReport() {
                let printContent = document.getElementById('printSection').innerHTML;
                let printWindow = window.open('', '_blank');

                // Construct the full HTML with Bootstrap styling
                printWindow.document.write(`
        <html>
        <head>
            <title>Print Report</title>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
            <style>
                body { padding: 20px; }
            </style>
        </head>
        <body>
            ${printContent}
        </body>
        </html>
    `);

                printWindow.document.close();

                // Ensure styles are fully loaded before printing
                printWindow.onload = function() {
                    printWindow.print();
                    printWindow.close();
                };
            }

            function downloadPDF() {
                // Ensure jsPDF and html2canvas are available
                const {
                    jsPDF
                } = window.jspdf;

                if (typeof html2canvas === 'undefined') {
                    console.error("html2canvas is not loaded!");
                    return;
                }

                let cardElement = document.getElementById('printSection'); // Get the report card
                let teacherComment = document.getElementById('teacherComment')?.value || 'No comments';

                html2canvas(cardElement, {
                    scale: 3,
                    useCORS: true
                }).then(canvas => {
                    let imgData = canvas.toDataURL('image/png');
                    let pdf = new jsPDF('p', 'mm', 'a4');

                    // Scale Image for PDF
                    let imgWidth = 190; // Max width for A4
                    let imgHeight = (canvas.height * imgWidth) / canvas.width; // Maintain aspect ratio
                    if (imgHeight > 250) imgHeight = 250; // Limit height if too big

                    pdf.addImage(imgData, 'PNG', 10, 30, imgWidth, imgHeight);

                    // Save PDF
                    pdf.save("Assessment_Report.pdf");
                }).catch(error => console.error("Error generating PDF:", error));
            }
        </script>
        <script>
            function fetchAssessments() {
                let class_code = document.getElementById('report_class_code')?.value.trim();
                let term = document.getElementById('report_term')?.value.trim();
                let student_no = document.getElementById('student_no')?.value.trim();
                let subject_type = document.getElementById('subject_type')?.value.trim();
                let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                console.log("Captured values:", {
                    class_code,
                    term,
                    student_no,
                    subject_type
                });

                fetch('{{ route('newassessment.fetchAssessments') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            class_code,
                            term,
                            student_no,
                            subject_type
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log({
                            'current_d': data
                        });

                        let studentInfo = document.getElementById('student_info');
                        let comment = document.getElementById('comment');
                        let tbody = document.getElementById('assessmentData');

                        // Clear previous table data
                        tbody.innerHTML = '';

                        if (data.length > 0) {
                            // Display student details
                            studentInfo.innerHTML = `
                <p><strong>Student Name:</strong> ${data[0].student_name}</p>
                <p><strong>Class:</strong> ${data[0].class_name}</p>
            `;
                            comment.innerHTML = `<h6>${data[0].ct_remarks}</h6>`;

                            // Populate assessment data
                            data.forEach(assess => {
                                tbody.innerHTML += `<tr>
                    <td style="font-size: 12px; padding: 10px 5px; border: 1px solid #444444;">${assess.subname}</td>
                    <td style="font-size: 12px; padding: 10px 5px; border: 1px solid #444444;">${assess.class_score}</td>
                    <td style="font-size: 12px; padding: 10px 5px; border: 1px solid #444444;">${assess.sat1 ?? 'N/A'}</td>
                    <td style="font-size: 12px; padding: 10px 5px; border: 1px solid #444444;">${assess.sat2 ?? 'N/A'}</td>
                    <td style="font-size: 12px; padding: 10px 5px; border: 1px solid #444444;">${assess.total_score ?? 'N/A'}</td>
                    <td style="font-size: 12px; padding: 10px 5px; border: 1px solid #444444;">${assess.exams}</td>
                    <td style="font-size: 12px; padding: 10px 5px; border: 1px solid #444444;">${assess.exams70}</td>
                    <td style="font-size: 12px; padding: 10px 5px; border: 1px solid #444444;">${assess.total_grade}</td>
                    <td style="font-size: 12px; padding: 10px 5px; border: 1px solid #444444;">${assess.grade}</td>
                    <td style="font-size: 12px; padding: 10px 5px; border: 1px solid #444444;">${assess.t_remarks}</td>
                </tr>`;
                            });
                        } else {
                            // Ensure student info is displayed even when no assessment data exists
                            studentInfo.innerHTML = `
                <p><strong>Student Name:</strong> ${document.getElementById('report_student_name')?.value || 'N/A'}</p>
                <p><strong>Class:</strong> ${document.getElementById('report_class_name')?.value || 'N/A'}</p>
            `;
                            comment.innerHTML = `<h6>No comments available</h6>`;
                            showNoDataMessage();
                        }
                    })
                    .catch(error => {
                        console.error("Error fetching assessments:", error);
                        showNoDataMessage();
                    });
            }
        </script>

        <script>
            $(document).ready(function() {

                document.getElementById('searchStudent').addEventListener('keyup', function() {
                    let searchValue = this.value.toLowerCase();
                    let tableRows = document.querySelectorAll('#studentTable tbody tr');

                    tableRows.forEach(row => {
                        let studentNumber = row.cells[0].textContent.toLowerCase();
                        let studentName = row.cells[1].textContent.toLowerCase();

                        if (studentNumber.includes(searchValue) || studentName.includes(searchValue)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });

                document.getElementById('searchStudent2').addEventListener('keyup', function() {
                    let searchValue2 = this.value.toLowerCase();
                    let tableRows2 = document.querySelectorAll('#studentTable2 tbody tr');

                    tableRows2.forEach(row => {
                        let studentNumber2 = row.cells[0].textContent.toLowerCase();
                        let studentName2 = row.cells[1].textContent.toLowerCase();

                        if (studentNumber2.includes(searchValue2) || studentName2.includes(
                            searchValue2)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });

                document.getElementById('searchStudent3').addEventListener('keyup', function() {
                    let searchValue3 = this.value.toLowerCase();
                    let tableRows3 = document.querySelectorAll('#studentTable3 tbody tr');

                    tableRows2.forEach(row => {
                        let studentNumber3 = row.cells[0].textContent.toLowerCase();
                        let studentName3 = row.cells[1].textContent.toLowerCase();

                        if (studentNumber3.includes(searchValue3) || studentName3.includes(
                            searchValue3)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });

                document.addEventListener("DOMContentLoaded", function() {
                    var filterTab = document.getElementById("filter-tab");
                    var reportTab = document.getElementById("report-tab");

                    filterTab.addEventListener("click", function() {
                        filterTab.classList.add("active");
                        reportTab.classList.remove("active");
                    });

                    reportTab.addEventListener("click", function() {
                        reportTab.classList.add("active");
                        filterTab.classList.remove("active");
                    });
                });



                $('#class_code').change(function() {
                    let classCode = $(this).val();

                    if (classCode) {
                        $.ajax({
                            url: "{{ route('getSubjectsByClass') }}",
                            method: "GET",
                            data: {
                                class_code: classCode
                            },
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

                $('#class_code2').change(function() {
                    let classCode = $(this).val();

                    if (classCode) {
                        $.ajax({
                            url: "{{ route('getSubjectsByClass') }}",
                            method: "GET",
                            data: {
                                class_code: classCode
                            },
                            success: function(response) {
                                let subjectDropdown = $('#subcode2');
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

                $('#class_code3').change(function() {
                    let classCode = $(this).val();

                    if (classCode) {
                        $.ajax({
                            url: "{{ route('getSubjectsByClass') }}",
                            method: "GET",
                            data: {
                                class_code: classCode
                            },
                            success: function(response) {
                                let subjectDropdown = $('#subcode3');
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



                let studentTable2 = $('#studentTable2 tbody');

                function showNoDataMessage2() {
                    studentTable2.html(`
            <tr>
                <td colspan="8" class="text-center text-muted">No data available in table</td>
            </tr>
        `);
                }


                showNoDataMessage2();
                $('#filterForm2').on('submit', function(e) {
                    e.preventDefault();
                    let classCode = $('#class_code2').val();
                    let subcode = $('#subcode2').val();
                    let term = $('#term2').val();

                    $.ajax({
                        url: "{{ route('newassessment.filter') }}",
                        method: "GET",
                        data: {
                            class_code: classCode,
                            subcode: subcode,
                            term: term
                        },
                        success: function(response) {
                            studentTable2.empty();
                            if (response.students.length > 0) {
                                response.students.sort().forEach(function(student) {
                                    studentTable2.append(`
                            <tr>
                                <td style="font-size: 14px; padding: 15px 10px;">${student.student_no}</td>
                                <td style="font-size: 14px; padding: 15px 10px;">${student.fname} ${student.mname} ${student.lname}</td>
                                <td style="font-size: 14px; padding: 15px 10px;">${student.current_class}</td>
                                {{-- <td style="font-size: 14px; padding: 15px 10px;">${student.class_score}</td> --}}
                                <td style="font-size: 14px; padding: 15px 10px;">${student.sat1_paper1}</td>
                                <td style="font-size: 14px; padding: 15px 10px;">${student.sat1_paper2}</td>
                                {{-- <td style="font-size: 14px; padding: 15px 10px;">${student.total_score}</td> --}}
                                {{-- <td style="font-size: 14px; padding: 15px 10px;">${student.exams}</td> --}}
                                {{-- <td style="font-size: 14px; padding: 15px 10px;">${student.exams70}</td> --}}
                                <td style="font-size: 14px; padding: 15px 10px;">${student.sat1}</td>
                                <td style="font-size: 14px; padding: 15px 10px;">${student.grade}</td>
                                <td style="font-size: 14px; padding: 15px 10px;">
                                    <button class="btn btn-sm btn-primary edit-assessment-sat1-btn"
                                            data-id="${student.transid}"
                                            data-student="${student.student_no}"
                                            data-subcode="${student.subcode}"
                                            data-class="${student.class_code}"
                                            data-term="${student.term}">
                                        <i class="fa fa-edit"></i>
                                    </button>

                                    <button class="btn btn-sm btn-danger delete-assessment-btn"
                                           data-id="${student.transid}">
                                           <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `);
                                });
                            } else {
                                showNoDataMessage2();
                            }
                        },
                        error: function() {
                            showNoDataMessage2();
                        }
                    });
                });



                let studentTable3 = $('#studentTable3 tbody');

                function showNoDataMessage3() {
                    studentTable3.html(`
            <tr>
                <td colspan="8" class="text-center text-muted">No data available in table</td>
            </tr>
        `);
                }


                showNoDataMessage3();
                $('#filterForm3').on('submit', function(e) {
                    e.preventDefault();
                    let classCode = $('#class_code3').val();
                    let subcode = $('#subcode3').val();
                    let term = $('#term3').val();

                    $.ajax({
                        url: "{{ route('newassessment.filter') }}",
                        method: "GET",
                        data: {
                            class_code: classCode,
                            subcode: subcode,
                            term: term
                        },
                        success: function(response) {
                            studentTable3.empty();
                            if (response.students.length > 0) {
                                response.students.sort().forEach(function(student) {
                                    studentTable3.append(`
                            <tr>
                                <td style="font-size: 14px; padding: 15px 10px;">${student.student_no}</td>
                                <td style="font-size: 14px; padding: 15px 10px;">${student.fname} ${student.mname} ${student.lname}</td>
                                <td style="font-size: 14px; padding: 15px 10px;">${student.current_class}</td>
                                {{-- <td style="font-size: 14px; padding: 15px 10px;">${student.class_score}</td> --}}
                                <td style="font-size: 14px; padding: 15px 10px;">${student.sat2_paper1}</td>
                                <td style="font-size: 14px; padding: 15px 10px;">${student.sat2_paper2}</td>
                                {{-- <td style="font-size: 14px; padding: 15px 10px;">${student.total_score}</td> --}}
                                {{-- <td style="font-size: 14px; padding: 15px 10px;">${student.exams}</td> --}}
                                {{-- <td style="font-size: 14px; padding: 15px 10px;">${student.exams70}</td> --}}
                                <td style="font-size: 14px; padding: 15px 10px;">${student.sat2}</td>
                                <td style="font-size: 14px; padding: 15px 10px;">${student.grade}</td>
                                <td style="font-size: 14px; padding: 15px 10px;">
                                    <button class="btn btn-sm btn-primary edit-assessment-sat2-btn"
                                            data-id="${student.transid}"
                                            data-student="${student.student_no}"
                                            data-subcode="${student.subcode}"
                                            data-class="${student.class_code}"
                                            data-term="${student.term}">
                                        <i class="fa fa-edit"></i>
                                    </button>

                                    <button class="btn btn-sm btn-danger delete-assessment-btn"
                                           data-id="${student.transid}">
                                           <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `);
                                });
                            } else {
                                showNoDataMessage3();
                            }
                        },
                        error: function() {
                            showNoDataMessage3();
                        }
                    });
                });



                let studentTable = $('#studentTable tbody');

                function showNoDataMessage() {
                    studentTable.html(`
            <tr>
                <td colspan="12" class="text-center text-muted">No data available in table</td>
            </tr>
        `);
                }


                showNoDataMessage();
                $('#filterForm').on('submit', function(e) {
                    e.preventDefault();
                    let classCode = $('#class_code').val();
                    let subcode = $('#subcode').val();
                    let term = $('#term').val();

                    $.ajax({
                        url: "{{ route('newassessment.filter') }}",
                        method: "GET",
                        data: {
                            class_code: classCode,
                            subcode: subcode,
                            term: term
                        },
                        success: function(response) {
                            studentTable.empty();
                            if (response.students.length > 0) {
                                response.students.sort().forEach(function(student) {
                                    studentTable.append(`
                            <tr>
                                <td style="font-size: 14px; padding: 15px 10px;">${student.student_no}</td>
                                <td style="font-size: 14px; padding: 15px 10px;">${student.fname} ${student.mname} ${student.lname}</td>
                                <td style="font-size: 14px; padding: 15px 10px;">${student.current_class}</td>
                                <td style="font-size: 14px; padding: 15px 10px;">${student.class_score}</td>
                                <td style="font-size: 14px; padding: 15px 10px;">${student.sat1}</td>
                                <td style="font-size: 14px; padding: 15px 10px;">${student.sat2}</td>
                                <td style="font-size: 14px; padding: 15px 10px;">${student.total_score}</td>
                                <td style="font-size: 14px; padding: 15px 10px;">${student.exams}</td>
                                <td style="font-size: 14px; padding: 15px 10px;">${student.exams70}</td>
                                <td style="font-size: 14px; padding: 15px 10px;">${student.total_grade}</td>
                                <td style="font-size: 14px; padding: 15px 10px;">${student.grade}</td>
                                <td style="font-size: 14px; padding: 15px 10px;">
                                    <button class="btn btn-sm btn-primary edit-assessment-btn"
                                            data-id="${student.transid}"
                                            data-student="${student.student_no}"
                                            data-subcode="${student.subcode}"
                                            data-class="${student.class_code}"
                                            data-term="${student.term}">
                                        <i class="fa fa-edit"></i>
                                    </button>

                                    <button class="btn btn-sm btn-danger delete-assessment-btn"
                                           data-id="${student.transid}">
                                           <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `);
                                });
                            } else {
                                showNoDataMessage();
                            }
                        },
                        error: function() {
                            showNoDataMessage();
                        }
                    });
                });



                $("#edit-student-sat1-form-admin").submit(function(e) {
                    e.preventDefault();

                    let formData = $(this).serialize();

                    // Confirmation modal using SweetAlert
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "Do you want to update this assessment?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Submit',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Proceed with the AJAX request after confirmation
                            Swal.fire({
                                text: "Updating...",
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                didOpen: () => {
                                    Swal.showLoading(); // Show a loading indicator
                                }
                            });

                            $.ajax({
                                url: "{{ route('newassessment.store') }}",
                                type: "POST",
                                data: formData,
                                dataType: "json",
                                success: function(response) {
                                    Swal.close(); // Close loading indicator
                                    if (response.ok) {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Success',
                                            text: 'Assessment updated successfully!',
                                            confirmButtonText: 'OK'
                                        }).then(() => {
                                            $("#edit-assess-sat1-modal").modal(
                                                "hide");
                                            //location.reload();
                                            $("#filterForm2").submit();
                                        });
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Oops...',
                                            text: response.msg,
                                            confirmButtonText: 'OK'
                                        });
                                    }
                                },
                                error: function(xhr) {
                                    Swal.close(); // Close loading indicator
                                    console.log(xhr.responseText);
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


                $("#edit-student-sat2-form-admin").submit(function(e) {
                    e.preventDefault();

                    let formData = $(this).serialize();

                    // Confirmation modal using SweetAlert
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "Do you want to update this assessment?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Submit',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Proceed with the AJAX request after confirmation
                            Swal.fire({
                                text: "Updating...",
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                didOpen: () => {
                                    Swal.showLoading(); // Show a loading indicator
                                }
                            });

                            $.ajax({
                                url: "{{ route('newassessment.store') }}",
                                type: "POST",
                                data: formData,
                                dataType: "json",
                                success: function(response) {
                                    Swal.close(); // Close loading indicator
                                    if (response.ok) {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Success',
                                            text: 'Assessment updated successfully!',
                                            confirmButtonText: 'OK'
                                        }).then(() => {
                                            $("#edit-assess-sat2-modal").modal(
                                                "hide");
                                            //location.reload();
                                            $("#filterForm3").submit();
                                        });
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Oops...',
                                            text: response.msg,
                                            confirmButtonText: 'OK'
                                        });
                                    }
                                },
                                error: function(xhr) {
                                    Swal.close(); // Close loading indicator
                                    console.log(xhr.responseText);
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


                $("#edit-student-endofterm-form-admin").submit(function(e) {
                    e.preventDefault();

                    let formData = $(this).serialize();

                    // Confirmation modal using SweetAlert
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "Do you want to update this assessment?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Submit',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Proceed with the AJAX request after confirmation
                            Swal.fire({
                                text: "Updating...",
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                didOpen: () => {
                                    Swal.showLoading(); // Show a loading indicator
                                }
                            });

                            $.ajax({
                                url: "{{ route('newassessment.store') }}",
                                type: "POST",
                                data: formData,
                                dataType: "json",
                                success: function(response) {
                                    Swal.close(); // Close loading indicator
                                    if (response.ok) {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Success',
                                            text: 'Assessment updated successfully!',
                                            confirmButtonText: 'OK'
                                        }).then(() => {
                                            $("#edit-assess-modal").modal("hide");
                                            //location.reload();
                                            $("#filterForm").submit();
                                        });
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Oops...',
                                            text: response.msg,
                                            confirmButtonText: 'OK'
                                        });
                                    }
                                },
                                error: function(xhr) {
                                    Swal.close(); // Close loading indicator
                                    console.log(xhr.responseText);
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



                $(document).on("click", ".edit-assessment-btn", function() {
                    let assessmentId = $(this).data("id");
                    let classCode = $('#class_code').val();
                    let subcode = $('#subcode').val();
                    let term = $('#term').val();

                    if (!assessmentId) {
                        alert("Assessment ID is missing!");
                        return;
                    }
                    let assessmentUrl = "{{ route('newassessment.getAssessment', ':id') }}".replace(':id',
                        assessmentId);

                    $.ajax({
                        url: assessmentUrl,
                        type: "GET",
                        data: {
                            class_code: classCode,
                            subcode: subcode,
                            term: term
                        },
                        dataType: "json",
                        success: function(data) {
                            if (!data) {
                                alert("Error: Assessment not found.");
                                return;
                            }

                            // Fill modal with data for editing
                            $("#edit-ass-school_code").val(data.school_code);
                            $("#edit-ass-code").val(data.transid);
                            $("#edit-ass-student-id").val(data.student_no);
                            $("#edit-ass-student-display").val(data.student_name);
                            $("#edit-ass-class-id").val(data.class_code);
                            $("#edit-ass-course").val(data.subcode);
                            $("#edit-ass-sat1").val(data.sat1);
                            $("#edit-ass-sat2").val(data.sat2);
                            $("#edit-ass-term").val(data.term);
                            $("#edit-ass-class_score").val(data.class_score);
                            $("#edit-ass-exam").val(data.exams)

                            $("#edit-assess-modal").modal("show");
                        },
                        error: function(xhr) {
                            console.log("AJAX Error:", xhr.responseText); // Debugging
                            alert("Error fetching assessment details.");
                        },
                    });

                });



                $(document).on("click", ".edit-assessment-sat1-btn", function() {
                    let assessmentId = $(this).data("id");
                    let classCode = $('#class_code2').val();
                    let subcode = $('#subcode2').val();
                    let term = $('#term2').val();

                    if (!assessmentId) {
                        alert("Assessment ID is missing!");
                        return;
                    }
                    let assessmentUrl = "{{ route('newassessment.getAssessment', ':id') }}".replace(':id',
                        assessmentId);

                    $.ajax({
                        url: assessmentUrl,
                        type: "GET",
                        data: {
                            class_code: classCode,
                            subcode: subcode,
                            term: term
                        },
                        dataType: "json",
                        success: function(data) {
                            if (!data) {
                                alert("Error: Assessment not found.");
                                return;
                            }

                            // Fill modal with data for editing
                            $("#edit-ass-sat1-school_code").val(data.school_code);
                            $("#edit-ass-sat1-code").val(data.transid);
                            $("#edit-ass-sat1-student-id").val(data.student_no);
                            $("#edit-ass-sat1-student-display").val(data.student_name);
                            $("#edit-ass-sat1-class-id").val(data.class_code);
                            $("#edit-ass-sat1-course").val(data.subcode);
                            $("#edit-ass-sat1_paper1").val(data.sat1_paper1);
                            $("#edit-ass-sat1_paper2").val(data.sat1_paper2);
                            $("#edit-ass-sat2_paper1").val(data.sat2_paper1);
                            $("#edit-ass-sat2_paper2").val(data.sat2_paper2);
                            $("#edit-ass-sat1-term").val(data.term);
                            $("#edit-ass-sat1-class_score").val(data.class_score);
                            $("#edit-ass-sat1-exam").val(data.exams);
                            $("#edit-ass-sat1-sat1").val(data.sat1);
                            $("#edit-ass-sat1-sat2").val(data.sat2);

                            $("#edit-assess-sat1-modal").modal("show");
                        },
                        error: function(xhr) {
                            console.log("AJAX Error:", xhr.responseText); // Debugging
                            alert("Error fetching assessment details.");
                        },
                    });

                });


                $(document).on("click", ".edit-assessment-sat2-btn", function() {
                    let assessmentId = $(this).data("id");
                    let classCode = $('#class_code3').val();
                    let subcode = $('#subcode3').val();
                    let term = $('#term3').val();

                    if (!assessmentId) {
                        alert("Assessment ID is missing!");
                        return;
                    }
                    let assessmentUrl = "{{ route('newassessment.getAssessment', ':id') }}".replace(':id',
                        assessmentId);

                    $.ajax({
                        url: assessmentUrl,
                        type: "GET",
                        data: {
                            class_code: classCode,
                            subcode: subcode,
                            term: term
                        },
                        dataType: "json",
                        success: function(data) {
                            if (!data) {
                                alert("Error: Assessment not found.");
                                return;
                            }

                            // Fill modal with data for editing
                            $("#edit-ass-sat2-school_code").val(data.school_code);
                            $("#edit-ass-sat2-code").val(data.transid);
                            $("#edit-ass-sat2-student-id").val(data.student_no);
                            $("#edit-ass-sat2-student-display").val(data.student_name);
                            $("#edit-ass-sat2-class-id").val(data.class_code);
                            $("#edit-ass-sat2-course").val(data.subcode);
                            $("#edit-ass-sat2_paper1").val(data.sat2_paper1);
                            $("#edit-ass-sat2_paper2").val(data.sat2_paper2);
                            $("#edit-ass-sat1_paper1").val(data.sat1_paper1);
                            $("#edit-ass-sat1_paper2").val(data.sat1_paper2);
                            $("#edit-ass-sat2-term").val(data.term);
                            $("#edit-ass-sat2-class_score").val(data.class_score);
                            $("#edit-ass-sat2-exam").val(data.exams);
                            $("#edit-ass-sat2-sat1").val(data.sat1);
                            $("#edit-ass-sat2-sat2").val(data.sat2);

                            $("#edit-assess-sat2-modal").modal("show");
                        },
                        error: function(xhr) {
                            console.log("AJAX Error:", xhr.responseText); // Debugging
                            alert("Error fetching assessment details.");
                        },
                    });

                });

                $(document).on("click", ".delete-assessment-btn", function() {
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
                                url: "{{ route('newassessment.delete') }}",
                                method: "POST",
                                data: {
                                    transid: transid,
                                    _token: "{{ csrf_token() }}"
                                },
                                success: function(response) {
                                    Swal.fire("Deleted!", "Assessment has been deleted.",
                                        "success");
                                    $("#filterForm").submit(); //Refresh the table
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
