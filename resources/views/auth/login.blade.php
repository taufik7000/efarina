<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Efarina TV</title>
    <style>
        /* Anda bisa menambahkan styling di sini atau menggunakan file CSS terpisah */
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #f3f4f6; }
        .login-card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        .login-card h1 { text-align: center; margin-bottom: 1.5rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; }
        .form-group input { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        .form-group button { width: 100%; padding: 0.75rem; border: none; background-color: #3b82f6; color: white; border-radius: 4px; cursor: pointer; font-size: 1rem; }
        .error { color: red; font-size: 0.875rem; margin-top: 1rem; }
    </style>
</head>
<body>
    <div class="login-card">
        <h1>Login Efarina TV</h1>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input id="password" type="password" name="password" required>
            </div>

            <div class="form-group" style="display: flex; align-items: center;">
                <input id="remember_me" type="checkbox" name="remember">
                <label for="remember_me" style="margin-left: 0.5rem; margin-bottom: 0;">Ingat saya</label>
            </div>

            <div class="form-group">
                <button type="submit">Login</button>
            </div>
        </form>

        @if ($errors->any())
            <div class="error">
                {{ $errors->first('email') }}
            </div>
        @endif
    </div>
</body>
</html>