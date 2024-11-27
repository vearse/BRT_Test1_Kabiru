<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlumeReserveTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'brt_code',
        'reserved_amount',
        'status',
        'expiry_date'
    ];

    protected $dates = ['expiry_date'];

    protected $casts = [
        'expiry_date' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function isExpired()
    {
        return $this->expiry_date && now()->greaterThan($this->expiry_date);
    }

    public function checkAndUpdateStatus()
    {
        if ($this->isExpired()) {
            $this->status = 'expired';
            $this->save();
        }
        return $this;
    }

}
