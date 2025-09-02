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

        // Calculate YTD totals for the same staff member up to current month
        $ytdPayrolls = Payroll::with('earning', 'deduction')
            ->where('staff_id', $payslip->staff_id)
            ->where('pay_year', $payslip->pay_year)
            ->where('pay_month', '<=', $payslip->pay_month)
            ->where('deleted', 0)
            ->get();

        // Calculate YTD earnings
        $ytdBasicSalary = $ytdPayrolls->sum('earning.basic_salary');
        $ytdHodIncrement = $ytdPayrolls->sum('earning.hod_increment');
        $ytdFoodAllowance = $ytdPayrolls->sum('earning.food_allowance');
        $ytdBoarding = $ytdPayrolls->sum('earning.boarding');
        $ytdDutyAllowance = $ytdPayrolls->sum('earning.duty_allowance');
        $ytdSalaryIncrement = $ytdPayrolls->sum('earning.salary_increment');

        // Calculate YTD deductions
        $ytdGraPaye = $ytdPayrolls->sum('deduction.gra_paye');
        $ytdSsnitT2 = $ytdPayrolls->sum('deduction.ssnit_t2');
        $ytdLoanRepayment = $ytdPayrolls->sum('deduction.loan_repayment');
        $ytdFeesPayment = $ytdPayrolls->sum('deduction.fees_payment');
        $ytdLandPayment = $ytdPayrolls->sum('deduction.land_payment');
        $ytdSsnitLoan = $ytdPayrolls->sum('deduction.ssnit_loan');

        // Calculate current month gross
        $gross = $payslip->earning?->basic_salary + $payslip->earning?->hod_increment +
            $payslip->earning?->food_allowance + $payslip->earning?->boarding +
            $payslip->earning?->duty_allowance;

        // Calculate YTD gross
        $ytdGross = $ytdBasicSalary + $ytdHodIncrement + $ytdFoodAllowance +
            $ytdBoarding + $ytdDutyAllowance;

        // Calculate YTD totals
        $ytdTotalEarnings = $ytdPayrolls->sum(function ($payroll) {
            return $payroll->totalEarnings();
        });

        $ytdTotalDeductions = $ytdPayrolls->sum(function ($payroll) {
            return $payroll->totalDeductions();
        });

        $ytdNetPay = $ytdTotalEarnings - $ytdTotalDeductions;

        $slips = [
            [
                'desc' => 'Basic Salary',
                'earn' => $payslip->earning?->basic_salary,
                'deduct' => null,
                'ytd_earn' => $ytdBasicSalary,
                'ytd_deduct' => null,
            ],
            [
                'desc' => 'Extra Duty Allowance',
                'earn' => $payslip->earning?->duty_allowance,
                'deduct' => null,
                'ytd_earn' => $ytdDutyAllowance,
                'ytd_deduct' => null,
            ],
            [
                'desc' => 'Salary Increment',
                'earn' => $payslip->earning?->salary_increment,
                'deduct' => null,
                'ytd_earn' => $ytdSalaryIncrement,
                'ytd_deduct' => null,
            ],
            [
                'desc' => 'HOD Increment',
                'earn' => $payslip->earning?->hod_increment,
                'deduct' => null,
                'ytd_earn' => $ytdHodIncrement,
                'ytd_deduct' => null,
            ],
            [
                'desc' => 'Food Allowance',
                'earn' => $payslip->earning?->food_allowance,
                'deduct' => null,
                'ytd_earn' => $ytdFoodAllowance,
                'ytd_deduct' => null,
            ],
            [
                'desc' => 'Boarding Allowance',
                'earn' => $payslip->earning?->boarding,
                'deduct' => null,
                'ytd_earn' => $ytdBoarding,
                'ytd_deduct' => null,
            ],
            [
                'desc' => 'GRA-PAYE',
                'earn' => null,
                'deduct' => $payslip->deduction?->gra_paye,
                'ytd_earn' => null,
                'ytd_deduct' => $ytdGraPaye,
            ],
            [
                'desc' => 'SSNIT T2',
                'earn' => null,
                'deduct' => $payslip->deduction?->ssnit_t2,
                'ytd_earn' => null,
                'ytd_deduct' => $ytdSsnitT2,
            ],
            [
                'desc' => 'Loan Repayment',
                'earn' => null,
                'deduct' => $payslip->deduction?->loan_repayment,
                'ytd_earn' => null,
                'ytd_deduct' => $ytdLoanRepayment,
            ],
            [
                'desc' => 'School Fees Payment',
                'earn' => null,
                'deduct' => $payslip->deduction?->fees_payment,
                'ytd_earn' => null,
                'ytd_deduct' => $ytdFeesPayment,
            ],
            [
                'desc' => 'Land Payment',
                'earn' => null,
                'deduct' => $payslip->deduction?->land_payment,
                'ytd_earn' => null,
                'ytd_deduct' => $ytdLandPayment,
            ],
            [
                'desc' => 'SSNIT Loan',
                'earn' => null,
                'deduct' => $payslip->deduction?->ssnit_loan,
                'ytd_earn' => null,
                'ytd_deduct' => $ytdSsnitLoan,
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
            // YTD totals
            'ytdTotalEarnings' => $ytdTotalEarnings,
            'ytdTotalDeductions' => $ytdTotalDeductions,
            'ytdNetPay' => $ytdNetPay,
            'ytdGross' => $ytdGross,
        ]);
    }
}
