<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Meal;
use Illuminate\Database\Eloquent\SoftDeletes;

class RestaurantTable extends Model
{
	use SoftDeletes;

	protected $primaryKey = 'table_number';

    protected $fillable = ['table_number'];

    public function meals()
    {
        return $this->hasMany(Meal::class,'table_number');
    }
}
