<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $category = Category::with('products')->orderBy('created_at', 'desc')->get();
        if ($request->ajax()) {
            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => Category::count(),
                'recordsFiltered' => Category::count(),
                'data' => $category
            ]);
        } else {
            return view('index', ["categories" => $category]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = Validator($request->all(), [
            'name' => 'required|min:3|unique:categories',
        ]);
        if($validate->errors()->first()){
            return redirect()->back()->withErrors($validate)->withInput();
            //return response()->json(['status'=>false ,'msg'=>$validate->errors()->first('name')]);
        }
        try {
            Category::create([
                'name' => $request->input('name')
            ]);
            return response()->json(['status' => true, 'msg' => 'Category added successfully!']);
        } catch (\Throwable $th) {
            dd($th);
            return response()->json(['status' => false, 'msg' => 'Something went wrong! Please try later again.']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        return view('alter', [
            'category' => $category,
            'title' => "Edit Category",
            'action' => 'PUT',
            'actionUrl' => route('category.update', $category),
        ]);

        // try {
        //     $data = Category::findOrFail($category->id);
        //     $data['action'] = route('category.update',$category->id);

        //     return response()->json(['status' => true, 'data'=>$data, 'msg' => 'Category Fetched successfully!']);
        // } catch (\Throwable $th) {
        //     return response()->json(['status' => false, 'msg' => 'Something went wrong! Please try later again.']);
        // }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $validate = Validator($request->all(), [
            'name' => 'required|min:3|unique:categories,name,'.$category->id,
        ]);
        if($validate->errors()->first()){
            return redirect()->back()->withErrors($validate)->withInput();
            //return response()->json(['status'=>false ,'msg'=>$validate->errors()->first('name')]);
        }
        $category->update(['name'=>$request->input('name')]);
        
        return redirect()->route('category.index')->with('success', "Category updated successfully.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
