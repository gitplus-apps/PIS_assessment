<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ClassRoom;

class Grade extends Model
{
    //
    const UPDATED_AT = "modifydate";
    const CREATED_AT = "createdate";

    protected $table = "tblgrade";
    protected $primaryKey = "transid";
    public $incrementing = false;

    protected $fillable = [
        "grade_code","grade_desc","transid","section","curriculum","createdate",
        "deleted", "createuser", "modifyuser", "modifydate","school_code"
    ];

    public function classes()
    {
        return $this->hasMany(ClassRoom::class, 'grade_code', 'grade_code');
    }
}
