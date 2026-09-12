<?php

namespace App\Services;

use App\Models\Perawat;

class CvPdfService
{
    public function generate(Perawat $perawat): string
    {
        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            throw new \RuntimeException('Paket PDF belum terpasang. Jalankan: composer require barryvdh/laravel-dompdf');
        }

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('perawat.cv_pdf', compact('perawat'))
            ->setPaper('a4')
            ->output();
    }
}
