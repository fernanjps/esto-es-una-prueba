<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:255",
            "email" => "required|string|email|max:255|unique:users",
            "password" => "required|string|min:8|confirmed",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                "message" => "Validation errors",
                "errors" => $validator->errors()
            ], 422);
        }

        $user = User::create([
            "name" => $request->name,
            "email" => $request->email,
            "password" => Hash::make($request->password),
            "role" => "user" // Por defecto todos son usuarios normales
        ]);

        return response()->json([
            "success" => true,
            "message" => "User registered successfully",
            "user" => $user
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "email" => "required|email",
            "password" => "required|string|min:6",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                "message" => "Validation errors",
                "errors" => $validator->errors()
            ], 422);
        }

        if (!$token = JWTAuth::attempt($validator->validated())) {
            return response()->json([
                "success" => false,
                "message" => "Invalid credentials"
            ], 401);
        }

        $user = Auth::user();
        
        // Agregar estadísticas del usuario
        $user->reviews_count = $user->reviews()->count();
        $user->average_rating = $user->reviews()->avg("rating");

        return response()->json([
            "success" => true,
            "token" => $token,
            "user" => $user
        ]);
    }

    public function logout()
    {
        JWTAuth::logout();
        return response()->json([
            "success" => true,
            "message" => "Successfully logged out"
        ]);
    }

    public function me()
    {
        $user = Auth::user();
        
        // Agregar estadísticas del usuario
        $user->reviews_count = $user->reviews()->count();
        $user->average_rating = $user->reviews()->avg("rating");

        return response()->json([
            "success" => true,
            "user" => $user
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $rules = [
            "name" => "required|string|max:255",
            "email" => "required|string|email|max:255|unique:users,email," . $user->id,
        ];

        // Solo validar contraseña si se proporciona
        if ($request->filled("password")) {
            $rules["current_password"] = "required|string";
            $rules["password"] = "required|string|min:8|confirmed";
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                "message" => "Validation errors",
                "errors" => $validator->errors()
            ], 422);
        }

        // Verificar contraseña actual si se quiere cambiar
        if ($request->filled("password")) {
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    "success" => false,
                    "message" => "Current password is incorrect"
                ], 422);
            }
        }

        // Actualizar datos
        $updateData = [
            "name" => $request->name,
            "email" => $request->email,
        ];

        if ($request->filled("password")) {
            $updateData["password"] = Hash::make($request->password);
        }

        $user->update($updateData);

        return response()->json([
            "success" => true,
            "message" => "Profile updated successfully",
            "user" => $user
        ]);
    }
}