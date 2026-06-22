<?php

namespace App\Exports;

use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Maatwebsite\Excel\Facades\Excel;

abstract class BaseExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
       abstract public function collection(): Collection;
       abstract public function headings(): array;
       abstract public function title(): string;

       // Optional — override kalau perlu custom
       public function styles(Worksheet $sheet): array
       {
              return [
                     1 => ['font' => ['bold' => true]],
              ];
       }

       //     public function downloadPdf(): mixed
       //     {
       //         $data = $this->collection();
       //         $headings = $this->headings();
       //         $title = $this->title();

       //         $pdf = Pdf::loadView('exports.pdf', compact('data', 'headings', 'title'))
       //             ->setPaper('a4', 'landscape');

       //         return $pdf->download($this->filename() . '.pdf');
       //     }

       public function downloadPdf(): mixed
       {
              $data = $this->collection();
              $headings = $this->headings();
              $title = $this->title();

              $pdf = Pdf::loadView('exports.pdf', compact('data', 'headings', 'title'))
                     ->setPaper('a4', 'landscape');

              return $pdf->download($this->filename() . '.pdf');
       }

       public function downloadExcel(): BinaryFileResponse
       {
              return Excel::download($this, $this->filename() . '.xlsx', ExcelFormat::XLSX);
       }

       public function downloadCsv(): BinaryFileResponse
       {
              return Excel::download($this, $this->filename() . '.csv', ExcelFormat::CSV);
       }

       protected function filename(): string
       {
              return strtolower(str_replace(' ', '_', $this->title())) . '_' . now()->format('Ymd');
       }
}
