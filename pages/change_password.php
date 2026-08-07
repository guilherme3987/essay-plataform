<?php
require_once __DIR__ . '/../api/utils/session.php';
start_secure_session();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
$user = $_SESSION['user'];
$firstName = explode(' ', $user['name'])[0];
$role = $user['role'];
$dashboard = $role === 'admin' ? 'admin.php' : ($role === 'teacher' ? 'teacher_dashboard.php' : 'student_dashboard.php');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar Senha - RedaçãoPro</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
</head>
<body class="flex w-full min-h-screen bg-slate-50 overflow-hidden text-slate-800">
    <div class="flex-1 flex flex-col justify-center items-center p-6 w-full max-w-md mx-auto">
        <div class="w-full text-center mb-8">
            <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-xl shadow-indigo-600/30">
                <span class="text-white font-black text-2xl">🔒</span>
            </div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Alterar Senha</h1>
            <p class="text-sm text-slate-500 font-medium mt-2">Olá, <?= htmlspecialchars($firstName) ?>. Por segurança, defina uma nova senha antes de continuar.</p>
        </div>

        <div class="w-full bg-white rounded-3xl shadow-xl border border-slate-100 p-8">
            <div id="alert-box" class="hidden mb-6 text-xs font-bold p-4 rounded-xl text-center leading-relaxed"></div>

            <form id="change-form" class="space-y-5">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 mb-2">Nova Senha</label>
                    <input type="password" id="password" required placeholder="Mínimo de 6 caracteres" class="w-full p-4 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold outline-none focus:border-indigo-400 focus:bg-white transition-colors" />
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 mb-2">Confirme a Nova Senha</label>
                    <input type="password" id="confirm_password" required placeholder="Repita a nova senha" class="w-full p-4 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold outline-none focus:border-indigo-400 focus:bg-white transition-colors" />
                </div>
                <button type="submit" id="btn-submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-xl transition-all duration-200 text-[11px] uppercase tracking-widest mt-2 shadow-lg shadow-indigo-600/30">
                    SALVAR E CONTINUAR
                </button>
            </form>

            <div class="mt-6">
                <a href="../api/logout.php" class="block w-full text-center text-slate-500 hover:text-rose-600 text-[10px] font-bold uppercase tracking-widest">Sair do Sistema</a>
            </div>
        </div>
    </div>

    <script>
        const CSRF_TOKEN = <?= json_encode(csrf_token()) ?>;

        const originalFetch = window.fetch;
        window.fetch = function (url, opts = {}) {
            const method = (opts.method || 'GET').toUpperCase();
            if (method !== 'GET' && method !== 'HEAD' && method !== 'OPTIONS') {
                if (!opts.headers) opts.headers = {};
                opts.headers['X-CSRF-Token'] = CSRF_TOKEN;
            }
            return originalFetch(url, opts);
        };

        const alertBox = document.getElementById('alert-box');

        document.getElementById('change-form').addEventListener('submit', async (e) => {
            e.preventDefault();

            const btn = document.getElementById('btn-submit');
            btn.disabled = true;
            btn.textContent = 'PROCESSANDO...';
            alertBox.classList.add('hidden');

            const payload = {
                password: document.getElementById('password').value,
                confirm_password: document.getElementById('confirm_password').value
            };

            try {
                const res = await fetch('../api/api_change_password.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();

                alertBox.classList.remove('hidden', 'bg-rose-50', 'text-rose-600', 'border-rose-200', 'bg-emerald-50', 'text-emerald-700', 'border-emerald-200');

                if (res.ok) {
                    alertBox.classList.add('bg-emerald-50', 'text-emerald-700', 'border', 'border-emerald-200');
                    alertBox.textContent = data.success;
                    setTimeout(() => { window.location.href = '<?= $dashboard ?>'; }, 1200);
                } else {
                    alertBox.classList.add('bg-rose-50', 'text-rose-600', 'border', 'border-rose-200');
                    alertBox.textContent = data.error || 'Erro ao alterar a senha.';
                }
            } catch (err) {
                alertBox.classList.remove('hidden');
                alertBox.classList.add('bg-rose-50', 'text-rose-600', 'border', 'border-rose-200');
                alertBox.textContent = 'Erro de conexão com o servidor.';
            } finally {
                btn.disabled = false;
                btn.textContent = 'SALVAR E CONTINUAR';
            }
        });
    </script>
</body>
</html>
