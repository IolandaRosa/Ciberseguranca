<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Response;
use App\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

define('YOUR_SERVER_URL', 'https://projetociberseguranca.azurewebsites.net');
// Check "oauth_clients" table for next 2 values:
define('CLIENT_ID', '2');
define('CLIENT_SECRET','HriWHKJ2UnraerOaV7sF80EyRdSSN8bMXn83Mh2R');

class LoginControllerAPI extends Controller
{
	public function unauthorizedAccess(Request $request){

		if($request->logFile != null) {
            $file = $request->file('logFile');
            $path = basename($file->store('logs', 'public'));
            return response()->json(['msg'=>'File stored with sucess'], 200);
        }
        else{
        	response()->json(['msg'=>'No file'], 400);
        }

	}

	public function login(Request $request)
	{
		$http = new \GuzzleHttp\Client;
		$response = $http->post(YOUR_SERVER_URL.'/oauth/token', [
			'form_params' => [
				'grant_type' => 'password',
				'client_id' => CLIENT_ID,
				'client_secret' => CLIENT_SECRET,
				'username' => $request->email,
				'password' => $request->password,
				'scope' => ''
			],
			'exceptions' => false,
		]);
		$errorCode= $response->getStatusCode();
		if ($errorCode=='200') {
			return json_decode((string) $response->getBody(), true);
		} else {
			return response()->json(['msg'=>'User credentials are invalid'], $errorCode);
		}
	}

	public function loginUsername(Request $request){
		$http = new \GuzzleHttp\Client;
		
		$user = User::where('username', '=', $request->input('username'))->get();

		$response = $http->post(YOUR_SERVER_URL.'/oauth/token', [
			'form_params' => [
				'grant_type' => 'password',
				'client_id' => CLIENT_ID,
				'client_secret' => CLIENT_SECRET,
				'username' => $user[0]->email,
				'password' => $request->password,
				'scope' => ''
			],
			'exceptions' => false,
		]);
		$errorCode= $response->getStatusCode();
		if ($errorCode=='200') {
			return json_decode((string) $response->getBody(), true);
		} else {
			return response()->json(['msg'=>'User credentials are invalid'], $errorCode);
		}
	}

	public function logout()
	{
		\Auth::guard('api')->user()->token()->revoke();
		\Auth::guard('api')->user()->token()->delete();
		return response()->json(['msg'=>'Token revoked'], 200);
	}
}