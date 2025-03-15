<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\StorePreferencesRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Mail\ResetPasswordMail;


class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request)
    {
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json(['token' => $token], 201);
        } catch (\Exception $e) {
            Log::error("Register Failed", ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to register user.'], 500);
        }
    }

    /**
     * Handle user login.
     */
    public function login(Request $request)
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json(['message' => 'Email not found.'], 404);
            }
            
            if (!Hash::check($request->password, $user->password)) {
                return response()->json(['message' => 'Incorrect password.'], 401);
            }            

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json(['token' => $token]);
        } catch (\Exception $e) {
            Log::error("Login Failed", ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to log in.'], 500);
        }
    }

    /**
     * Handle user logout.
     */
    public function logout(Request $request)
    {
        try {    
            $token = $request->bearerToken();
            if (!$token) {
                Log::error("Logout Failed: No Token Provided");
                return response()->json(['message' => 'Unauthorized.'], 401);
            }
    
            $user = $this->getUserByToken($token);
    
            if (!$user) {
                Log::error("Logout Failed: Invalid Token");
                return response()->json(['message' => 'Unauthorized.'], 401);
            }
        
            // Delete tokens
            $user->tokens()->delete();    
            return response()->json(['message' => 'Logged out successfully']);
    
        } catch (\Exception $e) {
            Log::error("Logout Failed", ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to log out.'], 500);
        }
    }    

    /**
     * Change password for the logged-in user.
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            $token = $request->bearerToken();
            if (!$token) {
                Log::error("Authentication Failed: No Token Provided");
                return response()->json(['message' => 'Unauthorized.'], 401);
            }
    
            $user = $this->getUserByToken($token);
    
            if (!$user) {
                Log::error("Authentication Failed: Invalid Token");
                return response()->json(['message' => 'Unauthorized.'], 401);
            }
        
            // Validate old password
            if (!Hash::check($request->old_password, $user->password)) {
                Log::error("Change Password Failed: Incorrect Old Password");
                return response()->json(['message' => 'Incorrect old password.'], 400);
            }
    
            // Update password
            $user->update(['password' => Hash::make($request->new_password)]);    
            return response()->json(['message' => 'Password updated successfully.']);
    
        } catch (\Exception $e) {
            Log::error("Change Password Error", ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to change password.'], 500);
        }
    }

    /**
     * Forgot password.
    */
    public function forgotPassword(ForgotPasswordRequest $request)
    {            
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();
    
        $token = bin2hex(random_bytes(32));
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => hash('sha256', $token),
            'created_at' => Carbon::now(),
        ]);        
    
        try {
            Mail::to($request->email)->send(new ResetPasswordMail($token, $request->email));
            Log::info('Password reset email sent successfully', ['email' => $request->email]);
        } catch (\Exception $e) {
            Log::error('Failed to send password reset email', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to send reset link.'], 500);
        }
    
        return response()->json(['message' => 'Password reset link sent.']);
    }

    /**
     * Reset password.
    */
    public function resetPassword(ResetPasswordRequest $request)
    {
        // Check if token exists
        $resetRecord = DB::table('password_reset_tokens')
        ->where('email', $request->email)
        ->where('token', hash('sha256', $request->token))
        ->first();    

        if (!$resetRecord) {
            Log::error('Invalid password reset token', ['email' => $request->email]);
            return response()->json(['message' => 'Invalid token or email.'], 400);
        }

        // Check token expiration
        if (Carbon::parse($resetRecord->created_at)->addMinutes(30)->isPast()) {
            Log::error('Expired password reset token', ['email' => $request->email]);
            return response()->json(['message' => 'Token has expired.'], 400);
        }

        // Reset the password
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            Log::error('User not found during password reset', ['email' => $request->email]);
            return response()->json(['message' => 'User not found.'], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        Log::info('Password reset successful', ['email' => $request->email]);
        return response()->json(['message' => 'Password reset successful.']);
    }

    /**
     * Get User.
    */
    public function getUser(Request $request)
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user = $this->getUserByToken($token);
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return response()->json($user);
    }

    /**
     * Get User by token.
    */
    private function getUserByToken($token)
    {
        return User::whereHas('tokens', function ($query) use ($token) {
            $query->where('token', hash('sha256', $token));
        })->first();
    }

    /**
     * Store Preferences.
    */
    public function storePreferences(StorePreferencesRequest $request)
    {
        try {
            $token = $request->bearerToken();
            if (!$token) {
                Log::error("Logout Failed: No Token Provided");
                return response()->json(['message' => 'Unauthorized.'], 401);
            }
    
            $user = $this->getUserByToken($token);
            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
    
            // Sync the user preferences
            $user->preferredCategories()->sync($request->categories ?? []);
            $user->preferredSources()->sync($request->sources ?? []);
            $user->preferredAuthors()->sync($request->authors ?? []);
    
            return response()->json(['message' => 'Preferences saved successfully']);
        } catch (\Exception $e) {
            Log::error("Failed to store preferences", ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to save preferences.'], 500);
        }
    }    
}