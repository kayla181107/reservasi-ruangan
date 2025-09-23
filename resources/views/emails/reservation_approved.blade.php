<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservasi Disetujui</title>
</head>
<body>
    <h2>Halo, {{ $reservation->user->name }} 👋</h2>

    <p>Reservasi Anda telah <strong>DISETUJUI</strong> 🎉</p>

    <ul>
        <li><strong>Ruangan:</strong> {{ $reservation->room->name }}</li>
        <li><strong>Tanggal:</strong> {{ $reservation->date->format('d M Y') }} ({{ $reservation->day_of_week }})</li>
        <li><strong>Waktu:</strong> {{ substr($reservation->start_time,0,5) }} - {{ substr($reservation->end_time,0,5) }}</li>
        <li><strong>Keterangan:</strong> {{ $reservation->description ?? '-' }}</li>
    </ul>

    <p>Silakan gunakan ruangan sesuai jadwal. Terima kasih 🙏</p>
</body>
</html>