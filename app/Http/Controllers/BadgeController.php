<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\Blockchain;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Utilities\Pinata;
use User;

class BadgeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Blockchain $blockchain)
    {
        $badges = Badge::where('blockchain_id', $blockchain->id)->paginate(12);
        return response()->json(['message' => 'Badges fetched successfully', 'badges' => $badges], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string|max:64',
                'description' => 'required|string|max:255',
                'image' => 'image|mimes:jpg,bmp,png',
                'properties' => 'string',
                'blockchain_id' => 'required|exists:blockchains,id'
            ]
        );

        if ($validator->fails()) {
            return response()->json($validator->messages(), 500);
        }

        $path = Storage::disk('public')->put('images/', $request->image);
        $fullPath = Storage::disk('public')->path($path);
    
        $pinata = new Pinata(env('PINATA_API_KEY'), env('PINATA_SECRET_API_KEY'));
        $responseFile = $pinata->pinFileToIPFS($fullPath);

        $metaData = [
            "name" => $request->name,
            "description" => $request->description,
            "image" => "https://gateway.pinata.cloud/ipfs/" . $responseFile['IpfsHash'],
            "properties" => json_decode($request->properties)
        ];


        $fileName = substr($path, strrpos($path, '/') + 1, strrpos($path, '.') - strpos($path, '/') + 1 - 3);
        Storage::disk('public')->put('metadata/' . $fileName . '.json', json_encode($metaData));

        $responseJson = $pinata->pinJSONToIPFS($metaData);

        return response()->json(['message' => 'Badge created successfully', 'responseJson' => $responseJson, 'responseFile' => $responseFile], 200);     
    }

    public function updateSentToAddress(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'sent_to_address' => 'string|required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 500);
        }

        try {
            DB::beginTransaction();
            Badge::find($id)->update(
                ['sent_to_address' => $request->sent_to_address]
            );
            DB::commit();
            return response()->json(['message' => 'Sent to address updated successfully'], 200);
        } catch (\Exception $e) {
            DB::rollback();
            report($e);
            return response()->json(['There was an error while updating sent to address'], 500);
        }
    }

    public function refresh(Request $request)
    {
        $user = Auth()->user();
        $validator = Validator::make($request->all(), [
            'assets' => 'array',
            'original_address' => 'string',
            'blockchain_id' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 500);
        }

        try {
            DB::beginTransaction();

            if ($request->assets) {
                foreach ($request->assets as $asset) {
                    $badge = Badge::firstOrNew(['token_id' => (int)$asset['tokenId'], 'blockchain_id' => $request->blockchain_id], [
                        'blockchain_id' => $request->blockchain_id,
                        'name' => isset($asset['name']) ? $asset['name'] : '',
                        'description' => $asset['description'] ? $asset['description'] : '',
                        'current_amount' => $asset['current_amount'],
                        'created_amount' => $asset['created_amount'],
                        'img_path' => isset($asset['image_url']) ? $asset['image_url'] : '',
                        'token_id' => $asset['tokenId'],
                        'original_address' => $request->original_address,
                        'properties' => isset($asset['properties']) ? $asset['properties'] : null,
                        'user_id' => $user->id
                    ]);

                    $badge->save();
                }

            }
            $badges = Badge::whereBlockchainId($request->blockchain_id)->paginate(12);
            DB::commit();
            return response()->json(['message' => 'Badges refreshed successfully', 'badges' => $badges], 200);
        } catch (\Exception $e) {
            DB::rollback();
            report($e);
            return response()->json(['There was an error while refreshing badges'], 500);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }
}