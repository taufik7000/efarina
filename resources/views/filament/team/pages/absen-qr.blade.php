<x-filament-panels::page>
    {{-- Tambahkan meta tag CSRF --}}
    @push('styles')
        <meta name="csrf-token" content="{{ csrf_token() }}">
    @endpush

    <div class="p-6 bg-white rounded-xl shadow-sm dark:bg-gray-800">
        <div id="scanner-container" style="width: 100%; max-width: 500px; margin: auto;">
            <div id="qr-reader" style="width: 100%;"></div>
        </div>

        <div id="hasil-scan" class="mt-4 text-center font-semibold"></div>

        <div class="flex justify-center mt-4">
            <x-filament::button id="start-scan-btn">
                Mulai Pindai
            </x-filament::button>
        </div>
    </div>

    {{-- Memuat pustaka pemindai QR dari CDN --}}
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    {{-- Masukkan JavaScript yang sudah diperbaiki dari artifact sebelumnya --}}
    <script>
  document.addEventListener('DOMContentLoaded', function () {
    const readerElement = document.getElementById('qr-reader');
    const resultElement = document.getElementById('hasil-scan');
    const startButton = document.getElementById('start-scan-btn');
    let html5QrCode;
    let isScanning = false;

    // Helper function untuk menampilkan pesan dengan styling
    function showMessage(message, type = 'info') {
        const colors = {
            success: 'text-green-500',
            error: 'text-red-500',
            warning: 'text-yellow-500',
            info: 'text-blue-500'
        };
        resultElement.innerHTML = `<span class="${colors[type]}">${message}</span>`;
    }

    // Helper function untuk log error ke console
    function logError(context, error, additionalInfo = {}) {
        console.group(`🔴 Error in ${context}`);
        console.error('Error:', error);
        console.log('Error Name:', error.name);
        console.log('Error Message:', error.message);
        console.log('Additional Info:', additionalInfo);
        console.log('Timestamp:', new Date().toISOString());
        console.groupEnd();
    }

    // Fungsi untuk cek dukungan kamera
    async function checkCameraSupport() {
        console.log('🔍 Checking camera support...');
        
        // Cek apakah browser mendukung getUserMedia
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            throw new Error('Browser tidak mendukung akses kamera');
        }

        // Cek apakah dalam HTTPS atau localhost
        const isSecure = location.protocol === 'https:' || location.hostname === 'localhost' || location.hostname === '127.0.0.1';
        if (!isSecure) {
            console.warn('⚠️ Kamera mungkin tidak bekerja di HTTP. Gunakan HTTPS atau localhost.');
        }

        // Cek daftar kamera yang tersedia
        try {
            const devices = await navigator.mediaDevices.enumerateDevices();
            const cameras = devices.filter(device => device.kind === 'videoinput');
            
            console.log('📷 Available cameras:', cameras.length);
            cameras.forEach((camera, index) => {
                console.log(`Camera ${index + 1}:`, {
                    label: camera.label || 'Unknown camera',
                    deviceId: camera.deviceId
                });
            });

            if (cameras.length === 0) {
                throw new Error('Tidak ada kamera yang terdeteksi pada perangkat ini');
            }

            return cameras;
        } catch (error) {
            console.error('Error enumerating devices:', error);
            throw new Error('Gagal mengakses daftar kamera');
        }
    }

    // Fungsi untuk request permission kamera
    async function requestCameraPermission() {
        console.log('🔐 Requesting camera permission...');
        
        try {
            // Request permission dengan constraints yang lebih flexible
            const stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: { ideal: "environment" }, // Prefer rear camera
                    width: { ideal: 640 },
                    height: { ideal: 480 }
                }
            });

            console.log('✅ Camera permission granted');
            
            // Stop stream karena kita hanya test permission
            stream.getTracks().forEach(track => track.stop());
            
            return true;
        } catch (error) {
            console.error('❌ Camera permission denied or failed:', error);
            throw error;
        }
    }

    // Helper function untuk mendapatkan CSRF token
    function getCsrfToken() {
        const metaToken = document.querySelector('meta[name="csrf-token"]');
        if (metaToken) {
            return metaToken.getAttribute('content');
        }
        
        const cookieMatch = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
        if (cookieMatch) {
            return decodeURIComponent(cookieMatch[1]);
        }
        
        return null;
    }

    // Fungsi saat QR berhasil di-scan
    async function onScanSuccess(decodedText, decodedResult) {
        if (isScanning) return; // Prevent multiple scans
        isScanning = true;

        try {
            console.log('🎯 QR Code detected:', decodedText.substring(0, 15) + '...');
            
            // Stop scanner
            await html5QrCode.stop();
            showMessage('Memvalidasi token...', 'info');

            // Validasi token format
            if (!decodedText || decodedText.length < 10) {
                showMessage('QR Code tidak valid. Format token salah.', 'error');
                setTimeout(restartScanner, 3000);
                return;
            }

            // Dapatkan CSRF token
            const csrfToken = getCsrfToken();
            if (!csrfToken) {
                showMessage('Token keamanan tidak ditemukan. Silakan refresh halaman.', 'error');
                return;
            }

            // Kirim request ke server
            const response = await fetch(window.location.origin + '/api/absensi/validasi', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ token: decodedText }),
            });

            const data = await response.json();

            if (response.ok) {
                showMessage(data.pesan || 'Absensi berhasil!', 'success');
                setTimeout(() => window.location.reload(), 2000);
            } else {
                const pesanError = data.pesan || 'Terjadi kesalahan pada server.';
                showMessage(pesanError, 'error');
                setTimeout(restartScanner, 3000);
            }

        } catch (error) {
            let errorMessage = 'Terjadi kesalahan tidak dikenal.';
            
            if (error.name === 'TypeError' && error.message.includes('fetch')) {
                errorMessage = 'Gagal terhubung ke server. Periksa koneksi internet.';
            } else if (error.message) {
                errorMessage = error.message;
            }

            showMessage(errorMessage, 'error');
            setTimeout(restartScanner, 3000);
        } finally {
            isScanning = false;
        }
    }

    // Fungsi saat QR scan gagal (ini akan dipanggil sangat sering, jadi kita filter)
    function onScanFailure(error) {
        // Hanya log error yang penting
        if (!error.includes('QR code parse error') && !error.includes('No QR code found')) {
            console.warn('QR Scan Warning:', error);
        }
    }

    // Fungsi untuk restart scanner
    function restartScanner() {
        startButton.style.display = 'block';
        showMessage('Siap untuk scan ulang', 'info');
    }

    // Fungsi untuk start scanning dengan berbagai fallback
    async function startScanning() {
        try {
            showMessage('Mengecek dukungan kamera...', 'info');
            
            // 1. Cek dukungan kamera
            await checkCameraSupport();
            
            showMessage('Meminta izin kamera...', 'info');
            
            // 2. Request permission
            await requestCameraPermission();
            
            showMessage('Memulai scanner...', 'info');
            
            // 3. Initialize HTML5-QRCode
            html5QrCode = new Html5Qrcode("qr-reader");
            
            // 4. Konfigurasi scanning yang lebih robust
            const qrCodeSuccessCallback = onScanSuccess;
            const qrCodeErrorCallback = onScanFailure;
            
            // 5. Try different camera configurations
            const configs = [
                // Config 1: Environment camera (rear camera)
                {
                    constraints: { facingMode: "environment" },
                    config: { fps: 10, qrbox: { width: 250, height: 250 } }
                },
                // Config 2: Any camera
                {
                    constraints: { facingMode: "user" },
                    config: { fps: 10, qrbox: { width: 250, height: 250 } }
                },
                // Config 3: Default camera dengan constraint minimal
                {
                    constraints: true,
                    config: { fps: 5, qrbox: 200 }
                }
            ];

            let scannerStarted = false;

            for (let i = 0; i < configs.length && !scannerStarted; i++) {
                try {
                    console.log(`🔄 Trying camera config ${i + 1}:`, configs[i]);
                    
                    await html5QrCode.start(
                        configs[i].constraints,
                        configs[i].config,
                        qrCodeSuccessCallback,
                        qrCodeErrorCallback
                    );
                    
                    scannerStarted = true;
                    console.log(`✅ Scanner started with config ${i + 1}`);
                    showMessage('Arahkan kamera ke QR Code di Kiosk.', 'info');
                    startButton.style.display = 'none';
                    
                } catch (error) {
                    console.warn(`⚠️ Config ${i + 1} failed:`, error.message);
                    if (i === configs.length - 1) {
                        throw error; // Throw error hanya jika semua config gagal
                    }
                }
            }

        } catch (error) {
            let cameraError = 'Gagal memulai kamera.';
            
            console.error('Camera initialization failed:', error);
            
            if (error.name === 'NotAllowedError' || error.message.includes('Permission denied')) {
                cameraError = 'Akses kamera ditolak. Silakan:';
                cameraError += '<br>1. Klik ikon kamera di address bar';
                cameraError += '<br>2. Pilih "Allow" untuk kamera';
                cameraError += '<br>3. Refresh halaman ini';
            } else if (error.name === 'NotFoundError' || error.message.includes('No camera')) {
                cameraError = 'Kamera tidak ditemukan. Pastikan:';
                cameraError += '<br>1. Perangkat memiliki kamera';
                cameraError += '<br>2. Kamera tidak digunakan aplikasi lain';
            } else if (error.name === 'NotSupportedError') {
                cameraError = 'Browser tidak mendukung akses kamera.';
                cameraError += '<br>Gunakan Chrome, Firefox, atau Safari terbaru.';
            } else if (error.message.includes('HTTPS')) {
                cameraError = 'Kamera hanya bisa diakses melalui HTTPS.';
                cameraError += '<br>Hubungi administrator untuk mengaktifkan HTTPS.';
            }
            
            showMessage(cameraError, 'error');
            logError('Camera Access', error);
            startButton.style.display = 'block';
        }
    }

    // Event listener untuk tombol start
    startButton.addEventListener('click', async () => {
        startButton.disabled = true;
        await startScanning();
        startButton.disabled = false;
    });

    // Debug info on page load
    console.log('📱 Device info:', {
        userAgent: navigator.userAgent,
        isSecureContext: window.isSecureContext,
        protocol: location.protocol,
        hostname: location.hostname
    });

    // Cek dukungan awal
    if (!navigator.mediaDevices) {
        showMessage('Browser tidak mendukung akses kamera. Gunakan browser yang lebih baru.', 'error');
        startButton.style.display = 'none';
    }
});
    </script>
</x-filament-panels::page>