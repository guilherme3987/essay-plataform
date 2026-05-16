<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Nova Senha - RedaçãoPro</title>
    <link rel="stylesheet" href="./assets/css/tailwind.css">
    <style>
        .anim-up { animation: fadeUp 0.5s ease-out forwards; opacity: 0; transform: translateY(20px); }
        .delay-100 { animation-delay: 100ms; }
        @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="flex w-full min-h-screen bg-slate-50 overflow-hidden text-slate-800">

    <div id="toast" class="fixed bottom-4 right-4 z-50 max-w-sm px-6 py-4 rounded-xl shadow-2xl text-white text-[10px] sm:text-xs font-bold uppercase tracking-widest border-b-4 hidden transition-opacity duration-300"></div>

    <div class="flex-1 flex flex-col justify-center items-center p-6 relative z-10 w-full max-w-md mx-auto">
        
        <div class="w-full text-center mb-8 anim-up">
            <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-xl shadow-indigo-600/30">
                <span class="text-white font-black text-2xl">🔒</span>
            </div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Nova Senha</h1>
            <p class="text-sm text-slate-500 font-medium mt-2">Crie uma senha forte e segura.</p>
        </div>

        <div class="w-full bg-white rounded-3xl shadow-xl border border-slate-100 p-8 anim-up delay-100">
            
            <div id="alert-box" class="hidden mb-6 text-xs font-bold p-4 rounded-xl text-center leading-relaxed"></div>

            <form id="reset-form" class="space-y-5">
                <input type="hidden" id="token" value="">

                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 mb-2">Nova Senha</label>
                    <input type="password" id="password" required placeholder="Mínimo de 6 caracteres" class="w-full p-4 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold outline-none focus:border-indigo-400 focus:bg-white transition-colors" />
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 mb-2">Confirme a Nova Senha</label>
                    <input type="password" id="confirm_password" required placeholder="Repita a nova senha" class="w-full p-4 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold outline-none focus:border-indigo-400 focus:bg-white transition-colors" />
                </div>

                <button type="submit" id="btn-submit" class="w-full bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white font-bold py-4 rounded-xl transition-all duration-200 text-[11px] uppercase tracking-widest mt-2 shadow-lg shadow-indigo-600/30">
                    SALVAR NOVA SENHA
                </button>
            </form>
            
            <div id="success-action" class="hidden mt-6">
                <a href="login.php" class="block w-full text-center bg-slate-900 hover:bg-slate-800 text-white font-bold py-4 rounded-xl transition-all duration-200 text-[11px] uppercase tracking-widest shadow-lg">
                    IR PARA O LOGIN
                </a>
            </div>
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

        // Extrai o token da URL (ex: ?token=abc123xyz) ao carregar a página
        window.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const token = urlParams.get('token');
            
            if (!token) {
                document.getElementById('reset-form').classList.add('hidden');
                const alertBox = document.getElementById('alert-box');
                alertBox.classList.remove('hidden');
                alertBox.classList.add('bg-rose-50', 'text-rose-600', 'border', 'border-rose-200');
                alertBox.textContent = 'Token de segurança ausente. Use o link enviado para o seu e-mail.';
            } else {
                document.getElementById('token').value = token;
            }
        });

        document.getElementById('reset-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const btn = document.getElementById('btn-submit');
            const alertBox = document.getElementById('alert-box');
            
            btn.disabled = true;
            btn.textContent = 'PROCESSANDO...';
            alertBox.classList.add('hidden');

            const payload = {
                token: document.getElementById('token').value,
                password: document.getElementById('password').value,
                confirm_password: document.getElementById('confirm_password').value
            };

            try {
                // Como este arquivo está na raiz, a API está em ./api/
                const res = await fetch('./api/api_reset_password.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();
                alertBox.classList.remove('hidden', 'bg-rose-50', 'text-rose-600', 'border-rose-200', 'bg-emerald-50', 'text-emerald-700', 'border-emerald-200');

                if (res.ok) {
                    alertBox.classList.add('bg-emerald-50', 'text-emerald-700', 'border', 'border-emerald-200');
                    alertBox.textContent = data.success;
                    
                    // Esconde o formulário e mostra o botão de ir pro login
                    document.getElementById('reset-form').classList.add('hidden');
                    document.getElementById('success-action').classList.remove('hidden');
                    showToast('SENHA ATUALIZADA', 'success');
                } else {
                    alertBox.classList.add('bg-rose-50', 'text-rose-600', 'border', 'border-rose-200');
                    alertBox.textContent = data.error || 'Erro ao redefinir a senha.';
                }
            } catch (err) {
                alertBox.classList.remove('hidden');
                alertBox.classList.add('bg-rose-50', 'text-rose-600', 'border', 'border-rose-200');
                alertBox.textContent = 'Erro de conexão com o servidor.';
            } finally {
                btn.disabled = false;
                btn.textContent = 'SALVAR NOVA SENHA';
            }
        });
    </script>
</body>
</html>