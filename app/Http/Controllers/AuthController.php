<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\APIResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use APIResponse;

    public function login(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('name', $request->name)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {

            return $this->errorResponse([
                'name' => ['The provided credentials are incorrect.'],
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $this->successResponse($user->createToken($request->name)->plainTextToken);
    }
}
