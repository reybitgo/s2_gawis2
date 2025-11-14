<!DOCTYPE html>
<html>
<head>
    <title>Test Login</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <h1>Test Login Form</h1>

    @if (session('status'))
        <div style="color: green; margin: 10px 0;">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div style="color: red; margin: 10px 0;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" style="margin: 20px 0;">
        @csrf
        <div style="margin: 10px 0;">
            <label for="email">Email:</label><br>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required
                   style="width: 300px; padding: 5px;" placeholder="admin@ewallet.com">
        </div>

        <div style="margin: 10px 0;">
            <label for="password">Password:</label><br>
            <input type="password" name="password" id="password" required
                   style="width: 300px; padding: 5px;" placeholder="Admin123!@#">
        </div>

        <div style="margin: 10px 0;">
            <input type="checkbox" name="remember" id="remember">
            <label for="remember">Remember me</label>
        </div>

        <button type="submit" style="padding: 10px 20px; background: #007cba; color: white; border: none;">
            Login
        </button>
    </form>

    <p>Test credentials:</p>
    <ul>
        <li>Admin: admin@ewallet.com / Admin123!@#</li>
        <li>Member: member@ewallet.com / Member123!@#</li>
    </ul>

    <script>
        // Debug form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            console.log('Form submitting...');
            console.log('CSRF Token:', document.querySelector('meta[name="csrf-token"]').content);
            console.log('Email:', document.getElementById('email').value);
            console.log('Password length:', document.getElementById('password').value.length);
        });
    </script>
</body>
</html>