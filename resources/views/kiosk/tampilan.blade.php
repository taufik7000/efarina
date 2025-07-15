<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiosk Absensi - Efarina TV</title>
    {{-- Memuat script Vite untuk Reverb dan CSS --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
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
        .kehadiran-list {
            margin-top: 30px;
            text-align: left;
            max-height: 200px; /* Batasi tinggi dan buat scrollable */
            overflow-y: auto;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        .kehadiran-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            padding: 8px;
            background-color: #f9f9f9;
            border-radius: 8px;
            opacity: 0;
            animation: fadeIn 0.5s forwards;
        }
        .kehadiran-item .info {
            margin-left: 10px;
        }
        .kehadiran-item .nama { font-weight: 700; }
        .kehadiran-item .jam { font-size: 0.9em; color: #555; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
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

        {{-- Daftar Kehadiran --}}
        <div class="kehadiran-list" id="daftar-hadir">
            <h3 style="margin-top:0; text-align:center;">Kehadiran Hari Ini</h3>
            {{-- Data awal saat halaman dimuat --}}
            @forelse ($kehadiranHariIni as $kehadiran)
                <div class="kehadiran-item" style="opacity:1;">
                    <div class="info">
                        <div class="nama">{{ $kehadiran->pengguna->name }}</div>
                        <div class="jam">Masuk pukul: {{ \Carbon\Carbon::parse($kehadiran->jam_masuk)->format('H:i:s') }}</div>
                    </div>
                </div>
            @empty
                <p id="belum-ada-absen" style="text-align:center; color:#888;">Belum ada yang absen hari ini.</p>
            @endforelse
        </div>
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

        /**
         * Fungsi untuk menambahkan item kehadiran baru ke daftar di UI.
         */
        function tambahKehadiran(data) {
            const daftarHadir = document.getElementById('daftar-hadir');
            const pesanKosong = document.getElementById('belum-ada-absen');

            // Hapus pesan "belum ada absen" jika ada
            if (pesanKosong) {
                pesanKosong.remove();
            }

            // Buat elemen baru
            const itemBaru = document.createElement('div');
            itemBaru.classList.add('kehadiran-item');
            itemBaru.innerHTML = `
                <div class="info">
                    <div class="nama">${data.nama}</div>
                    <div class="jam">Masuk pukul: ${data.jamMasuk}</div>
                </div>
            `;
            
            // Tambahkan ke bagian atas daftar
            daftarHadir.prepend(itemBaru);
        }

        // Menjalankan fungsi saat halaman selesai dimuat
        document.addEventListener('DOMContentLoaded', () => {
            tampilkanJam(); // Tampilkan jam pertama kali
            setInterval(tampilkanJam, 1000); // Perbarui jam setiap detik
            setInterval(refreshQrCode, 15000); // Perbarui QR code setiap 15 detik

            // Mendengarkan Event dari Reverb
            // Pastikan window.Echo sudah dimuat oleh resources/js/app.js
            if (window.Echo) {
                console.log('Echo is available. Listening for events...');
                window.Echo.channel('kiosk')
                    .listen('AbsensiBerhasil', (e) => {
                        console.log('Event AbsensiBerhasil diterima:', e);
                        // Panggil fungsi untuk memperbarui UI
                        tambahKehadiran(e);
                    });
            } else {
                console.error('Echo is not defined. Make sure `npm run dev` is running and app.js is loaded correctly.');
            }
        });
    </script>

</body>
</html>