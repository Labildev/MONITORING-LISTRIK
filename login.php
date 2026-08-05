<?php
session_start();
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Izaz Power Monitor</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --primary: #3b82f6;
            --primary-hover: #2563eb;
            --error: #ef4444;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body {
            background-color: var(--bg-color);
            background-image: radial-gradient(circle at top right, #1e293b, transparent 40%),
                              radial-gradient(circle at bottom left, #0f172a, transparent 40%);
            color: var(--text-main);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .login-card {
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.5s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .login-card h2 { text-align: center; margin-bottom: 8px; font-weight: 700; font-size: 24px; }
        .login-card p { text-align: center; color: var(--text-muted); margin-bottom: 24px; font-size: 14px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 14px; font-weight: 500; margin-bottom: 8px; color: var(--text-muted); }
        .form-group input {
            width: 100%; padding: 12px 16px; border-radius: 8px; border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(15, 23, 42, 0.6); color: white; font-size: 16px; outline: none; transition: border 0.3s;
        }
        .form-group input:focus { border-color: var(--primary); }
        .btn-login {
            width: 100%; padding: 12px; background: var(--primary); color: white; border: none; border-radius: 8px;
            font-size: 16px; font-weight: 600; cursor: pointer; transition: background 0.3s, transform 0.1s;
        }
        .btn-login:hover { background: var(--primary-hover); }
        .btn-login:active { transform: scale(0.98); }
        .error-msg { color: var(--error); font-size: 14px; text-align: center; margin-bottom: 16px; display: none; }
    </style>
</head>
<body>
    <div class="login-card">
        <img src="assets/img/Logo_Politeknik_Negeri_Lhokseumawe.png" alt="Logo Poltek" style="height: 60px; margin-bottom: 1rem;">
        <h2>Izaz Power Monitor</h2>
        <p>Login ke Panel Admin</p>
        <div class="error-msg" id="errorMsg"></div>
        <form id="loginForm">
            <div class="form-group">
                <label>Username</label>
                <input type="text" id="username" required autocomplete="username">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" id="password" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn-login" id="btnSubmit">Masuk</button>
        </form>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('btnSubmit');
            const err = document.getElementById('errorMsg');
            
            btn.textContent = 'Memproses...';
            btn.disabled = true;
            err.style.display = 'none';

            const formData = new FormData();
            formData.append('username', document.getElementById('username').value);
            formData.append('password', document.getElementById('password').value);

            try {
                const res = await fetch('api/login.php', { method: 'POST', body: formData });
                const data = await res.json();
                
                if (data.success) {
                    window.location.href = 'dashboard.php';
                } else {
                    err.textContent = data.error;
                    err.style.display = 'block';
                    btn.textContent = 'Masuk';
                    btn.disabled = false;
                }
            } catch (error) {
                err.textContent = 'Terjadi kesalahan jaringan.';
                err.style.display = 'block';
                btn.textContent = 'Masuk';
                btn.disabled = false;
            }
        });
    </script>
</body>
</html>
