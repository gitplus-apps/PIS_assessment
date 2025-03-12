@extends('layout.app')

@section('page-content')
    <!-- modules-->
    <div class="row">
        <div class="col-lg-4 col-xl-4 mb-4">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="me-3">
                            <div class="text-xs font-weight-bold text-white text-uppercase mb-1">Courses</div>
                            <div class="h5 mb-0 font-weight-bold text-white">{{ $courses }}</div>
                        </div>
                        <i class="fas fa-calendar fa-2x text-white"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-xl-4 mb-4">
            <div class="card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="me-3">
                            <div class="text-xs font-weight-bold text-white text-uppercase mb-1">Programmes</div>
                            <div class="h5 mb-0 font-weight-bold text-white">{{$prog}}</div>
                        </div>
                        <i class="fas fa-book fa-2x text-white"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-xl-4 mb-4">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="me-3">
                            <div class="text-xs font-weight-bold text-white text-uppercase mb-1">Students</div>
                            <div class="h5 mb-0 font-weight-bold text-white">{{ $students }}</div>
                        </div>
                        <i class="fas fa-user-graduate fa-2x text-white"></i>
                    </div>
                </div>
            </div>
        </div>
        
    </div>

   
<div class="row mt-4">
    <div class="col-md-6">
        <label for="courseFilter" class="form-label">Filter by Course:</label>
        <select id="courseFilter" class="form-control">
            <option value="">All Courses</option>
            @foreach($coursesList as $course)
                <option value="{{ $course->subcode }}">{{ $course->subname }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="row mt-2">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h6 class="text-primary">Total Score Distribution</h6>
            </div>
            <div class="card-body" style="height: 400px;">
                <canvas id="totalScoreChart" style="height: 300px !important;"></canvas>
            </div>
        </div>
    </div>
</div>


    <div class="row mt-4 mb-5">
    <div class="col-md-6 col-lg-12">
        <div class="card card-chart shadow">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 text-primary">Students</h6>
                <input type="text" id="searchInput" class="form-control w-25" placeholder="Search...">
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm table-striped table-hover dataTable js-exportable"
                        id="class-breakdown-table" style="width: 100%">
                        <thead class="">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Course</th>
                                <th>Total Score</th>
                            </tr>
                        </thead>
                        <tbody id="studentTableBody">
                          @foreach($studentsD as $student)
                            <tr class="bg-light">
                            <td>{{ $student->student_no }}</td>
                            <td>{{ $student->fname }} {{ $student->lname }}</td>
                            <td>{{ $student->subcode }} {{ $student->subname }}</td>
                            <td class="student-totalScore">{{ $student->total_score }}</td>
                            </tr>
                          @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- Pagination Controls -->
                <div class="d-flex justify-content-between mt-3">
                    <button id="prevPage" class="btn btn-primary" disabled>Previous</button>
                    <span id="pageInfo" class="align-self-center"></span>
                    <button id="nextPage" class="btn btn-primary">Next</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        let courseFilter = document.getElementById("courseFilter");
        let studentRows = document.querySelectorAll("#studentTableBody tr");
        let ctx = document.getElementById("totalScoreChart").getContext("2d");

        let scoreRanges = [
            { min: 0.00, max: 10.00 },
            { min: 10.01, max: 20.00 },
            { min: 20.01, max: 30.00 },
            { min: 30.01, max: 40.00 },
            { min: 40.01, max: 50.00 },
            { min: 50.01, max: 60.00 },
            { min: 60.01, max: 70.00 },
            { min: 70.01, max: 80.00 },
            { min: 80.01, max: 90.00 },
            { min: 90.01, max: 100.00 }
        ];

        let chart = new Chart(ctx, {
            type: "bar",
            data: {
                labels: scoreRanges.map(range => `${range.min} - ${range.max}`),
                datasets: [{
                    label: "Number of Students",
                    data: Array(10).fill(0),
                    backgroundColor: "rgba(54, 162, 235, 0.6)",
                    borderColor: "rgba(54, 162, 235, 1)",
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        function updateChart(filteredStudents) {
            let scores = Array(10).fill(0);
            filteredStudents.forEach(row => {
                let score = parseFloat(row.querySelector(".student-totalScore").textContent);
                scoreRanges.forEach((range, index) => {
                    if (score >= range.min && score <= range.max) {
                        scores[index]++;
                    }
                });
            });

            chart.data.datasets[0].data = scores;
            chart.update();
        }

        courseFilter.addEventListener("change", function () {
            let selectedCourse = this.value;
            let filteredRows = [];

            studentRows.forEach(row => {
                let courseText = row.children[2].textContent.trim();
                let courseCode = courseText.split(" ")[0];

                if (selectedCourse === "" || courseCode === selectedCourse) {
                    row.style.display = "";
                    filteredRows.push(row);
                } else {
                    row.style.display = "none";
                }
            });

            updateChart(filteredRows);
        });

        updateChart(studentRows); // Initialize chart with all students
    });
</script>



<script>
    document.addEventListener("DOMContentLoaded", function () {
        let rowsPerPage = 10; // Number of rows per page
        let currentPage = 1;
        let rows = Array.from(document.querySelectorAll("#studentTableBody tr"));
        let totalRows = rows.length;
        let totalPages = Math.ceil(totalRows / rowsPerPage);
        let searchActive = false;

        function updatePagination() {
            totalRows = document.querySelectorAll("#studentTableBody tr:not([hidden])").length;
            totalPages = Math.ceil(totalRows / rowsPerPage);
            currentPage = 1; // Reset to first page
            showPage(currentPage);
        }

        function showPage(page) {
            if (searchActive) return; // Disable pagination during search
            let visibleRows = rows.filter(row => !row.hidden);
            let start = (page - 1) * rowsPerPage;
            let end = start + rowsPerPage;

            visibleRows.forEach((row, index) => {
                row.style.display = index >= start && index < end ? "" : "none";
            });

            document.getElementById("prevPage").disabled = page === 1;
            document.getElementById("nextPage").disabled = page === totalPages || totalRows === 0;
            document.getElementById("pageInfo").innerText = `Page ${page} of ${totalPages || 1}`;
        }

        document.getElementById("prevPage").addEventListener("click", function () {
            if (currentPage > 1) {
                currentPage--;
                showPage(currentPage);
            }
        });

        document.getElementById("nextPage").addEventListener("click", function () {
            if (currentPage < totalPages) {
                currentPage++;
                showPage(currentPage);
            }
        });

        // Search Functionality
        document.getElementById("searchInput").addEventListener("keyup", function () {
            let filter = this.value.toLowerCase();
            searchActive = filter.length > 0; // Track if search is active

            rows.forEach(row => {
                let text = row.innerText.toLowerCase();
                row.hidden = !text.includes(filter);
            });

            updatePagination(); // Recalculate pagination based on search results
        });

        // Initial Pagination Display
        showPage(currentPage);
    });
</script>
    
    {{-- <footer class="footer">
        <div class="card">
            <div class="card-body">
                <div class="d-sm-flex justify-content-center justify-content-sm-between">
                    <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">Copyright © 2020 <a
                            href="https://www.bootstrapdash.com/" class="text-muted" target="_blank">Bootstrapdash</a>.
                        All rights reserved.</span>
                    <span class="float-none float-sm-right d-block mt-1 mt-sm-0 text-center text-muted">Free <a
                            href="https://www.bootstrapdash.com/" class="text-muted" target="_blank">Bootstrap
                            dashboard</a> templates from Bootstrapdash.com</span>
                </div>
            </div>
        </div>
    </footer> --}}
@endsection
<!-- main content here -->






<!-- partial:partials/_settings-panel.html -->
<!-- <div class="theme-setting-wrapper">
        <div id="settings-trigger"><i class="typcn typcn-cog-outline"></i></div>
        <div id="theme-settings" class="settings-panel">
          <i class="settings-close typcn typcn-times"></i>
          <p class="settings-heading">SIDEBAR SKINS</p>
          <div class="sidebar-bg-options selected" id="sidebar-light-theme"><div class="img-ss rounded-circle bg-light border mr-3"></div>Light</div>
          <div class="sidebar-bg-options" id="sidebar-dark-theme"><div class="img-ss rounded-circle bg-dark border mr-3"></div>Dark</div>
          <p class="settings-heading mt-2">HEADER SKINS</p>
          <div class="color-tiles mx-0 px-4">
            <div class="tiles success"></div>
            <div class="tiles warning"></div>
            <div class="tiles danger"></div>
            <div class="tiles info"></div>
            <div class="tiles dark"></div>
            <div class="tiles default"></div>
          </div>
        </div>
      </div>
    -->
<!-- partial -->

<!-- partial -->
