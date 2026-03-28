<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    // Redireciona para o login se não for admin
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - RedaçãoPro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
    <style> body { font-family: 'Outfit', sans-serif; } </style>
</head>
<body class="min-h-screen bg-slate-50 text-slate-800">
    
    <nav class="bg-slate-900 sticky top-0 z-20 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-white font-extrabold text-xl tracking-tight">RedaçãoPro</span>
                <span class="h-4 w-px bg-slate-700"></span>
                <span class="text-slate-400 text-[10px] uppercase tracking-widest font-bold">Painel de Controle</span>
            </div>
        </div>
    </nav>

    <main class="max-w-xl mx-auto px-4 py-12">
        <div class="bg-white rounded-3xl border border-slate-200 p-8 shadow-sm">
            <h2 class="text-2xl font-extrabold text-slate-900 mb-6">Cadastrar Novo Usuário</h2>
            
            <div id="alertBox" class="hidden mb-6 rounded-xl p-4 shadow-sm"></div>

            <form id="addUserForm" class="space-y-6">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Nome Completo</label>
                    <input type="text" id="name" required class="w-full py-3 px-4 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:border-indigo-600 focus:bg-white transition-colors">
                </div>
                
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">E-mail</label>
                    <input type="email" id="email" required class="w-full py-3 px-4 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:border-indigo-600 focus:bg-white transition-colors">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Senha</label>
                        <input type="password" id="password" required class="w-full py-3 px-4 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:border-indigo-600 focus:bg-white transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Nível de Acesso</label>
                        <select id="role" required class="w-full py-3 px-4 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:border-indigo-600 focus:bg-white transition-colors appearance-none">
                            <option value="student">Aluno</option>
                            <option value="teacher">Professor</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>
                </div>

                <button type="submit" id="submitBtn" class="w-full bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white font-bold py-4 rounded-xl transition-all duration-200 text-[11px] uppercase tracking-widest shadow-lg shadow-indigo-600/30">
                    Cadastrar Usuário
                </button>
            </form>
        </div>
    </main>

    <script>
        document.getElementById('addUserForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const btn = document.getElementById('submitBtn');
            const alertBox = document.getElementById('alertBox');
            btn.disabled = true;
            btn.textContent = 'PROCESSANDO...';

            const payload = {
                name: document.getElementById('name').value,
                email: document.getElementById('email').value,
                password: document.getElementById('password').value,
                role: document.getElementById('role').value
            };

            try {
                const response = await fetch('../api/api_add_user.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();

                alertBox.classList.remove('hidden', 'bg-emerald-50', 'border-emerald-200', 'bg-rose-50', 'border-rose-200');
                
                if (response.ok) {
                    alertBox.classList.add('bg-emerald-50', 'border', 'border-emerald-200', 'text-emerald-700');
                    alertBox.innerHTML = `<p class="text-sm font-bold">${data.success}</p>`;
                    document.getElementById('addUserForm').reset();
                } else {
                    alertBox.classList.add('bg-rose-50', 'border', 'border-rose-200', 'text-rose-700');
                    alertBox.innerHTML = `<p class="text-sm font-bold">${data.error}</p>`;
                }
            } catch (error) {
                alertBox.classList.remove('hidden');
                alertBox.classList.add('bg-rose-50', 'border', 'border-rose-200', 'text-rose-700');
                alertBox.innerHTML = `<p class="text-sm font-bold">Erro de conexão com o servidor.</p>`;
            } finally {
                btn.disabled = false;
                btn.textContent = 'CADASTRAR USUÁRIO';
                setTimeout(() => alertBox.classList.add('hidden'), 4000);
            }
        });
    </script>
</body>
</html>