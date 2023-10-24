<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class canDeleteUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $loggedInUser = Auth::user();
        $user = User::find($request->route('user'))->first();
        error_log(json_encode($user));
        if($loggedInUser->id !== $user->id && $user->email_verified_at !== null) return response()->json(['message' => 'You are not allowed to delete the selected user.'], 403);
        return $next($request);    
    }
}
