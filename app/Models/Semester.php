<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    use HasFactory;

    protected $table = 'tblsemester';
    protected $primaryKey = 'transid';
    public $incrementing = false;
    protected $fillable = ['transid', 'sem_code', 'sem_desc', 'createuser', 'createdate'];

    public static function generateSemesterCode()
    {
        $lastSemester = self::latest('createdate')->first();
        $lastCode = $lastSemester ? (int) substr($lastSemester->sem_code, 3) : 0;
        return 'SEM' . str_pad($lastCode + 1, 5, '0', STR_PAD_LEFT);
    }
}
