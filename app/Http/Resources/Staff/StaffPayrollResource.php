<?php

namespace App\Http\Resources\Staff;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StaffPayrollResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        $url = url("payslip/{$this->transid}");

        // Log::info('Staff Name', [$this->staffID()]);

        $actualStaff = DB::table('tblstaff')
            ->where('staffno', Auth::user()->userid)
            ->first();

        return [
            'id' => $this->transid,
            'school_code' => Auth::user()->school_code,
            'staff_name' => "{$actualStaff->fname} {$actualStaff->mname} {$actualStaff->lname}",
            'staffno' => Auth::user()->userid,
            'date' => date('Y-m-d', strtotime($this->createdate)),
            'month' => $this->pay_month,
            'year' => $this->pay_year,
            'basic_salary' => $this->earning->basic_salary ?? 0,
            'duty_allowance' => $this->earning->duty_allowance ?? 0,
            'food_allowance' => $this->earning->food_allowance ?? 0,
            'hod_increment' => $this->earning->hod_increment ?? 0,
            'gra_paye' => $this->deduction->gra_paye ?? 0,
            'ssnit_t2' => $this->deduction->ssnit_t2 ?? 0,
            'fees_payment' => $this->deduction->fees_payment ?? 0,
            'land_payment' => $this->deduction->land_payment ?? 0,
            'loan_repayment' => $this->deduction->loan_repayment ?? 0,
            'ssnit_loan' => $this->deduction->ssnit_loan ?? 0,
            't_earning' => number_format($this->totalEarnings(), 2) ?? 0,
            't_deduction' => number_format($this->totalDeductions(), 2) ?? 0,
            'net' => number_format($this->netPay(), 2) ?? 0,
            'action' => Auth::user()->usertype === 'STA'
            ? <<<HTML
            <a href="$url" target="_blank"><button class='rounded btn btn-light btn-sm print-btn'>Print</button></a>
            <button class='rounded btn btn-info btn-sm view-btn'>View</button>
            HTML
            : <<<HTML
            <a href="$url" target="_blank"><button class='rounded btn btn-light btn-sm print-btn'>Print</button></a>
            <button class='rounded btn btn-info btn-sm view-btn'>View</button>
            <button class='rounded btn btn-info btn-sm edit-btn'>Edit</button>
            <button class='rounded btn btn-outline-danger btn-sm delete-btn'>
                <i class='far fa-trash-alt'></i>
            </button>
            HTML,
        ];
    }
}
