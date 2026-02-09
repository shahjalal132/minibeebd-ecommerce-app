<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Admin Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  @php $info = \App\Models\Information::first(); @endphp
  <link rel="shortcut icon" href="{{ asset('uploads/img/'.($info->site_logo ?? 'logo.png')) }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>

  <style>
    body{
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #a2d9ff, #f4f6ff);
    }
    .login-card{
      width: 100%;
      max-width: 400px;
      background: rgba(255,255,255,0.9);
      backdrop-filter: blur(12px);
      border-radius: 16px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      padding: 40px 30px;
      text-align: center;
    }
    .login-card img{
      width: 90px;
      height: 90px;
      object-fit: cover;
      border-radius: 50%;
      border: 2px solid #ddd;
      margin-bottom: 12px;
    }
    .login-card h1{
      font-size: 1.25rem;
      font-weight: 600;
      color: #333;
      margin-bottom: 25px;
    }
    .btn-login{
      background: linear-gradient(90deg, #0ea5a4, #2563eb);
      color: #fff;
      border: none;
    }
    .btn-login:hover{
      opacity: 0.9;
    }
  </style>
</head>
<body>
  <div class="login-card">
    <img src="{{ asset('uploads/img/'.($info->site_logo ?? 'logo.png')) }}" alt="Logo">
    <h1>{{ $info->site_name ?? config('app.name') }}</h1>

    <form method="POST" action="{{ route('admin.postLogin') }}">
      @csrf
      @if(session()->has('success'))
        <div class="alert alert-danger">{{ session('success') }}</div>
      @endif

      <div class="mb-3 text-start">
        <label for="username" class="form-label">Username</label>
        <input type="text" id="username" name="username"
               class="form-control @error('username') is-invalid @enderror"
               placeholder="Enter your username"
               value="{{ isset($_COOKIE['user']) ? $_COOKIE['user'] : old('username') }}">
        @error('username')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <div class="mb-3 text-start">
        <label for="password" class="form-label">Password</label>
        <input type="password" id="password" name="password"
               class="form-control @error('password') is-invalid @enderror"
               placeholder="Enter your password"
               value="{{ isset($_COOKIE['pass']) ? $_COOKIE['pass'] : '' }}">
        @error('password')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <div class="form-check text-start mb-3">
        <input type="checkbox" class="form-check-input" id="remember"
               name="remember" @if(isset($_COOKIE['user']) && isset($_COOKIE['pass'])) checked @endif>
        <label class="form-check-label" for="remember">Remember me</label>
      </div>

      <button type="submit" class="btn btn-login w-100 py-2">Login</button>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
