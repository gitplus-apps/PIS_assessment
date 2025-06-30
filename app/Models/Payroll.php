<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Payroll extends Model
{
    const UPDATED_AT = 'modifydate';

    const CREATED_AT = 'createdate';

    protected $table = 'tblpayroll';

    protected $primaryKey = 'transid';

    public $incrementing = false;

    protected $fillable = [
        'pay_month', 'pay_year', 'transid', 'staffno', 'amount', 'dtype', 'item_code', 'save',
        'deleted', 'createuser', 'modifyuser', 'modifydate', 'school_code', 'createdate',
    ];

    public function earning(): HasOne
    {
        return $this->hasOne(EarningItem::class, 'transid', 'transid');
    }

    public function deduction(): HasOne
    {
        return $this->hasOne(DeductionItem::class, 'transid', 'transid');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staffno');
    }

    public function totalEarnings()
    {
        return $this->earning?->basic_salary + $this->earning?->duty_allowance + $this->earning?->salary_increment;
    }

    public function totalDeductions()
    {
        $deducts = $this->deduction?->gra_paye + $this->deduction?->ssnit_t2 + $this->deduction?->loan_repayment
        + $this->deduction?->fees_payment + $this->deduction?->lateness + $this->deduction?->uniform;

        return $deducts;
    }

    public function staffName()
    {
        $actualStaff = DB::table('tblstaff')
            ->where('staffno',Auth::user()->userid)
            ->first();

        return "{$actualStaff->fname} {$actualStaff->mname} {$actualStaff->lname}";
    }

    public function staffID()
    {
            $actualStaff = DB::table('tblstaff')
            ->where('staffno',Auth::user()->userid)
            ->first();
        return $actualStaff->staffno;
    }

    public function schoolCode(){
        return $this->staff?->school_code;
    }

    public function netPay()
    {
        return $this->totalEarnings() - $this->totalDeductions();
    }
}
