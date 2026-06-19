<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;
    protected $table = "aa_districts";
    protected $guarded = ['id', 'created_at', 'updated_at'];
    public $timestamps = false;
}
