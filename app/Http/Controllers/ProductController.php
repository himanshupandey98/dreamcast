<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $product = Product::with('category')->orderBy('created_at', 'desc')->get();
        $category = Category::pluck('name','id');

        if ($request->ajax()) {
            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => Product::count(),
                'recordsFiltered' => Product::count(),
                'data' => $product
            ]);
        } else {
            return view('product.index', ["product" => $product, 'category'=>$category]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = Validator($request->all(), [
            'name' => 'required|min:3',
            'category_id'=>'required',
            'price'=>'numeric|min:1'
        ]);

        if($validate->errors()->first()){
            return redirect()->back()->withErrors($validate)->withInput();
        }
        try {
            Product::create([
                'name' => $request->input('name'),
                'category_id'=>$request->input('category_id'),
                'price'=>$request->input('price')
            ]);

            return response()->json(['status' => true, 'msg' => 'Product added successfully!']);
        } catch (\Throwable $th) {
            dd($th);
            return response()->json(['status' => false, 'msg' => 'Something went wrong! Please try later again.']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $category = Category::pluck('name','id');
        return view('product.alter', [
            'product' => $product,
            'category'=>$category,
            'title' => "Edit Product",
            'action' => 'PUT',
            'actionUrl' => route('product.update', $product),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validate = Validator($request->all(), [
            'name' => 'required|min:3',
            'category_id'=>'required',
            'price'=>'required|min:1'
        ]);
        if($validate->errors()->first()){
            return redirect()->back()->withErrors($validate)->withInput();
            //return response()->json(['status'=>false ,'msg'=>$validate->errors()->first('name')]);
        }
        $product->update([
            'name'=>$request->input('name'), 
            'category_id'=>$request->input('category_id'),
            'price'=>$request->input('price')
        ]);
        
        return redirect()->route('product.index')->with('success', "Product details updated successfully.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
