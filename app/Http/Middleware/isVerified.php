<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
class isVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = User::whereEmail($request->input('email'))->first();
        if($user && $user->email_verified_at === null) return response()->json(['message' => 'Email has not been verified. Please wait while an admin varifies your email.'], 403);
        return $next($request);
    }
}
