<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservasi Disetujui</title>
</head>
<body style="font-family: Arial, sans-serif; line-height:1.6; color:#333;">
    <h2>Halo, {{ $reservation->user->name }} 👋</h2>

    <p>
        Kami dengan senang hati menginformasikan bahwa 
        reservasi Anda telah <strong style="color:green;">DISETUJUI</strong> 🎉
    </p>

    <p>Berikut detail reservasi Anda:</p>

    <ul style="list-style:none; padding:0; margin:0;">
        <li><strong>📌 Ruangan:</strong> {{ $reservation->room->name }}</li>
        <li><strong>📅 Tanggal:</strong> {{ $reservation->date->format('d M Y') }} ({{ $reservation->day_of_week }})</li>
        <li><strong>⏰ Waktu:</strong>  {{ substr($reservation->start_time,0,5) }} - {{ substr($reservation->end_time,0,5) }}</li>
        <li><strong>📝 Keterangan:</strong> {{ $reservation->reason ?? '-' }}</li>
    </ul>

    <p style="margin-top:20px;">
        Mohon hadir dan menggunakan ruangan sesuai jadwal yang telah ditentukan.<br>
        Terima kasih atas kerja sama Anda 🙏
    </p>
</body>
</html>
