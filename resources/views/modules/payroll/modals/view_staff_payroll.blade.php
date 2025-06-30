@php
    $items = [
        ['desc' => 'Basic Salary', 'name' => 'basic_salary'],
        ['desc' => 'Extra Duty Allowance', 'name' => 'duty_allowance'],
        ['desc' => 'GRA-PAYE', 'name' => 'gra_paye'],
        ['desc' => 'SSNIT T2', 'name' => 'ssnit_t2'],
        ['desc' => 'Loan Repayment', 'name' => 'loan_repayment'],
        ['desc' => 'School Fees Payment', 'name' => 'fees_payment'],
        ['desc' => 'Land Payment', 'name' => 'land_payment'],
        ['desc' => 'SSNIT Loan', 'name' => 'ssnit_loan'],
    ];
@endphp

<div class="modal fade" id="view-staff-payroll-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark">
                <h5 class="modal-title text-white" id="exampleModalLabel">Staff Payslip</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="view-payroll-form">
                    @csrf
                    <div class="row">
                        <div class="form-group col-12">
                            <label>Staff *</label>
                            <input type="text" id="view-modal-staff-name" disabled
                                class="form-control form-control-sm">
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-6">
                            <label>Year *</label>
                            <input type="text" id="view-modal-staff-year" disabled
                                class="form-control form-control-sm">
                        </div>
                        <div class="form-group col-6">
                            <label>Month *</label>
                            <input type="text" id="view-modal-staff-month" disabled
                                class="form-control form-control-sm">
                        </div>
                    </div>


                    <input name="national_id" id="view-modal-staff-national_id" type="hidden" value="Ghana Card"/>

                            <div class="form-group">
                                <label>School</label>

                                <input readonly type="string" id="view-modal-staff-school_name" class="form-control form-control-sm">
                            </div>


                            <div class="form-group">
                                <label>Position</label>

                                <input readonly type="string" id="view-modal-staff-position" class="form-control form-control-sm">
                            </div>


                            <div class="form-group">
                                <label>Ghana Card Number</label>

                                <input type="string"
                                    id="view-modal-staff-national_id_no" class="form-control form-control-sm">
                            </div>

                    <div class="row">
                        @foreach ($items as $item)
                            <div class="form-group col-6">
                                <label>{{ $item['desc'] }}</label>
                                <input type="text" name="{{ $item['name'] }}" disabled
                                    id="view-modal-staff-{{ $item['name'] }}" class="form-control form-control-sm">
                            </div>
                        @endforeach
                    </div>

                    <hr>

                    <div class="row">
                        <div class="form-group col-4">
                            <label>Total Earnings</label>
                            <input type="text" id="view-modal-staff-t_earning" disabled
                                class="form-control form-control-sm">
                        </div>
                        <div class="form-group col-4">
                            <label>Total Deductions</label>
                            <input type="text" id="view-modal-staff-t_deduction" disabled
                                class="form-control form-control-sm">
                        </div>
                        <div class="form-group col-4">
                            <label>Net Salary</label>
                            <input type="text" id="view-modal-staff-net" disabled
                                class="form-control form-control-sm">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light btn-sm rounded" type="button" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
