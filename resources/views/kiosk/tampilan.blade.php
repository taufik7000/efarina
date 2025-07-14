<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiosk Absensi - Efarina TV</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');
        html, body {
            height: 100%;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Roboto', sans-serif;
            background-color: #eef2f7;
            color: #333;
        }
        .kiosk-panel {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px Dpx rgba(0, 0, 0, 0.05);
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        .kiosk-panel h1 {
            margin: 0 0 10px 0;
            font-size: 2.2em;
        }
        #jam {
            font-size: 4.5em;
            font-weight: 700;
            margin-bottom: 20px;
            color: #2c3e50;
        }
        #qrcode-container {
            min-height: 300px; /* Memberi ruang agar layout tidak "loncat" saat gambar QR dimuat */
            display: flex;
            justify-content: center;
            align-items: center;
        }
        #qrcode-container img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>

    <div class="kiosk-panel">
        <h1>Silakan Pindai untuk Absen</h1>
        <div id="jam"></div>
        <div id="qrcode-container">
            <img id="qrcode-image" src="{{ route('kiosk.qrcode') }}" alt="QR Code Absensi">
        </div>
        <p>QR Code akan diperbarui secara otomatis.</p>
    </div>

    <script>
        /**
         * Fungsi untuk menampilkan jam lokal secara real-time.
         */
        function tampilkanJam() {
            const sekarang = new Date();
            // Format waktu ke format Indonesia (HH:MM:SS)
            document.getElementById('jam').textContent = sekarang.toLocaleTimeString('id-ID');
        }

        /**
         * Fungsi untuk memperbarui gambar QR Code dengan menambahkan parameter timestamp
         * untuk mencegah browser menggunakan gambar dari cache.
         */
        function refreshQrCode() {
            const qrImage = document.getElementById('qrcode-image');
            // Menambahkan timestamp sebagai query parameter untuk membuat URL unik
            qrImage.src = "{{ route('kiosk.qrcode') }}?t=" + new Date().getTime();
        }

        // Menjalankan fungsi saat halaman selesai dimuat
        document.addEventListener('DOMContentLoaded', () => {
            tampilkanJam(); // Tampilkan jam pertama kali
            setInterval(tampilkanJam, 1000); // Perbarui jam setiap detik
            setInterval(refreshQrCode, 15000); // Perbarui QR code setiap 15 detik
        });
    </script>

</body>
</html>