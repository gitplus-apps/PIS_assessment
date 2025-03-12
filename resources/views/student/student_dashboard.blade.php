@extends('layout.app')

@section('page-content')
    <!-- modules-->
    <div class="row">
    <div class="col-lg-4 col-xl-4 mb-4">
            <div class="card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="me-3">
                            <div class="text-xs font-weight-bold text-white text-uppercase mb-1">Programme</div>
                            @foreach($prog as $p)
                            <div class="h6 mb-0 font-weight-bold text-white">{{ $p->prog_desc }}</div>
                            @endforeach
                        </div>
                        <i class="fas fa-user-graduate fa-2x text-white"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-xl-4 mb-4">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="me-3">
                            <div class="text-xs font-weight-bold text-white text-uppercase mb-1">Courses</div>
                            <div class="h5 mb-0 font-weight-bold text-white">{{ $courses }}</div>
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
                            <div class="text-xs font-weight-bold text-white text-uppercase mb-1">GPA</div>
                            <div class="h5 mb-0 font-weight-bold text-white">{{ $gpa }}</div>
                        </div>
                        <i class="fas fa-chalkboard-teacher fa-2x text-white"></i>
                    </div>
                </div>
            </div>
        </div>
        
    </div>




    <div class="row mt-2">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h6 class="text-primary">Total Score Distribution</h6>
            </div>
            <div class="card-body" style="height: 400px;">
                <canvas id="performanceChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>
</div>


<div class="row mt-4 mb-5">
    <div class="col-md-6 col-lg-12">
        <div class="card card-chart shadow">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 text-primary">My Courses</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm table-striped table-hover dataTable js-exportable"
                        id="class-breakdown-table" style="width: 100%">
                        <thead class="">
                            <tr>
                                <th>Course</th>
                                <th>Credit Hours</th>
                            </tr>
                        </thead>
                        <tbody id="studentTableBody">
                          @foreach($coursesD as $course)
                            <tr class="bg-light">
                            <td>{{ $course->subcode }} - {{ $course->subname }}</td>
                            <td>{{ $course->credit }}</td>
                            </tr>
                          @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
document.addEventListener("DOMContentLoaded", function() {
    var ctx = document.getElementById('performanceChart').getContext('2d');
    var performanceChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! $courseTitles !!}, // Course names
            datasets: [{
                label: 'Average Score',
                data: {!! $averageScores !!}, // Avg scores per course
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
                maintainAspectRatio: false,
                scales: {
                            yAxes: [{
                                ticks: {
                                    beginAtZero: true,
                                    max: 100,
                                    stepSize: 10,
                                     callback: function(value) {
                                     return value + '%';
                                    }
                                }
                            }]
                        }
                    }
    });
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