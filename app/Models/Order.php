<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;
    use HasFactory;
    protected $with = ['user'];

    protected $fillable = [
        'user_id',
        'total',
        'date'
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function details(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DetailOrder::class, 'order_id');
    }
}
