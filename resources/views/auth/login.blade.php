<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Attendance System</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>

    <div class="login-container">
        <div class="login-card">
            
            <!-- LOGO SECTION CONTAINER -->
            <div class="login-logo-wrapper">
                <!-- Option A: If you have a physical image asset (uncomment below) -->
                <img src="{{ asset('images/logo.jpeg') }}" alt="Logo" class="login-logo">

                <!-- Option B: High-quality, modern inline dynamic icon (Ready to use instantly) -->
                {{-- <svg class="login-logo-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/>
                    <path d="M12 6v6l4 2"/>
                </svg> --}}
            </div>

            <h2>Selamat Datang!</h2>
            <p class="subtitle">Silakan masukkan detail Anda untuk login</p>

            @if ($errors->any())
                <div class="error-box">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf
                
                <div class="input-group">
                    <label for="login">Email or NIP</label>
                    <input type="text" name="login" id="login" placeholder="EMP-2026-XXXX" required autofocus>
                </div>

                <div class="input-group">
                    <label for="password">Kata Sandi</label>
                    <input type="password" name="password" id="password" placeholder="••••••••" required>
                </div>

                <button type="submit" class="login-btn">Login</button>
            </form>

            <div class="footer">
                <p>&copy; 2026 Fortunet</p>
            </div>
        </div>
    </div>

</body>
</html>