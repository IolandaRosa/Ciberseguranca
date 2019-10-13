<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('items', 'ItemControllerAPI@index');

//Rota acrescentada para smartphone enviar erro de login
Route::post('unauthorizedAccess','LoginControllerAPI@unauthorizedAccess')->name('unauthorizedAccess');


Route::post('login', 'LoginControllerAPI@login')->name('login');
Route::post('loginUsername', 'LoginControllerAPI@loginUsername')->name('loginUsername');
Route::middleware('auth:api')->post('logout', 'LoginControllerAPI@logout');

// US2
Route::middleware(['auth:api','manager'])->post('users/registerWorker', 'UserControllerAPI@registerWorker');
Route::patch('users/confirmRegistration/{id}', 'UserControllerAPI@confirmRegistration');

Route::middleware('auth:api')->get('users/me', 'UserControllerAPI@myProfile');

//US4
Route::middleware('auth:api')->patch('users/password/{id}','UserControllerAPI@changePassword');

//US5
Route::middleware('auth:api')->put('users/update/{id}','UserControllerAPI@update');

//US6
Route::middleware('auth:api')->get('users/dateShift/{id}','UserControllerAPI@currentShiftInformation');
Route::middleware('auth:api')->patch('users/startShift/{id}','UserControllerAPI@startShift');
Route::middleware('auth:api')->patch('/users/endShift/{id}','UserControllerAPI@endShift');

//US9
Route::middleware(['auth:api','isCook'])->get('users/orders/{id}','UserControllerAPI@cookOrdersList');

//US11
Route::middleware(['auth:api','isCook'])->get('unsignedOrders','OrderControllerAPI@obtainUnsignedOrders');
Route::middleware(['auth:api','isCookOrWaiter'])->patch('orders/state/{id}','OrderControllerAPI@updateState');
Route::middleware(['auth:api','isCook'])->patch('orders/cooks/{id}','OrderControllerAPI@updateCook');

//US12
Route::middleware(['auth:api','isWaiter'])->get('meals/nonActiveTables', 'MealControllerAPI@nonActiveTables');
Route::middleware(['auth:api','isWaiter'])->post('meals/createMeal', 'MealControllerAPI@createMeal');

//US13
Route::middleware(['auth:api','isWaiter'])->get('meals/myMeals/{id}', 'MealControllerAPI@myMeals');
Route::middleware(['auth:api','isWaiter'])->post('orders/createOrder', 'OrderControllerAPI@createOrder');
Route::middleware(['auth:api','isWaiter'])->put('meals/totalPrice', 'MealControllerAPI@updateTotalPrice');

//US14
Route::middleware(['auth:api','isWaiter'])->get('user/myOrdersWaiter/{id}', 'UserControllerAPI@myOrdersWaiter');

//US15
Route::middleware(['auth:api','isWaiter'])->delete('orders/{id}', 'OrderControllerAPI@destroy');

//US16
Route::middleware(['auth:api','isCook'])->get('orders/responsibleWaiter/{id}', 'OrderControllerAPI@responsibleWaiter');

//US17
Route::middleware(['auth:api','isWaiter'])->get('user/myPreparedOrdersWaiter/{id}', 'UserControllerAPI@myPreparedOrdersWaiter');

//US19
Route::middleware(['auth:api','isManagerOrWaiter'])->get('orders/ordersOfaMeal/{id}', 'OrderControllerAPI@ordersOfAMeal');

//US20
Route::middleware(['auth:api','isWaiter'])->patch('meals/terminateMeal/{id}', 'MealControllerAPI@terminateMeal');
Route::middleware(['auth:api','isCookOrWaiter'])->get('meals/mealFromOrder/{id}', 'MealControllerAPI@mealFromOrder');

//US21
Route::middleware(['auth:api','isWaiter'])->post('invoices/create/{id}', 'InvoiceControllerAPI@createInvoice');
Route::middleware(['auth:api','isWaiter'])->post('invoiceItems/create/{mealid}/{invoiceId}', 'InvoiceItemControllerAPI@createInvoiceItems');

//US28
Route::middleware(['auth:api','manager'])->get('tables', 'TableControllerAPI@index');
Route::middleware(['auth:api','manager'])->post('tables', 'TableControllerAPI@store');
Route::middleware(['auth:api','manager'])->put('tables/{id}', 'TableControllerAPI@update');
Route::middleware(['auth:api','manager'])->delete('tables/{id}', 'TableControllerAPI@destroy');
Route::middleware(['auth:api','manager'])->post('items', 'ItemControllerAPI@store');
Route::middleware(['auth:api','manager'])->get('items/{id}', 'ItemControllerAPI@show');
Route::middleware(['auth:api','manager'])->put('items/{id}', 'ItemControllerAPI@update');
Route::middleware(['auth:api','manager'])->delete('items/{id}', 'ItemControllerAPI@destroy');

//US22
Route::middleware(['auth:api','isCashierOrManager'])->get('invoices/pending', 'InvoiceControllerAPI@pendingInvoicesWithWaiter');

Route::middleware(['auth:api','isCashier'])->post('invoices/pay/{id}', 'InvoiceControllerAPI@payInvoice');

Route::middleware(['auth:api','isCashierOrManager'])->get('invoices/downloadPdf/{id}', 'InvoiceControllerAPI@downloadInvoicePdf');

Route::middleware(['auth:api','isCashier'])->get('invoices/paid', 'InvoiceControllerAPI@paidInvoices');

Route::middleware(['auth:api','isCashierOrManager'])->get('invoiceItems/items/{id}', 'InvoiceItemControllerAPI@invoicesItems');

//US29
Route::middleware(['auth:api','manager'])->get('users', 'UserControllerAPI@index');

Route::middleware(['auth:api','manager'])->get('user/{id}', 'UserControllerAPI@show');

Route::middleware(['auth:api','manager'])->get('users/blocked', 'UserControllerAPI@blockedUsers');

Route::middleware(['auth:api','manager'])->get('users/unBlocked', 'UserControllerAPI@unblockedUsers');

Route::middleware(['auth:api','manager'])->get('users/deleted', 'UserControllerAPI@deletedUsers');

Route::middleware(['auth:api','manager'])->patch('user/block/{id}', 'UserControllerAPI@blockUser');

Route::middleware(['auth:api','manager'])->patch('user/unBlock/{id}', 'UserControllerAPI@unBlockUser');

Route::post('user/blockedOrNot/{param}', 'UserControllerAPI@userByEmail');

Route::middleware(['auth:api','manager'])->delete('users/{id}', 'UserControllerAPI@destroy');

//US31
Route::middleware(['auth:api','manager'])->get('meals/activeOrTeminatedMeals', 'MealControllerAPI@activeOrTeminatedMeals');

//US35
Route::middleware(['auth:api','manager'])->get('meals', 'MealControllerAPI@index');
Route::middleware(['auth:api','manager'])->get('paidMeals', 'MealControllerAPI@getPaidMeals');
Route::middleware(['auth:api','manager'])->get('notPaidMeals','MealControllerAPI@getNotPaidMeals');

//US36
Route::middleware(['auth:api','manager'])->get('meals/{id}', 'MealControllerAPI@show');
Route::middleware(['auth:api','manager'])->get('meals/items/{id}', 'MealControllerAPI@itemsFromMeal');

//US37
Route::middleware(['auth:api','manager'])->get('invoices', 'InvoiceControllerAPI@index');
Route::middleware(['auth:api','manager'])->patch('invoices/state/{id}','InvoiceControllerAPI@updateState');
Route::middleware(['auth:api','manager'])->patch('meals/notPaid/{id}', 'MealControllerAPI@markMealAsNotPaid');

//US39
Route::middleware(['auth:api','manager'])->get('users/cooks', 'UserControllerAPI@allCooks');
Route::middleware(['auth:api','manager'])->get('users/waiters', 'UserControllerAPI@allWaiters');
Route::middleware(['auth:api','manager'])->get('statistics/meals/', 'StatisticControllerAPI@statisticsMealsByDayByUser');
Route::middleware(['auth:api','manager'])->get('statistics/orders/{id}', 'StatisticControllerAPI@statisticsOrdersByDayByCook');
Route::middleware(['auth:api','manager'])->get('statistics/ordersCooks','StatisticControllerAPI@averageOrdersByDayByCook');
Route::middleware(['auth:api','manager'])->get('statistics/ordersWaiters','StatisticControllerAPI@averageOrdersByDayByWaiter');

//US40
Route::middleware(['auth:api','manager'])->get('statistics/ordersMealsByMonth','StatisticControllerAPI@totalOrdersMealsByMonth');
Route::middleware(['auth:api','manager'])->get('statistics/timeMealsByMonth','StatisticControllerAPI@timeMealByMonth');
Route::middleware(['auth:api','manager'])->get('statistics/ordersMonths','StatisticControllerAPI@ordersMonths');
Route::middleware(['auth:api','manager'])->get('statistics/timeOrdersItemsByMonth/{month}','StatisticControllerAPI@timeOrderItemsByMonth');


//testing
Route::middleware(['auth:api','manager'])->get('invoicesTest', 'InvoiceControllerAPI@invoicesTest');
Route::middleware(['auth:api','manager'])->get('invoices/state/{state}', 'InvoiceControllerAPI@getInvoicesByState');
Route::middleware(['auth:api','manager'])->get('invoices/responsible_waiter_id/{waiterId}', 'InvoiceControllerAPI@getInvoicesByWaiterId');

Route::middleware(['auth:api','isWaiter'])->get('user/myOrdersWaiterSeasoning/{id}', 'UserControllerAPI@getWaiterSeasoning');
Route::middleware(['auth:api','isWaiter'])->get('user/myOrdersWaiterUnseasoning/{id}', 'UserControllerAPI@getWaiterUnSeasoning');

