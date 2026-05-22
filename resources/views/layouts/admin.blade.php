<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fortunet - Admin Panel</title>
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="icon" type="image/jpg" href="{{ asset('images/logo.jpeg') }}">
</head>
<body>
    <div class="admin-wrapper">
        @include('admin.partials.sidebar') 

        <main class="content">
            @yield('content')
        </main>
    </div>
</body>
</html>