<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservasi Dibatalkan</title>
</head>
<body style="font-family: Arial, sans-serif; line-height:1.6; color:#333;">
    <h2>Halo Admin 👋</h2>

    <p>Seorang user telah <strong style="color:red;">Membatalkan</strong> reservasi dengan detail berikut:</p>

    <ul style="list-style:none; padding:0; margin:0;">
        <li><strong>👤 User:</strong> {{ $reservation->user->name }} ({{ $reservation->user->email }})</li>
        <li><strong>📌 Ruangan:</strong> {{ $reservation->room->name }}</li>
        <li><strong>📅 Hari:</strong> {{ $reservation->day_of_week }}</li>
        <li><strong>📆 Tanggal:</strong> {{ $reservation->date->format('d M Y') }}</li>
        <li><strong>⏰ Waktu:</strong> {{ substr($reservation->start_time,0,5) }} - {{ substr($reservation->end_time,0,5) }}</li>
        <li><strong>📝 Alasan Pembatalan:</strong> {{ $reservation->reason ?? '-' }}</li>
    </ul>

    <p style="margin-top:20px;">
        Mohon untuk menindaklanjuti jika diperlukan.<br>
        Terima kasih 🙏
    </p>
</body>
</html>
