<?php

namespace App\Http\Controllers;

use App\Models\Reward;
use App\Models\Transaction;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    public function create(Request $request)
    {
        try {
            $request->validate([
                'machine_id' => 'nullable|string',
                'reward_id' => 'nullable|integer'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'code' => '400',
                'status' => 'error',
                'message' => 'Invalid input data.'
            ], 400);
        }

        try {
            $callback_id = Str::random(50);
            $user_id = Auth::user()->id;

            if ($request->input('reward_id') !== null) {
                $reward_point = Reward::where('id', $request->input('reward_id'))->value('point');

                $data = Transaction::create([
                    'transaction_id' => $callback_id,
                    'user_id' => $user_id,
                    'status_point' => 1,
                    'point' => $reward_point
                ]);

                User::where('id', $user_id)
                    ->decrement('point', $reward_point);

                return response()->json([
                    'code' => '201',
                    'status' => 'success',
                    'message' => 'Berhasil menukar hadiah',
                    'data'  => $data
                ], 201);
            }

            if ($request->input('machine_id') !== null) {
                $client = new Client();

                $url = env('BASE_URL_IOT') . '/machine/update/' . $request->input('machine_id');
                $response = $client->put($url, [
                    'headers' => [
                        'x-api-key' => env('API_KEY_IOT'),
                    ],
                    'json' => [
                        'user_id' => $user_id,
                        'callback_url' => (env('APP_URL') . '/api/v1/transaction/update/' . $callback_id),
                    ]
                ]);

                if ($response->getStatusCode() == 409) {
                    throw new \Exception('Mesin sedang digunakan');
                } else if ($response->getStatusCode() !== 200) {
                    throw new \Exception('Failed to update machine');
                }

                $data = Transaction::create([
                    'transaction_id' => $callback_id,
                    'user_id' => $user_id,
                    'status_point' => 0,
                    'point' => 0
                ]);

                return response()->json([
                    'code' => '201',
                    'status' => 'success',
                    'message' => 'Terkoneksi dengan mesin',
                    'data'  => $data
                ], 201);
            }
        } catch (\Exception $e) {
            return response()->json([
                'code' => '500',
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update($id, Request $request)
    {
        try {
            $request->validate([
                'weight' => 'required|numeric'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'code' => '400',
                'status' => 'error',
                'message' => 'Invalid weight data.'
            ], 400);
        }

        $transaction = Transaction::where('transaction_id', $id)->first();

        if ($transaction === null) {
            return response()->json([
                'code' => '404',
                'status' => 'error',
                'message' => 'Transaction not found'
            ], 404);
        }

        // if ($transaction->status_point !== 0) {
        //     return response()->json([
        //         'code' => '400',
        //         'status' => 'error',
        //         'message' => 'Transaction already completed'
        //     ], 400);
        // }

        try {
            $transaction->weight = $request->input('weight', 0);
            $transaction->point = $transaction->weight * 10; // Assuming 10 points per gram
            $transaction->save();

            User::where('id', $transaction->user_id)
                ->increment('point', $transaction->weight * 10); // Assuming 1 points per gram

            return response()->json([
                'code' => '200',
                'status' => 'success',
                'message' => 'Transaksi berhasil diperbarui',
                'data' => $transaction
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => '500',
                'status' => 'error',
                'message' => 'Internal server error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function history(Request $request)
    {
        try {
            $transactions = Transaction::where('user_id', Auth::user()->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'code' => '200',
                'status' => 'success',
                'message' => 'Berhasil mengambil data transaksi',
                'data' => $transactions
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'code' => '500',
                'status' => 'error',
                'message' => 'Internal Server Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
