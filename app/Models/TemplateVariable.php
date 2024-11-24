<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateVariable extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'template_name',
        'variable_name',
        'field_name',
    ];
}
