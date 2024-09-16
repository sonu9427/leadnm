<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use ZipArchive;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    //
    public function index(Request $request)
    {
        // Data to be included in the PDF
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

        // Load the PDF view and generate the PDF as string
        $pdf = Pdf::loadView('invoice', ['data' => $data]);
        $pdfContent = $pdf->output();

        // Create a temporary file for the zip archive
        $zipFilePath = tempnam(sys_get_temp_dir(), 'invoices') . '.zip';
        $zip = new ZipArchive;

        if ($zip->open($zipFilePath, ZipArchive::CREATE) === TRUE) {
            // Add the PDF content to the zip file in memory
            $zip->addFromString('invoice.pdf', $pdfContent);
            $zip->close();
        }

        // Return the zip file as a download response
        return response()->download($zipFilePath, 'invoices.zip')->deleteFileAfterSend(true);
    }
}

//     public function index1(Request $request)
//     {
//         $data = [
//             [
//                 'quantity' => 2,
//                 'description' => 'Gold',
//                 'price' => '$500.00'
//             ],
//             [
//                 'quantity' => 3,
//                 'description' => 'Silver',
//                 'price' => '$300.00'
//             ],
//             [
//                 'quantity' => 5,
//                 'description' => 'Platinum',
//                 'price' => '$200.00'
//             ]
//         ];

//         $pdf = Pdf::loadView('invoice', ['data' => $data]);

//         // Define file paths
//         $pdfFilePath = storage_path('app/public/invoice.pdf');
//         $zipFilePath = storage_path('app/public/invoices.zip');

//         // Store the generated PDF in the file system
//         Storage::put('public/invoice.pdf', $pdf->output());

//         // Create a zip archive
//         $zip = new ZipArchive;
//         if ($zip->open($zipFilePath, ZipArchive::CREATE) === TRUE) {
//             // Add the PDF file to the zip archive
//             $zip->addFile($pdfFilePath, 'invoice.pdf');
//             $zip->close();
//         }

//         // Return the zip file as a download response
//         return response()->download($zipFilePath)->deleteFileAfterSend(true);
       
//        // $pdf = Pdf::loadView('invoice', ['data' => $data]);
       
//         //return $pdf->download();
//     }
// }
