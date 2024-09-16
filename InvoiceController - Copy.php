<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    //
    public function index(Request $request)
    {
        $data = [
            [
                'quantity' => 2,
                'description' => 'Gold',
                'price' => '$500.00'
            ],
            [
                'quantity' => 3,
                'description' => 'Silver',
                'price' => '$300.00'
            ],
            [
                'quantity' => 5,
                'description' => 'Platinum',
                'price' => '$200.00'
            ]
        ];
       
        $pdf = Pdf::loadView('invoice', ['data' => $data]);
       
        return $pdf->download();
    }
}
