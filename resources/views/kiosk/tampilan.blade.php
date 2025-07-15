<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiosk Absensi - Efarina TV Championship</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');
        
        html, body {
            height: 100%;
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #333;
            overflow-x: hidden;
        }

        /* Floating shapes for background */
        .bg-shape {
            position: fixed;
            border-radius: 50%;
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
        }

        .bg-shape:nth-child(1) {
            width: 100px;
            height: 100px;
            background: #fbbf24;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .bg-shape:nth-child(2) {
            width: 150px;
            height: 150px;
            background: #10b981;
            top: 60%;
            right: 10%;
            animation-delay: 2s;
        }

        .bg-shape:nth-child(3) {
            width: 80px;
            height: 80px;
            background: #f59e0b;
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .container {
            display: flex;
            min-height: 100vh;
            padding: 20px;
            gap: 20px;
        }

        .main-panel {
            flex: 1;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .main-panel::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #fbbf24, #10b981, #f59e0b);
        }

        .sidebar {
            width: 400px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 20px;
            max-height: 100vh;
            overflow-y: auto;
        }

        .main-panel h1 {
            margin: 0 0 20px 0;
            font-size: 2.5em;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .championship-badge {
            display: inline-block;
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            font-size: 0.9em;
            font-weight: 600;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(251, 191, 36, 0.3);
        }

        #jam {
            font-size: 4em;
            font-weight: 700;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        #qrcode-container {
            min-height: 300px;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 15px;
            margin-bottom: 20px;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        #qrcode-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: conic-gradient(from 0deg, #667eea, #764ba2, #667eea);
            animation: rotate 4s linear infinite;
            z-index: 1;
        }

        #qrcode-container::after {
            content: '';
            position: absolute;
            inset: 3px;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 12px;
            z-index: 2;
        }

        #qrcode-container img {
            max-width: 100%;
            height: auto;
            position: relative;
            z-index: 3;
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .scan-instruction {
            font-size: 1.1em;
            color: #6b7280;
            margin-bottom: 10px;
        }

        .auto-refresh-info {
            font-size: 0.9em;
            color: #9ca3af;
            font-style: italic;
        }

        .sidebar-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .sidebar-title {
            font-size: 1.3em;
            font-weight: 700;
            color: #374151;
            margin-bottom: 10px;
        }

        .live-indicator {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #ef4444;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 600;
        }

        .live-dot {
            width: 8px;
            height: 8px;
            background: white;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                max-height: 400px;
            }
            
            #jam {
                font-size: 3em;
            }
            
            .main-panel h1 {
                font-size: 2em;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .main-panel, .sidebar {
                padding: 20px;
            }
            
            #jam {
                font-size: 2.5em;
            }
        }

        /* Scrollbar styling */
        .sidebar::-webkit-scrollbar {
            width: 8px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>
<body>
    <!-- Background floating shapes -->
    <div class="bg-shape"></div>
    <div class="bg-shape"></div>
    <div class="bg-shape"></div>

    <div class="container">
        <div class="main-panel">
            <div class="championship-badge">
                🏆 CHAMPIONSHIP MODE AKTIF 🏆
            </div>
            
            <h1>Scan & Compete!</h1>
            
            <div id="jam"></div>
            
            <div id="qrcode-container">
                <img id="qrcode-image" src="{{ route('kiosk.qrcode') }}" alt="QR Code Absensi">
            </div>
            
            <p class="scan-instruction">
                Pindai QR Code untuk bergabung dalam kompetisi kedisiplinan!
            </p>
            <p class="auto-refresh-info">
                QR Code akan diperbarui secara otomatis setiap 15 detik
            </p>
        </div>

        <div class="sidebar">
            <div class="sidebar-header">
                <h2 class="sidebar-title">Live Dashboard</h2>
                <div class="live-indicator">
                    <div class="live-dot"></div>
                    LIVE
                </div>
            </div>
            
            <livewire:kiosk-kehadiran />
        </div>
    </div>

    @livewireScripts
    
    <script>
        function tampilkanJam() {
            const jamElement = document.getElementById('jam');
            if(jamElement) {
                jamElement.textContent = new Date().toLocaleTimeString('id-ID');
            }
        }

        function refreshQrCode() {
            const qrImage = document.getElementById('qrcode-image');
            if(qrImage) {
                qrImage.src = "{{ route('kiosk.qrcode') }}?t=" + new Date().getTime();
            }
        }

        function addFloatingShapes() {
            // Add some dynamic floating elements
            const shapes = ['🏆', '⭐', '🎯', '🔥', '⚡'];
            shapes.forEach((shape, index) => {
                const element = document.createElement('div');
                element.innerHTML = shape;
                element.style.position = 'fixed';
                element.style.fontSize = '2em';
                element.style.opacity = '0.1';
                element.style.pointerEvents = 'none';
                element.style.animation = `float ${3 + index}s ease-in-out infinite`;
                element.style.animationDelay = `${index * 0.5}s`;
                element.style.left = `${Math.random() * 100}%`;
                element.style.top = `${Math.random() * 100}%`;
                element.style.zIndex = '1';
                document.body.appendChild(element);
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            tampilkanJam();
            setInterval(tampilkanJam, 1000); 
            setInterval(refreshQrCode, 15000);
            addFloatingShapes();
        });
    </script>

</body>
</html>