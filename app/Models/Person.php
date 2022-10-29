<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Person extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $table = 'persons';
    protected $primaryKey ='id';
    protected $keyType = 'string';
    protected $fillable = ['id','first_name','last_name', 'email', 'document_type', 'document_number', 'direction', 'phone_number'];

    protected $appends = [
        'full_name'
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'person_id');
    }

    public function getFullNameAttribute() {
        return "{$this->first_name} {$this->last_name}";
    }
}
