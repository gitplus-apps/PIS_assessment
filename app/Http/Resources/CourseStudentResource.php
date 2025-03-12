<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CourseStudentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return  [
            "coursecode" => $this->subcode,
            "coursedesc" => $this->subname,
            "student" => $this->fname . $this->lname,
            "acyear" => $this->admyear,
            "semester" => $this->admsemester,


        ];
    }
}
