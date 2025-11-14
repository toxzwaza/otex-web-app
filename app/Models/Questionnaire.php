<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Questionnaire extends Model
{
    use HasFactory;

    protected $fillable = [
        'uid',
        'school',
        'department',
        'grade',
        'gender',
        'casting_experience',
        'casting_staff',
        'sand_experience',
        'sand_staff',
        'memo'
    ];
}
