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
    <link rel="stylesheet" href="assets/css/login.css?v=<?php echo time(); ?>">
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
