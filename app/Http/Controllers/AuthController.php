<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendOtpMail;
use App\Mail\SendResetOtpMail;

class AuthController extends Controller
{
    // 🔹 Register
    public function register(Request $request)
    {
        $request->validate([
            'email'=>'required|email|unique:users',
            'password'=>'required|min:8|confirmed',
            'first_name'=>'required',
            'last_name'=>'required',
            'phone'=>'nullable',
            'age'=>'nullable|integer',
            'gender'=>'nullable',
            'country'=>'nullable',
            'education_level'=>'nullable',
            'japanese_level'=>'nullable',
        ]);

        $otp = rand(100000,999999);

        $user = User::create([
            'email'=>$request->email,
            'password'=>Hash::make($request->password),
            'first_name'=>$request->first_name,
            'last_name'=>$request->last_name,
            'phone'=>$request->phone,
            'age'=>$request->age,
            'gender'=>$request->gender,
            'country'=>$request->country,
            'education_level'=>$request->education_level,
            'japanese_level'=>$request->japanese_level,
            'otp'=>$otp,
            'otp_expires_at'=>now()->addMinutes(10)
        ]);

        Mail::to($user->email)->send(new SendOtpMail($otp));

        return response()->json([
            'message'=>'Registered. Check email for OTP'
        ]);
    }

    // 🔹 Verify OTP
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email'=>'required|email',
            'otp'=>'required'
        ]);

        $user = User::where('email',$request->email)->first();

        if(!$user) return response()->json(['message'=>'User not found'],404);

        if($user->otp !== $request->otp)
            return response()->json(['message'=>'Invalid OTP'],400);

        if(now()->gt($user->otp_expires_at))
            return response()->json(['message'=>'OTP expired'],400);

        $user->email_verified_at = now();
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->save();
        $token = $user->createToken('token')->plainTextToken;

        return response()->json([
            'message'=>'تم التحقق من الايميل وانشاء الحساب',
            'token' => $token,
            'user' => $user
        ]);
    }
    public function profile (){
        return auth()->user();
    }
    // 🔹 Login
    public function login(Request $request)
    {
        $request->validate([
            'email'=>'required',
            'password'=>'required'
        ]);

        $user = User::where('email',$request->email)->first();

        if(!$user || !Hash::check($request->password,$user->password))
            return response()->json(['message'=>'Invalid credentials'],401);

        if(!$user->email_verified_at)
            return response()->json(['message'=>'Verify email first'],403);

        $token = $user->createToken('token')->plainTextToken;

        return response()->json([
            'token'=>$token,
            'user'=>$user
        ]);
    }

    // 🔹 Resend OTP
    public function resendOtp(Request $request)
    {
        $user = User::where('email',$request->email)->first();

        if(!$user) return response()->json(['message'=>'User not found'],404);

        $otp = rand(100000,999999);

        $user->otp = $otp;
        $user->otp_expires_at = now()->addMinutes(10);
        $user->save();

        Mail::to($user->email)->send(new SendOtpMail($otp));

        return response()->json(['message'=>'OTP resent']);
    }

    // 🔹 Logout
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message'=>'Logged out']);
    }


    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $otp = rand(100000, 999999);

        $user->reset_otp = $otp;
        $user->reset_otp_expires_at = now()->addMinutes(10);
        $user->save();

        Mail::to($user->email)->send(new SendResetOtpMail($otp));

        return response()->json([
            'message' => 'Reset code sent to email'
        ]);
    }
    public function verifyResetOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->reset_otp !== $request->otp) {
            return response()->json(['message' => 'Invalid OTP'], 400);
        }

        if (now()->gt($user->reset_otp_expires_at)) {
            return response()->json(['message' => 'OTP expired'], 400);
        }

        return response()->json([
            'message' => 'OTP verified successfully'
        ]);
    }
        public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required',
            'password' => 'required|min:6|confirmed'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->reset_otp !== $request->otp) {
            return response()->json(['message' => 'Invalid OTP'], 400);
        }

        if (now()->gt($user->reset_otp_expires_at)) {
            return response()->json(['message' => 'OTP expired'], 400);
        }

        $user->password = Hash::make($request->password);

        $user->reset_otp = null;
        $user->reset_otp_expires_at = null;

        $user->save();

        return response()->json([
            'message' => 'Password reset successfully'
        ]);
    }
}
