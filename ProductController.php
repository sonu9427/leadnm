<?php

// app/Http/Controllers/ProductController.php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Store a newly created product and attachments.
     */
    public function store(Request $request)
    {
      //  exit("ffffffffffffffff");
        // Validate the incoming request data
        // $validated = Validator::make($request->all(), [
        //     'name' => 'required|string|max:255',  // Validate product name
        //     'attachments' => 'required|array',   // Ensure attachments are present
        //     'attachments.*.file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240', // Validate file type
        //     'attachments.*.doc_type' => 'required|in:datasheet,setup_guide', // Validate doc_type
        // ]);

        // if ($validated->fails()) {
        //     return response()->json([
        //         'error' => $validated->errors()
        //     ], 422); // Unprocessable Entity
        // }

        // Create the product
        $product = Product::create([
            'name' => $request->name, // Insert product name
        ]);

        // Handle the file uploads and save attachments
        foreach ($request->attachments as $attachment) {
            // Validate if the file is present and is a valid file
            if ($file = $attachment['file']) {
                // Store the file in the 'attachments' directory
                $path = $file->store('attachments', 'public'); // Store in the public disk

                // Create a new attachment record for this product
                Attachment::create([
                    'product_id' => $product->id,
                    'file_path' => $path,
                    'doc_type' => $attachment['doc_type'], // 'datasheet' or 'setup_guide'
                ]);
            }
        }

        // Return success response with the product data
        return response()->json([
            'message' => 'Product and attachments created successfully!',
            'product' => $product,
        ], 201); // Created
    }
}
