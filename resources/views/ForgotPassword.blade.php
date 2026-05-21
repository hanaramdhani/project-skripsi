<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>POS | Lupa Password</title>

  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="{{ asset('lte/plugins/fontawesome-free/css/all.min.css') }}">
  <link rel="stylesheet" href="{{ asset('lte/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('lte/dist/css/adminlte.min.css') }}">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="hold-transition login-page">
<div class="login-box">
  <div class="login-logo">
    <b>Point of Sales</b>
  </div>

  <div class="card">
    <div class="card-body login-card-body">

      @if (!empty($reset_success))
        {{-- ===== TAMPILAN HASIL RESET ===== --}}
        <p class="login-box-msg text-success">
          <i class="bi bi-check-circle-fill"></i> Password berhasil direset
        </p>

        <div class="alert alert-info">
          <p class="mb-1"><strong>Username:</strong> {{ $reset_username }}</p>
          <p class="mb-1"><strong>Password Baru:</strong></p>
          <div class="d-flex align-items-center">
            <code id="newpass" class="bg-white p-2 border rounded mr-2 flex-grow-1" style="font-size: 18px; letter-spacing: 1px;">{{ $new_password }}</code>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="copyPassword()" title="Salin">
              <i class="bi bi-clipboard"></i>
            </button>
          </div>
          <small class="text-muted d-block mt-2">
            <i class="bi bi-exclamation-triangle"></i>
            Catat / salin password ini sekarang. Setelah halaman ditutup, password tidak bisa dilihat lagi.
            Disarankan segera login dan ganti password lewat administrator.
          </small>
        </div>

        <a href="{{ url('/login') }}" class="btn btn-primary btn-block">
          <i class="bi bi-box-arrow-in-right"></i> Login Sekarang
        </a>

        <script>
          function copyPassword() {
            const txt = document.getElementById('newpass').innerText;
            navigator.clipboard.writeText(txt).then(function () {
              alert('Password disalin ke clipboard.');
            });
          }
        </script>

      @else
        {{-- ===== FORM REQUEST RESET ===== --}}
        <p class="login-box-msg">Reset Password</p>
        <p class="text-muted small mb-3">
          Masukkan username Anda. Sistem akan membuat password baru otomatis dan menampilkannya di layar.
        </p>

        @if ($errors->any())
          <div class="alert alert-danger py-2 px-3 mb-3">
            {{ $errors->first() }}
          </div>
        @endif

        <form action="{{ url('/forgot-password') }}" method="POST" autocomplete="off">
          @csrf
          <div class="input-group mb-3">
            <input type="text" name="username" class="form-control" placeholder="Username"
                   value="{{ old('username') }}" required autofocus>
            <div class="input-group-append">
              <div class="input-group-text">
                <span class="fas fa-user"></span>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <button type="submit" class="btn btn-primary btn-block">
                <i class="bi bi-arrow-clockwise"></i> Reset Password
              </button>
            </div>
          </div>
        </form>

        <p class="mt-3 mb-0 text-center">
          <a href="{{ url('/login') }}"><i class="bi bi-arrow-left"></i> Kembali ke Login</a>
        </p>
      @endif

    </div>
  </div>
</div>

<script src="{{ asset('lte/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('lte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('lte/dist/js/adminlte.min.js') }}"></script>
</body>
</html>
