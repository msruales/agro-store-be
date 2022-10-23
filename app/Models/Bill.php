<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bill extends Model
{
    use SoftDeletes;
    use HasFactory;
    protected $with = ['details','client','user'];

    protected $fillable = [
        'client_id',
        'user_id',
        'type_voucher',
        'type_pay',
        'notes',
        'status',
        'serial_voucher',
        'num_voucher',
        'tax',
        'utility',
        'total',
        'date'
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function client(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Person::class, 'client_id');
    }

    public function details(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DetailBill::class, 'bill_id');
    }
}
