<?php
session_start();
// Se já estiver logado, redireciona para o respectivo painel
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
    <title>Criar Conta - RedaçãoPro</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <style>
        .anim-up {
            animation: fadeUp 0.5s ease-out forwards;
            opacity: 0;
            transform: translateY(20px);
        }

        .delay-100 {
            animation-delay: 100ms;
        }

        .delay-200 {
            animation-delay: 200ms;
        }

        @keyframes fadeUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body class="flex w-full min-h-screen bg-slate-50 overflow-hidden text-slate-800">

    <div id="toast" class="fixed bottom-4 right-4 z-50 max-w-sm px-6 py-4 rounded-xl shadow-2xl text-white text-[10px] sm:text-xs font-bold uppercase tracking-widest border-b-4 hidden transition-opacity duration-300"></div>

    <div class="flex-1 flex flex-col justify-center items-center p-6 relative z-10 w-full max-w-md mx-auto">
        <div class="w-full text-center mb-8 anim-up">

            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Criar Conta</h1>
            <p class="text-sm text-slate-500 font-medium mt-2">Junte-se à plataforma RedaçãoPro</p>
        </div>

        <div class="w-full bg-white rounded-3xl shadow-xl border border-slate-100 p-8 anim-up delay-100">
            <div id="error-box" class="hidden mb-6 bg-rose-50 border border-rose-200 text-rose-600 text-xs font-bold p-4 rounded-xl uppercase tracking-widest text-center">
                <span id="error-msg"></span>
            </div>

            <form id="register-form" class="space-y-5">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 mb-2">Nome Completo</label>
                    <input type="text" id="name" required placeholder="Digite seu nome" class="w-full p-4 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium outline-none focus:border-indigo-400 focus:bg-white transition-colors" />
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 mb-2">E-mail</label>
                    <input type="email" id="email" required placeholder="seu@email.com" class="w-full p-4 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium outline-none focus:border-indigo-400 focus:bg-white transition-colors" />
                </div>
                <div class="space-y-2">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Eu sou um...</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer" onclick="selectRole(this)">
                            <input type="radio" name="role" value="student" class="peer sr-only appearance-none" checked>
                            <div class="role-card p-4 rounded-xl border-2 border-indigo-600 bg-indigo-50 ring-2 ring-indigo-400 ring-offset-2 scale-[1.02] text-center transition-all duration-200 hover:scale-[1.02] active:scale-[0.97]">
                                <span class="text-2xl mb-1 block">👨‍🎓</span>
                                <span class="role-label text-xs font-bold text-indigo-700 uppercase tracking-widest">Aluno</span>
                            </div>
                        </label>
                        <label class="cursor-pointer" onclick="selectRole(this)">
                            <input type="radio" name="role" value="teacher" class="peer sr-only appearance-none">
                            <div class="role-card p-4 rounded-xl border-2 border-slate-200 text-center transition-all duration-200 hover:border-indigo-300 hover:bg-indigo-50/50 hover:scale-[1.02] active:scale-[0.97]">
                                <span class="text-2xl mb-1 block">👨‍🏫</span>
                                <span class="role-label text-xs font-bold text-slate-600 uppercase tracking-widest">Professor</span>
                            </div>
                        </label>
                    </div>

                    <script>
                        function selectRole(label) {
                            document.querySelectorAll('.role-card').forEach(card => {
                                card.classList.remove('border-indigo-600', 'bg-indigo-50', 'ring-2', 'ring-indigo-400', 'ring-offset-2', 'scale-[1.02]');
                                card.classList.add('border-slate-200');
                                card.closest('label').querySelector('.role-label').classList.replace('text-indigo-700', 'text-slate-600');
                            });

                            const card = label.querySelector('.role-card');
                            card.classList.add('border-indigo-600', 'bg-indigo-50', 'ring-2', 'ring-indigo-400', 'ring-offset-2', 'scale-[1.02]');
                            card.classList.remove('border-slate-200');
                            label.querySelector('.role-label').classList.replace('text-slate-600', 'text-indigo-700');
                        }
                    </script>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 mb-2">Senha</label>
                        <input type="password" id="password" required minlength="6" placeholder="Mín. 6 letras" class="w-full p-4 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium outline-none focus:border-indigo-400 focus:bg-white transition-colors" />
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 mb-2">Confirmar</label>
                        <input type="password" id="confirm-password" required minlength="6" placeholder="Repita a senha" class="w-full p-4 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium outline-none focus:border-indigo-400 focus:bg-white transition-colors" />
                    </div>
                </div>

                <button type="submit" id="btn-submit" class="w-full bg-slate-900 hover:bg-slate-800 active:scale-95 text-white font-bold py-4 rounded-xl transition-all duration-200 text-[11px] uppercase tracking-widest mt-2 shadow-lg shadow-slate-900/20">
                    CADASTRAR E ENTRAR
                </button>
            </form>
        </div>

        <div class="mt-8 text-center anim-up delay-200">
            <p class="text-xs text-slate-500 font-medium">Já possui uma conta?</p>
            <a href="login.php" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 uppercase tracking-widest mt-1 inline-block transition-colors">FAZER LOGIN AGORA</a>
        </div>
    </div>

    <script>
        const showToast = (msg, type = 'info') => {
            const toast = document.getElementById('toast');
            toast.textContent = msg;
            toast.className = `fixed bottom-4 right-4 z-50 max-w-sm px-6 py-4 rounded-xl shadow-2xl text-white text-[10px] sm:text-xs font-bold uppercase tracking-widest border-b-4 transition-opacity duration-300 ${type === 'success' ? 'bg-emerald-600 border-emerald-800' : type === 'error' ? 'bg-rose-600 border-rose-800' : 'bg-slate-900 border-black'}`;
            toast.style.opacity = '1';
            setTimeout(() => toast.style.opacity = '0', 3500);
        };

        document.getElementById('register-form').addEventListener('submit', async (e) => {
            e.preventDefault();

            const btn = document.getElementById('btn-submit');
            const errorBox = document.getElementById('error-box');

            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm-password').value;

            // Validação de senhas iguais no frontend
            if (password !== confirmPassword) {
                document.getElementById('error-msg').textContent = 'As senhas não coincidem.';
                errorBox.classList.remove('hidden');
                return;
            }

            btn.disabled = true;
            btn.textContent = 'CRIANDO CONTA...';
            errorBox.classList.add('hidden');

            const payload = {
                name: document.getElementById('name').value,
                email: document.getElementById('email').value,
                password: password,
                role: document.querySelector('input[name="role"]:checked').value
            };

            try {
                const res = await fetch('../api/api_register.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();

                if (res.ok) {
                    showToast('CONTA CRIADA COM SUCESSO! REDIRECIONANDO...', 'success');
                    // Aguarda 1.5s e redireciona para o login
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 1500);
                } else {
                    document.getElementById('error-msg').textContent = data.error || 'Erro ao criar conta.';
                    errorBox.classList.remove('hidden');
                }
            } catch (err) {
                document.getElementById('error-msg').textContent = 'Erro de conexão com o servidor.';
                errorBox.classList.remove('hidden');
            } finally {
                if (!btn.disabled) {
                    btn.disabled = false;
                    btn.textContent = 'CADASTRAR E ENTRAR';
                }
            }
        });
    </script>
</body>

</html>