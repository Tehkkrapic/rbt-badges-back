<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\Blockchain;
use App\Models\User;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Client;
use Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use stdClass;
use App\Utilities\Pinata;

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
        $user = $request->user();

        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string|max:64',
                'description' => 'required|string|max:255',
                'created_amount' => 'required|numeric|gt:0',
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

        return response()->json(['message' => 'Badge created successfully', 'metaDataResult' => $responseJson, 'responseFile' => $responseFile], 200);


        // try {
        //     DB::beginTransaction();

        //     $badge = new Badge();
        //     $badge->fill($request->all());
        //     $badge->current_amount = $request->created_amount;
        //     $badge->img_path=$path;
        //     $badge->user()->associate(User::get()->first());

        //     $badge->save();
        //     DB::commit();


        //     return response()->json(['message' => 'Badge created successfully', 'badge' => $badge], 200);
        // } catch (\Exception $e) {            
        //     DB::rollback();
        //     report($e);
        //     return response()->json(['There was an error while creating a new badge'], 500);
        // }
     
    }

    public function mint()
    {
        $client = new Client([
            'base_uri' => 'https://cardano-testnet.tangocrypto.com/',
            RequestOptions::HEADERS => [
                'x-api-key' => '5886e6a5ca624441b8f2f5289f753116',
                'Content-Type' => 'application/json'
            ],
        ]);

        $data = [
            'tokens' => array(
                [
                    "name" => "Tango 03",
                    "asset_name" => "Tango03",
                    "description" => "If you get all tangled up, just tango on.",
                    "image" => "",
                ]
            )
        ];

        //$response = $client->post('/fbc8dfbe8d14435bba35c906ab9d793b/v1/nft/collections/01gyqcqn4t4pqjn7jwd9z44qa1/tokens',  ['body'=>json_encode($data)]);


        $client2 = new Client([
            'base_uri' => 'https://cardano-testnet.tangocrypto.com/',
            RequestOptions::HEADERS => [
                'x-api-key' => '5886e6a5ca624441b8f2f5289f753116',
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ],
        ]);

        error_log("Zas");
        $assetsResp = $client2->get('/fbc8dfbe8d14435bba35c906ab9d793b/v1/nft/collections/01gyqcqn4t4pqjn7jwd9z44qa1/tokens');
        //        error_log(2);
        error_log($assetsResp->getBody()->getContents());

        $data2 = [
            'type' => 'fixed',
            "price" => 6000000,
            "reservation_time" => 500,
            'tokens' => array('01gywrtx31ztt4seqsfb21dd8w')
        ];

        error_log(json_encode($data2));
        $response = $client2->post('/fbc8dfbe8d14435bba35c906ab9d793b/v1/nft/collections/01gyqcqn4t4pqjn7jwd9z44qa1/sales', ['body' => json_encode($data2)]);
        error_log($response->getBody()->getContents());
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
                error_log(json_encode($request->assets));
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
                        'properties' => isset($asset['properties']) ? $asset['properties'] : null
                    ]);
                    $badge->user_id = 1;

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