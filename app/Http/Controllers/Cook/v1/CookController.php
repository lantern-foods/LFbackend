<?php

namespace App\Http\Controllers\Cook\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CookDocumentRequest;
use App\Http\Requests\CookProfileEditRequest;
use App\Http\Requests\CookRequest;
use App\Models\Cook;
use App\Models\CookDocument;
use App\Traits\Clients;
use App\Traits\Cooks;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;

class CookController extends Controller
{
    use Cooks;
    use Clients;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cooks = Cook::with('shifts')->get();

        if ($cooks->isEmpty()) {
            return response()->json([
                'status' => 'no_data',
                'message' => 'No records',
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Request successful',
            'data' => $cooks,
        ]);


    }

    public function all_cook_meals()
    {
        $client_id = Auth::user()->id;

        $allcook_meals = Cook::with('meals.meal_images')
            // ->where('express_status', 1)
            ->get();

        if (!$allcook_meals->isEmpty()) {
            $data = [
                'status' => 'success',
                'message' => 'Request successful',
                'data' => $allcook_meals,
            ];
        } else {
            $data = [
                'status' => 'no_data',
                'message' => 'No records',
            ];
        }
        return response()->json($data);
    }

    public function all_cook_meals_express()
    {
        {
            $allcook_meals = Cook::with('meals.meal_images')
                // where shift id not null
                // ->where('express_status', 1)
                ->get();

            if (!$allcook_meals->isEmpty()) {
                $data = [
                    'status' => 'success',
                    'message' => 'Request successful',
                    'data' => $allcook_meals,
                ];
            } else {
                $data = [
                    'status' => 'no_data',
                    'message' => 'No records',
                ];
            }
            return response()->json($data);
        }
    }

    public function all_cook_meal(string $id)
    {
        $allcook_meal = Cook::with(['shifts', 'meals.meal_images'])->where('id', $id)->get();

        if (!$allcook_meal->isEmpty()) {
            $data = [
                'status' => 'success',
                'message' => 'Request successful',
                'data' => $allcook_meal,
            ];
        } else {
            $data = [
                'status' => 'no_data',
                'message' => 'No records',
            ];
        }
        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CookRequest $request)
    {
        $validated = $request->validated();

        $clientId = $validated['client_id'];
        $kitchenName = $validated['kitchen_name'];

        if (Cook::where('client_id', $clientId)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sorry, you already exist as a cook',
            ]);
        }
        $phone_number = $validated['mpesa_number'];

        list($msisdn, $network) = $this->get_msisdn_network($phone_number);

        $cook = Cook::create([
            'client_id' => $clientId,
            'kitchen_name' => $kitchenName,
            'id_number' => $validated['id_number'],
            'mpesa_number' => $phone_number,
            'alt_phone_number' => $validated['alt_phone_number'],
            'health_number' => $validated['health_number'],
            'health_expiry_date' => $validated['health_expiry_date'],
            'physical_address' => $validated['physical_address'],
            'shrt_desc' => $validated['shrt_desc'],
            'google_map_pin' => $validated['google_map_pin'],
            'status' => 2,
        ]);

        if (!$cook) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create account. Please try again',
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Account created successfully. Proceed to upload required documents',
            'clientID' => $cook->id,
        ]);
    }

    /**
     * Upload images/Documents.
     */
    public function uploadDocuments(CookDocumentRequest $request)
    {
        $validated = $request->validated();

        $cookId = $validated['cook_id'];
        $idFront = $request->file('id_front');
        $idBack = $request->file('id_back');
        $healthCert = $request->file('health_cert');
        $profilePic = $request->file('profile_pic');

        // Ensure all files are provided
        if (!$idFront || !$idBack || !$healthCert || !$profilePic) {
            return response()->json([
                'status' => 'error',
                'message' => 'All required files must be provided',
            ]);
        }

        try {
            $idFrontPath = $this->uploadToS3($idFront, 'id_front', $cookId);
            $idBackPath = $this->uploadToS3($idBack, 'id_back', $cookId);
            $healthCertPath = $this->uploadToS3($healthCert, 'health_cert', $cookId);
            $profilePicPath = $this->uploadToS3($profilePic, 'profile_pic', $cookId);

            // Save paths to database
            CookDocument::create([
                'cook_id' => $cookId,
                'id_front' => $idFrontPath,
                'id_back' => $idBackPath,
                'profile_pic' => $profilePicPath,
                'health_cert' => $healthCertPath,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'All documents uploaded successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to upload documents: ' . $e->getMessage(),
            ]);
        }
    }

    private function uploadToS3($file, $prefix, $cookId)
    {
        $filename = $prefix . '_' . $cookId . '_' . time() . '.' . $file->getClientOriginalExtension();

        // Check if the file is an image
        if (in_array($file->getClientOriginalExtension(), ['jpg', 'jpeg', 'png'])) {
            $image = Image::make($file)->resize(300, 200);
            $fileContents = (string) $image->encode();
        } else {
            // Treat as binary content for other file types
            $fileContents = file_get_contents($file);
        }

        // Upload to S3

        $path = "cooks/{$cookId}/{$filename}";
        Storage::disk('s3')->put($path, $fileContents, 'public');

        return Storage::disk('s3')->url($path);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $cook = Cook::with('shifts')->where('id', $id)->first();

        if (!empty($cook)) {
            $data = [
                'status' => 'success',
                'message' => 'Request successful!',
                'data' => $cook,
            ];
        } else {
            $data = [
                'status' => 'no_data',
                'message' => 'Unable to load your profile. Please try again!'
            ];
        }
        return response()->json($data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CookProfileEditRequest $request, string $id)
    {
        $request->validated();

        $kitchen_name = $request->input('kitchen_name');
        $mpesa_number = $request->input('mpesa_number');
        $physical_address = $request->input('physical_address');
        $google_map_pin = $request->input('google_map_pin');

        if ($this->kitchenNameExists($kitchen_name) && !$this->kitchennameBelongsToCook($id, $kitchen_name)) {
            $data = [
                'status' => 'error',
                'message' => 'Kitchen name is already in use by another cook!',
            ];
            return response()->json($data);
        } elseif ($this->mpesanoExists($mpesa_number) && !$this->mpesanoBelongsToCook($id, $mpesa_number)) {
            $data = [
                'status' => 'error',
                'message' => 'Mpesa number is already in use by another cook!'
            ];
            return response()->json($data);
        }

        $cook = Cook::where('id', $id)->first();

        if (!empty($cook)) {
            $cook->kitchen_name = $kitchen_name;
            $cook->mpesa_number = $mpesa_number;
            $cook->physical_address = $physical_address;
            $cook->google_map_pin = $google_map_pin;

            if ($cook->update()) {
                $data = [
                    'status' => 'success',
                    'message' => 'Profile updated successfully',
                ];
            } else {
                $data = [
                    'status' => 'error',
                    'message' => 'A problem was encountered, your profile was NOT updated. Please try again!'
                ];
            }
        } else {
            $data = [
                'status' => 'no_data',
                'message' => 'Unable to locate your profile for update. Please try again!'
            ];
        }
        return response()->json($data);
    }

    public function profile(string $id)
    {
        $profile_cook = Cook::where('client_id', $id)->first();
        //        cook profile picture
        if (empty($profile_cook)) {
            return response()->json([
                'status' => 'no_data',
                'message' => 'Unable to load your profile. Please try again!'
            ], 404);
        }

        $profile_picture = null;

        if (CookDocument::where('cook_id', $profile_cook->id)->exists()) {
            $profile_picture = CookDocument::where('cook_id', $profile_cook->id)->first()->profile_pic;
        }

        if (!empty($profile_cook)) {
            $data = [
                'status' => 'success',
                'message' => 'Request successful!',
                'profile_pic' => $profile_picture,
                'data' => $profile_cook,
            ];
        } else {
            $data = [
                'status' => 'no_data',
                'message' => 'Unable to load your profile. Please try again!'
            ];
        }
        return response()->json($data);
    }

    //    get cook transactions
    public function all_transactions(string $id)
    {
        $client_id = Cook::where('id', $id)->first()->client_id;

        $order_ids = DB::table('orders')->where('client_id', $client_id)->get()->pluck('id');

        $transactions = DB::table('order_payments')->whereIn('order_id', $order_ids)->get();

        if (!empty($transactions)) {
            $data = [
                'status' => 'success',
                'message' => 'Request successful!',
                'data' => $transactions,
            ];
        } else {
            $data = [
                'status' => 'no_data',
                'message' => 'Unable to load your transactions. Please try again!'
            ];
        }
        return response()->json($data);
    }
}
