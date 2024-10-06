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
     * Display a listing of the resource.
     */
    public function index()
    {
        $allocated_drivers = Driver::with('vehicle')->get();

        $driversWithoutVehicles = Driver::doesntHave('vehicle')->get();

            $data = [
                'status' => 'success',
                'message' => 'Request successful',
                'data' => [$allocated_drivers, $driversWithoutVehicles],
            ];


           

        return response()->json($data);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DriverRequest $request)
    {
        $request->validated();

        $company_id = $request->input('company_id');
        $email = $request->input('email');
        $driver_name = $request->input('driver_name');
        $phone_number = $request->input('phone_number');
        $id_number = $request->input('id_number');
        $date_of_birth = $request->input('date_of_birth');
        $gender = $request->input('gender');

        $driver = Driver::create([
            'company_id' => $company_id,
            'email' => $email,
            'driver_name' => $driver_name,
            'phone_number' => $phone_number,
            'id_number' => $id_number,
            'date_of_birth' => $date_of_birth,
            'gender' => $gender,
        ]);

        if ($driver) {

            $data = [
                'status' => 'success',
                'message' => 'Driver creeated successfully. Kindly proceed to upload required documents!',
                'driverId' => $driver->id,
            ];
        } else {

            $data = [
                'status' => 'error',
                'message' => 'A problem was encountered, Driver was NOT created. Please try again!',
            ];
        }
        return response()->json($data);

    }

    /***
     * Upload Images Documents
     */
    public function UploadImages(DriverImageRequest $request)
    {
        $request->validated();

        $driverId = $request->input('driverId');
        $idFront = $request->file('id_front');
        $idBack = $request->file('id_back');
        $profilePic = $request->file('profile_pic');

        // Upload id_front image to S3
        $idFrontPath = $this->uploadToS3($idFront, 'id_front', $driverId);

        // Upload id_back image to S3
        $idBackPath = $this->uploadToS3($idBack, 'id_back', $driverId);

        // Upload profile_pic image to S3
        $profilePicPath = $this->uploadToS3($profilePic, 'profile_pic', $driverId);

        // Upload health_cert to S3

        // Check if all file paths have values
        if (empty($idFrontPath) || empty($idBackPath) || empty($profilePicPath)) {

            $data = [
                'status' => 'error',
                'message' => 'Oops! A problem was encountered,the documents were not uploaded. Please try again or contact Support.',
            ];

        } else {
            // Save paths to database using create
            $driverdocument = DriverDocument::create([
                'driver_id' => $driverId,
                'id_front' => $idFrontPath,
                'id_back' => $idBackPath,
                'profile_pic' => $profilePicPath,
            ]);

            if ($driverdocument) {
                $driver = Driver::where('id', $driverId)->first();
                // Dispatch the SendOtpJob to send OTP asynchronously
                $this->sendDriverOtp($driver);
                $data = [
                    'status' => 'success',
                    'message' => 'All documents uploaded successfully!',
                ];
            }else {

                $data = [
                    'status' => 'error',
                    'message' => 'A problem was encountered, account was NOT created. Please try again!',
                ];
    
            }

        }
        return response()->json($data);

    }

    public function sendDriverOtp(Driver $driver)
    {

        // Generate a random 6-digit OTP
        $otp = mt_rand(100000, 999999);

        // Save the OTP to the client record in the database
        $driver->update(['drive_otp' => $otp]);

        dispatch(new SendDriverOtp($driver));

        $data = [
            'status' => 'success',
            'message' => 'OTP sent successfully',
        ];
        return response()->json($data);
    }

    private function uploadToS3($file, $prefix, $driverId)
    {
        $filename = $prefix . '_' . $driverId . '_' . time() . '.' . $file->getClientOriginalExtension();

        // Check if the file is an image
        if (in_array($file->getClientOriginalExtension(), ['jpg', 'jpeg', 'png'])) {
            $image = Image::make($file)->resize(300, 200);
            $fileContents = (string) $image->encode();
        } else {
            // If it's a PDF, store it as a PDF
            if ($file->getClientOriginalExtension() == 'pdf') {
                $fileContents = file_get_contents($file);
            } else {
                // For other file types (non-image, non-PDF), treat as binary content
                $fileContents = file_get_contents($file);
            }
        }

        // Upload to S3
        $path = "drivers/{$driverId}/{$filename}";
        Storage::disk('s3')->put($path, $fileContents, 'public');

        return Storage::disk('s3')->url($path);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $driver = Driver::where('id', $id)->first();

        if (!empty($driver)) {

            $data = [
                'status' => 'success',
                'message' => 'Request successful!',
                'data' => $driver,
            ];
        } else {

            $data = [
                'status' => 'no_data',
                'message' => 'Unable to load driver profile. Please try again!',
            ];
        }
        return response()->json($data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDriverRequest $request, string $id)
    {
        $request->validated();

        $driver_name = $request->input('driver_name');
        $email = $request->input('email');
        $phone_number = $request->input('phone_number');
        $id_number = $request->input('id_number');
        $date_of_birth = $request->input('date_of_birth');
        $gender = $request->input('gender');

        if ($this->emailAddressExists($email) && !$this->emailBelongsToDriver($id, $email)) {

            $data = [
                'status' => 'error',
                'message' => 'Email address is already in use by another driver!',

            ];
            return response()->json($data);
        } elseif ($this->phonenoExists($phone_number) && !$this->phoneBelongsToDriver($id, $phone_number)) {

            $data = [
                'status' => 'error',
                'message' => 'Phone number is already in use by another driver',
            ];
            return response()->json($data);
        } elseif ($this->idnumberExists($id_number) && !$this->idnumberBelongsToDriver($id, $id_number)) {

            $data = [
                'status' => 'error',
                'message' => 'Id number is already in use by another driver',
            ];

            return response()->json($data);
        }

        $driver = Driver::where('id', $id)->first();

        if (!empty($driver)) {

            $driver->driver_name = $driver_name;
            $driver->id_number = $id_number;
            $driver->phone_number = $phone_number;
            $driver->email = $email;
            $driver->gender = $gender;
            $driver->date_of_birth = $date_of_birth;

            if ($driver->update()) {

                $data = [
                    'status' => 'success',
                    'message' => 'Driver updated successfully',
                ];
            } else {

                $data = [
                    'status' => 'error',
                    'message' => 'A problem was encountered. Driver was NOT updated. Please try again',
                ];
            }
            return response()->json($data);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

}
