<?php

namespace App\Http\Controllers;

use App\User;
use Response;
use App\Order;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\Order as OrderResource;
use App\Meal;


class StatisticControllerAPI extends Controller
{

    public function statisticsMealsByDayByUser(){

        $response=[];

        $totalOfMealsByFunc=Meal::select([DB::raw('count(*) AS total'),'responsible_waiter_id AS resp'])->where('state','!=','active')->where('state','!=','terminated')->groupBy('responsible_waiter_id')
        ->get();

        $totalOfDays=Meal::select([DB::raw('DATE(start)')])->where('state','!=','active')->where('state','!=','terminated')
        ->groupBy(DB::raw('DATE(start)'))
        ->get();

        array_push($response,count($totalOfDays));
        array_push($response,$totalOfMealsByFunc);


        return Response::json($response, 200);
    }

    public function averageOrdersByDayByCook(){

        $response=[];

        $totalOfDays=Order::select([DB::raw('DATE(start)')])->where('state','!=','pending')->where('state','!=','confirmed')->where('state','!=','in preparation')->where('state','!=','prepared')
        ->groupBy(DB::raw('DATE(start)'))
        ->get();

        $totalOfOrdersByCook=Order::select([DB::raw('count(*) AS total'),'responsible_cook_id AS resp'])->where('state','!=','confirmed')->where('state','!=','in preparation')->where('state','!=','prepared')->groupBy('responsible_cook_id')
        ->get();

        array_push($response,count($totalOfDays));
        array_push($response,$totalOfOrdersByCook);


        return Response::json($response, 200);
    }

    public function averageOrdersByDayByWaiter(){
        $response=[];

        $totalOfDays=Order::select([DB::raw('DATE(start)')])->where('state','!=','pending')->where('state','!=','confirmed')->where('state','!=','in preparation')->where('state','!=','prepared')
        ->groupBy(DB::raw('DATE(start)'))
        ->get();

        $totalOfOrdersByWaiter=Order::join('meals', 'orders.meal_id', '=', 'meals.id')->select([DB::raw('count(*) AS total'),'meals.responsible_waiter_id AS resp'])
        ->where('orders.state','!=','pending')->where('orders.state','!=','confirmed')->where('orders.state','!=','in preparation')->where('orders.state','!=','prepared')
        ->groupBy('meals.responsible_waiter_id')
        ->get();

        array_push($response,count($totalOfDays));
        array_push($response,$totalOfOrdersByWaiter);


        return Response::json($response, 200);
    }

    public function statisticsOrdersByDayByCook($id){

        $user=User::findOrFail($id);

        if($user->type=='cook'){

            $orders=$user->orders()->orderBy('start')->get();

            return OrderResource::collection($orders);

        }else{

            $orders = Order::join('meals', 'orders.meal_id', '=', 'meals.id')->where('meals.responsible_waiter_id','=',$id)->orderBy('orders.start')->select(
                'orders.id','orders.state','orders.item_id','orders.meal_id','orders.responsible_cook_id',
                'orders.start','orders.end','orders.created_at','orders.updated_at')->get();

            return OrderResource::collection($orders);
        }
    }

    public function totalOrdersMealsByMonth(){
        $response=[];

        $orders=Order::select([DB::raw('DATE_FORMAT(start,"%m-%Y") AS date'),DB::raw('count(*) AS total')])->where('state','!=','pending')->where('state','!=','confirmed')->where('state','!=','in preparation')->where('state','!=','prepared')
        ->groupBy(DB::raw('DATE_FORMAT(start,"%m-%Y")'))
        ->orderBy(DB::raw('DATE_FORMAT(start,"%m-%Y")'))
        ->get();

        $meals=Meal::select([DB::raw('DATE_FORMAT(start,"%m-%Y") AS date'),DB::raw('count(*) AS total')])->where('state','!=','active')->where('state','!=','terminated')
        ->groupBy(DB::raw('DATE_FORMAT(start,"%m-%Y")'))
        ->orderBy(DB::raw('DATE_FORMAT(start,"%m-%Y")'))
        ->get();


        array_push($response,$orders);
        array_push($response,$meals);

        return Response::json($response, 200);
    }

    public function timeMealByMonth(){
        $response=[];

        $monthsTotals=Meal::select([DB::raw('DATE_FORMAT(start,"%m-%Y") AS date'),DB::raw('count(*) AS total')])->whereNotNull('end')->where('state','!=','active')->where('state','!=','terminated')
        ->groupBy(DB::raw('DATE_FORMAT(start,"%m-%Y")'))
        ->orderBy(DB::raw('DATE_FORMAT(start,"%m-%Y")'))
        ->get();

        $times=Meal::select('end','start')->whereNotNull('end')->where('state','!=','active')->where('state','!=','terminated')->orderBy(DB::raw('DATE_FORMAT(start,"%m-%Y")'))->get();

        array_push($response,$monthsTotals);
        array_push($response,$times);

        return Response::json($response, 200);
    }

    public function ordersMonths(){
        $response=[];

        $m=Order::select(DB::raw('DATE_FORMAT(start,"%m-%Y") AS date'))->where('state','!=','pending')->where('state','!=','confirmed')->where('state','!=','in preparation')->where('state','!=','prepared')
        ->groupBy(DB::raw('DATE_FORMAT(start,"%m-%Y")'))
        ->orderBy(DB::raw('DATE_FORMAT(start,"%m-%Y")'))
        ->get();

        array_push($response,$m);

        return Response::json($response, 200);
    }

    public function timeOrderItemsByMonth($month){

        $response=[];

        $time=Order::select('start','end')->where('state','!=','pending')->where('state','!=','confirmed')->where('state','!=','in preparation')->where('state','!=','prepared')
        ->whereNotNull('end')
        ->where(DB::raw('DATE_FORMAT(start,"%m-%Y")'),'=',$month)
        ->orderBy('item_id')
        ->get();

        $items=Order::select(['item_id',DB::raw('count(*) AS total')])->where('state','!=','pending')->where('state','!=','confirmed')->where('state','!=','in preparation')->where('state','!=','prepared')
        ->whereNotNull('end')
        ->where(DB::raw('DATE_FORMAT(start,"%m-%Y")'),'=',$month)
        ->groupBy('item_id')
        ->orderBy('item_id')
        ->get();

        array_push($response,$time);
        array_push($response,$items);

        return Response::json($response, 200);
    }
}
