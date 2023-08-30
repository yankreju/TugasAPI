<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed'
        ]);

        if ($validated->fails()) {
            return response()->json($validated->errors(), 422);
        }

        $validated['password'] = Hash::make($request->password);

        $user = User::create($validated);

        if ($user) {
            return response()->json([
                'success' => true,
                'user' => $user
            ], 200);
        }

        return response()->json([
            'success' => false
        ], 409);
    }
}
