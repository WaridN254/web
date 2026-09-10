<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Platform Login - HALIS SaaS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { background: #fff; border-radius: 20px; padding: 48px 40px; width: 100%; max-width: 420px; box-shadow: 0 25px 50px rgba(0,0,0,.25); }
        .login-header { text-align: center; margin-bottom: 32px; }
        .login-header .logo { display: inline-flex; align-items: center; gap: 8px; margin-bottom: 12px; }
        .login-header .logo span { font-size: 1.5rem; font-weight: 800; color: #1a1a2e; }
        .login-header .logo .badge { background: #6366f1; color: #fff; font-size: .65rem; font-weight: 700; padding: 2px 8px; border-radius: 10px; }
        .login-header p { font-size: .9rem; color: #64748b; }
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; font-size: .82rem; font-weight: 600; color: #374151; margin-bottom: 6px; }
        .form-input { width: 100%; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 10px; font-size: .92rem; font-family: inherit; transition: border-color .15s; }
        .form-input:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.1); }
        .form-check { display: flex; align-items: center; gap: 8px; margin-bottom: 20px; }
        .form-check input { width: 16px; height: 16px; accent-color: #6366f1; }
        .form-check label { font-size: .85rem; color: #64748b; }
        .btn-login { width: 100%; padding: 12px; background: #6366f1; color: #fff; border: none; border-radius: 10px; font-size: .95rem; font-weight: 700; cursor: pointer; font-family: inherit; transition: background .15s; }
        .btn-login:hover { background: #4f46e5; }
        .error-msg { background: #fee2e2; color: #991b1b; padding: 10px 14px; border-radius: 8px; font-size: .85rem; margin-bottom: 16px; }
        .back-link { display: block; text-align: center; margin-top: 20px; font-size: .85rem; color: #64748b; }
        .back-link a { color: #6366f1; font-weight: 600; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <div class="logo">
                <span>HALIS</span>
                <span class="badge">PLATFORM</span>
            </div>
            <p>Sign in to your platform account</p>
        </div>

        @if(session('errors'))
            <div class="error-msg">{{ session('errors')->first() }}</div>
        @endif

        @if(session('error'))
            <div class="error-msg">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('platform.login.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input class="form-input" type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input class="form-input" type="password" id="password" name="password" required>
            </div>
            <div class="form-check">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Remember me</label>
            </div>
            <button type="submit" class="btn-login">Sign In</button>
        </form>

        <div class="back-link">
            <a href="/">← Back to website</a>
        </div>
    </div>
</body>
</html>
