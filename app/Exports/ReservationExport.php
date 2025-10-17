<?php

namespace App\Exports;

use App\Models\Reservation;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ReservationExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;
    public function __construct($startDate = null, $endDate = null)
    {
        $this->startDate = $startDate;

        $this->endDate = $endDate;
    }

    public function collection()
    {
        $query = Reservation::with(['user', 'room'])->orderBy('date', 'desc');

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('date', [$this->startDate, $this->endDate]);
        }
        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama Pengguna',
            'Ruangan',
            'Tanggal',
            'Waktu Mulai',
            'Waktu Selesai',
            'Status',
            'Alasan',
        ];
    }

    public function map($reservation): array
    {
        return [
            $reservation->id,
            $reservation->user->name ?? '-',
            $reservation->room->name ?? '-',
            $reservation->date,
            $reservation->start_time,
            $reservation->end_time,
            ucfirst($reservation->status),
            $reservation->reason ?? '-',
        ];
    }
}