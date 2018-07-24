<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ApiException;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Client;

class AuthController extends Controller
{
    public function login (Request $request) {
		validator($request->all(), [
			'username' => 'required',
			'password' => 'required'
		])->validate();

		$this->validateClientExistence($request);

		/** @var User $user */
		$user = User::query()->where('email', $request->input('username'))->first();

		if ($user && Hash::check($request->input('password'), $user->getAuthPassword())) {

			$internalParams = [
				'grant_type'    => 'password',
				'client_id'     => $request->input('client_id'),
				'client_secret' => $request->input('client_secret'),
				'username'      => $request->input('email'),
				'password'      => $request->input('password'),
				'scope'         => '*',
			];

			$proxy = Request::create(
				'oauth/token',
				'POST',
				$internalParams
			);

			/** @var \Illuminate\Http\Response $internalResp */
			$internalResp =  Route::dispatch($proxy);
			$respData = json_decode($internalResp->getContent(), true);

			if (! isset($respData['access_token'])) {
				throw ApiException::runtimeException($respData['message']);
			}

			return response()->json([
				'status' => 'success',
				'data' => [
					'user' => $user,
					'token' => $respData
				]
			]);
		}

		throw ApiException::runtimeException('These credentials do not match our records');
	}

    public function register (Request $request) {
		validator($request->all(), [
			'name' => 'required|string|max:255',
			'email' => 'required|string|email|max:255|unique:users',
			'password' => 'required|string|min:6|confirmed',
		])->validate();

		$this->validateClientExistence($request);

		/** @var User $user */
		$user = User::create([
			'name' => $request->input('name'),
			'email' => $request->input('email'),
			'password' => bcrypt($request->input('password')),
		]);

		$internalParams = [
			'grant_type'    => 'password',
			'client_id'     => $request->input('client_id'),
			'client_secret' => $request->input('client_secret'),
			'username'      => $request->input('email'),
			'password'      => $request->input('password'),
			'scope'         => '*',
		];

		$request->replace($internalParams);

		$proxy = Request::create(
			'oauth/token',
			'POST',
			$internalParams
		);

		/** @var \Illuminate\Http\Response $internalResp */
		$internalResp =  Route::dispatch($proxy);
		$respData = json_decode($internalResp->getContent(), true);

		if (! isset($respData['access_token'])) {
			throw ApiException::runtimeException($respData['message']);
		}

		return response()->json([
			'status' => 'success',
			'data' => [
				'user' => $user,
				'token' => $respData
			]
		]);
	}

	private function validateClientExistence (Request $request) {
		$clientExistence = Client::query()
			->where('id', $request->input('client_id'))
			->where('secret', $request->input('client_secret'))
			->where('revoked', false)
			->where('password_client', 1)->exists();

		if ($clientExistence === false) {
			throw ApiException::invalidRequestException();
		}
	}
}
