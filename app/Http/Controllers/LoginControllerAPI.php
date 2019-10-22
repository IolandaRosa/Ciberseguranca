<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Response;
use App\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
use App\Http\Resources\User as UserResource;
use DateTime;
use Illuminate\Support\Facades\Log;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

// define('YOUR_SERVER_URL', 'https://projetociberseguranca.azurewebsites.net');
define('YOUR_SERVER_URL', 'http://cyb.test');
// Check "oauth_clients" table for next 2 values:
define('CLIENT_ID', '2');
define('CLIENT_SECRET','HZkaxy1HV52pKeDqfxFKPEtfrv2AY84O48ksFXzr');

class LoginControllerAPI extends Controller
{

	public function unauthorizedAccess(Request $request){

		if($request->logFile != null) {
            $file = $request->file('logFile');
            // Manually specify a file name...
			Storage::putFileAs('public/logs',$file, 'file.txt');

            //$path = basename($file->store('logs', 'public'));
            
            return response()->json(['msg'=>'File stored with sucess'], 200);
        }
        else{
        	response()->json(['msg'=>'No file'], 400);
        }

	}

	public function login(Request $request)
	{
		$http = new \GuzzleHttp\Client;

		$user = User::where('email', '=', $request->input('email'))->get()->first();

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
		$errorCode = $response->getStatusCode();
		if ($errorCode=='200') {

			$this->resetUserFailedAttempts($user);

			return json_decode((string) $response->getBody(), true);
		} else {

			$this->incrementCountForDatabase($request, $user);

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

			$this->resetUserFailedAttempts($user->first());

			return json_decode((string) $response->getBody(), true);
		} else {

			$this->incrementCountForDatabase($request, $user->first());

			return response()->json(['msg'=>'User credentials are invalid'], $errorCode);
		}
	}

	//Logger help functions
	public function resetUserFailedAttempts($user){
		$user->failed_attempts=0;
		$user->save();
        new UserResource($user);
	}

	public function incrementCountForDatabase(Request $request, $user){
		//failed_attempts','last_failed_attempt
		//Se timestamp for null
		if($user->last_failed_attempt==null){
			//Coloca primeira tentativa falhada
			$user->last_failed_attempt = new DateTime();
			$user->failed_attempts+=1;
		}
		else{
			//Vai ver a diferença do tempo 
			$time = $this->timestampdiff($user->last_failed_attempt);
			//se foré menor 10
			if((int)$time<10){

				//Incrementa tentativas
				$user->failed_attempts+=1;

				//Ve se ja atingiu as 3 tentativas
				if($user->failed_attempts>=3){
					//vai colocar tentativas a 0
					$user->failed_attempts=0;

					//gera syslog
					$message = $this->generateLogMessage($request);

					//first parameter passed to Monolog\Logger sets the logging channel name
					$sysLog = new Logger('my-channel');

					$handler = new StreamHandler(storage_path('logs/syslogs/auth.log'));
					$handler->setFormatter($this->getLogFormatter());

					$sysLog->pushHandler($handler);

					$sysLog->warning($message);
					
				}
				else{
					//atualiza ultimo timestamp
					$user->last_failed_attempt = new DateTime();
					/*
					$user->request_log=0;
					*/
				}
			}
			else{
				//Zera tudo
				$user->failed_attempts==1;
			}
		}

		$user->save();
        new UserResource($user);
		
	}

	protected function getLogFormatter()
    {
        // the default output format is "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"

        $format = str_replace(
            '[%datetime%] %channel%.%level_name%:',
            '',LineFormatter::SIMPLE_FORMAT);

        return new LineFormatter($format, null, true, true);
    }

	public function generateLogMessage(Request $request){

		//<36>1 1985-04-12T19:20:50.52-04:00 http://project.dad RestaurantServer - ID01 - BOM'Utilizador com username e email falhou login por 5 vezes em menos de 10 minutos em RestaurantServerApp'

		$timestamp=date('Y-m-d\TH:i:sP', microtime(true));

        $hostname=request()->headers->get('host');

        //Structured data
        $origin=request()->headers->get('origin');
        $ip=request()->server('REMOTE_ADDR');

        $structuredData='[restaurantAppSDID@32473 origin="'.$origin.' '.'"ip="'.$ip.'"]';

		return "<36>1 ".$timestamp." ".$hostname." RestaurantServer - ID01 ".$structuredData." BOM'Utilizador falhou mais 3 tentativas num intervalo menor que 10 minutos on App/Http/Controllers/LoginControllerAPI'";
	}

	public function timestampdiff($qw)
	{
		$dateTime1 = strtotime($qw); 
	    $datetime2 = microtime(true);

	    return ($datetime2-$dateTime1)/1000/60;
	}

	public function logout()
	{
		\Auth::guard('api')->user()->token()->revoke();
		\Auth::guard('api')->user()->token()->delete();
		return response()->json(['msg'=>'Token revoked'], 200);
	}
}