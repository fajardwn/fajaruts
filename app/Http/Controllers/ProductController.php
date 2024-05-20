<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;


class ProductController extends Controller
{
    public function store(Request $request)
    {
        // $data = JWT::decode($request->bearerToken(), new Key(env('JWT_SECRET_KEY'), 'HS256'));
        // $user = User::find($data->id);

        // User is authenticated, proceed with validation and data creation
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|integer',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'category_id' => 'required|string|max:255', // Accept category as name or id
            'expired_at' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages())->setStatusCode(422);
        }

        $validated = $validator->validated();

        // Handle category input (name or ID)
        $categoryInput = $validated['category_id'];
        if (is_numeric($categoryInput)) {
            // If category is numeric, treat it as an ID
            $category = Category::find($categoryInput);
        } else {
            // Otherwise, treat it as a name
            $category = Category::firstOrCreate(['name' => $categoryInput]);
        }

        if (!$category) {
            return response()->json(['message' => 'Invalid category'], 422);
        }

        $validated['category_id'] = $category->id;

        // Retrieve user ID and email
        // $userId = $user->id;
        // $userEmail = $user->email;

        // // Ensure user ID is not null before proceeding
        // if (!$userId) {
        //     return response()->json(['message' => 'User ID not found'], 401);
        // }

        
        // Add user email as modified_by
        // $validated['modified_by'] = $userEmail;
        $validated['modified_by'] = 'tes@gmail.com';
        
        // Handle image upload
        if ($request->hasFile('image')) {
            $filePath = $request->file('image')->store('images', 'public');
            $validated['image'] = $filePath;
        }
        // Create the product
        $product = Product::create($validated);

        return response()->json([
            'message' => "Success",
            'data' => $product
        ], 200);
    }

    public function index()
    {
        return response()->json(Product::with('category')->get(), 200);
    }


    public function update(Request $request, $id)
{
    // $data = JWT::decode($request->bearerToken(), new Key(env('JWT_SECRET_KEY'), 'HS256'));
    // $user = User::find($data->id);

    $validator = Validator::make($request->all(), [
        'name' => 'sometimes|string|max:255',
        'description' => 'sometimes|string',
        'price' => 'sometimes|integer',
        'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        'category_id' => 'sometimes|string|max:255', // Accept category as name or id
        'expired_at' => 'sometimes|date',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->messages())->setStatusCode(422);
    }

    $validated = $validator->validated();

    if (isset($validated['category_id'])) {
        $categoryInput = $validated['category_id'];
        if (is_numeric($categoryInput)) {
            // If category is numeric, treat it as an ID
            $category = Category::find($categoryInput);
        } else {
            // Otherwise, treat it as a name
            $category = Category::firstOrCreate(['name' => $categoryInput]);
        }

        if (!$category) {
            return response()->json(['message' => 'Invalid category'], 422);
        }

        $validated['category_id'] = $category->id;
    }

    // Retrieve user ID and email
    // $userId = $user->id;
    // $userEmail = $user->email;

    // // Ensure user ID is not null before proceeding
    // if (!$userId) {
    //     return response()->json(['message' => 'User ID not found'], 401);
    // }

    // Add user email as modified_by
    // $validated['modified_by'] = $userEmail;
    $validated['modified_by'] = 'tes@gmail.com';

    $product = Product::find($id);

    if ($product) {
        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if (!is_null($product->image)) {
                Storage::disk('public')->delete($product->image);
                // Store new image
                $filePath = $request->file('image')->store('images', 'public');
                $validated['image'] = $filePath;
            } else {
                
                $filePath = $request->file('image')->store('images', 'public');
                $validated['image'] = $filePath;
                
            }
        }

        $product->update($validated);
        // $product->save();


        return response()->json([
            'msg' => 'Data ID: ' . $id . ' updated success',
            'data' => $product
        ], 200);
    }

    return response()->json([
        'msg' => 'Data ID: ' . $id . ' not found'
    ], 404);
}



    public function destroy($id)
    {
        $product = Product::find($id);

        if ($product) {
            $product->delete();

            return response()->json([
                'msg' => 'Product ID: ' . $id . 'deleted success'
            ], 200);
        }

        return response()->json([
            'msg' => 'Product ID: ' . $id . ' not found',
        ], 404);
    }
}
