<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login Bootstrap Template</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            height: 100vh;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 2rem;
            border-radius: 1rem;
            background: #fff;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.25);
        }

        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.5);
            border-color: #667eea;
        }

        .btn-primary {
            background: #667eea;
            border: none;
            transition: background 0.3s ease;
        }

        .btn-primary:hover {
            background: #5a67d8;
        }

        .login-title {
            font-weight: 700;
            color: #333;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .form-text {
            font-size: 0.9rem;
        }
    </style>
</head>

<body>
    <div class="login-card">
        <h2 class="login-title">Login</h2>
        {{-- handle error --}}
        @error('email')
            <div class="alert alert-danger">
                {{ $message }}
            </div>
        @enderror
        <form action="{{ route('login') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label">Alamat Email</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="nama@contoh.com"
                    required />
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Kata Sandi</label>
                <input type="password" class="form-control" id="password" name="password"
                    placeholder="Masukkan kata sandi" required />
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" name="remember" />
                <label class="form-check-label" for="remember">Ingat saya</label>
            </div>
            <button type="submit" class="btn btn-primary w-100">Masuk</button>
            <div class="mt-3 text-center">
                <a href="#" class="form-text">Lupa kata sandi?</a>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS Bundle (Popper + Bootstrap JS) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
