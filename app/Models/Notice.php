<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    use HasFactory;
    protected $table = 'tblnotice';
    protected $primaryKey = 'transid';
    public $incrementing = false;
    protected $fillable = [
        'school_code', 'notice_recipient', 'notice_title', 'notice_details',
        'course_recipient', 'posted_by', 'date_posted', 'date_start', 'date_end', 
        'modifydate'
    ];
}


