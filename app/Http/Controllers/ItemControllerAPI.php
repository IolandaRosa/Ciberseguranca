<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\Item as ItemResource;
use App\Item;
use Illuminate\Support\Facades\Auth;
use Response;
use Illuminate\Validation\Rule;

class ItemControllerAPI extends Controller
{
	public function index(Request $request)
	{
		return ItemResource::collection(Item::all());
	}

	public function show($id){
		$item=Item::findOrFail($id);

		return new ItemResource($item);
	}

	public function store(Request $request)
	{
		if(Auth::guard('api')->user()->type != 'manager'){
			return Response::json([
				'unauthorized' => 'Access forbiden! Only managers are allowed'
			], 401);
		}

		$validateData=$request->validate([
			'name' => 'required|min:3|unique:items',
			'description' => 'required|min:6',
			'price' => 'required',
			'type' => Rule::in(['dish', 'drink']),
			'photo_url' => 'required|image|mimes:jpg,jpeg,png'
		]);

		if (!is_numeric($request->input('price'))|| $request->input('price')<=0 || $request->input('price')>1000.00) {
			return Response::json([
				'error' => 'The price must be a numeric value between 0.01 and 1000.00'
			], 422);
		}


		$item = new Item();
		$item->fill($validateData);

		$image = $request->file('photo_url');
		$path = basename($image->store('items', 'public'));
		$item->photo_url = basename($path);

		$item->save();

		return new ItemResource($item);
	}

	public function update(Request $request, $id)
	{
		if(Auth::guard('api')->user()->type != 'manager'){
			return Response::json([
				'unauthorized' => 'Access forbiden! Only managers are allowed'
			], 401);
		}

		$item=Item::findOrFail($id);

		$validateData=$request->validate([
			'name' => 'required|min:3|'.Rule::unique('items')->ignore($item),
			'description' => 'required|min:6',
			'price' => 'required',
			'type' => Rule::in(['dish', 'drink']),
			'photo_url' => 'nullable|image|mimes:jpg,jpeg,png'
		]);

		if (!is_numeric($request->input('price'))|| $request->input('price')<=0 || $request->input('price')>1000.00) {
			return Response::json([
				'error' => 'The price must be a numeric value between 0.01 and 1000.00'
			], 422);
		}

		$item->name=$request->input('name');
		$item->description=$request->input('description');
		$item->price=$request->input('price');
		$item->type=$request->input('type');

		if($request->photo_url != null) {
			$image = $request->file('photo_url');
			$path = basename($image->store('items', 'public'));
			$item->photo_url = basename($path);
		}

		$item->update();

		return new ItemResource($item);
	}

	public function destroy($id)
	{
		$item = Item::findOrFail($id);

		$orders = $item->orders;

		$invoice_items = $item->invoice_items;

		if($orders->isEmpty() && $invoice_items->isEmpty()){
			$item->forceDelete();
			return new ItemResource($item);
		}

		$item->delete();

		return new ItemResource($item);
	}
}
