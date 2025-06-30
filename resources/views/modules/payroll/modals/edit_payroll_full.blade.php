@php
    $items = [
        ['desc' => 'Basic Salary', 'name' => 'basic_salary', 'type' => 'earning'],
        ['desc' => 'Extra Duty Allowance', 'name' => 'duty_allowance', 'type' => 'earning'],
        ['desc' => 'Food Allowance', 'name' => 'food_allowance', 'type' => 'earning'],
        ['desc' => 'HOD\'s Allowance', 'name' => 'hod_increment', 'type' => 'earning'],
        ['desc' => 'Boarding', 'name' => 'boarding', 'type' => 'earning'],
        ['desc' => 'GRA-PAYE', 'name' => 'gra_paye', 'readonly' => true, 'type' => 'deduction'],
        ['desc' => 'SSNIT T2', 'name' => 'ssnit_t2', 'readonly' => true, 'type' => 'deduction'],
        ['desc' => 'Loan Repayment', 'name' => 'loan_repayment', 'type' => 'deduction'],
        ['desc' => 'School Fees Payment', 'name' => 'fees_payment', 'type' => 'deduction'],
        ['desc' => 'Land Payment', 'name' => 'land_payment', 'type' => 'deduction'],
        ['desc' => 'SSNIT Loan', 'name' => 'ssnit_loan', 'type' => 'deduction'],
    ];
@endphp

<style>
    input[readonly] {
        background-color: #f5f5f5;
        color: #333;
    }
</style>

<div class="modal fade" id="edit-payroll-full-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark">
                <h5 class="modal-title text-white" id="exampleModalLabel">Update Staff Payroll</h5>
                <a href="#"><span class="close text-white" data-dismiss="modal" aria-label="Close"
                        aria-hidden="false">×</span></a>
            </div>


            <div class="modal-body">
                <form id="view-payroll-form">
                    @csrf

                    <input type="hidden" id="view-staff-payroll-id" name="transid">
                    <input type="hidden" id="view-staff-payroll-school_code" name="school_code"
                        value="{{ Auth::user()->school_code }}">
                    <input type="hidden" id="view-staff-payroll-staff" name="staff">

                    <div class="row">
                        <div class="form-group col-12">
                            <label>Staff *</label>
                            <input type="text" id="view-staff-payroll-name" class="form-control form-control-sm"
                                readonly>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-6">
                            <label>Year *</label>
                            <input type="text" id="view-staff-payroll-year" class="form-control form-control-sm"
                                readonly>
                        </div>
                        <div class="form-group col-6">
                            <label>Month *</label>
                            <input type="text" id="view-staff-payroll-month" class="form-control form-control-sm"
                                readonly>
                        </div>
                    </div>

                    <input name="national_id" id="view-staff-payroll-national_id" type="hidden" value="Ghana Card"/>

                            <div class="form-group">
                                <label>School</label>

                                <input readonly type="string" id="view-staff-payroll-school_name" class="form-control form-control-sm">
                            </div>


                            <div class="form-group">
                                <label>Position</label>

                                <input readonly type="string" id="view-staff-payroll-position" class="form-control form-control-sm">
                            </div>


                            <div class="form-group">
                                <label>Ghana Card Number</label>

                                <input type="string"
                                    id="view-staff-payroll-national_id_no" class="form-control form-control-sm">
                            </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">Earnings</h6>
                            @foreach ($items as $item)
                                @if ($item['type'] === 'earning')
                                    <div class="form-group">
                                        <label>{{ $item['desc'] }}</label>
                                        <input type="number" step="0.01" min="0" name="{{ $item['name'] }}"
                                            id="view-staff-payroll-{{ $item['name'] }}"
                                            class="form-control form-control-sm"
                                            {{ isset($item['readonly']) ? 'readonly' : '' }}>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        <div class="col-md-6">
                            <h6 class="text-danger">Deductions</h6>
                            @foreach ($items as $item)
                                @if ($item['type'] === 'deduction')
                                    <div class="form-group">
                                        <label>{{ $item['desc'] }}</label>
                                        <input type="number" step="0.01" min="0" name="{{ $item['name'] }}"
                                            id="view-staff-payroll-{{ $item['name'] }}"
                                            class="form-control form-control-sm"
                                            {{ isset($item['readonly']) ? 'readonly' : '' }}>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="form-group col-4">
                            <label>Total Earnings</label>
                            <input type="number" step="0.01" min="0" name="t_earning"
                                id="view-staff-payroll-t_earning" class="form-control form-control-sm" readonly>
                        </div>
                        <div class="form-group col-4">
                            <label>Total Deductions</label>
                            <input type="number" step="0.01" min="0" name="t_deduction"
                                id="view-staff-payroll-t_deduction" class="form-control form-control-sm" readonly>
                        </div>
                        <div class="form-group col-4">
                            <label>Net Salary</label>
                            <input type="number" step="0.01" min="0" name="net"
                                id="view-staff-payroll-net" class="form-control form-control-sm" readonly>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button class="btn btn-light btn-sm" data-dismiss="modal">Close</button>
                <button class="btn btn-primary btn-sm" type="button" id="update-payroll-btn">Update</button>
            </div>
        </div>
    </div>
</div>
