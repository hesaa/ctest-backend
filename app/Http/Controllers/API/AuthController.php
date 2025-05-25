<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseController
{
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return $this->sendResponse($validator->errors(), 422);
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $token = Auth::login($user);

        $login = [
            'token' => $token,
            'expired' => Auth::factory()->getTTL() * 60,
            'name'  => $user->name,
            'email' => $user->email
        ];

        return $this->sendResponse($login, 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendResponse($validator->errors(), 422);
        }

        $token = Auth::attempt(['email' => $request->email, 'password' => $request->password]);

        if (!$token) {
            return $this->sendResponse([], 400, 'The email or password was incorrect.');
        }

        $user = Auth::user();

        $login = [
            'token' => $token,
            'expired' => Auth::factory()->getTTL() * 60,
            'name'  => $user->name,
            'email' => $user->email
        ];


        return $this->sendResponse($login);
    }

    public function refresh(): JsonResponse
    {
        $data = [
            'token' => Auth::refresh(),
            'expired' => Auth::factory()->getTTL() * 60,
            'name'  => Auth::user()->name,
            'email' => Auth::user()->email
        ];

        return $this->sendResponse($data);
    }

    public function logout(): JsonResponse
    {
        Auth::logout();
        return $this->sendResponse([], 200, 'Logout succesfully.');
    }

    public function me(): JsonResponse
    {
        return $this->sendResponse(Auth::user());
    }
}
