<?php
session_start();
// Proteção da rota
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'teacher') {
    header('Location: login.php');
    exit;
}
$user = $_SESSION['user'];
$firstName = explode(' ', $user['name'])[0];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Avaliador - RedaçãoPro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
    <style> body { font-family: 'Outfit', sans-serif; } </style>
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 relative">

    <div id="toast" class="fixed bottom-4 right-4 z-50 max-w-sm px-6 py-4 rounded-xl shadow-2xl text-white text-[10px] sm:text-xs font-bold uppercase tracking-widest border-b-4 hidden transition-opacity duration-300"></div>

    <div id="modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md p-6 space-y-5 transform transition-all">
            <div>
                <h3 class="font-extrabold text-slate-900 text-base">Apontar Erro</h3>
                <p class="text-xs text-slate-400 mt-1">Selecione a competência afetada e descreva o erro marcado.</p>
            </div>
            <div id="modal-comps" class="grid grid-cols-5 gap-2"></div>
            <div id="modal-desc" class="hidden rounded-xl px-4 py-3"></div>
            <textarea id="modal-comment" rows="3" placeholder="Descreva o erro encontrado neste trecho..." class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-sm resize-none outline-none focus:border-indigo-400 transition-colors leading-relaxed"></textarea>
            <div class="flex gap-3">
                <button id="modal-cancel" class="flex-1 py-3 rounded-xl border-2 border-slate-200 text-slate-600 font-bold text-xs uppercase tracking-wider hover:bg-slate-50 transition-colors">Cancelar</button>
                <button id="modal-confirm" disabled class="flex-[2] py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs uppercase tracking-wider disabled:opacity-40 disabled:cursor-not-allowed transition-colors">Confirmar Apontamento</button>
            </div>
        </div>
    </div>

    <nav class="bg-slate-900 sticky top-0 z-20 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3 sm:gap-4">
                <span class="text-white font-extrabold text-lg sm:text-xl tracking-tight">RedaçãoPro</span>
                <span class="h-4 w-px bg-slate-700"></span>
                <span class="text-slate-400 text-[9px] sm:text-[10px] uppercase tracking-widest font-bold hidden sm:block">Portal do Avaliador</span>
            </div>
            
            <div class="hidden md:flex items-center gap-6 absolute left-1/2 transform -translate-x-1/2">
                <button onclick="switchTeacherView('queue')" class="text-xs font-bold text-slate-300 hover:text-white transition-colors uppercase tracking-widest">Fila de Correção</button>
                <button onclick="switchTeacherView('themes')" class="text-xs font-bold text-slate-300 hover:text-white transition-colors uppercase tracking-widest">Gerenciar Temas</button>
            </div>
            
            <div class="flex items-center gap-4 sm:gap-6">
                <span class="hidden sm:block text-slate-300 text-xs font-bold uppercase tracking-wider"><?= htmlspecialchars($firstName) ?></span>
                <a href="../api/logout.php" class="text-[9px] sm:text-[10px] font-bold text-slate-500 hover:text-white transition-colors uppercase tracking-widest px-2 py-1 hover:bg-slate-800 rounded">ENCERRAR SESSÃO</a>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-8 sm:py-10">

        <div id="view-queue">
            <div class="mb-8 flex flex-col sm:flex-row sm:items-end justify-between gap-4 border-b border-slate-200 pb-6">
                <div>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">Fila de Correção</h2>
                    <p class="text-slate-500 text-sm mt-2 font-medium"><strong id="queue-count" class="text-slate-700">0</strong> avaliações aguardando seu parecer.</p>
                </div>
            </div>
            <div id="queue-list" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5 sm:gap-6"></div>
        </div>
        
        <div id="view-themes" class="hidden">
            <div class="max-w-4xl mx-auto space-y-10">
                
                <div>
                    <div class="mb-8 border-b border-slate-200 pb-6 flex justify-between items-end">
                        <div>
                            <h2 id="form-title-h2" class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">Publicar Tema de Redação</h2>
                            <p class="text-slate-500 text-sm mt-2 font-medium">Crie textos motivadores para orientar os alunos.</p>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm transition-all" id="theme-form-container">
                        <form id="theme-form" class="space-y-6">
                            <input type="hidden" id="theme-id" value="">
                            
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Título do Tema</label>
                                <input type="text" id="theme-title" required class="w-full p-4 bg-slate-50 border-2 border-slate-100 rounded-xl text-lg font-bold text-slate-800 outline-none focus:border-indigo-400 transition-colors" placeholder="Ex: Os desafios da inteligência artificial..." />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Imagem de Apoio (Opcional)</label>
                                <input type="file" id="theme-image" accept="image/*" class="w-full p-3 bg-slate-50 border-2 border-slate-100 rounded-xl text-sm outline-none transition-colors" />
                                <p id="edit-img-notice" class="text-xs text-amber-600 mt-2 hidden font-medium">Deixe vazio para manter a imagem atual.</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Texto de Apoio / Artigo</label>
                                <textarea id="theme-content" required rows="6" class="w-full p-4 bg-slate-50 border-2 border-slate-100 rounded-xl text-sm text-slate-700 outline-none focus:border-indigo-400 transition-colors leading-relaxed resize-y"></textarea>
                            </div>
                            
                            <div class="flex flex-col sm:flex-row gap-3 pt-2">
                                <button type="button" onclick="submitTheme('draft')" class="flex-1 bg-white border-2 border-slate-200 text-slate-600 hover:bg-slate-50 active:scale-95 font-bold py-4 rounded-xl transition-all text-xs uppercase tracking-widest">
                                    Salvar Rascunho
                                </button>
                                <button type="button" onclick="submitTheme('published')" class="flex-[2] bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white font-bold py-4 rounded-xl transition-all text-xs uppercase tracking-widest shadow-lg">
                                    Publicar para Alunos
                                </button>
                            </div>
                            <button type="button" id="btn-cancel-edit" onclick="cancelEditTheme()" class="hidden w-full text-center text-[10px] font-bold text-rose-500 uppercase tracking-widest hover:underline mt-2">
                                Cancelar Edição
                            </button>
                        </form>
                    </div>
                </div>

                <div class="pt-6">
                    <h3 class="text-xl font-bold text-slate-900 mb-6 flex items-center gap-3">
                        Seus Temas
                        <span id="themes-count" class="text-xs bg-indigo-100 text-indigo-700 px-2.5 py-1 rounded-full font-black">0</span>
                    </h3>
                    <div id="teacher-themes-list" class="space-y-4"></div>
                </div>

            </div>
        </div>

        <div id="view-workspace" class="hidden grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <div class="lg:col-span-4 order-1 lg:order-2 space-y-5 lg:sticky lg:top-24 pb-4">
                <div class="flex items-center justify-between">
                    <button id="btn-back" class="text-[10px] sm:text-xs font-bold text-slate-500 hover:text-indigo-600 uppercase tracking-widest transition-colors flex items-center gap-2 group">
                        <span class="group-hover:-translate-x-1 transition-transform">←</span> VOLTAR À FILA
                    </button>
                    <span id="student-name" class="text-sm font-semibold text-slate-600 truncate ml-3">Aluno</span>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Pontuação por Competência</h3>
                        <div>
                            <span id="total-score" class="text-2xl font-black text-slate-900">0</span>
                            <span class="text-sm text-slate-400 font-semibold">/1000</span>
                        </div>
                    </div>
                    <div class="h-1.5 bg-slate-100"><div id="score-bar" class="h-full bg-indigo-500 transition-all duration-500" style="width: 0%"></div></div>
                    <div id="comp-levels-container" class="p-4 space-y-3"></div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-4">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-400">Parecer Final</label>
                    <textarea id="general-feedback" rows="4" placeholder="Escreva o feedback construtivo para o aluno..." class="w-full p-4 bg-slate-50 border-2 border-slate-100 rounded-xl text-sm resize-none outline-none focus:border-indigo-400 transition-colors leading-relaxed"></textarea>
                    <button id="btn-publish" onclick="submitCorrection('final')" class="w-full bg-slate-900 hover:bg-slate-700 active:scale-95 text-white font-bold py-4 rounded-xl uppercase tracking-widest text-xs transition-all duration-200">PUBLICAR RESULTADO FINAL</button>
                    <button id="btn-draft" onclick="submitCorrection('draft')" class="w-full bg-white border-2 border-slate-200 hover:border-slate-300 hover:bg-slate-50 active:scale-95 text-slate-600 font-bold py-3 rounded-xl text-[10px] uppercase tracking-widest transition-all duration-200">SALVAR RASCUNHO ATUAL</button>
                </div>

                <div id="comments-box" class="hidden bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Apontamentos na Redação</h3>
                        <span id="comments-count" class="text-xs font-black text-slate-600 bg-slate-100 px-2 py-0.5 rounded-full">0</span>
                    </div>
                    <div id="comments-list" class="p-4 space-y-2 max-h-64 overflow-y-auto"></div>
                </div>
            </div>

            <div class="lg:col-span-8 order-2 lg:order-1 space-y-3">
                <div id="comp-legends" class="flex flex-wrap gap-2"></div>
                <div class="bg-white p-2 sm:p-3 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="w-full overflow-x-auto bg-slate-50 rounded-xl">
                        <div id="drawing-area" class="relative min-w-[700px] md:min-w-full inline-block cursor-crosshair select-none">
                            <img id="essay-image" src="" class="w-full h-auto block rounded-xl" draggable="false" />
                            <svg id="svg-overlay" viewBox="0 0 100 100" preserveAspectRatio="none" class="absolute top-0 left-0 w-full h-full pointer-events-none"></svg>
                            <div id="markers-overlay" class="absolute top-0 left-0 w-full h-full pointer-events-none"></div>
                        </div>
                    </div>
                </div>
                <p class="text-center text-[11px] text-slate-400 font-medium">✏️ Arraste sobre a redação para marcar um trecho</p>
            </div>
        </div>
    </main>

    <script>
        const COMPETENCIES = {
            1: { id: 1, short: 'C1', title: 'Modalidade Escrita Formal', desc: 'Domínio da norma culta.', bg: 'bg-blue-500', light: 'bg-blue-50', border: 'border-blue-300', text: 'text-blue-700', stroke: 'rgba(59,130,246,0.80)', hex: '#3b82f6' },
            2: { id: 2, short: 'C2', title: 'Compreensão do Tema', desc: 'Aplicação de conceitos para o texto dissertativo.', bg: 'bg-violet-500', light: 'bg-violet-50', border: 'border-violet-300', text: 'text-violet-700', stroke: 'rgba(139,92,246,0.80)', hex: '#8b5cf6' },
            3: { id: 3, short: 'C3', title: 'Seleção e Argumentação', desc: 'Interpretação de informações e argumentos.', bg: 'bg-emerald-500', light: 'bg-emerald-50', border: 'border-emerald-300', text: 'text-emerald-700', stroke: 'rgba(16,185,129,0.80)', hex: '#10b981' },
            4: { id: 4, short: 'C4', title: 'Mecanismos Linguísticos', desc: 'Construção da argumentação.', bg: 'bg-amber-500', light: 'bg-amber-50', border: 'border-amber-300', text: 'text-amber-700', stroke: 'rgba(245,158,11,0.80)', hex: '#f59e0b' },
            5: { id: 5, short: 'C5', title: 'Proposta de Intervenção', desc: 'Respeito aos direitos humanos.', bg: 'bg-rose-500', light: 'bg-rose-50', border: 'border-rose-300', text: 'text-rose-700', stroke: 'rgba(244,63,94,0.80)', hex: '#f43f5e' }
        };

        const showToast = (msg, type = 'info') => {
            const toast = document.getElementById('toast');
            toast.textContent = msg;
            toast.className = `fixed bottom-4 right-4 z-50 max-w-sm px-6 py-4 rounded-xl shadow-2xl text-white text-[10px] sm:text-xs font-bold uppercase tracking-widest border-b-4 transition-opacity duration-300 ${type === 'success' ? 'bg-emerald-600 border-emerald-800' : type === 'error' ? 'bg-rose-600 border-rose-800' : 'bg-slate-900 border-black'}`;
            toast.style.opacity = '1';
            setTimeout(() => toast.style.opacity = '0', 3500);
        };

        let teacherThemes = [];

        // Alternar Telas
        window.switchTeacherView = (view) => {
            document.getElementById('view-queue').classList.add('hidden');
            document.getElementById('view-workspace').classList.add('hidden');
            document.getElementById('view-themes').classList.add('hidden');
            
            if (view === 'queue') {
                document.getElementById('view-queue').classList.remove('hidden');
                loadPendingEssays();
            } else if (view === 'themes') {
                document.getElementById('view-themes').classList.remove('hidden');
                loadTeacherThemes(); // Carrega os temas ao abrir a aba
            }
        };

        // --- LÓGICA DE TEMAS DO PROFESSOR ---
        async function loadTeacherThemes() {
            try {
                const res = await fetch('../api/api_get_teacher_themes.php');
                teacherThemes = await res.json();
                renderTeacherThemes();
            } catch (e) {
                showToast('Erro ao carregar temas', 'error');
            }
        }

        function renderTeacherThemes() {
            const listDiv = document.getElementById('teacher-themes-list');
            document.getElementById('themes-count').textContent = teacherThemes.length;

            if (teacherThemes.length === 0) {
                listDiv.innerHTML = `<div class="bg-slate-50 border-2 border-slate-200 border-dashed rounded-2xl py-12 text-center"><p class="font-bold text-slate-400 text-sm">Nenhum tema criado.</p></div>`;
                return;
            }

            listDiv.innerHTML = teacherThemes.map((t, i) => {
                const date = new Date(t.created_at).toLocaleDateString('pt-BR');
                const hasImg = t.image_path ? true : false;
                const statusBadge = t.status === 'draft' 
                    ? `<span class="bg-amber-100 text-amber-700 px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-widest">Rascunho</span>` 
                    : `<span class="bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-widest">Publicado</span>`;
                
                return `
                <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 flex flex-col sm:flex-row gap-4 sm:gap-6 items-start hover:border-indigo-300 transition-colors duration-300 group">
                    ${hasImg ? `<img src="../api/${t.image_path}" class="w-full sm:w-32 h-24 object-cover rounded-xl shrink-0 bg-slate-100">` : `<div class="w-full sm:w-32 h-24 bg-indigo-50 rounded-xl flex items-center justify-center shrink-0 border border-indigo-100"><span class="text-indigo-300 text-2xl">📝</span></div>`}
                    
                    <div class="flex-1 min-w-0 w-full">
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="flex items-center gap-2">
                                ${statusBadge}
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">${date}</p>
                            </div>
                            <div class="flex items-center gap-2 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity">
                                <button onclick="editTheme(${i})" class="w-7 h-7 bg-indigo-50 hover:bg-indigo-600 text-indigo-600 hover:text-white rounded-lg flex items-center justify-center transition-colors shadow-sm" title="Editar">✏️</button>
                                <button onclick="deleteTheme(${t.id})" class="w-7 h-7 bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white rounded-lg flex items-center justify-center transition-colors shadow-sm" title="Excluir">🗑️</button>
                            </div>
                        </div>
                        <h4 class="font-bold text-slate-900 text-base mb-1.5 truncate pr-8">${t.title}</h4>
                        <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed">${t.content}</p>
                    </div>
                </div>`;
            }).join('');
        }

        window.submitTheme = async (statusToSave) => {
            const title = document.getElementById('theme-title').value;
            const content = document.getElementById('theme-content').value;
            
            if(!title || !content) return showToast('PREENCHA O TÍTULO E O CONTEÚDO', 'error');

            const formData = new FormData();
            formData.append('id', document.getElementById('theme-id').value);
            formData.append('title', title);
            formData.append('content', content);
            formData.append('status', statusToSave);
            
            const imageFile = document.getElementById('theme-image').files[0];
            if(imageFile) formData.append('image', imageFile);

            try {
                const res = await fetch('../api/api_save_theme.php', { method: 'POST', body: formData });
                const data = await res.json();
                if (!res.ok) throw new Error(data.error || 'Erro ao salvar');
                
                showToast(statusToSave === 'draft' ? 'RASCUNHO SALVO' : 'TEMA PUBLICADO', 'success');
                cancelEditTheme();
                loadTeacherThemes();
            } catch (err) {
                showToast(err.message, 'error');
            }
        };

        window.editTheme = (idx) => {
            const t = teacherThemes[idx];
            document.getElementById('theme-id').value = t.id;
            document.getElementById('theme-title').value = t.title;
            document.getElementById('theme-content').value = t.content;
            
            document.getElementById('form-title-h2').textContent = "Editando Tema";
            document.getElementById('btn-cancel-edit').classList.remove('hidden');
            document.getElementById('edit-img-notice').classList.remove('hidden');
            document.getElementById('theme-form-container').classList.add('ring-4', 'ring-indigo-100');
            
            window.scrollTo({ top: 0, behavior: 'smooth' });
        };

        window.cancelEditTheme = () => {
            document.getElementById('theme-id').value = '';
            document.getElementById('theme-form').reset();
            document.getElementById('form-title-h2').textContent = "Publicar Tema de Redação";
            document.getElementById('btn-cancel-edit').classList.add('hidden');
            document.getElementById('edit-img-notice').classList.add('hidden');
            document.getElementById('theme-form-container').classList.remove('ring-4', 'ring-indigo-100');
        };

        window.deleteTheme = async (id) => {
            if(!confirm('Tem certeza que deseja excluir permanentemente este tema?')) return;
            try {
                const res = await fetch('../api/api_delete_theme.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });
                if(!res.ok) throw new Error('Erro ao excluir');
                showToast('TEMA EXCLUÍDO', 'success');
                loadTeacherThemes();
            } catch (err) {
                showToast('ERRO AO EXCLUIR', 'error');
            }
        };

        // O Restante da lógica (Essays, SVG, Correção) permanece inalterado
        let essays = [];
        let activeEssay = null;
        let inlineComments = [];
        let compLevels = { 1: 0, 2: 0, 3: 0, 4: 0, 5: 0 };
        
        async function loadPendingEssays() {
            try {
                const res = await fetch('../api/api_get_pending_essays.php');
                essays = await res.json();
                renderQueue();
            } catch (e) {
                showToast('Erro ao carregar', 'error');
            }
        }

        function renderQueue() {
            document.getElementById('queue-count').textContent = essays.length;
            const listDiv = document.getElementById('queue-list');
            if(essays.length === 0) {
                listDiv.innerHTML = `<div class="col-span-full bg-white rounded-3xl border-2 border-dashed border-slate-200 py-20 text-center"><p class="text-3xl mb-3">🎉</p><p class="font-bold text-slate-600 text-lg">Tudo em dia!</p><p class="text-slate-400 text-sm mt-1">Não há redações pendentes.</p></div>`;
                return;
            }
            listDiv.innerHTML = essays.map(essay => {
                const date = new Date(essay.created_at).toLocaleDateString('pt-BR');
                const isDraft = essay.status === 'in_correction';
                return `<div onclick="openEssay(${essay.id})" class="group bg-white rounded-2xl p-5 sm:p-6 border border-slate-200 hover:border-indigo-400 hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 cursor-pointer flex flex-col"><div class="flex justify-between items-start mb-4"><span class="font-mono text-[11px] font-bold bg-slate-100 text-slate-500 px-2 py-1 rounded">#${essay.id}</span> ${isDraft ? `<span class="text-[10px] bg-amber-100 text-amber-700 px-2.5 py-1 rounded-full font-black uppercase flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse inline-block"></span> Rascunho</span>` : ''}</div><h3 class="font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">${essay.student_name || 'Aluno'}</h3><p class="text-xs text-slate-400 mt-auto pt-4">${date}</p></div>`;
            }).join('');
        }

        window.openEssay = async (id) => {
            try {
                const res = await fetch(`../api/api_get_essay_details.php?id=${id}`);
                activeEssay = await res.json();
                
                document.getElementById('view-queue').classList.add('hidden');
                document.getElementById('view-workspace').classList.remove('hidden');
                
                document.getElementById('student-name').textContent = activeEssay.student_name;
                document.getElementById('essay-image').src = '../api/' + activeEssay.file_path;
                document.getElementById('general-feedback').value = activeEssay.correction?.general_feedback || '';
                
                if(activeEssay.correction) {
                    compLevels = {
                        1: Math.round(activeEssay.correction.comp1_score / 40),
                        2: Math.round(activeEssay.correction.comp2_score / 40),
                        3: Math.round(activeEssay.correction.comp3_score / 40),
                        4: Math.round(activeEssay.correction.comp4_score / 40),
                        5: Math.round(activeEssay.correction.comp5_score / 40)
                    };
                    inlineComments = activeEssay.correction.comments || [];
                } else {
                    compLevels = { 1: 0, 2: 0, 3: 0, 4: 0, 5: 0 };
                    inlineComments = [];
                }

                renderCompPanels();
                renderAllSVGComments();
            } catch (e) { showToast('ERRO AO CARREGAR DADOS', 'error'); }
        };

        document.getElementById('btn-back').addEventListener('click', () => {
            document.getElementById('view-workspace').classList.add('hidden');
            document.getElementById('view-queue').classList.remove('hidden');
            activeEssay = null;
        });

        function updateScore() {
            const total = Object.values(compLevels).reduce((s, lvl) => s + (lvl * 40), 0);
            document.getElementById('total-score').textContent = total;
            document.getElementById('score-bar').style.width = `${(total/1000)*100}%`;
        }

        window.setCompLevel = (compId, level) => {
            compLevels[compId] = level;
            renderCompPanels();
        };

        function renderCompPanels() {
            updateScore();
            document.getElementById('comp-levels-container').innerHTML = Object.values(COMPETENCIES).map(c => `
                <div class="${c.light} border ${c.border} rounded-xl p-3">
                    <div class="flex items-center justify-between mb-2.5">
                        <div class="flex items-center gap-2 min-w-0"><span class="w-6 h-6 rounded-full ${c.bg} flex items-center justify-center text-white text-[9px] font-black">${c.short}</span><span class="text-[11px] font-bold ${c.text} truncate">${c.title}</span></div>
                        <span class="text-sm font-black ${c.text} flex-shrink-0 ml-2">${compLevels[c.id] * 40}</span>
                    </div>
                    <div class="flex gap-1">
                        ${[0,1,2,3,4,5].map(lvl => `<button onclick="setCompLevel(${c.id}, ${lvl})" class="flex-1 py-1.5 rounded-lg text-[10px] font-black transition-all duration-150 ${compLevels[c.id] === lvl ? `${c.bg} text-white shadow-sm` : 'bg-white/80 text-slate-500 hover:bg-white border border-white/60'}">${lvl}</button>`).join('')}
                    </div>
                </div>
            `).join('');
            
            document.getElementById('comp-legends').innerHTML = Object.values(COMPETENCIES).map(c => `<div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg ${c.light} border ${c.border}"><span class="w-3.5 h-3.5 rounded-full ${c.bg}"></span><span class="text-[10px] font-bold ${c.text}">${c.short} — ${c.title}</span></div>`).join('');
        }

        const drawingArea = document.getElementById('drawing-area');
        const essayImage = document.getElementById('essay-image');
        const svgOverlay = document.getElementById('svg-overlay');
        const markersOverlay = document.getElementById('markers-overlay');
        
        let isDrawing = false, currentPath = [], currentPolyline = null;

        function getCursorPosition(e) {
            const rect = essayImage.getBoundingClientRect();
            return { x: ((e.clientX - rect.left) / rect.width) * 100, y: ((e.clientY - rect.top) / rect.height) * 100 };
        }

        drawingArea.addEventListener('mousedown', (e) => {
            e.preventDefault(); isDrawing = true; currentPath = [getCursorPosition(e)];
            currentPolyline = document.createElementNS('http://www.w3.org/2000/svg', 'polyline');
            currentPolyline.setAttribute('fill', 'none'); currentPolyline.setAttribute('stroke', 'rgba(79,70,229,0.7)');
            currentPolyline.setAttribute('stroke-width', '2.5'); currentPolyline.setAttribute('stroke-linecap', 'round');
            currentPolyline.setAttribute('stroke-linejoin', 'round'); currentPolyline.setAttribute('vector-effect', 'non-scaling-stroke');
            svgOverlay.appendChild(currentPolyline);
        });

        drawingArea.addEventListener('mousemove', (e) => {
            if (!isDrawing) return;
            currentPath.push(getCursorPosition(e));
            currentPolyline.setAttribute('points', currentPath.map(p => `${p.x},${p.y}`).join(' '));
        });

        function finishDrawing() {
            if (!isDrawing) return;
            isDrawing = false;
            if (currentPath.length < 2) { if (currentPolyline) currentPolyline.remove(); return; }
            openModal();
        }
        drawingArea.addEventListener('mouseup', finishDrawing); drawingArea.addEventListener('mouseleave', finishDrawing);

        let selectedCompId = null;

        function openModal() {
            document.getElementById('modal').classList.remove('hidden');
            document.getElementById('modal-comment').value = '';
            document.getElementById('modal-confirm').disabled = true;
            document.getElementById('modal-desc').classList.add('hidden');
            selectedCompId = null;
            
            document.getElementById('modal-comps').innerHTML = Object.values(COMPETENCIES).map(c => `<button onclick="selectModalComp(${c.id})" class="flex flex-col items-center gap-1.5 p-2 rounded-xl border-2 transition-all duration-150 border-slate-200 bg-slate-50 hover:border-slate-300" id="mbtn-${c.id}"><span class="w-9 h-9 rounded-full ${c.bg} flex items-center justify-center text-white text-[10px] font-black shadow-sm">${c.short}</span></button>`).join('');
        }

        window.selectModalComp = (id) => {
            selectedCompId = id;
            Object.values(COMPETENCIES).forEach(c => document.getElementById(`mbtn-${c.id}`).className = `flex flex-col items-center gap-1.5 p-2 rounded-xl border-2 transition-all duration-150 ${id === c.id ? `${c.border} ${c.light}` : 'border-slate-200 bg-slate-50 hover:border-slate-300'}`);
            
            const c = COMPETENCIES[id];
            const desc = document.getElementById('modal-desc');
            desc.className = `${c.light} border ${c.border} rounded-xl px-4 py-3 block`;
            desc.innerHTML = `<p class="text-xs font-bold ${c.text}">${c.short} — ${c.title}</p><p class="text-xs text-slate-500 mt-1 leading-relaxed">${c.desc}</p>`;
            checkModalState();
        };

        document.getElementById('modal-comment').addEventListener('input', checkModalState);
        function checkModalState() { document.getElementById('modal-confirm').disabled = !(selectedCompId && document.getElementById('modal-comment').value.trim()); }

        document.getElementById('modal-cancel').addEventListener('click', () => {
            if (currentPolyline) currentPolyline.remove();
            document.getElementById('modal').classList.add('hidden');
        });

        document.getElementById('modal-confirm').addEventListener('click', () => {
            const commentText = document.getElementById('modal-comment').value.trim();
            const firstPt = currentPath[0];
            inlineComments.push({
                x_pos: parseFloat(firstPt.x.toFixed(4)), y_pos: parseFloat(firstPt.y.toFixed(4)),
                comment: commentText, excerpt: JSON.stringify(currentPath), competency: selectedCompId
            });
            document.getElementById('modal').classList.add('hidden');
            renderAllSVGComments();
        });

        window.removeComment = (idx) => { inlineComments.splice(idx, 1); renderAllSVGComments(); };

        function renderAllSVGComments() {
            svgOverlay.innerHTML = ''; markersOverlay.innerHTML = '';
            
            const box = document.getElementById('comments-box');
            if(inlineComments.length === 0) { box.classList.add('hidden'); return; }
            box.classList.remove('hidden');
            document.getElementById('comments-count').textContent = inlineComments.length;

            let htmlList = '';
            inlineComments.forEach((c, i) => {
                const comp = COMPETENCIES[c.competency];
                const pts = typeof c.excerpt === 'string' ? JSON.parse(c.excerpt) : c.excerpt;
                
                const poly = document.createElementNS('http://www.w3.org/2000/svg', 'polyline');
                poly.setAttribute('points', pts.map(p => `${p.x},${p.y}`).join(' '));
                poly.setAttribute('fill', 'none'); poly.setAttribute('stroke', comp.stroke); poly.setAttribute('stroke-width', '2.5'); poly.setAttribute('stroke-linecap', 'round'); poly.setAttribute('stroke-linejoin', 'round'); poly.setAttribute('vector-effect', 'non-scaling-stroke');
                svgOverlay.appendChild(poly);

                const marker = document.createElement('div');
                marker.className = `absolute w-6 h-6 sm:w-7 sm:h-7 text-white rounded-full flex items-center justify-center text-[10px] sm:text-[11px] font-black shadow-lg transform -translate-x-1/2 -translate-y-1/2 pointer-events-auto ring-2 ring-white cursor-help hover:scale-125 hover:z-30 transition-all`;
                marker.style.left = `${c.x_pos}%`; marker.style.top = `${c.y_pos}%`; marker.style.backgroundColor = comp.hex;
                marker.title = `${comp.short}: ${c.comment}`; marker.textContent = i + 1;
                markersOverlay.appendChild(marker);

                htmlList += `<div class="relative flex items-start gap-3 p-3 ${comp.light} border ${comp.border} rounded-xl"><span class="flex-shrink-0 w-6 h-6 rounded-full ${comp.bg} text-white text-[10px] font-black flex items-center justify-center mt-0.5">${i + 1}</span><div class="min-w-0 flex-1 pr-5"><span class="text-[9px] font-black ${comp.text} uppercase">${comp.short}</span><p class="text-xs text-slate-700 mt-0.5 leading-snug">${c.comment}</p></div><button onclick="removeComment(${i})" class="absolute top-2 right-2 w-5 h-5 flex items-center justify-center text-slate-400 hover:text-red-500 transition-colors text-xs">✕</button></div>`;
            });
            document.getElementById('comments-list').innerHTML = htmlList;
        }

        window.submitCorrection = async (status) => {
            const feedback = document.getElementById('general-feedback').value;
            if (status === 'final' && !feedback) return showToast('PREENCHA O PARECER GERAL', 'error');

            const payload = {
                status: status,
                score: Object.values(compLevels).reduce((s, lvl) => s + (lvl * 40), 0),
                general_feedback: feedback,
                comp1_score: compLevels[1] * 40, comp2_score: compLevels[2] * 40, comp3_score: compLevels[3] * 40, comp4_score: compLevels[4] * 40, comp5_score: compLevels[5] * 40,
                comments: inlineComments
            };

            try {
                const res = await fetch(`../api/api_correct_essay.php?id=${activeEssay.id}`, { 
                    method: 'POST', 
                    headers: { 'Content-Type': 'application/json' }, 
                    body: JSON.stringify(payload) 
                });
                
                const data = await res.json();
                if (!res.ok) throw new Error(data.error || 'Erro desconhecido no servidor');

                showToast(status === 'final' ? 'AVALIAÇÃO REGISTRADA' : 'RASCUNHO GUARDADO', 'success');
                if(status === 'final') {
                    setTimeout(() => { 
                        document.getElementById('view-workspace').classList.add('hidden'); 
                        document.getElementById('view-queue').classList.remove('hidden'); 
                        loadPendingEssays(); 
                    }, 1000);
                }
            } catch (e) { 
                showToast(e.message || 'ERRO AO SALVAR', 'error'); 
            }
        };

        // Carrega inicialmente a fila de correção
        loadPendingEssays();
    </script>
</body>
</html>