<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\DataLegalitas;
use App\Models\Lamaran;
use App\Models\Lowongan;
use App\Models\Perawat;
use App\Models\Rekrutmen;
use App\Models\RiwayatPekerjaan;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function perawatCsv(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'nik', 'nama', 'status_kesiapan', 'status_ketenagakerjaan', 'status_str', 'nomor_str', 'str_mulai', 'str_sampai', 'sisa_hari_str',
            ]);

            Perawat::with('dataLegalitas')
                ->orderBy('nama')
                ->chunk(500, function ($perawats) use ($handle) {
                    foreach ($perawats as $p) {
                        $strMulai = optional($p->dataLegalitas?->str_berlaku_mulai)->format('Y-m-d');
                        $strSampai = optional($p->dataLegalitas?->str_berlaku_sampai)->format('Y-m-d');
                        $sisaHari = null;
                        if ($p->dataLegalitas?->str_berlaku_sampai) {
                            $sisaHari = now()->startOfDay()->diffInDays($p->dataLegalitas->str_berlaku_sampai, false);
                        }

                        fputcsv($handle, [
                            $p->nik,
                            $p->nama,
                            $p->status_kesiapan,
                            $p->status_ketenagakerjaan,
                            $p->dataLegalitas?->status_str,
                            $p->dataLegalitas?->nomor_str,
                            $strMulai,
                            $strSampai,
                            $sisaHari,
                        ]);
                    }
                });

            fclose($handle);
        }, 'laporan-perawat.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function lowonganCsv(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'mitra_pengguna', 'judul', 'status', 'tipe_penempatan', 'negara_penempatan', 'provinsi_penempatan', 'kota_penempatan', 'published_at',
            ]);

            Lowongan::with('mitraPengguna')
                ->latest()
                ->chunk(500, function ($lowongan) use ($handle) {
                    foreach ($lowongan as $item) {
                        fputcsv($handle, [
                            $item->mitraPengguna?->nama,
                            $item->judul,
                            $item->status,
                            $item->tipe_penempatan,
                            $item->negara_penempatan,
                            $item->provinsi_penempatan,
                            $item->kota_penempatan,
                            optional($item->published_at)->format('Y-m-d H:i:s'),
                        ]);
                    }
                });

            fclose($handle);
        }, 'laporan-lowongan.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function rekrutmenCsv(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['mitra_pengguna', 'perawat', 'status', 'locked_at', 'hired_at']);

            Rekrutmen::with(['mitraPengguna', 'perawat'])
                ->latest()
                ->chunk(500, function ($items) use ($handle) {
                    foreach ($items as $item) {
                        fputcsv($handle, [
                            $item->mitraPengguna?->nama,
                            $item->perawat?->nama,
                            $item->status,
                            optional($item->locked_at)->format('Y-m-d H:i:s'),
                            optional($item->hired_at)->format('Y-m-d H:i:s'),
                        ]);
                    }
                });

            fclose($handle);
        }, 'laporan-rekrutmen.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function lamaranCsv(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['mitra_pengguna', 'lowongan', 'perawat', 'status', 'created_at']);

            Lamaran::with(['mitraPengguna', 'lowongan', 'perawat'])
                ->latest()
                ->chunk(500, function ($items) use ($handle) {
                    foreach ($items as $item) {
                        fputcsv($handle, [
                            $item->mitraPengguna?->nama,
                            $item->lowongan?->judul,
                            $item->perawat?->nama,
                            $item->status,
                            $item->created_at?->format('Y-m-d H:i:s'),
                        ]);
                    }
                });

            fclose($handle);
        }, 'laporan-lamaran.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function legalitasCsv(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'nik', 'nama', 'nomor_str', 'status_str', 'str_mulai', 'str_sampai', 'sumber_validasi', 'regulator_organisasi', 'terakhir_validasi',
            ]);

            DataLegalitas::with(['perawat', 'regulatorOrganisasi'])
                ->latest('terakhir_validasi_at')
                ->chunk(500, function ($items) use ($handle) {
                    foreach ($items as $item) {
                        fputcsv($handle, [
                            $item->perawat?->nik,
                            $item->perawat?->nama,
                            $item->nomor_str,
                            $item->status_str,
                            optional($item->str_berlaku_mulai)->format('Y-m-d'),
                            optional($item->str_berlaku_sampai)->format('Y-m-d'),
                            $item->sumber_validasi,
                            $item->regulatorOrganisasi?->nama,
                            optional($item->terakhir_validasi_at)->format('Y-m-d H:i:s'),
                        ]);
                    }
                });

            fclose($handle);
        }, 'laporan-legalitas.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function riwayatPekerjaanCsv(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'perawat', 'mitra_pengguna', 'posisi', 'status_pekerjaan', 'verifikasi_status', 'sumber_data', 'verified_at',
            ]);

            RiwayatPekerjaan::with(['perawat', 'mitraPengguna'])
                ->latest()
                ->chunk(500, function ($items) use ($handle) {
                    foreach ($items as $item) {
                        fputcsv($handle, [
                            $item->perawat?->nama,
                            $item->mitraPengguna?->nama,
                            $item->posisi,
                            $item->status_pekerjaan,
                            $item->verifikasi_status,
                            $item->sumber_data,
                            optional($item->verified_at)->format('Y-m-d H:i:s'),
                        ]);
                    }
                });

            fclose($handle);
        }, 'laporan-riwayat-pekerjaan.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function auditLogsCsv(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'waktu', 'user', 'role', 'action', 'subject_type', 'subject_id', 'meta',
            ]);

            AuditLog::with('user')
                ->latest()
                ->chunk(500, function ($items) use ($handle) {
                    foreach ($items as $item) {
                        fputcsv($handle, [
                            $item->created_at?->format('Y-m-d H:i:s'),
                            $item->user?->name,
                            $item->user?->role,
                            $item->action,
                            $item->subject_type,
                            $item->subject_id,
                            json_encode($item->meta, JSON_UNESCAPED_SLASHES),
                        ]);
                    }
                });

            fclose($handle);
        }, 'audit-logs.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
