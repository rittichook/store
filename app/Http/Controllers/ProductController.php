<?php

namespace App\Http\Controllers;


use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::all();

        return response()->json(['data' => $products], 200);
    }
    public function show($id)
    {
        $product = Product::where('image', $id)->first();

        if ($product) {
            $imageUrl = url('uploads/products/' . $product->image);

            // Redirect to the image URL, which will open in a new tab
            return redirect()->away($imageUrl);
        }
        // Handle the case where the product is not found (e.g., return a 404 response)
        abort(404, 'not found');
    }

    public function store(Request $request)
    {
        // Validate the request using Validator facade
        $validator = Validator::make($request->all(), [
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Your existing code to store the product
        $product = new Product;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time().'.'.$image->getClientOriginalExtension();
            $path = public_path('uploads/products/');
            $image->move($path, $imageName);
            $product->image = $imageName;

            // Get the URL of the uploaded image
            $imageUrl = url('uploads/products/' . $imageName);
        }

        $product->save();

        // Return a JSON response with the image URL
        return response()->json([
            'message' => 'Created successfully',
            'image_url' => isset($imageUrl) ? $imageUrl : null,
        ], 201, [], JSON_UNESCAPED_SLASHES);



    }
        public function update(Request $request, $id)
    {
        // $request->validate([
        //     'name' => 'required',
        //     'description' => 'required',
        //     'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
        // ]);

        // $product = Product::find($id);

        // if (!$product) {
        //     return response()->json(['message' => 'Product not found'], 404);
        // }

        // $product->name = $request->name;
        // $product->description = $request->description;

        // if ($request->hasFile('image')) {
        //     $image = $request->file('image');
        //     $imageName = time().'.'.$image->getClientOriginalExtension();
        //     $path = public_path('uploads/products/');
        //     $image->move($path, $imageName);
        //     $product->image = $imageName;
        // }

        // $product->save();

        // return response()->json(['message' => 'Product updated successfully', 'data' => $product], 200);
    }

    public function destroy($id)
    {
        $product = Product::where('image', $id)->first();

        if (!$product) {
            return response()->json(['message' => 'not found'], 404);
        }

        // Delete the image file if it exists
        if ($product->image) {
            $imagePath = public_path('uploads/products/') . $product->image;
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $product->delete();

        return response()->json(['message' => 'Deleted successfully'], 200);
    }
}


