<?php

namespace App\Http\Controllers\Api\v1\Auth;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Person;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use function response;

class AuthController extends ApiController
{

    public function get_user(): \Illuminate\Http\JsonResponse
    {
        return $this->successResponse([
            'user' => Auth::user(),
        ]);
    }

    public function login(LoginRequest $request): \Illuminate\Http\JsonResponse
    {

        $data = $request->validated();

        if(!$person = Person::where('email', $data['email'])->with('user')->first()){
            return $this->errorResponse([
                'msg' => 'CREDENTIALS_INVALID',
            ],401);
        }

        $user = $person->user;

        if(!$this->login_by_user($user, $data['password'])){
            return $this->errorResponse([
                'msg' => 'CREDENTIALS_INVALID',
            ],401);
        }

        $token = Auth::user()->createToken('MyTokenAppVivero')->accessToken;

        return $this->successResponse([
            'user' => Auth::user(),
            'token' => $token
        ]);

    }

    public function register(RegisterRequest $request): \Illuminate\Http\JsonResponse
    {

        try {
            $data = $request->validated();

            $person = new Person();

            $person->email = $data['email'];

            $person->save();

            $user = new User();

            $user->password = Hash::make($data['password']);
            $user->user_name = $data['name'];
            $user->role_id = $data['role_id'];

            $person->user()->save($user);

            $token = $user->createToken('MyTokenAppVivero')->accessToken;

            return $this->successResponse([
                'message' => 'ok',
                'token' => $token,
                'user' => $user
            ]);

        }catch (\Exception $e){
            return $this->errorResponse($e->getMessage());
        }


    }

    public function login_by_user(User $user, $password): bool
    {

        if (Hash::check($password, $user->password)) {
            Auth::loginUsingId($user->id);
            return true;
        }

        return false;
    }

    public function logout()
    {


    }

}
