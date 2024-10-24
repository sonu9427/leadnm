<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

       // Check if a price range filter is applied
        if ($request->price_range) {
            [$min, $max] = explode('-', $request->price_range);
            $query->whereBetween('total_paid', [(int)$min, (int)$max]);
        }

        // if ($request->min_price !== null && $request->max_price !== null) {
        //     $query->whereBetween('total_paid', [(int)$request->min_price, (int)$request->max_price]);
        // }

        if ($request->ajax()) {
            return datatables()->of($query)
                ->addColumn('category_name', function ($row) {
                    return $row->category->category_name ?? 'N/A';
                })
                ->make(true);
        }

        return view('product');
    }
}
