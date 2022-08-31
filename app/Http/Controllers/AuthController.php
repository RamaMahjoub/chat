<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Mail\Verify;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule as ValidationRule;
use Intervention\Image\Facades\Image;
use Nette\Utils\Random;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->only(
            'logout',
            'resent_code',
            'profile',
            'edit_profile'
        );
    }

    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|confirmed|min:8',
            'name' => 'required|unique:users,name',
            'image' => 'file'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->toJson(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $user = new User();
        $user->email = $request->email;
        $user->password = Crypt::encryptString($request->password);
        $user->name = $request->name;
        if ($request->has('image')) {
            $file = $request->file('image');
            $file_name = time() . rand(111111111, 9999999999) . '.' . $file->getClientOriginalExtension();
            $path = '/uploads/images';
            $storagePath = storage_path('app' . $path);
            if (!File::exists($storagePath)) {
                File::makeDirectory($storagePath, 0755, true);
            }
            Image::make($file->getRealPath())->save($storagePath . "/" . $file_name, 40, "jpg");

            $user->image = $path . "/" . $file_name;
        }
        $user->save();

        // return response($user);

        $token =  $user->createToken('API Token')->plainTextToken;

        $verification_code = Random::generate(6, '0-9'); //Generate verification code
        DB::table('verifications')->insert(['user_id' => $user->id, 'verification_code' => $verification_code]);

        $email = $request->email;
        Mail::to($email)->send(new Verify($verification_code));

        return response()->json([
            'message' => 'Thanks for signing up!.',
            'data' => [
                'user' => new UserResource($user),
                'access_token' => $token
            ],
        ], Response::HTTP_OK);
    }

    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'verification_code' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->toJson(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $check = DB::table('verifications')->where('verification_code', $request->verification_code)->first();

        if (!is_null($check)) {
            $user = User::findOrFail($check->user_id);

            $user->update(['is_verified' => true]);
            DB::table('verifications')->where('verification_code', $request->verification_code)->delete();

            return response()->json([
                'message' => 'verified successfully',
                'data' => null,
            ], Response::HTTP_OK);
        }

        return response()->json([
            'error' => 'wrong code',
        ], Response::HTTP_BAD_REQUEST);
    }

    public function resent_code()
    {
        $user = User::findOrFail(Auth::id());
        $verification_code = Random::generate(6, '0-9'); //Generate verification code

        DB::table('verifications')->where('user_id', $user->id)->delete();
        DB::table('verifications')->insert(['user_id' => $user->id, 'verification_code' => $verification_code]);

        $email = $user->email;
        Mail::to($email)->send(new Verify($verification_code));

        return response()->json([
            'message' => 'the code resent successfully',
            'data' => null,
        ], Response::HTTP_OK);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->toJson(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $user = User::where('email', $request->email)->first();
        $user_password = Crypt::decryptString($user->password);
        //Check Password
        if (!$user || !($user_password == $request->password)) {
            return response()->json([
                'error' => 'Bad credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $token =  $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'message' => 'logged in successfully',
            'data' => [
                'user' => new UserResource($user),
                'access_token' => $token
            ],
        ], Response::HTTP_OK);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();
        return response()->json([
            'message' => 'Logged out successfully',
            'data' => null,
        ], Response::HTTP_OK);
    }

    public function profile()
    {
        return response()->json([
            'message' => 'user profile',
            'data' => new UserResource(Auth::user()),
        ], Response::HTTP_OK);
    }

    public function edit_profile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['string', ValidationRule::unique('users')->ignore(Auth::id())],
            'image' => 'file'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->toJson(),
            ], Response::HTTP_BAD_REQUEST);
        }

        // return response($request);

        $user = User::findOrFail(Auth::id());

        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('image')) {
            $file = $request->file('image');
            $file_name = time() . rand(111111111, 9999999999) . '.' . $file->getClientOriginalExtension();
            $path = '/uploads/images';
            $storagePath = storage_path('app' . $path);
            if (!File::exists($storagePath)) {
                File::makeDirectory($storagePath, 0755, true);
            }
            Image::make($file->getRealPath())->save($storagePath . "/" . $file_name, 40, "jpg");

            $user->image = $path . "/" . $file_name;
        }

        if (!$user->isDirty()) {
            return response()->json([
                'error' => 'You need to specify a different value to update',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user->save();

        return response()->json([
            'message' => 'profile information updated successfully',
            'data' => new UserResource($user),
        ], Response::HTTP_OK);
    }
}
