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
            <h2>Welcome Back</h2>
            <p class="subtitle">Please enter your details to login</p>

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
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" placeholder="••••••••" required>
                </div>

                <button type="submit" class="login-btn">Login</button>
            </form>

            <div class="footer">
                <p>&copy; 2026 Attendance Pro</p>
            </div>
        </div>
    </div>

</body>
</html>