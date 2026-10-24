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
    <div class="hidden lg:flex w-[45%] max-w-[600px] relative flex-col justify-between p-12 xl:p-16 bg-indigo-600 overflow-hidden flex-shrink-0 group">
        <div class="relative z-10 anim-up">
            <div class="inline-flex items-center gap-3 cursor-default">
                <div class="w-12 h-12 rounded-xl bg-white flex items-center justify-center transition-transform duration-500 hover:rotate-12 hover:scale-110 shadow-lg">
                    <span class="text-indigo-600 font-extrabold text-2xl">R</span>
                </div>
                <span class="text-white text-3xl font-bold tracking-tight">Redação<span class="text-indigo-200">Pro</span></span>
            </div>
        </div>
        <div class="relative z-10 space-y-6 anim-up delay-100">
            <h2 class="text-white text-4xl xl:text-5xl font-extrabold leading-[1.1] tracking-tight">A evolução da sua escrita começa <span class="text-indigo-200">aqui.</span></h2>
            <p class="text-indigo-100 text-lg leading-relaxed max-w-md font-medium">Conectamos o olhar clínico dos professores à dedicação dos alunos. Feedback estruturado para resultados reais.</p>
        </div>
        <div class="relative z-10 bg-indigo-800/40 border border-indigo-400/30 rounded-2xl p-8 anim-up delay-200 hover:bg-indigo-800/60 transition-colors duration-300">
            <p class="text-white text-base leading-relaxed font-medium mb-6">"A clareza nas correções mudou minha forma de estruturar argumentos. Subi 200 pontos no simulado em apenas um mês."</p>
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-indigo-500 flex items-center justify-center text-white text-sm font-bold border-2 border-indigo-400">MM</div>
                <div>
                    <p class="text-white text-sm font-bold tracking-wide">MARINA MACHADO</p>
                    <p class="text-indigo-300 text-xs font-medium mt-0.5">Aluna do 3º Ano</p>
                </div>
            </div>
        </div>
    </div>
    <div class="flex-1 flex flex-col justify-center px-6 sm:px-12 md:px-20 lg:px-24">
        <div class="w-full max-w-[420px] mx-auto anim-up">
            <div class="mb-10">
                <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">Bem-vindo de volta</h1>
                <p class="text-slate-500 mt-2 text-base">Insira suas credenciais para acessar o painel.</p>
            </div>
            <div id="error-box" class="hidden mb-6 bg-rose-50 border border-rose-200 rounded-xl p-4 shadow-sm anim-up">
                <p class="text-rose-700 text-xs font-bold uppercase tracking-widest">Aviso do Sistema</p>
                <p id="error-msg" class="text-rose-600 text-sm mt-1 font-medium"></p>
            </div>
            <form id="login-form" class="space-y-8">
                <div class="relative group">
                    <input type="email" id="email" required class="peer w-full py-2 bg-transparent outline-none text-slate-900 font-bold text-lg border-b-2 border-slate-200 focus:border-indigo-600 transition-colors duration-300 placeholder-transparent" placeholder="E-mail" />
                    <label for="email" class="absolute left-0 -top-3.5 text-xs font-bold uppercase tracking-wider text-slate-400 transition-all duration-300 peer-placeholder-shown:top-3 peer-placeholder-shown:text-base peer-placeholder-shown:font-normal peer-focus:-top-3.5 peer-focus:text-xs peer-focus:font-bold peer-focus:text-indigo-600 cursor-text">Endereço de E-mail</label>
                </div>
                <div class="relative group pt-2">
                    <input type="password" id="password" required class="peer w-full py-2 pr-20 bg-transparent outline-none text-slate-900 font-bold text-lg tracking-widest border-b-2 border-slate-200 focus:border-indigo-600 transition-colors duration-300 placeholder-transparent" placeholder="Senha" />
                    <label for="password" class="absolute left-0 -top-3.5 text-xs font-bold uppercase tracking-wider text-slate-400 transition-all duration-300 peer-placeholder-shown:top-3 peer-placeholder-shown:text-base peer-placeholder-shown:font-normal peer-focus:-top-3.5 peer-focus:text-xs peer-focus:font-bold peer-focus:text-indigo-600 cursor-text">Senha de Acesso</label>
                </div>
                <button type="submit" id="submit-btn" class="w-full bg-slate-900 hover:bg-indigo-600 text-white font-bold py-4 rounded-xl transition-all duration-300 text-sm uppercase tracking-widest shadow-lg disabled:opacity-70">
                    ACESSAR PLATAFORMA
                </button>
                <div class="mt-8 text-center anim-up delay-200">
                    <p class="text-xs text-slate-500 font-medium">Ainda não tem uma conta?</p>
                    <a href="register.php" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 uppercase tracking-widest mt-1 inline-block transition-colors">CRIAR CONTA AGORA</a>
                </div>
                <div class="text-slate-500 mt-2 text-base text-center ">
                    <a href="forgot_password.php">Esqueci a senha</a>
                </div>
            </form>
        </div>
    </div>
            </form>
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

        document.getElementById('login-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('submit-btn');
            const errorBox = document.getElementById('error-box');

            btn.disabled = true;
            btn.textContent = 'PROCESSANDO...';
            errorBox.classList.add('hidden');

            const payload = {
                email: document.getElementById('email').value,
                password: document.getElementById('password').value
            };

            try {
                const res = await fetch('../api/api_login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();

                if (res.ok) {
                    if (data.must_change_password) {
                        window.location.href = 'change_password.php';
                    } else if (data.role === 'student') window.location.href = 'student_dashboard.php';
                    else if (data.role === 'teacher') window.location.href = 'teacher_dashboard.php';
                    else if (data.role === 'admin') window.location.href = 'admin.php';
                } else {
                    document.getElementById('error-msg').textContent = data.error || 'Credenciais incorretas.';
                    errorBox.classList.remove('hidden');
                }
            } catch (err) {
                document.getElementById('error-msg').textContent = 'Erro de conexão com o servidor.';
                errorBox.classList.remove('hidden');
            } finally {
                btn.disabled = false;
                btn.textContent = 'ACESSAR PLATAFORMA';
            }
        });
    
        const teacherModal = document.getElementById('teacher-modal');
        document.getElementById('open-teacher-register').addEventListener('click', () => {
            teacherModal.classList.remove('hidden');
        });
        document.getElementById('close-teacher-modal').addEventListener('click', () => {
            teacherModal.classList.add('hidden');
        });
        teacherModal.addEventListener('click', (e) => {
            if (e.target === teacherModal) teacherModal.classList.add('hidden');
        });

        document.getElementById('teacher-register-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('teacher-submit-btn');
            const errorBox = document.getElementById('teacher-error-box');
            const password = document.getElementById('teacher-password').value;
            const confirmPassword = document.getElementById('teacher-confirm-password').value;

            if (password !== confirmPassword) {
                document.getElementById('teacher-error-msg').textContent = 'As senhas não coincidem.';
                errorBox.classList.remove('hidden');
                return;
            }

            btn.disabled = true;
            btn.textContent = 'CRIANDO CONTA...';
            errorBox.classList.add('hidden');

            const payload = {
                name: document.getElementById('teacher-name').value,
                email: document.getElementById('teacher-email').value,
                password: password,
                role: 'teacher'
            };

            try {
                const res = await fetch('../api/api_register.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (res.ok) {
                    alert('Conta de professor criada com sucesso! Faça login.');
                    teacherModal.classList.add('hidden');
                    document.getElementById('teacher-register-form').reset();
                } else {
                    document.getElementById('teacher-error-msg').textContent = data.error || 'Erro ao criar conta.';
                    errorBox.classList.remove('hidden');
                }
            } catch (err) {
                document.getElementById('teacher-error-msg').textContent = 'Erro de conexão com o servidor.';
                errorBox.classList.remove('hidden');
            } finally {
                btn.disabled = false;
                btn.textContent = 'CADASTRAR PROFESSOR';
            }
        });
</script>
</body>

</html>