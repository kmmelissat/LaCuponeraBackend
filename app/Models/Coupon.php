<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'commerce_id',
        'offer_title',
        'regular_price',
        'offer_price',
        'start_date',
        'finish_date',
        'deadline',
        'quantity_limit',
        'description',
    ];

    /**
     * Get the commerce that owns the coupon.
     */
    public function commerce()
    {
        return $this->belongsTo(Commerce::class);
    }

    /**
     * Get the users that have exchanged this coupon.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'exchange_coupons')
                    ->withPivot('exchange_date', 'coupon_status')
                    ->withTimestamps();
    }
}