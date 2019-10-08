<?php

namespace App\Http\Controllers;


use App\Meal;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Http\Resources\Invoice as InvoiceResource;
use App\Invoice;
use App\InvoiceItem;
Use PDF;
use Response;

class InvoiceControllerAPI extends Controller
{
    public function pendingInvoicesWithWaiter() {
        $pendingInvoices = Invoice::join('meals', 'invoices.meal_id', '=', 'meals.id')
        ->join('users', 'users.id', '=', 'meals.responsible_waiter_id')->where('invoices.state', '=', 'pending')
        ->get(['invoices.*', 'meals.responsible_waiter_id',  'meals.table_number','users.name as waiterName']);
        return InvoiceResource::collection($pendingInvoices);
    }

    public function paidInvoices(Request $request) {
        $query=Invoice::where('state', '=', 'paid');

        $array = json_decode($request->serverInfo, true);

        $perPage = $array['perPage'];

        $sort = $array['sort'];

        if(array_key_exists('field',$sort) && $sort['field'] != '')
        {

            $query = $query->orderBy($sort['field'], $sort['type']);

        }

        $total = $query->select(['invoices.*'])->count();
        $invoices = $query->select(['invoices.*'])->paginate($perPage);
        $output  = array($invoices, $total);

        return  $output;
    }

    public function createInvoice($mealId) {
        $meal = Meal::findOrFail($mealId);

        $invoice = new Invoice();
        $invoice->state = 'pending';
        $invoice->meal_id = $meal->id;
        $invoice->date = date('Y-m-d H:i:s');
        $invoice->total_price = $meal->total_price_preview;
        $invoice->save();

        return new InvoiceResource($invoice);
    }

    public function payInvoice(Request $request, $id) {
        $invoice = Invoice::findOrFail($id);

        $request->validate([
            'nif' => 'required|digits:9|numeric',
            'name' => 'required|min:3|regex:/^[A-Za-záàâãéèêíóôõúçÁÀÂÃÉÈÍÓÔÕÚÇ ]+$/',
        ]);

        $invoice->state = "paid";
        $invoice->nif = $request->input('nif');
        $invoice->name = $request->input('name');
        $invoice->save();

        $meal = Meal::findOrFail($invoice->meal_id);
        $meal->state = "paid";
        $meal->save();
        $this->createInvoicePdf($invoice->id);

        return new InvoiceResource($invoice);
    }

    public function createInvoicePdf($id) {
        $i= Invoice::join('meals', 'invoices.meal_id', '=', 'meals.id')
            ->join('users', 'users.id', '=', 'meals.responsible_waiter_id')->where('invoices.id', '=', $id)
            ->get(['invoices.*', 'meals.responsible_waiter_id', 'users.name as waiterName']);
        $invoice = $i[0];
        $items = InvoiceItem::where('invoice_id', '=', $invoice->id)->join('items', 'items.id','=', 'invoice_items.item_id')->get();

        $pdf = PDF::loadView('pdf.invoicePDF', compact(['invoice', 'items']));

        Storage::put("public/pdfInvoices/invoice_". $id .".pdf", $pdf->output());
    }

    public function updateState(Request $request, $id){

        $invoice=Invoice::findOrFail($id);

        if(($invoice->state == "paid"  || $invoice->state == "not paid"))
        {

            return Response::json( ['error' => 'Invalid state to update'], 422);
        }

        $invoice->state=$request->input('state');
        if($request->input('state') == "paid" || $request->input('state') == "not paid" )
        {
            $invoice->date = date('Y-m-d H:i:s');
        }

        $invoice->save();

        return new InvoiceResource($invoice);
    }

    public function downloadInvoicePdf( $id) {

        return response()->download(storage_path("app/public/pdfInvoices/invoice_" . $id . ".pdf"));
    }

    public function index( ) {
        $invoices = Invoice::join('meals', 'invoices.meal_id', '=', 'meals.id')
        ->join('users', 'users.id', '=', 'meals.responsible_waiter_id')
        ->get(['invoices.*', 'meals.responsible_waiter_id',  'meals.table_number','users.name as waiterName']);

        return InvoiceResource::collection($invoices);
    }

    public function invoicesTest(Request $request) {

        $query = Invoice::join('meals', 'invoices.meal_id', '=', 'meals.id')
        ->join('users', 'users.id', '=', 'meals.responsible_waiter_id');

        $array = json_decode($request->serverInfo, true);

        $perPage = $array['perPage'];
        $arr = $array['columnFilters'];

        if(array_key_exists('state',$arr) && $arr['state'] != '')
        {
            $query = $query->where('invoices.state','=',$arr['state']);
        }
        if(array_key_exists('responsible_waiter_id',$arr) && $arr['responsible_waiter_id'] != '')
        {
            $query = $query->where('meals.responsible_waiter_id','=',$arr['responsible_waiter_id']);
        }
        if(array_key_exists('date',$arr) && $arr['date'] != '')
        {
            //list($day, $month, $year) = explode('/', $arr['date']);
            //$query = $query->where('invoices.date','=',$year.'-'.$month.'-'.$day);

            $fulldate=$arr['date'];
            $fulldate=str_replace("/", "-", $fulldate);
            $fulldate=str_replace(" ", "-", $fulldate);


            //12-11-2018
            $v1=explode('-', $fulldate.'-', -1);

            $v1[0]=str_replace("-", "", $v1[0]);

            if(count($v1)==1){
                $query = $query->where('invoices.date','Like','%'.$v1[0].'%');
            }
            else if(count($v1)==2){
                $query = $query->where('invoices.date','Like','%'.$v1[1].'-'.$v1[0].'%');
            }
            else if(count($v1)==3){
                $query = $query->where('invoices.date','Like','%'.$v1[2].'-'.$v1[1].'-'.$v1[0].'%');
            }

        }

        $sort = $array['sort'];

        if(array_key_exists('field',$sort) && $sort['field'] != '')
        {

            $query = $query->orderBy($sort['field'], $sort['type']);

        }

        $total = $query->select(['invoices.*', 'meals.responsible_waiter_id',  'meals.table_number','users.name as waiterName'])->count();
        $invoices = $query->select(['invoices.*', 'meals.responsible_waiter_id',  'meals.table_number','users.name as waiterName'])->paginate($perPage);
        $output  = array($invoices, $total);

        return  $output;

    }
}
