<?php

namespace App\Http\Controllers\Cook\v1;

use App\Models\Cook;
use App\Traits\Cooks;
use App\Traits\Clients;
use App\Models\CookDocument;
use App\Http\Requests\CookRequest;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\CookDocumentRequest;
use App\Http\Requests\CookProfileEditRequest;
use Intervention\Image\ImageManagerStatic as Image;

class CookController extends Controller
{
    use Cooks, Clients;

    /**
     * Display a listing of all cooks with their shifts.
     */
    public function index()
    {
        $cooks = Cook::with('shifts')->get();

        if ($cooks->isEmpty()) {
            return response()->json([
                'status' => 'no_data',
                'message' => 'No records found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Request successful',
            'data' => $cooks,
        ]);
    }

    /**
     * Get all meals from cooks.
     */
    public function all_cook_meals()
    {
        $allcook_meals = Cook::with('meals.mealImages')->get();

        return $this->generateResponse($allcook_meals, 'Meals');
    }

    /**
     * Get all express meals from cooks.
     */
    public function all_cook_meals_express()
    {
        $allcook_meals = Cook::with('meals.mealImages')->get();

        return $this->generateResponse($allcook_meals, 'Express Meals');
    }

    /**
     * Get details of a specific cook's meals and shifts.
     */
    public function all_cook_meal(string $id)
    {
        $allcook_meal = Cook::with(['shifts', 'meals.mealImages'])->find($id);

        return $this->generateResponse($allcook_meal, 'Cook Meals');
    }

    /**
     * Create a new cook.
     */
    public function store(CookRequest $request)
    {
        $validated = $request->validated();

        if (Cook::where('client_id', $validated['client_id'])->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'You already exist as a cook.',
            ], 400);
        }

        // Validate the Mpesa number
        list($msisdn, $network) = $this->get_msisdn_network($validated['mpesa_number']);
        if (!$msisdn) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Mpesa number. Please provide a valid Kenyan phone number!',
            ], 400);
        }

        $cook = Cook::create([
            'client_id' => $validated['client_id'],
            'kitchen_name' => $validated['kitchen_name'],
            'id_number' => $validated['id_number'],
            'mpesa_number' => $msisdn,
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
                'message' => 'Failed to create account. Please try again.',
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Account created successfully. Proceed to upload required documents.',
            'clientID' => $cook->id,
        ], 201);
    }

    /**
     * Upload documents for a cook.
     */
    public function uploadDocuments(CookDocumentRequest $request)
    {
        $validated = $request->validated();

        $idFront = $request->file('id_front');
        $idBack = $request->file('id_back');
        $healthCert = $request->file('health_cert');
        $profilePic = $request->file('profile_pic');

        if (!$idFront || !$idBack || !$healthCert || !$profilePic) {
            return response()->json([
                'status' => 'error',
                'message' => 'All required files must be provided.',
            ], 400);
        }

        try {
            $documentData = [
                'cook_id' => $validated['cook_id'],
                'id_front' => $this->uploadToS3($idFront, 'id_front', $validated['cook_id']),
                'id_back' => $this->uploadToS3($idBack, 'id_back', $validated['cook_id']),
                'health_cert' => $this->uploadToS3($healthCert, 'health_cert', $validated['cook_id']),
                'profile_pic' => $this->uploadToS3($profilePic, 'profile_pic', $validated['cook_id']),
            ];

            CookDocument::create($documentData);

            return response()->json([
                'status' => 'success',
                'message' => 'Documents uploaded successfully.',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to upload documents: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Edit a cook's profile.
     */
    public function edit(string $id)
    {
        $cook = Cook::with('shifts')->find($id);

        return $this->generateResponse($cook, 'Cook Profile');
    }

    /**
     * Update a cook's profile.
     */
    public function update(CookProfileEditRequest $request, string $id)
    {
        $request->validated();

        $cook = Cook::find($id);

        if (!$cook) {
            return response()->json([
                'status' => 'no_data',
                'message' => 'Unable to locate your profile for update.',
            ], 404);
        }

        $cook->fill($request->only(['kitchen_name', 'mpesa_number', 'physical_address', 'google_map_pin']));

        if (!$cook->save()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update profile. Please try again.',
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully.',
        ], 200);
    }

    /**
     * View a cook's profile.
     */
    public function profile(string $id)
    {
        $profile_cook = Cook::where('client_id', $id)->first();
        $profile_picture = CookDocument::where('cook_id', $profile_cook->id)->value('profile_pic');

        return response()->json([
            'status' => 'success',
            'message' => 'Profile retrieved successfully.',
            'profile_pic' => $profile_picture,
            'data' => $profile_cook,
        ], 200);
    }

    /**
     * Get all transactions for a cook.
     */
    public function all_transactions(string $id)
    {
        $client_id = Cook::where('id', $id)->value('client_id');
        $order_ids = DB::table('orders')->where('client_id', $client_id)->pluck('id');
        $transactions = DB::table('order_payments')->whereIn('order_id', $order_ids)->get();

        return $this->generateResponse($transactions, 'Transactions');
    }

    /**
     * Helper method for handling responses.
     */
    private function generateResponse($data, $entity)
    {
        if (empty($data)) {
            return response()->json([
                'status' => 'no_data',
                'message' => "No $entity found.",
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => "$entity retrieved successfully.",
            'data' => $data,
        ], 200);
    }

    /**
     * Helper method for uploading to S3.
     */
    private function uploadToS3($file, $prefix, $cookId)
    {
        $filename = $prefix . '_' . $cookId . '_' . time() . '.' . $file->getClientOriginalExtension();

        if (in_array($file->getClientOriginalExtension(), ['jpg', 'jpeg', 'png'])) {
            $image = Image::make($file)->resize(300, 200);
            $fileContents = (string) $image->encode();
        } else {
            $fileContents = file_get_contents($file);
        }

        $path = "cooks/{$cookId}/{$filename}";
        Storage::disk('s3')->put($path, $fileContents, 'public');

        return Storage::disk('s3')->url($path);
    }

    /**
     * Helper method to validate and return the network for a given phone number (msisdn).
     */
    public function get_msisdn_network($msisdn)
    {
        // Define regex for different Kenyan networks
        $regex = [
            'airtel'     => '/^\+?(254|0|)7(?:[38]\d{7}|5[0-6]\d{6})\b/',
            'equitel'    => '/^\+?(254|0|)76[0-7]\d{6}\b/',
            'safaricom'  => '/^\+?(254|0|)(?:7[01249]\d{7}|1[01234]\d{7}|75[789]\d{6}|76[89]\d{6})\b/',
            'telkom'     => '/^\+?(254|0|)7[7]\d{7}\b/',
        ];

        // Match phone number against the patterns
        foreach ($regex as $operator => $re) {
            if (preg_match($re, $msisdn)) {
                // Normalize the phone number to 254 format
                return [preg_replace('/^\+?(254|0)/', '254', $msisdn), $operator];
            }
        }

        return [false, false];
    }
}
