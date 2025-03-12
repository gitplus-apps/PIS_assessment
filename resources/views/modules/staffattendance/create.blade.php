@extends('layouts.app')
@section('page-name', 'Staff Attendance')
@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
    <div class="col">
                <h3 class="page-title">@yield('page-name')
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/">Home</a></li>
                        <li class="breadcrumb-item active">Staff Attendance</li>
                    </ul>
            </div>
        <a href="{{ route('staffattendance.index') }}" class="btn btn-secondary">View Attendance</a>
    </div>

    <form method="POST" action="{{ route('staffattendance.store') }}">
        @csrf

        <div class="mb-3">
            <label for="subcode">Class</label>
            <select id="subcode" name="subcode" class="form-control m-b d-inline" required>
                <option value="">Select Course</option>
                @foreach($courses as $course)
                    <option value="{{ $course->subcode }}">{{ $course->subcode }} - {{ $course->subname }}</option>
                @endforeach
            </select>
        </div>

        

        <div class="mb-3">
            <label for="attendance_date">Date</label>
            <input type="date" id="attendance_date" name="attendance_date" class="form-control" required>
        </div>

        <div class="mb-3">
            <h4>Attendance Sheet</h4>
            <table class="table">
                <thead>
                    <tr>
                        <th>staffs</th>
                        <th>Present</th>
                        <th>Late</th>
                        <th>Absent</th>
                        <th>Holiday</th>
                    </tr>
                </thead>
                <tbody id="staff-list">
                    <tr><td colspan="5">Select a course to load staffs.</td></tr>
                </tbody>
            </table>
        </div>

        <button type="submit" class="btn btn-primary">Save Attendance</button>
    </form>
</div>

<script>
document.getElementById('subcode').addEventListener('change', function() {
    let courseCode = this.value;
    fetch("{{ route('staffattendance.getStaffs') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
        },
        body: JSON.stringify({ subcode: courseCode })
    })
    .then(response => response.json())
    .then(data => {
        let staffList = document.getElementById('staff-list');
        staffList.innerHTML = "";
        data.forEach(staff => {
            staffList.innerHTML += `
                <tr>
                    <td>${staff.fname} ${staff.lname}</td>
                    <td><input type="radio" name="staffs[${staff.staffno}]" value="Present"></td>
                    <td><input type="radio" name="staffs[${staff.staffno}]" value="Late"></td>
                    <td><input type="radio" name="staffs[${staff.staffno}]" value="Absent" checked></td>
                    <td><input type="radio" name="staffs[${staff.staffno}]" value="Holiday"></td>
                </tr>`;
        });
    });
});
</script>
@endsection
