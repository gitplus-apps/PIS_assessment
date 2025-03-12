<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $table = 'tblbranch';
    protected $primaryKey = 'transid';
    public $incrementing = false;
    protected $fillable = ['transid', 'school_code', 'branch_code', 'branch_desc', 'createuser', 'createdate'];

    public static function generateBranchCode()
    {
        $lastBranch = self::latest('createdate')->first();
        $lastCode = $lastBranch ? (int) substr($lastBranch->branch_code, 3) : 0;
        return 'BRA' . str_pad($lastCode + 1, 5, '0', STR_PAD_LEFT);
    }
}
