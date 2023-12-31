<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Blockchain;
use Illuminate\Http\Request;

class BlockchainController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {   
        try {
            $blockchains = Blockchain::get();
            return response()->json(['message'=> 'Blockchains fetched successfully', 'blockchains'=>$blockchains]);    
        }
        catch (\Exception $e) {
            report($e);
            return response()->json(['There was an error while fetching blockchains'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
