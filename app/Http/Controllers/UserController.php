<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function login(Request $request)
    {
        try {
            try {
                // Validate the request data
                $request->validate([
                    'email' => 'required|string|email',
                    'password' => 'required|min:8'
                ]);
            } catch (\Illuminate\Validation\ValidationException $e) {
                return response()->json([
                    'code' => '400',
                    'status' => 'error',
                    'message' => 'Email dan password harus diisi dan valid.'
                ], 400);
            }

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'code' => '401',
                    'status' => 'error',
                    'message' => 'Email dan password tidak valid.'
                ], 401);
            }

            $token = $user->createToken($user->name . '-AuthToken')->plainTextToken;

            return response()->json([
                'code' => '200',
                'status' => 'success',
                'message' => 'Login sukses',
                'data' => [
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'code' => '500',
                'status' => 'error',
                'message' => 'Internal Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function register(Request $request)
    {
        try {
            try {
                // Validate the request data
                $request->validate([
                    'name' => 'required|string',
                    'email' => 'required|string|email|unique:users',
                    'password' => 'required|min:8'
                ]);
            } catch (\Illuminate\Validation\ValidationException $e) {
                return response()->json([
                    'code' => '400',
                    'status' => 'error',
                    'message' => 'Data tidak valid: ' . $e->getMessage()
                ], 400);
            }

            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
            ]);

            return response()->json([
                'code' => '201',
                'status' => 'success',
                'message' => 'Registrasi sukses',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'code' => '500',
                'status' => 'error',
                'message' => 'Internal Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'code' => '200',
                'status' => 'success',
                'message' => 'Berhasil logout'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'code' => '500',
                'status' => 'error',
                'message' => 'Internal Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getDetailProfile()
    {
        try {
            // Logic to get user profile details
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'code' => '401',
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 401);
            }

            return response()->json([
                'code' => '200',
                'status' => 'success',
                'message' => 'Berhasil mendapatkan detail profil',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'code' => '500',
                'status' => 'error',
                'message' => 'Internal Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getPoint()
    {
        try {
            // Logic to get user points
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'code' => '401',
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 401);
            }

            return response()->json([
                'code' => '200',
                'status' => 'success',
                'message' => 'Berhasil mendapatkan jumlah poin',
                'data' => [
                    'points' => $user->point // Assuming point is a field in the User model
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'code' => '500',
                'status' => 'error',
                'message' => 'Internal Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function leaderboard()
    {
        try {
            $topUsers = Transaction::join('users', 'transactions.user_id', '=', 'users.id')
                ->select(
                    'users.name',
                    DB::raw('SUM(transactions.point) as total_points') // 2. Gabungkan select dan beri alias
                )
                ->where('transactions.status_point', 1) // 1. Tentukan kolom status_point dari tabel mana
                ->groupBy('users.id', 'users.name')
                ->havingRaw('SUM(transactions.point) > 0') // 1. Tentukan kolom point dari tabel mana
                ->orderBy('total_points', 'desc') // 3. Urutkan berdasarkan alias baru
                ->limit(10)
                ->get();

            return response()->json([
                'code' => '200',
                'status' => 'success',
                'message' => 'Berhasil mendapatkan leaderboard',
                'data' => $topUsers
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'code' => '500',
                'status' => 'error',
                'message' => 'Internal Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getBadge()
    {
        try {
            // Logic to get user badge
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'code' => '401',
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 401);
            }

            return response()->json([
                'code' => '200',
                'status' => 'success',
                'message' => 'Berhasil mendapatkan badge',
                'data' => [
                    'badge' => $user->badge // Assuming badge is a field in the User model
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'code' => '500',
                'status' => 'error',
                'message' => 'Internal Server Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
