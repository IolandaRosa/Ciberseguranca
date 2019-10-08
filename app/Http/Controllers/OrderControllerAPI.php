<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\Order as OrderResource;
use App\Order;
use App\User;
use Response;

class OrderControllerAPI extends Controller
{
    public function destroy($id)
    {
        $order=Order::findOrFail($id);
        if($order->state == 'pending') {
            $order->delete();
        }
        else{
            return Response::json( ['error' => 'Only possible to remove an pending order'], 422);
        }

        return new OrderResource($order);
    }

    public function obtainUnsignedOrders(){

        $orders = Order::whereNull('responsible_cook_id')->where('orders.state','=','confirmed')->get();
        return new OrderResource($orders);
    }

    public function responsibleWaiter($id){

        $orders = Order::join('meals', 'orders.meal_id', '=', 'meals.id')->where('orders.id','=',$id)->select(
            'meals.responsible_waiter_id'

        )->get();

        return new OrderResource($orders);
    }

    public function updateState(Request $request, $id){

        $order=Order::findOrFail($id);

        if(($order->state != "in preparation" && $order->state == "confirmed") &&
            ($order->state == "in preparation" && $request->input('state') != "prepared") &&
            ($order->state == "confirmed" && $request->input('state') != "in preparation")){

             return Response::json( ['error' => 'Invalid state to update'], 422);
         }

     $order->state=$request->input('state');

     if($request->input('state') == "delivered"){
         $order->end = date('Y-m-d H:m:s');
     }

     $order->save();

     return new OrderResource($order);
    }

    public function createOrder(Request $request){

        $request->validate([
            'state' => 'required|',
            'meal_id' => 'required|regex:/(^[0-9\+ ]+$)+/',
            'item_id' => 'required|regex:/(^[0-9\+ ]+$)+/',
        ]);

        $order = new Order();
        $order->state = $request->state;
        $order->meal_id = $request->meal_id;
        $order->item_id = $request->item_id;
        $order->start = date('Y-m-d H:m:s');
        $order->seasoning=$request->seasoning;

        $order->save();

        return new OrderResource($order);
    }

    public function updateCook(Request $request, $id){

        $order=Order::findOrFail($id);

        if($order->state == "pending" || $order->responsible_cook_id != null){
           return Response::json( ['error' => "Can't set a cook to this order"], 422);
       }

       $cook=User::findOrFail($request->input('cook'));

       $order->responsible_cook_id=$request->input('cook');

       $order->state='in preparation';

       $order->save();

       return new OrderResource($order);
   }

   public function ordersOfAMeal(Request $request, $id){

        $orders = Order::join('meals', 'orders.meal_id', '=', 'meals.id')->join('items', 'orders.item_id', '=', 'items.id')->where('meals.id','=',$id)->whereNull('items.deleted_at')->select(
            'orders.id',
            'orders.state',
            'orders.item_id',
            'orders.meal_id',
            'orders.start',
            'items.name',
            'items.price')->get();

        $orders = $orders->sortBy('state');
        return new OrderResource($orders);
    }
}
