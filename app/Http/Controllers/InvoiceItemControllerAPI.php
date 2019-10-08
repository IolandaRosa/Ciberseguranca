<?php

namespace App\Http\Controllers;

use App\InvoiceItem;
use App\Meal;
use App\Http\Resources\InvoiceItem as InvoiceItemResource;

class InvoiceItemControllerAPI extends Controller
{

    public function invoicesItems($id) {
        $items = InvoiceItem::where('invoice_id', '=', $id)->join('items', 'items.id','=', 'invoice_items.item_id')->get();

        return InvoiceItemResource::collection($items);
    }

    public function createInvoiceItems($mealId, $invoiceId) {
        $meal = Meal::findOrFail($mealId);

        $orders = $meal->orders->where('state', '=', 'delivered');

        $itemsIds = [];
        foreach($orders as $o) {
            if(!in_array($o->item_id, $itemsIds)) {
                $i = new InvoiceItem();
                $count = 0;
                foreach ($orders as $o1) {
                    if ($o->item_id == $o1->item_id) {
                        $count++;
                    }
                }

                $i->invoice_id = $invoiceId;
                $i->item_id = $o->item_id;
                $i->quantity = $count;
                $i->unit_price = $o->item->price;
                $i->sub_total_price = $count * $o->item->price;

                $i->save();
                $itemsIds[] = $i->item_id;
            }

        }
        $items = InvoiceItem::where('invoice_id', '=', $invoiceId)->get();

        return InvoiceItemResource::collection($items);
    }

}
