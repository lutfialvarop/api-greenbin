<?php

namespace App\Http\Controllers;

use App\Models\Reward;
use Illuminate\Http\Request;

class RewardController extends Controller
{
    public function getAll()
    {
        try {
            $dataReward = Reward::select('id', 'title', 'image', 'point')->orderBy('point', 'desc')->get();

            $dataReward = $dataReward->map(function ($reward) {
                $reward->image = env('APP_URL') . '/storage/' . $reward->image;
                return $reward;
            });

            return response()->json([
                'code' => '200',
                'status' => 'success',
                'message' => 'Rewards retrieved successfully',
                'data' => $dataReward
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => '500',
                'status' => 'error',
                'message' => 'Internal Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function create(Request $request)
    {
        try {
            try {
                $request->validate([
                    'title' => 'required|string|max:255',
                    'description' => 'nullable|string',
                    'point' => 'required|integer|min:1',
                    'image' => 'required|image|mimes:jpeg,png,jpg|max:2048'
                ]);
            } catch (\Illuminate\Validation\ValidationException $e) {
                return response()->json([
                    'code' => '400',
                    'status' => 'error',
                    'message' => 'Invalid input data.'
                ], 400);
            }

            $imagePath = $request->file('image')->store('rewards', 'public');

            $reward = Reward::create(array_merge($request->all(), ['image' => $imagePath]));

            return response()->json([
                'code' => '201',
                'status' => 'success',
                'message' => 'Reward created successfully',
                'data' => $reward
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'code' => '500',
                'status' => 'error',
                'message' => 'Internal Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function detail($id)
    {
        try {
            $reward = Reward::findOrFail($id);

            $reward->image = env('APP_URL') . '/storage/' . $reward->image;

            return response()->json([
                'code' => '200',
                'status' => 'success',
                'message' => 'Reward retrieved successfully',
                'data' => $reward
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'code' => '404',
                'status' => 'error',
                'message' => 'Reward not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'code' => '500',
                'status' => 'error',
                'message' => 'Internal Server Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
