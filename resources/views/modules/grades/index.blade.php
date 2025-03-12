@extends('layouts.app')
@section('page-name', 'Grade')
@section('content')

<div class="container mt-0">
<div class="col">
                <h3 class="page-title">@yield('page-name')
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/">Home</a></li>
                        <li class="breadcrumb-item active">Grade</li>
                    </ul>
            </div>

    <div class="">
        <div class="card-body p-3">
            <!-- Semester Selection Form -->
            <form method="GET" action="{{ route('grades.index') }}">
                
                    <label for="sem_code" class="form-label fw-semibold">Select Semester:</label>
                    <select name="sem_code" id="sem_code" class="form-control shadow-sm m-b d-inline select2" onchange="this.form.submit()">
                        <option value="">-- Select Semester --</option>
                        @foreach($semesters as $semester)
                            <option value="{{ $semester->sem_code }}" {{ $selectedSemester == $semester->sem_code ? 'selected' : '' }}>
                                {{ $semester->sem_desc }}
                            </option>
                        @endforeach
                    </select>
                
            </form>
        </div>
    </div>

    @if($grades->isNotEmpty())
        <div class="table-responsive mt-4">
            <table class="table table-striped table-hover align-middle text-center shadow-sm rounded-4">
                <thead class="card-header bg-primary text-white">
                    <tr>
                        <th>Course Code</th>
                        <th>Course Name</th>
                        <th>Total Test</th>
                        <th>Total Exam</th>
                        <th>Total Score</th>
                        <th>Grade</th>
                        <th>Credit Grade</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($grades as $grade)
                        <tr class="bg-light">
                            <td class="fw-bold text-primary">{{ $grade->subcode }}</td>
                            <td>{{ $grade->subname ?? 'N/A' }}</td>
                            <td>{{ $grade->total_test }}</td>
                            <td>{{ $grade->total_exam }}</td>
                            <td>{{ $grade->total_score }}</td>
                            <td class="fw-bold 
                                @if($grade->letter_grade == 'A') text-success 
                                @elseif($grade->letter_grade == 'B+') text-warning 
                                @elseif($grade->letter_grade == 'B') text-info 
                                @elseif($grade->letter_grade == 'C+') text-muted 
                                @elseif($grade->letter_grade == 'C') text-secondary 
                                @elseif($grade->letter_grade == 'D+') text-danger 
                                @elseif($grade->letter_grade == 'D') text-danger 
                                @elseif($grade->letter_grade == 'E') text-danger 
                                @else text-dark 
                                @endif">
                                {{ $grade->letter_grade }}
                            </td>
                            <td>{{ $grade->credit_grade }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="alert alert-success mt-4 text-center shadow-sm rounded-4">
        <h4>Semester GPA: <strong>{{ number_format($gpa, 2, '.', '') }}</strong></h4>
       </div>
    @else
        <div class="alert alert-info mt-4 text-center shadow-sm rounded-4">
            No grades available for the selected semester.
        </div>
    @endif
</div>

@endsection
