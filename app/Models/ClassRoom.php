<?php

namespace App\Models;

use App\Models\Student;
use App\Models\Grade;
use Illuminate\Database\Eloquent\Model;

class ClassRoom extends Model
{
    //
    const UPDATED_AT = "modifydate";
    const CREATED_AT = "createdate";

    protected $table = "tblclass";
    protected $primaryKey = "transid";
    public $incrementing = false;

    protected $fillable = [
        "grade_code","class_desc","transid","subclass","class_code","createdate",
        "deleted", "createuser", "modifyuser", "modifydate",
    ];

    public function grade()
    {
        return $this->belongsTo(Grade::class,'grade_code','grade_code');
    }

    public function pupils()
    {
        return $this->hasMany(Student::class,'class_code','current_class');
    }
}
