<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\ProductsImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::all();

        return view('import',compact('products'));
    }

    public function import(Request $request) 
    {
        $file = $request->file;
        $ext = $file->getClientOriginalExtension();
        $fileName = time().'.'.$ext;
        $file->move(public_path().'/uploads/',$fileName); 

        $path = public_path().'/uploads/'.$fileName;
       Excel::import(new ProductsImport,  $path);
        
        return redirect(route('products.import'))->with('success', 'All good!');
    }
}
