<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservasi Ditolak ❌</title>
</head>
<body style="font-family: Arial, sans-serif; line-height:1.6; color:#333;">
    <h2>Halo, {{ $reservation->user->name }} 👋</h2>

    <p>
        Mohon maaf, reservasi Anda telah <strong style="color:red;">DITOLAK</strong> ❌
    </p>

    <p>Berikut detail reservasi:</p>

    <ul style="list-style:none; padding:0; margin:0;">
        <li><strong>📌 Ruangan:</strong> {{ $reservation->room->name }}</li>
        <li><strong>📅 Hari & Tanggal:</strong> {{ $reservation->day_of_week }}, {{ $reservation->date->format('d M Y') }}</li>
        <li><strong>⏰ Waktu:</strong> {{ substr($reservation->start_time,0,5) }} - {{ substr($reservation->end_time,0,5) }}</li>
        <li><strong>📝 Alasan Penolakan:</strong> {{ $reason ?? 'Tidak ada alasan diberikan.' }}</li>
    </ul>

    <p style="margin-top:20px;">
        Silakan ajukan ulang reservasi dengan jadwal yang berbeda 🙏
    </p>
</body>
</html>
