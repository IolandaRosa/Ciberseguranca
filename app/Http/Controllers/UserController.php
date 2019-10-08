<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\RedirectResponse;
use App\User;

class UserController extends Controller
{
    
	public function confirmRegistration(Request $request) {
	    $id = $request->input('id');
		$user = User::findOrFail($id);

		if ($user->email_verified_at == null) {
			return view("users.confirmRegistration")->with('user', $user);
		}

		return redirect()->route("menu");
	}

}
