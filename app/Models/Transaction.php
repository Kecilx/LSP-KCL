<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ['user_id', 'date', 'total', 'pay_total'];
    


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
    public function details()
    {
        // Penamaan relasinya 'details' agar lebih intuitif
        return $this->hasMany(TransactionDetail::class);
    }
}
