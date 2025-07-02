<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\School;
use App\Models\Staff;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PayslipController extends Controller
{
    protected $payroll;

    public function show($transid): View
    {
        $payslip = Payroll::with('earning', 'deduction', 'staff')
            ->where('transid', $transid)
            ->where('deleted', 0)
            ->first();
        if (empty($payslip)) {
            abort(404);
        }

        $school = School::where('school_code', $payslip->staff?->school_code)
            ->first();

        $jobID = Staff::where('staffno', Auth::user()->userid)->pluck('job_position')->first();

        $jobPosition = DB::table('tblposition')->where('pos_code',$jobID)->pluck('pos_desc')->first();


        $gross = $payslip->earning?->basic_salary + $payslip->earning?->hod_increment + $payslip->earning?->food_allowance + $payslip->earning?->boarding + $payslip->earning?->duty_allowance;

        $slips = [
            [
                'desc' => 'Basic Salary',
                'earn' => $payslip->earning?->basic_salary,
                'deduct' => null,
            ],
            [
                'desc' => 'Extra Duty Allowance',
                'earn' => $payslip->earning?->duty_allowance,
                'deduct' => null,
            ],
            [
                'desc' => 'Salary Increment',
                'earn' => $payslip->earning?->salary_increment,
                'deduct' => null,
            ],
            [
                'desc' => 'GRA-PAYE',
                'earn' => null,
                'deduct' => $payslip->deduction?->gra_paye,
            ],
            [
                'desc' => 'SSNIT T2',
                'earn' => null,
                'deduct' => $payslip->deduction?->ssnit_t2,
            ],
            [
                'desc' => 'Loan Repayment',
                'earn' => null,
                'deduct' => $payslip->deduction?->loan_repayment,
            ],
            [
                'desc' => 'School Fees Payment',
                'earn' => null,
                'deduct' => $payslip->deduction?->fees_payment,
            ],

            [
                'desc' => 'Land Payment',
                'earn' => null,
                'deduct' => $payslip->deduction?->land_payment,
            ],

            [
                'desc' => 'SSNIT Loan',
                'earn' => null,
                'deduct' => $payslip->deduction?->ssnit_loan,
            ],
        ];


        return view('designs.payslip', [
            'slips' => $slips,
            'staff' => $payslip->staff,
            'month' => $payslip->pay_month,
            'year' => $payslip->pay_year,
            'payment_date' => $payslip->createdate,
            'totalEarning' => $payslip->totalEarnings(),
            'totalDeduction' => $payslip->totalDeductions(),
            'net' => $payslip->netPay(),
            'staff_name' => $payslip->staffName(),
            'school' => $school?->school_name,
            'gross' => $gross,
            'job_position' => $jobPosition,
        ]);
    }
}
