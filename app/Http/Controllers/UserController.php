<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Throwable;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::get();
        return response()->json(['message'=> 'Users fetched successfully', 'users' => $users], 200);
    }

   /**
     * Store a newly created resource in storage.
     */
    public function verify(Request $request, User $user)
    {
        try {
            DB::beginTransaction();

            $user->update([
                'email_verified_at' => Carbon::now()
            ]);

            DB::commit();
            return response()->json(['message'=> 'User verified successfully', 'user' => $user], 200); 
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json(['message'=> 'There was an error while verifying the user'], 500); 
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, user $user)
    {
        try {
            DB::beginTransaction();

            $user->delete();

            DB::commit();
            return response()->json(['message'=> 'Users deleted successfully'], 200); 
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json(['message'=> 'There was an error while deleting the user'], 500); 
        }

    }
}
