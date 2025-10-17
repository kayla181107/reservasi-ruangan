<?php

namespace App\Exports;

use App\Models\Reservation;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReservationExport implements FromCollection, WithHeadings, WithMapping
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate = null, $endDate = null)
    {
        $this->startDate = $startDate;
        $this->endDate   = $endDate;
    }

    // ✅ WAJIB ADA
    public function collection(): Collection
{
    $query = Reservation::with(['user', 'room'])->orderBy('date', 'asc');

    if ($this->startDate && $this->endDate) {
        $query->whereBetween('date', [$this->startDate, $this->endDate]);
    }

    return $query->get();
}


    public function headings(): array
    {
        return [
            'Tanggal',
            'Hari',
            'Ruangan',
            'Nama Karyawan',
            'Waktu Mulai',
            'Waktu Selesai',
            'Status',
            'Alasan'
        ];
    }

    public function map($reservation): array
    {
        return [
            Carbon::parse($reservation->date)->format('d-m-Y'),
            $reservation->day_of_week,
            $reservation->room->name ?? '-',
            $reservation->user->name ?? '-',
            $reservation->start_time,
            $reservation->end_time,
            ucfirst($reservation->status),
            $reservation->reason ?? '-',
        ];
    }
}