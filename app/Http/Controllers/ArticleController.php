<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function getTop5()
    {
        try {
            $dataArticle = Article::select('id', 'title', 'image')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

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
                    'image' => 'required|string',
                    'content' => 'required|string',
                ]);
            } catch (\Illuminate\Validation\ValidationException $e) {
                return response()->json([
                    'code' => '400',
                    'status' => 'error',
                    'message' => 'Invalid input data.'
                ], 400);
            }

            $article = Article::create([
                'title' => $request->title,
                'content' => $request->content,
                'image' => $request->image
            ]);

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
            $dataArticle = Article::select('id', 'title', 'image')
                ->orderBy('created_at', 'desc')
                ->get();

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
