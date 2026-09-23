<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->query('limit', 10);

        $query = Product::query();

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Implementasi Cache opsional (simpan selama 60 detik)
        $cacheKey = 'products_' . md5(json_encode($request->all()));
        $products = Cache::remember($cacheKey, 60, function () use ($query, $limit) {
            return $query->paginate($limit); // Paginate otomatis membaca parameter "page"
        });

        return response()->json($products);
    }

    public function show($id)
    {
        $product = Cache::remember('product_' . $id, 60, function () use ($id) {
            return Product::find($id);
        });

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404); // Sesuai instruksi return 404 JSON
        }

        return response()->json($product);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'price' => 'required|numeric',
            'category' => 'required|string',
            'images' => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400); // Sesuai instruksi return 400
        }

        $user = $request->user();
        $data = $request->all();
        $data['created_by'] = $user->username;
        $data['created_by_id'] = (string)$user->id;

        $product = Product::create($data);
        return response()->json($product, 201);
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $user = $request->user();
        $data = $request->all();
        $data['updated_by'] = $user->username;
        $data['updated_by_id'] = (string)$user->id;

        $product->update($data);
        return response()->json($product);
    }

    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $product->delete();
        return response()->json(['message' => 'Product deleted successfully']);
    }
}
