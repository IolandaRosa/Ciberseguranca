<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\RestaurantTable;
use App\Order;

class Meal extends Model
{
    //alterado
    protected $fillable = [
        'state','table_number','start','end','responsible_waiter_id', 'total_price_preview',
    ];


    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function meals()
    {
        return $this->hasMany(Meal::class);
    }

    public function orders_delivered()
    {
        return $this->hasMany(Order::class)->where('state', 'delivered');
    }

    public function table()
    {
        return $this->belongsTo(RestaurantTable::class);
    }

}
