@extends('layouts.app')
@section('page-name', 'Staff Attendance')
@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-0">
        <div class="col">
            <h3 class="page-title">@yield('page-name')
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item active">Staff Attendance</li>
                </ul>
            </h3>
        </div>

        <a href="{{ route('staffattendance.create') }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus"></i> Take Attendance
        </a>
    </div>

    <div class="">
        <div class="card-body">
            <form method="GET" action="{{ route('staffattendance.index') }}">
                <div class="row">
                    <div class="col-md-6">
                        <label for="subcode" class="form-label fw-semibold">Select Course:</label>
                        <select name="subcode" id="subcode" class="form-control select2" onchange="this.form.submit()">
                            <option value="">-- Select Course --</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->subcode }}" {{ $selectedCourse == $course->subcode ? 'selected' : '' }}>
                                    {{ $course->subcode }} - {{ $course->subname }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if ($selectedCourse)
    <div class="card shadow-sm mt-4">
        <div class="card-body">
            <h5 class="fw-bold">Attendance Record</h5>
            <div class="table-responsive">
                <table class="table table-hover table-bordered text-center">
                    <thead class="card-header bg-primary text-white">
                        <tr>
                            <th>staff</th>
                            @foreach($dates as $date)
                                <th>{{ \Carbon\Carbon::parse($date)->format('d-m-Y') }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($staffs as $staff)
                            <tr>
                                <td class="fw-semibold">{{ $staff->staffno }} - {{ $staff->fname }} {{ $staff->lname }}</td>
                                @foreach($dates as $date)
                                    <td>
                                        <span class="badge {{ isset($attendanceData[$staff->staffno][$date]) ? strtolower($attendanceData[$staff->staffno][$date]) : 'not-taken' }}">
                                            {{ $attendanceData[$staff->staffno][$date] ?? 'Not Taken' }}
                                        </span>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @else
        <div class="alert alert-info mt-4 text-center shadow-sm rounded-4">
            No attendance available for the selected course and semester.
        </div>
    @endif
</div>

<style>
    .present { background-color: #28a745 !important; color: white !important; font-weight: bold; padding: 6px; border-radius: 5px; }
    .late { background-color: #ffc107 !important; color: black !important; font-weight: bold; padding: 6px; border-radius: 5px; }
    .absent { background-color: #dc3545 !important; color: white !important; font-weight: bold; padding: 6px; border-radius: 5px; }
    .holiday { background-color: #6c757d !important; color: white !important; font-weight: bold; padding: 6px; border-radius: 5px; }
    .not-taken { background-color: #6c757d !important; color: white !important; font-weight: bold; padding: 6px; border-radius: 5px; }
</style>
@endsection 