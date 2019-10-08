<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Response;
use App\Http\Resources\RestaurantTable as TableResource;
use App\Http\Resources\Meal as MealResource;
use App\RestaurantTable;
use App\Meal;


class MealControllerAPI extends Controller
{
    public function index(Request $request)
    {
        $query = Meal::whereIn('state', ['active','terminated']);

        return $this->processRequest($request, $query);
    }


    public function parseDate($fulldate){

        $fulldate=str_replace("/", "-", $fulldate);
        $fulldate=str_replace(" ", "-", $fulldate);
        $fulldate=str_replace(":", "-", $fulldate);


        $v1=explode('-', $fulldate.'-', -1);
        $v1[0]=str_replace("-", "", $v1[0]);

        if(count($v1)==1){
            return $v1[0];
        }
        else if(count($v1)==2){
            return $v1[1].'-'.$v1[0];
        }
        else if(count($v1)==3){
            return $v1[2].'-'.$v1[1].'-'.$v1[0];
        }
        else if(count($v1)==4){
            return $v1[2].'-'.$v1[1].'-'.$v1[0].' '.$v1[3];
        }
        else if(count($v1)==5){
            return $v1[2].'-'.$v1[1].'-'.$v1[0].' '.$v1[3].':'.$v1[4];
        }
        else {
            return $v1[2].'-'.$v1[1].'-'.$v1[0].' '.$v1[3].':'.$v1[4].':'.$v1[5];
        }
    }

    public function getPaidMeals(Request $request){
        $query = Meal::where('state', 'paid');

        return $this->processRequest($request, $query);
        
    }

    public function getNotPaidMeals(Request $request){
        $query = Meal::where('state', 'not paid');

        return $this->processRequest($request, $query);
    }

    public function processRequest(Request $request, $query){
        $array = json_decode($request->serverInfo, true);

        $perPage = $array['perPage'];
        $arr = $array['columnFilters'];

        if(array_key_exists('responsible_waiter_id',$arr) && $arr['responsible_waiter_id'] != '')
        {
            $query = $query->where('responsible_waiter_id','=',$arr['responsible_waiter_id']);
        }

        if(array_key_exists('end',$arr) && $arr['end'] != '')
        {
            $date=$this->parseDate($arr['end']);

            $query = $query->where('end','Like','%'.$date.'%');
        }

        if(array_key_exists('start',$arr) && $arr['start'] != '')
        {
            $date=$this->parseDate($arr['start']);

            $query = $query->where('start','Like','%'.$date.'%');
        }

        $sort = $array['sort'];

        if(array_key_exists('field',$sort) && $sort['field'] != '')
        {

            $query = $query->orderBy($sort['field'], $sort['type']);

        }

        $total = $query->select(['meals.*'])->count();
        $invoices = $query->select(['meals.*'])->paginate($perPage);
        $output  = array($invoices, $total);

        return  $output;
    }

    public function show($id)
    {
        $meal=Meal::findOrFail($id);

        return new MealResource($meal);
    }

    public function itemsFromMeal($id){
        $meal=Meal::findOrFail($id);

        $orders=$meal->orders;

        $items=[];


        foreach ($orders as $order) {
            $item=$order->item;

            if($item != null){
                $a=array(
                    "id"=>$item['id'],
                    "type"=>$item['type'],
                    "name"=>$item['name'],
                    "price"=>$item['price'],
                    "order_state"=>$order['state'],
                    "order_id"=>$order['id']);
                
                array_push($items, $a);
            }
            
        }

        return Response::json($items, 200);
    }

    public function createMeal(Request $request) {
        $request->validate([
            'state' => 'required|',
            'table_number' => 'required|regex:/(^[0-9\+ ]+$)+/',
            'responsible_waiter_id' => 'required|regex:/(^[0-9\+ ]+$)+/',
        ]   );

        $meal = new Meal();
        $meal->state = $request->state;
        $meal->table_number = $request->table_number;
        $meal->responsible_waiter_id = $request->responsible_waiter_id;
        $meal->start = date('Y-m-d H:i:s');

        $meal->save();

        return new MealResource($meal);
    }

    public function updateTotalPrice(Request $request) {
        $meal = Meal::findOrFail($request->meal_id);

        $meal->total_price_preview = $meal->total_price_preview + $request->total_price_preview;

        $meal->save();

        return new MealResource($meal);
    }

    public function nonActiveTables(){

        //buscar todas as tables
        $tables = RestaurantTable::select(
            'restaurant_tables.table_number'
        )->get();

        //buscar as active
        $nonActiveTables = Meal::where('state', '=', 'active')->select(
            'meals.table_number'
        )->get();

        $outputTables =  RestaurantTable::whereIn('table_number',$tables)->whereNotIn('table_number',$nonActiveTables)->get();

        return TableResource::collection($outputTables);
    }

    public function activeOrTeminatedMeals(){

        $meals =  Meal::where('state', '=', 'active')->orWhere('state', '=', 'terminated')->get();

        return MealResource::collection($meals);
    }

    public function myMeals($id){

        $myMeals = Meal::where('responsible_waiter_id', '=', $id)->where('state', '=', 'active')->get();
        return MealResource::collection($myMeals);
    }

    public function terminateMeal($id){
        $meal = Meal::findOrFail($id);

        $meal->state = 'terminated';
        $meal->end = date('Y-m-d H:i:s');

        $orders = $meal->orders->where('state', '<>', 'delivered');

        foreach($orders as $order){
         $order->state = 'not delivered';
         $order->end = date('Y-m-d H:i:s');
         
    if($order->item != null)
    {         
        $meal->total_price_preview = $meal->total_price_preview - $order->item->price;
         }
     $order->save();
        
    }

     $meal->save();
     return new MealResource($meal);
 }

 public function mealFromOrder($id){
    $meal = Meal::join('orders', 'meals.id', '=', 'orders.meal_id')->where('orders.id','=',$id)->get();

    return new MealResource($meal);
}

public function markMealAsNotPaid($id){

    $meal = Meal::findOrFail($id);

    $meal->state = 'not paid';

    $orders = $meal->orders->where('state', '<>', 'delivered');

    foreach($orders as $order){
        $order->state = 'not delivered';
        $order->end = date('Y-m-d H:i:s');
        $order->save();
    }

    $meal->save();
    $meal = Meal::join('invoices', 'meals.id', '=', 'invoices.meal_id')->where('meals.id','=',$id)->select(
        'invoices.id'
    )->get();

    return new MealResource($meal);
}
}