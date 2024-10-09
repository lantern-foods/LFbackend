<?php

namespace App\Http\Controllers\Deliverycompany\v1;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\DriverDocument;
use App\Http\Requests\DriverImageRequest;
use App\Http\Requests\DriverRequest;
use App\Http\Requests\UpdateDriverRequest;
use App\Traits\Drivers;
use App\Jobs\SendDriverOtp;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;

class DriverController extends Controller
{
    use Drivers;

    /**
     * Display a listing of all drivers.
     */
    public function index()
    {
        $allocatedDrivers = Driver::with('vehicle')->get();
        $driversWithoutVehicles = Driver::doesntHave('vehicle')->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Request successful',
            'data' => [
                'allocated_drivers' => $allocatedDrivers,
                'drivers_without_vehicles' => $driversWithoutVehicles
            ],
        ]);
    }

    /**
     * Store a newly created driver.
     */
    public function store(DriverRequest $request)
    {
        $request->validated();

        $driver = Driver::create([
            'company_id' => $request->input('company_id'),
            'email' => $request->input('email'),
            'driver_name' => $request->input('driver_name'),
            'phone_number' => $request->input('phone_number'),
            'id_number' => $request->input('id_number'),
            'date_of_birth' => $request->input('date_of_birth'),
            'gender' => $request->input('gender'),
        ]);

        if ($driver) {
            return response()->json([
                'status' => 'success',
                'message' => 'Driver created successfully. Proceed to upload required documents!',
                'driverId' => $driver->id,
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'A problem was encountered. Driver was NOT created. Please try again!',
        ]);
    }

    /**
     * Upload driver documents.
     */
    public function uploadImages(DriverImageRequest $request)
    {
        $request->validated();
        $driverId = $request->input('driverId');

        $idFrontPath = $this->uploadToS3($request->file('id_front'), 'id_front', $driverId);
        $idBackPath = $this->uploadToS3($request->file('id_back'), 'id_back', $driverId);
        $profilePicPath = $this->uploadToS3($request->file('profile_pic'), 'profile_pic', $driverId);

        if (!$idFrontPath || !$idBackPath || !$profilePicPath) {
            return response()->json([
                'status' => 'error',
                'message' => 'Documents were not uploaded. Please try again or contact support!',
            ]);
        }

        $driverDocument = DriverDocument::create([
            'driver_id' => $driverId,
            'id_front' => $idFrontPath,
            'id_back' => $idBackPath,
            'profile_pic' => $profilePicPath,
        ]);

        if ($driverDocument) {
            $driver = Driver::find($driverId);
            $this->sendDriverOtp($driver);

            return response()->json([
                'status' => 'success',
                'message' => 'Documents uploaded successfully!',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'A problem was encountered. Please try again!',
        ]);
    }

    /**
     * Send OTP to the driver.
     */
    public function sendDriverOtp(Driver $driver)
    {
        $otp = mt_rand(100000, 999999);
        $driver->update(['drive_otp' => $otp]);

        dispatch(new SendDriverOtp($driver));

        return response()->json([
            'status' => 'success',
            'message' => 'OTP sent successfully',
        ]);
    }

    /**
     * Upload files to S3.
     */
    private function uploadToS3($file, $prefix, $driverId)
    {
        $filename = $prefix . '_' . $driverId . '_' . time() . '.' . $file->getClientOriginalExtension();

        if (in_array($file->getClientOriginalExtension(), ['jpg', 'jpeg', 'png'])) {
            $image = Image::make($file)->resize(300, 200);
            $fileContents = (string) $image->encode();
        } else {
            $fileContents = file_get_contents($file);
        }

        $path = "drivers/{$driverId}/{$filename}";
        Storage::disk('s3')->put($path, $fileContents, 'public');

        return Storage::disk('s3')->url($path);
    }

    /**
     * Show a specific driver's details.
     */
    public function edit(string $id)
    {
        $driver = Driver::find($id);

        if ($driver) {
            return response()->json([
                'status' => 'success',
                'message' => 'Request successful',
                'data' => $driver,
            ]);
        }

        return response()->json([
            'status' => 'no_data',
            'message' => 'Unable to load driver profile. Please try again!',
        ]);
    }

    /**
     * Update a driver's details.
     */
    public function update(UpdateDriverRequest $request, string $id)
    {
        $request->validated();
        $driver = Driver::find($id);

        if (!$driver) {
            return response()->json([
                'status' => 'no_data',
                'message' => 'Driver not found. Please try again!',
            ]);
        }

        if (
            $this->emailAddressExists($request->input('email')) && !$this->emailBelongsToDriver($id, $request->input('email')) ||
            $this->phonenoExists($request->input('phone_number')) && !$this->phoneBelongsToDriver($id, $request->input('phone_number')) ||
            $this->idnumberExists($request->input('id_number')) && !$this->idnumberBelongsToDriver($id, $request->input('id_number'))
        ) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email, phone number, or ID number is already in use by another driver!',
            ]);
        }

        $driver->update([
            'driver_name' => $request->input('driver_name'),
            'id_number' => $request->input('id_number'),
            'phone_number' => $request->input('phone_number'),
            'email' => $request->input('email'),
            'gender' => $request->input('gender'),
            'date_of_birth' => $request->input('date_of_birth'),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Driver updated successfully',
        ]);
    }
}
