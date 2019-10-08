<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'state','meal_id','nif', 'name', 'date', 'total_price'
    ];

    public function meal()
    {
        return $this->hasOne(Meal::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

}
