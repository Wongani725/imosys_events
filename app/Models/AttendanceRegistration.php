<?php

namespace App\Models;
use App\Models\Participant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class AttendanceRegistration extends Model
{

    use HasFactory;
    public $timestamps =  false;
    protected $table = "attendance_registration";
    protected $guarded = ["id"];
}
