<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TokenController extends Controller
{
    public function me(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();
        return response()->json([
            'user'      => $request->user()->only('id', 'name', 'email'),
            'token_name' => $token->name,
            'abilities'  => $token->abilities,
        ]);
    }
}
