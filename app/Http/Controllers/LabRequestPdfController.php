<?php

namespace App\Http\Controllers;

use App\Models\LabRequest;
use Barryvdh\DomPDF\Facade\Pdf;

class LabRequestPdfController extends Controller
{
    public function export()
    {
        $labRequests = LabRequest::all();

        $pdf = Pdf::loadView('pdf.lab-requests', compact('labRequests'));

        return $pdf->download('lab-requests.pdf');
    }
}