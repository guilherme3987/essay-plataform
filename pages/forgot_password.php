<?php
require_once __DIR__ . '/../api/utils/session.php';
start_secure_session();
if (isset($_SESSION['user'])) {
    $role = $_SESSION['user']['role'];
    if ($role === 'student') header('Location: student_dashboard.php');
    elseif ($role === 'teacher') header('Location: teacher_dashboard.php');
    elseif ($role === 'admin') header('Location: admin.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - RedaçãoPro</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
</head>

<body class="flex w-full min-h-screen bg-slate-50 overflow-hidden text-slate-800">

    <div id="toast" class="fixed bottom-4 right-4 z-50 max-w-sm px-6 py-4 rounded-xl shadow-2xl text-white text-[10px] sm:text-xs font-bold uppercase tracking-widest border-b-4 hidden transition-opacity duration-300"></div>

    <div class="flex-1 flex flex-col justify-center items-center p-6 relative z-10 w-full max-w-md mx-auto">
        
        <div class="w-full text-center mb-8 anim-up">

            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Recuperar Senha</h1>
            <p class="text-sm text-slate-500 font-medium mt-2">Digite o seu e-mail de acesso abaixo</p>
        </div>

        <div class="w-full bg-white rounded-3xl shadow-xl border border-slate-100 p-8 anim-up delay-100">
            
            <div id="alert-box" class="hidden mb-6 text-xs font-bold p-4 rounded-xl text-center leading-relaxed">
                <span id="alert-msg"></span>
            </div>

            <form id="forgot-form" class="space-y-5">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 mb-2">Seu E-mail Cadastrado</label>
                    <input type="email" id="email" required placeholder="exemplo@email.com" class="w-full p-4 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium outline-none focus:border-indigo-400 focus:bg-white transition-colors" />
                </div>

                <button type="submit" id="btn-submit" class="w-full bg-slate-900 hover:bg-slate-800 active:scale-95 text-white font-bold py-4 rounded-xl transition-all duration-200 text-[11px] uppercase tracking-widest mt-2 shadow-lg shadow-slate-900/20">
                    ENVIAR LINK DE RECUPERAÇÃO
                </button>
            </form>
        </div>

        <div class="mt-8 text-center anim-up delay-200">
            <a href="login.php" class="text-[11px] font-bold text-slate-500 hover:text-indigo-600 uppercase tracking-widest mt-1 inline-block transition-colors flex items-center justify-center gap-2">
                ← VOLTAR PARA O LOGIN
            </a>
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

        const showToast = (msg, type = 'info') => {
            const toast = document.getElementById('toast');
            toast.textContent = msg;
            toast.className = `fixed bottom-4 right-4 z-50 max-w-sm px-6 py-4 rounded-xl shadow-2xl text-white text-[10px] sm:text-xs font-bold uppercase tracking-widest border-b-4 transition-opacity duration-300 ${type === 'success' ? 'bg-emerald-600 border-emerald-800' : type === 'error' ? 'bg-rose-600 border-rose-800' : 'bg-slate-900 border-black'}`;
            toast.style.opacity = '1';
            setTimeout(() => toast.style.opacity = '0', 3500);
        };

        document.getElementById('forgot-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const btn = document.getElementById('btn-submit');
            const alertBox = document.getElementById('alert-box');
            
            btn.disabled = true;
            btn.textContent = 'ENVIANDO E-MAIL...';
            alertBox.classList.add('hidden');

            const payload = {
                email: document.getElementById('email').value
            };

            try {
                const res = await fetch('../api/api_forgot_password.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();

                alertBox.classList.remove('hidden', 'bg-rose-50', 'text-rose-600', 'border', 'border-rose-200', 'bg-emerald-50', 'text-emerald-700', 'border-emerald-200');

                if (res.ok) {
                    alertBox.classList.add('bg-emerald-50', 'text-emerald-700', 'border', 'border-emerald-200');
                    document.getElementById('alert-msg').textContent = data.success;
                    document.getElementById('forgot-form').reset();
                    showToast('E-MAIL ENVIADO!', 'success');
                } else {
                    alertBox.classList.add('bg-rose-50', 'text-rose-600', 'border', 'border-rose-200');
                    document.getElementById('alert-msg').textContent = data.error || 'Erro ao enviar e-mail.';
                }
            } catch (err) {
                alertBox.classList.remove('hidden');
                alertBox.classList.add('bg-rose-50', 'text-rose-600', 'border', 'border-rose-200');
                document.getElementById('alert-msg').textContent = 'Erro de conexão com o servidor.';
            } finally {
                btn.disabled = false;
                btn.textContent = 'ENVIAR LINK DE RECUPERAÇÃO';
            }
        });
        
    </script>
</body>
</html>