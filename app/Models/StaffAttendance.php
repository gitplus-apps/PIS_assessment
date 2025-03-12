<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffAttendance extends Model
{
    protected $table = 'tblstaff_attendance';
    protected $primaryKey = 'transid';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $fillable = [
        'transid', 'school_code', 'branch_code', 'semester', 'subcode', 'staffno', 'attendance_date', 'status', 'deleted', 'createuser', 'createdate', 'modifyuser', 'modifydate'
    ];
}
