<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;

class Order extends Model
{
    protected $fillable = [
         'id','state','item_id','meal_id','responsible_cook_id','start','end','seasoning',
    ];

    public function cookers()
    {
        return $this->belongsTo(User::class);
    }

    public function meals()
    {
        return $this->belongsTo(Meal::class);
    }


    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
