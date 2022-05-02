<?php

namespace App\Http\Controllers\Api\v1\Auth;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use function response;

class AuthController extends ApiController
{

    public function get_user() {

        return $this->successResponse([
            'user' => Auth::user(),
        ]);
    }

    public function login(LoginRequest $request) {


        if(!Auth::attempt($request->validated())){

            return response()->json([
                'message' => 'Credentials invalid'
            ], 401);
        }

        $token = Auth::user()->createToken('MyTokenAppVivero')->accessToken;

        return response()->json([
            'user' => Auth::user(),
            'token' => $token
        ], 200 );

    }

    public function register(RegisterRequest $request){

        $data = $request->validated();

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        $token = $user->createToken('MyTokenAppVivero')->accessToken;

        return response()->json([
            'message' => 'ok',
            'token' => $token,
            'user' => $user
        ], 200 );

    }

    public function logout(){


    }

}
