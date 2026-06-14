<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $request->merge([
            'peran' => strtolower($request->peran),
            'email' => strtolower($request->email),
        ]);

        $validator = Validator::make($request->all(), [
            'nama'     => 'required|string|max:100',
            'peran'    => 'required|in:mahasiswa,dosen,staff',
            'npm_nidn' => 'nullable|string|max:30',
            'email'    => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'nama.required'      => 'Nama wajib diisi.',
            'peran.required'     => 'Peran wajib dipilih.',
            'peran.in'           => 'Peran tidak valid.',
            'email.required'     => 'Email wajib diisi.',
            'email.email'        => 'Format email tidak valid.',
            'email.unique'       => 'Email sudah terdaftar.',
            'password.required'  => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = User::create([
            'name'     => $request->nama,
            'nama'     => $request->nama,
            'peran'    => $request->peran,
            'npm_nidn' => $request->npm_nidn,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil! Silakan login.',
            'data'    => [
                'id'    => $user->id,
                'nama'  => $user->nama,
                'email' => $user->email,
                'peran' => $user->peran,
            ],
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah.',
            ], 401);
        }

        $user->tokens()->delete();
        $token = $user->createToken('parkir-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil!',
            'data'    => [
                'token' => $token,
                'user'  => [
                    'id'       => $user->id,
                    'nama'     => $user->nama,
                    'email'    => $user->email,
                    'peran'    => $user->peran,
                    'npm_nidn' => $user->npm_nidn,
                ],
            ],
        ]);
    }

    public function tamuLogin(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nama'   => 'required|string|max:100',
            'tujuan' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $tamu = User::firstOrCreate(
            ['email' => 'tamu_' . now()->timestamp . '@tamu.unika.ac.id'],
            [
                'name'     => $request->nama,
                'nama'     => $request->nama,
                'peran'    => 'tamu',
                'password' => Hash::make(uniqid()),
            ]
        );

        $token = $tamu->createToken('tamu-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Akses tamu berhasil!',
            'data'    => [
                'token' => $token,
                'user'  => [
                    'id'    => $tamu->id,
                    'nama'  => $request->nama,
                    'peran' => 'tamu',
                ],
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
        ]);
    }

    public function profil(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $request->user(),
        ]);
    }
}