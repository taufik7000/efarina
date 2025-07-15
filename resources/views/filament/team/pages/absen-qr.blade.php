<div> {{-- Elemen pembungkus utama untuk Livewire --}}
    <x-filament-panels::page>
        @push('styles')
            <meta name="csrf-token" content="{{ csrf_token() }}">
        @endpush

        <div class="p-6 bg-white rounded-xl shadow-sm dark:bg-gray-800">
            <div id="scanner-container" style="width: 100%; max-width: 500px; margin: auto; position: relative; border-radius: 12px; overflow: hidden; background-color: #000;">
                
                {{-- Area untuk library html5-qrcode merender video kamera belakang --}}
                <div id="qr-reader" style="width: 100%; border: none;"></div>

                {{-- Video untuk menampilkan kamera depan saat selfie, awalnya tersembunyi --}}
                <video id="selfie-camera" playsinline style="width: 100%; height: auto; display: none;"></video>

                {{-- Overlay untuk countdown saat selfie --}}
                <div id="countdown-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); color: white; display: none; justify-content: center; align-items: center; flex-direction: column; font-size: 2em; z-index: 10;">
                    <p>Hadap Kamera Depan</p>
                    <p id="countdown-timer" style="font-size: 3em; font-weight: bold;"></p>
                </div>

                {{-- Canvas tersembunyi untuk proses pengambilan foto --}}
                <canvas id="photo-canvas" style="display: none;"></canvas>
            </div>

            <div id="hasil-scan" class="mt-4 text-center font-semibold"></div>

            <div id="start-button-container" class="flex justify-center mt-4">
                <x-filament::button id="start-scan-btn">
                    Mulai Pindai Absensi
                </x-filament::button>
            </div>
        </div>

        <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const qrReaderElement = document.getElementById('qr-reader');
            const selfieVideoElement = document.getElementById('selfie-camera');
            const startButton = document.getElementById('start-scan-btn');
            const startButtonContainer = document.getElementById('start-button-container');
            const resultElement = document.getElementById('hasil-scan');
            const countdownOverlay = document.getElementById('countdown-overlay');
            const countdownTimer = document.getElementById('countdown-timer');

            const html5QrCode = new Html5Qrcode("qr-reader");

            const showMessage = (message, type = 'info') => {
                const colors = { success: 'text-green-500', error: 'text-red-500', info: 'text-blue-500' };
                resultElement.innerHTML = `<span class="${colors[type]}">${message}</span>`;
                if (type === 'error' && startButtonContainer) {
                    startButtonContainer.style.display = 'flex';
                }
            };

            const getCsrfToken = () => document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const onScanSuccess = (decodedText, decodedResult) => {
                html5QrCode.stop().then(() => {
                    qrReaderElement.style.display = 'none';
                    takeSelfie(decodedText);
                }).catch(err => console.error("Gagal menghentikan scanner.", err));
            };

            const takeSelfie = async (decodedText) => {
                showMessage('Scan berhasil! Siap untuk foto...', 'info');
                try {
                    const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
                    selfieVideoElement.srcObject = stream;
                    selfieVideoElement.style.display = 'block';
                    await selfieVideoElement.play();

                    countdownOverlay.style.display = 'flex';
                    let countdown = 3;
                    countdownTimer.textContent = countdown;

                    const timer = setInterval(async () => {
                        countdown--;
                        countdownTimer.textContent = countdown > 0 ? countdown : '📸';
                        if (countdown <= 0) {
                            clearInterval(timer);
                            
                            const canvas = document.getElementById('photo-canvas');
                            canvas.width = selfieVideoElement.videoWidth;
                            canvas.height = selfieVideoElement.videoHeight;
                            canvas.getContext('2d').drawImage(selfieVideoElement, 0, 0);
                            const fotoBase64 = canvas.toDataURL('image/jpeg', 0.8);
                            
                            stream.getTracks().forEach(track => track.stop());
                            selfieVideoElement.style.display = 'none';
                            countdownOverlay.style.display = 'none';
                            
                            try {
                                const deviceInfo = navigator.userAgent;
                                const position = await new Promise((resolve, reject) => {
                                    navigator.geolocation.getCurrentPosition(resolve, reject, {
                                        enableHighAccuracy: true, timeout: 15000, maximumAge: 0
                                    });
                                });
                                const location = `${position.coords.latitude},${position.coords.longitude}`;
                                kirimAbsensi(decodedText, fotoBase64, location, deviceInfo);
                            } catch (geoError) {
                                showMessage('Gagal mengambil lokasi. Izinkan akses lokasi.', 'error');
                            }
                        }
                    }, 1000);
                } catch (err) {
                    showMessage('Gagal membuka kamera depan. Mengirim data tanpa foto.', 'error');
                    kirimAbsensi(decodedText, null, null, null); // Kirim tanpa foto jika gagal
                }
            };
            
            const startScanning = () => {
                startButtonContainer.style.display = 'none';
                showMessage('Arahkan kamera ke QR Code...', 'info');

                html5QrCode.start(
                    { facingMode: "environment" },
                    { fps: 10, qrbox: { width: 250, height: 250 } },
                    onScanSuccess,
                    (errorMessage) => { /* Abaikan */ }
                ).catch((err) => {
                    showMessage('Kamera tidak ditemukan atau izin ditolak.', 'error');
                });
            };

            const kirimAbsensi = async (token, foto, lokasi, infoPerangkat) => {
                // Jangan kirim jika data lokasi tidak ada (kecuali saat foto juga gagal)
                if (!lokasi && foto) {
                    showMessage('Data lokasi wajib ada. Gagal mengirim.', 'error');
                    return;
                }
                
                showMessage('Memvalidasi & mengirim data...', 'info');
                try {
                    const response = await fetch(`${window.location.origin}/api/absensi/validasi`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': getCsrfToken()
                        },
                        body: JSON.stringify({ token, foto, lokasi, info_perangkat: infoPerangkat })
                    });
                    const data = await response.json();
                    if (response.ok) {
                        showMessage(data.pesan || 'Absensi berhasil!', 'success');
                        setTimeout(() => window.location.reload(), 2000);
                    } else {
                        showMessage(data.pesan || 'Terjadi kesalahan.', 'error');
                    }
                } catch (error) {
                    showMessage('Gagal terhubung ke server.', 'error');
                }
            };

            startButton.addEventListener('click', startScanning);
        });
        </script>
    </x-filament-panels::page>
</div>