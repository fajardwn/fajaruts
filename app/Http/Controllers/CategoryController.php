<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages())->setStatusCode(422);
        }

        $validated = $validator->validated();

        $category = Category::create($validated);

        return response()->json([
            'message' => "Success",
            'data' => $category
        ], 201);
    }

    public function index()
    {
        return response()->json(Category::all(), 200);
    }

   
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|max:255|unique:categories,name,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages())->setStatusCode(422);
        }

        $validated = $validator->validated();

        $category = Category::find($id);

        if ($category) {
            $category->update($validated);

            return response()->json([
                'msg' => 'Data ID: ' . $id . ' update success',
                'data' => $category
            ], 200);
        }

        return response()->json([
            'msg' => 'Data ID: ' . $id . ' not found'
        ], 404);
    }

    public function destroy($id)
    {
        $category = Category::find($id);

        if ($category) {
            $category->delete();

            return response()->json([
                'msg' => 'Category ID: ' . $id . ' delete success'
            ], 200);
        }

        return response()->json([
            'msg' => 'Category ID: ' . $id . ' not found',
        ], 404);
    }
}
