<table>
    <thead>
    <tr>
        <th>Staff No</th>
        <th>Staff Name</th>
        <th>Basic Salary</th>
        <th>Extra Duty Allowance</th>
        <th>Salary Increment</th>
        <th>Loan Repayment</th>
        <th>School Fees Payment </th>
        <th>Lateness</th>
        <th>Uniform</th>
    </tr>
    </thead>
    <tbody>
        @foreach ($staffs as $staff)
            <tr>
                <td>{{ $staff->staffno }}</td>
                <td>{{ "$staff->fname $staff->mname $staff->lname" }}</td>
            </tr>
        @endforeach
    </tbody>
</table>