<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArticleController extends Controller
{
    public function getTop5()
    {
        try {
            $dataArticle = Article::select('id', 'title', 'slug', 'image')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            $dataArticle = $dataArticle->map(function ($article) {
                $article->image = env('APP_URL') . '/storage/' . $article->image;
                return $article;
            });
            // Logic to retrieve top 5 articles
            return response()->json([
                'code' => '200',
                'status' => 'success',
                'message' => 'Top 5 articles retrieved successfully',
                'data' => $dataArticle
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
                    'slug' => 'required|string|max:255',
                    'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                    'content' => 'required|string',
                ]);
            } catch (\Illuminate\Validation\ValidationException $e) {
                return response()->json([
                    'code' => '400',
                    'status' => 'error',
                    'message' => 'Invalid input data.'
                ], 400);
            }

            $imageFile = $request->file('image');
            $randomFileName = str()->random(25) . '.' . $imageFile->getClientOriginalExtension();
            $imagePath = 'articles/' . $randomFileName;

            // Store file using Storage facade
            Storage::disk('public')->put($imagePath, file_get_contents($imageFile));

            $article = Article::create([
                'title' => $request->title,
                'slug' => $request->slug,
                'content' => $request->content,
                'image' => $imagePath
            ]);

            // Add the full URL to the image in the response
            $article->image = env('APP_URL') . '/storage/' . $article->image;

            return response()->json([
                'code' => '201',
                'status' => 'success',
                'message' => 'Article created successfully',
                'data' => $article
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'code' => '500',
                'status' => 'error',
                'message' => 'Internal Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getAll()
    {
        try {
            $dataArticle = Article::select('id', 'title', 'slug', 'image')
                ->orderBy('created_at', 'desc')
                ->get();

            $dataArticle = $dataArticle->map(function ($article) {
                $article->image = url('/storage/' . $article->image);
                return $article;
            });

            return response()->json([
                'code' => '200',
                'status' => 'success',
                'message' => 'All articles retrieved successfully',
                'data' => $dataArticle
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => '500',
                'status' => 'error',
                'message' => 'Internal Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getDetail($id)
    {
        try {
            $article = Article::findOrFail($id);
            $article->increment('views');
            $article->image = env('APP_URL') . '/storage/' . $article->image;

            return response()->json([
                'code' => '200',
                'status' => 'success',
                'message' => 'Berhasil mendapatkan detail artikel',
                'data' => $article
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'code' => '404',
                'status' => 'error',
                'message' => 'Article not found'
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
