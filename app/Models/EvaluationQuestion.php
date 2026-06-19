<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluationQuestion extends Model
{
    use HasFactory;
    public $timestamps =  false;
    protected $table = "participant_evaluation";
    protected $guarded = ['id', "event_id", "description"];

    protected $fillable = [
        'event_id',
        'questions',
        'type',
        'options',
    ];
}
