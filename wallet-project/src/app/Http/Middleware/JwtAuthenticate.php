<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\JwtService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Amjad Khaliliah
 */
class JwtAuthenticate
{
    public function __construct(
        private JwtService $jwtService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('Authorization');

        if (! $header || ! str_starts_with($header, 'Bearer ')) {
            return response()->json(['message' => 'Unauthorized. Missing bearer token.'], 401);
        }

        $token = substr($header, 7);
        $payload = $this->jwtService->validateToken($token);

        if (! $payload || ! isset($payload['sub'])) {
            return response()->json(['message' => 'Unauthorized. Invalid token.'], 401);
        }

        $user = User::find($payload['sub']);

        if (! $user) {
            return response()->json(['message' => 'Unauthorized. User not found.'], 401);
        }

        auth()->setUser($user);

        return $next($request);
    }
}
