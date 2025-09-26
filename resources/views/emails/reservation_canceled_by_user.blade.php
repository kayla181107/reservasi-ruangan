<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reservasi Dibatalkan</title>
</head>
<body>
    <h2>Reservasi Dibatalkan oleh User</h2>
    <p>Halo Admin,</p>
    <p>Seorang user telah membatalkan reservasi dengan detail berikut:</p>

    <ul>
        <li>User: {{ $reservation->user->name }} ({{ $reservation->user->email }})</li>
        <li>Ruangan: {{ $reservation->room->name }}</li>
        <li><strong>Hari:</strong> {{ $reservation->day_of_week }}</li>
        <li>Tanggal: {{ $reservation->date }}</li>
        <li>Waktu: {{ $reservation->start_time }} - {{ $reservation->end_time }}</li>
        <li>Alasan Pembatalan: {{ $reservation->reason ?? '-' }}</li>
    </ul>

    <p>Terima kasih.</p>
</body>
</html>
