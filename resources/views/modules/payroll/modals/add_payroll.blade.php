<style>
    input[readonly] {
        background-color: #f0f0f0;
        color: #333;
    }
</style>

<div class="modal fade" id="add-payroll-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark">
                <h5 class="modal-title text-white" id="exampleModalLabel">Add Staff Payroll</h5>
                <a href="#"><span class="close text-white" data-dismiss="modal" aria-label="Close"
                        aria-hidden="false">×</span></a>
            </div>
            <div class="modal-body">
                <form id="add-payroll-form">
                    @csrf
                    <div class="form-group">
                        <span class="font-weight-bold text-danger"><u> All fields are required </u></span>
                    </div>

                    @php
                        $items = [
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

                    <div class="row">
                        <div class="form-group col-12">
                            <label for="">Staff *</label>
                            <select name="staff" class="form-control select2" required>
                                <option value="">-- Select --</option>
                                @foreach ($staff as $item)
                                    <option value="{{ $item->staffno }}">
                                        {{ $item->fname . ' ' . $item->mname . ' ' . $item->lname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-6">
                            <label for="">Year *</label>
                            <select name="year" class="form-control select2" required>
                                <option value="">-- Select --</option>
                                @foreach ($acyear as $item)
                                    <option value="{{ $item->acyear_code }}">{{ $item->acyear_desc }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-6">
                            <label for="">Month *</label>
                            <select name="month" class="form-control select2" required>
                                <option value="">-- Select --</option>
                                <option value="JAN">January</option>
                                <option value="FEB">February</option>
                                <option value="MAR">March</option>
                                <option value="APR">April</option>
                                <option value="MAY">May</option>
                                <option value="JUN">June</option>
                                <option value="JULY">July</option>
                                <option value="AUG">August</option>
                                <option value="SEP">September</option>
                                <option value="OCT">October</option>
                                <option value="NOV">November</option>
                                <option value="DEC">December</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">Earnings</h6>

                            <!-- ['desc' => 'Basic Salary', 'name' => 'basic_salary', 'type' => 'earning'], -->
                            <div class="form-group">
                                <label>Basic Salary</label>

                                <input type="number" step="0.01" min="0" name="basic_salary" value="0"
                                    id="basic_salary" class="form-control form-control-sm">
                            </div>

                            <!-- National ID type is supposed to be added to the form but should be hidden -->
                            <input name="national_id" id="national_id" type="hidden" value="Ghana Card"/>

                            <div class="form-group">
                                <label>School</label>

                                <input readonly type="string" name="school_name" id="school_name" class="form-control form-control-sm">
                            </div>


                            <div class="form-group">
                                <label>Position</label>

                                <input readonly type="string" name="position" id="position" class="form-control form-control-sm">
                            </div>


                            <div class="form-group">
                                <label>Ghana Card Number</label>

                                <input type="string" name="national_id_no"
                                    id="national_id_no" class="form-control form-control-sm">
                            </div>
                            @foreach ($items as $item)
                                @if ($item['type'] === 'earning')
                                    <div class="form-group">
                                        <label>{{ $item['desc'] }}</label>
                                        <input type="number" step="0.01" min="0" name="{{ $item['name'] }}"
                                            id="{{ $item['name'] }}" class="form-control form-control-sm"
                                            {{ isset($item['readonly']) && $item['readonly'] ? 'readonly' : '' }}>
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
                                            id="{{ $item['name'] }}" class="form-control form-control-sm"
                                            {{ isset($item['readonly']) && $item['readonly'] ? 'readonly' : '' }}>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="form-group col-4">
                            <label>Total Earnings</label>
                            <input type="number" step="0.01" min="0" name="t_earning" id="t_earning"
                                class="form-control form-control-sm" readonly>
                        </div>

                        <div class="form-group col-4">
                            <label>Total Deductions</label>
                            <input type="number" step="0.01" min="0" name="t_deduction" id="t_deduction"
                                class="form-control form-control-sm" readonly>
                        </div>

                        <div class="form-group col-4">
                            <label>Net Salary</label>
                            <input type="number" step="0.01" min="0" name="net" id="net"
                                class="form-control form-control-sm" readonly>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button class="btn btn-light btn-sm rounded" type="button" data-dismiss="modal">Cancel</button>
                <button class="btn btn-light btn-sm rounded" form="add-payroll-form" type="reset">Reset</button>
                <button class="btn btn-primary btn-sm rounded" form="add-payroll-form" type="submit"
                    name="submit">
                    <i class=""></i> Submit
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Ghana Tax System - Updated for 2024/2025 rates
let selectedStaffno;

const SSNIT_RATE = 5.5;

function calculatePAYE(grossIncome, reliefs = 0) {
    const ssnit = grossIncome * 0.055;
    let taxable = grossIncome - ssnit - reliefs;
    if (taxable <= 0) return 0;

    const bands = [
        { cap: 490.00, rate: 0 },
        { cap: 600.00, rate: 5 },
        { cap: 730.00, rate: 10 },
        { cap: 3896.67, rate: 17.5 },
        { cap: 19896.67, rate: 25 },
        { cap: 50416.67, rate: 30 },
        { cap: Infinity, rate: 35 }
    ];

    let tax = 0;
    let prevCap = 0;

    for (let i = 0; i < bands.length; i++) {
        const band = bands[i];
        const bandSize = band.cap - prevCap;

        if (taxable <= 0) break;

        const amountInBand = Math.min(taxable, bandSize);
        tax += amountInBand * (band.rate / 100);

        taxable -= amountInBand;
        prevCap = band.cap;
    }

    return Math.round(tax * 100) / 100;
}


// function calculatePAYE(grossIncome,ssnit, reliefs = 0) {
//     let taxable = grossIncome - ssnit - reliefs;
//     if (taxable <= 0) return 0;
//
//     const bands = [
//         { cap: 490.00, rate: 0 },
//         { cap: 600.00, rate: 5 },
//         { cap: 730.00, rate: 10 },
//         { cap: 3896.67, rate: 17.5 },
//         { cap: 19896.67, rate: 25 },
//         { cap: 50416.67, rate: 30 },
//         { cap: Infinity, rate: 35 }
//     ];
//
//     let tax = 0;
//     let prevCap = 0;
//
//     for (const band of bands) {
//         if (taxable <= 0) break;
//
//         const bandSize = band.cap - prevCap;
//         const amountInBand = Math.min(taxable, bandSize);
//         tax += amountInBand * (band.rate / 100);
//
//         taxable -= amountInBand;
//         prevCap = band.cap;
//         console.log("band size:", bandSize, "rate:", band.rate, "taxable:", taxable);
//     }
//
//     return Math.round(tax * 100) / 100;
// }



// function calculateSSNIT(grossIncome) {
//     const floatGross = parseFloat(grossIncome);
//     return floatGross * 0.055;
// }

function updatePayrollCalculations() {
    const earningFields = ['basic_salary', 'duty_allowance', 'food_allowance', 'hod_increment', 'boarding'];
    const deductionFields = ['loan_repayment', 'fees_payment', 'land_payment', 'ssnit_loan'];

    let grossIncome = 0;
    let totalAllowances = 0;

    earningFields.forEach(field => {
        const val = parseFloat($(`input[name="${field}"]`).val()) || 0;
        grossIncome += val;

        // All except basic_salary are allowances
        if (field !== 'basic_salary') totalAllowances += val;
    });

    const ssnit = Math.round(grossIncome * 0.055 * 100) / 100;
    // const paye = calculatePAYE(grossIncome); // ✅ FIXED
    const paye = (grossIncome, reliefs = 0) => {
        const ssnit = grossIncome * 0.055;
        let taxable = grossIncome - ssnit - reliefs;
        if (taxable <= 0) return 0;

        const bands = [
            { cap: 490.00, rate: 0 },
            { cap: 600.00, rate: 5 },
            { cap: 730.00, rate: 10 },
            { cap: 3896.67, rate: 17.5 },
            { cap: 19896.67, rate: 25 },
            { cap: 50416.67, rate: 30 },
            { cap: Infinity, rate: 35 }
        ];

        let tax = 0;
        let prevCap = 0;

        for (let i = 0; i < bands.length; i++) {
            const band = bands[i];
            const bandSize = band.cap - prevCap;

            if (taxable <= 0) break;

            const amountInBand = Math.min(taxable, bandSize);
            tax += amountInBand * (band.rate / 100);

            taxable -= amountInBand;
            prevCap = band.cap;
        }

        return Math.round(tax * 100) / 100;
    };

    let otherDeductions = 0;
    deductionFields.forEach(field => {
        const val = parseFloat($(`input[name="${field}"]`).val()) || 0;
        otherDeductions += val;
    });

    const totalDeductions = ssnit + paye(grossIncome) + otherDeductions;
    const netSalary = grossIncome - totalDeductions;

    // console.log({
    //     "Gross": grossIncome,
    //     "Allowances": totalAllowances,
    //     "SSNIT": ssnit,
    //     "PAYE": paye(grossIncome),
    //     "Other Deductions": otherDeductions,
    //     "Net": netSalary
    // });

    $('#gra_paye').val(paye(grossIncome).toFixed(2));
    $('#ssnit_t2').val(ssnit.toFixed(2));
    $('#t_earning').val(grossIncome.toFixed(2));
    $('#t_deduction').val(totalDeductions.toFixed(2));
    $('#net').val(Math.max(0, netSalary).toFixed(2));
}



// Bind calculation to input changes
$('#add-payroll-form input[type="number"]').on('input', updatePayrollCalculations);

// Handle form reset
$('#add-payroll-form').on('reset', function() {
    setTimeout(() => {
        $('#gra_paye, #ssnit_t2, #t_earning, #t_deduction, #net').val('');
    }, 0);
});

// Form submission
const addPayrollForm = document.getElementById("add-payroll-form");
$(addPayrollForm).submit(function(e) {
    e.preventDefault();

    var formdata = new FormData(addPayrollForm);
    formdata.append("createuser", createuser);
    formdata.append("school_code", school_code);

    Swal.fire({
        title: 'Are you sure you want to add payroll for this staff?',
        text: "Or click cancel to abort!",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Submit'
    }).then((result) => {
        if (result.value) {
            Swal.fire({
                text: "Adding please wait...",
                showConfirmButton: false,
                allowEscapeKey: false,
                allowOutsideClick: false
            });

            // Update staff credentials
            fetch(`${appUrl}/update-staffcredentials/${school_code}/${selectedStaffno}`, {
                method: 'POST',
                body: formdata,
                headers: {
                    'XSRF-TOKEN': $('meta[name="xsrf-token"]').attr('content'),
                    'laravel_session': $('meta[name="laravel_session"]').attr('content'),
                    'Accept': 'application/json'
                }
            }).then(response => {
                if (!response.ok) {
                    throw new Error("Failed to update ID card details.");
                }
                return response.json();
            });

            // Add payroll
            fetch(`${appUrl}/staff/add_payroll`, {
                method: "POST",
                body: formdata,
                headers: {
                    'XSRF-TOKEN': $('meta[name="xsrf-token"]').attr('content'),
                    'laravel_session': $('meta[name="laravel_session"]').attr('content'),
                    'Accept': 'application/json'
                }
            })
            .then(function(data) {
                if (!data.ok) {
                    Swal.fire({
                        text: data.msg,
                        type: "error"
                    });
                    return;
                }
                Swal.fire({
                    text: "Payroll added successfully",
                    type: "success"
                });
                $("#add-payroll-modal").modal('hide');
                $("select").val(null).trigger('change');
                payrollTable.ajax.reload(null, false);
                addPayrollForm.reset();
            }).catch(function(err) {
                if (err) {
                    Swal.fire({
                        text: "Adding payroll failed"
                    });
                }
            });
        }
    });
});

// Staff selection handler
$('select[name="staff"]').on('change', function() {
    const staffno = $(this).val();
    const schoolCode = school_code;

    if (!staffno) {
        $('#basic_salary').val('');
        return;
    }

    selectedStaffno = staffno;

    // Fetch staff details
    fetch(`${appUrl}/get-selected-staff/${schoolCode}/${staffno}`, {
        method: 'GET',
        headers: {
            'X-XSRF-TOKEN': $('meta[name="xsrf-token"]').attr('content'),
            'laravel_session': $('meta[name="laravel_session"]').attr('content'),
            'Accept': 'application/json'
        }
    }).then(res => {
        if (!res.ok) throw new Error("Failed to fetch active staff");
        return res.json();
    }).then(dataResponse => {
        const { data } = dataResponse;
        const card = data.national_id_no;
        const cardType = data.national_id;
        $('#national_id_no').val(card);
        $('#national_id').val(cardType);
        const schoolName = data.school_name;
        $('#school_name').val(schoolName);
        const position = data.job_positon;
        $('#position').val(position);
    });

    // Fetch base salary
    fetch(`${appUrl}/staff/payroll/get-base-salary/${schoolCode}/${staffno}`, {
        method: 'GET',
        headers: {
            'X-XSRF-TOKEN': $('meta[name="xsrf-token"]').attr('content'),
            'laravel_session': $('meta[name="laravel_session"]').attr('content'),
            'Accept': 'application/json'
        }
    })
    .then(res => {
        if (!res.ok) throw new Error("Failed to fetch salary");
        return res.json();
    })
    .then(data => {
        const salary = data.amount ?? 0;
        $('#basic_salary').val(parseFloat(salary).toFixed(2));
        updatePayrollCalculations();
    })
    .catch(err => {
        console.error("Failed to fetch base salary:", err);
        $('#basic_salary').val(0);
        updatePayrollCalculations();
    });
});
</script>
