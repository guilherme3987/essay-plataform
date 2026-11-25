// Camada de dados da demonstração RedaçãoPro.
// Camada de dados locais da demonstração RedaçãoPro.
// As páginas continuam chamando ../api/api_*.php; esta camada intercepta essas chamadas.
(function () {
  'use strict';

  const DB_KEY = 'rp_demo_db_v2';
  const PROFILE_KEY = 'rp_demo_profile_v1';
  const PROFILES = window.DEMO_PROFILES || {
    student: { id: 3, label: 'Aluno', page: 'student_dashboard.html' },
    teacher: { id: 2, label: 'Professor', page: 'teacher_dashboard.html' },
    admin: { id: 1, label: 'Administrador', page: 'admin.html' }
  };

  class HttpError extends Error { constructor(s, m) { super(m); this.status = s; } }

  function setProfile(role) {
    if (!PROFILES[role]) return;
    try { localStorage.setItem(PROFILE_KEY, role); } catch (e) { /* armazenamento indisponível */ }
  }

  function profile() {
    let role = null;
    try { role = localStorage.getItem(PROFILE_KEY); } catch (e) { /* ignore */ }
    if (!PROFILES[role]) role = window.DEMO_ROLE;
    return PROFILES[role] ? role : 'student';
  }

  // A página declara DEMO_ROLE; isso apenas escolhe o perfil da demonstração, não autentica ninguém.
  if (window.DEMO_ROLE && PROFILES[window.DEMO_ROLE]) setProfile(window.DEMO_ROLE);

  function load() {
    try {
      const d = JSON.parse(localStorage.getItem(DB_KEY));
      if (d && Array.isArray(d.users)) return d;
    } catch (e) { /* storage inválido: recria a demo */ }
    const d = JSON.parse(JSON.stringify(window.DEMO_SEED));
    try { localStorage.setItem(DB_KEY, JSON.stringify(d)); } catch (e) { /* ignore */ }
    return d;
  }
  const save = db => {
    try { localStorage.setItem(DB_KEY, JSON.stringify(db)); return true; } catch (e) { return false; }
  };
  const json = (o, s) => new Response(JSON.stringify(o), { status: s || 200, headers: { 'Content-Type': 'application/json' } });
  const now = () => new Date().toISOString().slice(0, 19);
  const nid = a => a.reduce((m, x) => Math.max(m, x.id || 0), 0) + 1;
  const desc = (a, b) => String(b.created_at).localeCompare(String(a.created_at));
  const need = (c, role) => { if (!c.user || c.user.role !== role) throw new HttpError(403, 'Perfil não disponível nesta tela.'); };
  const uname = (db, id) => (db.users.find(u => u.id === id) || {}).name;

  function currentUser(db) {
    const role = profile(), pref = PROFILES[role];
    return (db.users.find(u => u.id === pref.id && u.role === role) ||
            db.users.find(u => u.role === role) ||
            { id: 0, name: 'Demo', email: '', role });
  }

  // ---- helpers de arquivo ----
  function toDataURL(file, maxW) {
    return new Promise((res, rej) => {
      const url = URL.createObjectURL(file), img = new Image();
      img.onload = () => {
        const k = Math.min(1, maxW / img.width), cv = document.createElement('canvas');
        cv.width = Math.round(img.width * k); cv.height = Math.round(img.height * k);
        cv.getContext('2d').drawImage(img, 0, 0, cv.width, cv.height);
        URL.revokeObjectURL(url); res(cv.toDataURL('image/jpeg', 0.78));
      };
      img.onerror = () => { URL.revokeObjectURL(url); rej(new HttpError(400, 'Não foi possível ler a imagem.')); };
      img.src = url;
    });
  }
  const readRaw = f => new Promise((res, rej) => { const r = new FileReader(); r.onload = () => res(r.result); r.onerror = rej; r.readAsDataURL(f); });
  const blobCache = {};
  window.assetUrl = function (p) {
    if (!p) return '';
    if (/^data:application\/pdf/.test(p)) {
      if (!blobCache[p]) {
        const b = atob(p.split(',')[1]), u = new Uint8Array(b.length);
        for (let i = 0; i < b.length; i++) u[i] = b.charCodeAt(i);
        blobCache[p] = URL.createObjectURL(new Blob([u], { type: 'application/pdf' }));
      }
      return blobCache[p];
    }
    return /^(data:|blob:|https?:)/.test(p) ? p : '../' + p;
  };

  const corr = (db, eid, full) => {
    const k = db.corrections.find(k => k.essay_id === eid); if (!k) return null;
    const o = full ? Object.assign({}, k) : { score: k.score, general_feedback: k.general_feedback, comp1_score: k.comp1_score, comp2_score: k.comp2_score, comp3_score: k.comp3_score, comp4_score: k.comp4_score, comp5_score: k.comp5_score };
    o.comments = db.comments.filter(m => m.correction_id === k.id); return o;
  };

  function removeUser(db, id) {
    const eids = db.essays.filter(e => e.user_id === id).map(e => e.id);
    const kids = db.corrections.filter(k => eids.includes(k.essay_id)).map(k => k.id);
    db.comments = db.comments.filter(m => !kids.includes(m.correction_id));
    db.corrections = db.corrections.filter(k => !kids.includes(k.id));
    db.essays = db.essays.filter(e => e.user_id !== id);
    db.class_students = db.class_students.filter(x => x.student_id !== id);
    const tids = db.themes.filter(t => t.created_by === id).map(t => t.id);
    db.themes = db.themes.filter(t => t.created_by !== id);
    db.theme_classes = db.theme_classes.filter(x => !tids.includes(x.theme_id));
    db.classes.filter(k => k.teacher_id === id).forEach(k => dropClass(db, k.id));
    db.users = db.users.filter(u => u.id !== id);
  }
  function dropClass(db, id) {
    db.classes = db.classes.filter(k => k.id !== id);
    db.class_students = db.class_students.filter(x => x.class_id !== id);
    db.theme_classes = db.theme_classes.filter(x => x.class_id !== id);
  }
  function newUser(c) {
    const b = c.body, email = String(b.email || '').trim().toLowerCase();
    if (!b.name || !email) throw new HttpError(400, 'Preencha nome e e-mail.');
    if (c.db.users.some(u => u.email.toLowerCase() === email)) throw new HttpError(400, 'E-mail já cadastrado.');
    const r = ['student', 'teacher', 'admin'].includes(b.role) ? b.role : 'student';
    c.db.users.push({ id: nid(c.db.users), name: b.name, email, role: r, created_at: now() });
    c.dirty = true;
  }

  // ---- endpoints simulados ----
  const H = {
    get_themes(c) {
      need(c, 'student');
      const mine = c.db.class_students.filter(x => x.student_id === c.user.id).map(x => x.class_id);
      const ids = new Set(c.db.theme_classes.filter(x => mine.includes(x.class_id)).map(x => x.theme_id));
      return c.db.themes.filter(t => t.status === 'published' && ids.has(t.id)).sort(desc)
        .map(t => ({ id: t.id, title: t.title, content: t.content, image_path: t.image_path, pdf_path: t.pdf_path, created_at: t.created_at }));
    },
    get_my_essays(c) {
      need(c, 'student');
      return c.db.essays.filter(e => e.user_id === c.user.id).sort(desc).map(e => ({
        id: e.id, file_path: e.file_path, status: e.status, created_at: e.created_at,
        correction: e.status === 'corrected' ? corr(c.db, e.id, false) : null }));
    },
    async upload_essay(c) {
      need(c, 'student');
      const f = c.fd && c.fd.get('file');
      if (!f || !f.size) throw new HttpError(400, 'Nenhum arquivo enviado.');
      if (!/^image\//.test(f.type)) throw new HttpError(400, 'Na demonstração, envie a redação como imagem (JPG, PNG ou WEBP).');
      c.db.essays.push({ id: nid(c.db.essays), user_id: c.user.id, file_path: await toDataURL(f, 1400), status: 'pending', created_at: now() });
      c.dirty = true; return { success: true };
    },

    get_teacher_themes(c) {
      need(c, 'teacher');
      return c.db.themes.filter(t => t.created_by === c.user.id).sort(desc).map(t => Object.assign({}, t,
        { class_ids: c.db.theme_classes.filter(x => x.theme_id === t.id).map(x => x.class_id) }));
    },
    async save_theme(c) {
      need(c, 'teacher');
      const b = c.body; if (!b.title || !b.content) throw new HttpError(400, 'Preencha o título e o conteúdo.');
      let t = b.id ? c.db.themes.find(t => t.id === Number(b.id) && t.created_by === c.user.id) : null;
      if (!t) { t = { id: nid(c.db.themes), created_by: c.user.id, image_path: null, pdf_path: null, created_at: now() }; c.db.themes.push(t); }
      t.title = b.title; t.content = b.content; t.status = b.status === 'draft' ? 'draft' : 'published';
      const img = c.fd && c.fd.get('image'), pdf = c.fd && c.fd.get('pdf');
      if (img && img.size) t.image_path = await toDataURL(img, 1000);
      if (pdf && pdf.size) { if (pdf.size > 1.5e6) throw new HttpError(400, 'PDF muito grande para a demonstração (máx. 1,5 MB).'); t.pdf_path = await readRaw(pdf); }
      let ids = []; try { ids = JSON.parse(b.class_ids || '[]'); } catch (e) { /* ignore */ }
      c.db.theme_classes = c.db.theme_classes.filter(x => x.theme_id !== t.id);
      ids.forEach(id => c.db.theme_classes.push({ theme_id: t.id, class_id: Number(id) }));
      c.dirty = true; return { success: true, id: t.id };
    },
    delete_theme(c) {
      need(c, 'teacher'); const id = Number(c.body.id);
      c.db.themes = c.db.themes.filter(t => t.id !== id);
      c.db.theme_classes = c.db.theme_classes.filter(x => x.theme_id !== id);
      c.dirty = true; return { success: true };
    },

    get_pending_essays(c) {
      need(c, 'teacher');
      return c.db.essays.filter(e => e.status === 'pending' || (e.status === 'in_correction' && c.db.corrections.some(k => k.essay_id === e.id && k.teacher_id === c.user.id)))
        .sort(desc).map(e => ({ id: e.id, status: e.status, created_at: e.created_at, student_name: uname(c.db, e.user_id) }));
    },
    get_essay_details(c) {
      need(c, 'teacher'); const id = Number(c.qs.get('id')), e = c.db.essays.find(e => e.id === id);
      if (!e) throw new HttpError(404, 'Redação não encontrada.');
      const o = Object.assign({}, e, { student_name: uname(c.db, e.user_id) });
      const k = c.db.corrections.find(k => k.essay_id === id && k.teacher_id === c.user.id);
      if (k) o.correction = corr(c.db, id, true);
      return o;
    },
    correct_essay(c) {
      need(c, 'teacher'); const id = Number(c.qs.get('id')), b = c.body, e = c.db.essays.find(e => e.id === id);
      if (!e) throw new HttpError(404, 'Redação não encontrada.');
      let k = c.db.corrections.find(k => k.essay_id === id && k.teacher_id === c.user.id);
      if (!k) { k = { id: nid(c.db.corrections), essay_id: id, teacher_id: c.user.id }; c.db.corrections.push(k); }
      Object.assign(k, { score: +b.score || 0, general_feedback: b.general_feedback || '' });
      for (let i = 1; i <= 5; i++) k['comp' + i + '_score'] = +b['comp' + i + '_score'] || 0;
      c.db.comments = c.db.comments.filter(m => m.correction_id !== k.id);
      (b.comments || []).forEach(m => c.db.comments.push({ id: nid(c.db.comments), correction_id: k.id, x_pos: m.x_pos, y_pos: m.y_pos, comment: m.comment, excerpt: m.excerpt, competency: m.competency }));
      e.status = b.status === 'final' ? 'corrected' : 'in_correction';
      c.dirty = true; return { success: true };
    },

    manage_classes(c) {
      need(c, 'teacher'); const a = c.qs.get('action') || c.body.action, b = c.body;
      if (a === 'get_classes') return c.db.classes.filter(k => k.teacher_id === c.user.id).sort(desc).map(k => Object.assign({}, k, {
        students: c.db.class_students.filter(x => x.class_id === k.id).map(x => c.db.users.find(u => u.id === x.student_id)).filter(Boolean).map(u => ({ id: u.id, name: u.name, email: u.email })) }));
      if (a === 'get_all_students') return c.db.users.filter(u => u.role === 'student').sort((x, y) => x.name.localeCompare(y.name)).map(u => ({ id: u.id, name: u.name, email: u.email }));
      if (a === 'create_class') {
        if (!String(b.name || '').trim()) throw new HttpError(400, 'O nome da turma é obrigatório.');
        c.db.classes.push({ id: nid(c.db.classes), teacher_id: c.user.id, name: b.name, created_at: now() });
      } else if (a === 'delete_class') dropClass(c.db, Number(b.id));
      else if (a === 'add_student') {
        const x = { class_id: Number(b.class_id), student_id: Number(b.student_id) };
        if (!c.db.class_students.some(y => y.class_id === x.class_id && y.student_id === x.student_id)) c.db.class_students.push(x);
      } else if (a === 'remove_student') c.db.class_students = c.db.class_students.filter(x => !(x.class_id === Number(b.class_id) && x.student_id === Number(b.student_id)));
      else throw new HttpError(400, 'Ação inválida.');
      c.dirty = true; return { success: true };
    },

    admin(c) {
      need(c, 'admin'); const a = c.qs.get('action') || c.body.action, d = c.db;
      if (a === 'get_stats') return {
        students: d.users.filter(u => u.role === 'student').length,
        teachers: d.users.filter(u => u.role === 'teacher').length,
        essays_total: d.essays.length,
        essays_corrected: d.essays.filter(e => e.status === 'corrected').length,
        themes: d.themes.length
      };
      if (a === 'get_users') return d.users.filter(u => u.id !== c.user.id).sort(desc).map(u => ({ id: u.id, name: u.name, email: u.email, role: u.role, created_at: u.created_at }));
      if (a === 'delete_user') {
        const id = Number(c.body.id), target = d.users.find(u => u.id === id);
        if (!target) throw new HttpError(404, 'Usuário não encontrado.');
        if (id === c.user.id) throw new HttpError(400, 'Você não pode excluir o perfil ativo.');
        if (d.users.filter(u => u.role === target.role).length <= 1) throw new HttpError(400, 'A demonstração precisa manter ao menos um usuário de cada perfil.');
        removeUser(d, id); c.dirty = true; return { success: true };
      }
      if (a === 'add_user') { newUser(c); return { success: 'Perfil criado com sucesso!' }; }
      throw new HttpError(400, 'Ação inválida.');
    }
  };

  // ---- intercepta as chamadas de API das páginas ----
  const realFetch = window.fetch.bind(window);
  window.fetch = async function (url, opts) {
    opts = opts || {};
    const u = typeof url === 'string' ? url : (url && url.url) || String(url);
    const m = u.match(/api_([a-z_]+)\.php(\?.*)?$/);
    if (!m) return realFetch(url, opts);
    const db = load();
    const c = { qs: new URLSearchParams(m[2] || ''), body: {}, fd: null, db, user: currentUser(db), dirty: false };
    if (opts.body instanceof FormData) {
      c.fd = opts.body;
      opts.body.forEach((v, k) => { if (typeof v === 'string') c.body[k] = v; });
    } else if (typeof opts.body === 'string') {
      try { c.body = JSON.parse(opts.body) || {}; } catch (e) { /* ignore */ }
    }
    try {
      const h = H[m[1]];
      if (!h) throw new HttpError(503, 'Recurso indisponível na versão de demonstração.');
      const out = await h(c);
      if (c.dirty && !save(c.db)) throw new HttpError(507, 'Não foi possível salvar as alterações. Reinicie o caderno e tente novamente.');
      return json(out);
    } catch (e) {
      if (e instanceof HttpError) return json({ error: e.message }, e.status);
      console.error(e);
      return json({ error: 'Erro interno da demonstração.' }, 500);
    }
  };

  // ---- navegação por perfil ----
  function targetPage(role) {
    const cfg = PROFILES[role];
    if (!cfg) return 'index.html';
    const path = location.pathname.replace(/[^/]*$/, '');
    return (/\/pages\/$/.test(path) ? '' : 'pages/') + cfg.page;
  }
  window.demoSelectProfile = function (role) {
    setProfile(role);
    location.href = targetPage(role);
  };
  window.demoSwitchProfile = window.demoSelectProfile;
  window.demoReset = function () {
    try {
      localStorage.removeItem(DB_KEY);
      localStorage.removeItem(PROFILE_KEY);
    } catch (e) { /* ignore */ }
    location.reload();
  };

  document.addEventListener('DOMContentLoaded', () => {
    const db = load(), user = currentUser(db);
    document.querySelectorAll('[data-first-name]').forEach(el => { el.textContent = user.name.split(' ')[0]; });

    // Seletor de perfil injetado ao lado do nome, em qualquer painel.
    const anchor = document.querySelector('[data-first-name]');
    if (anchor && !document.getElementById('profile-switcher')) {
      const select = document.createElement('select');
      select.id = 'profile-switcher';
      select.title = 'Trocar perfil';
      select.style.cssText = 'background:#f8f6f1;color:#1a1a1a;border:1px solid rgba(26,26,26,.25);border-radius:0;padding:4px 8px;font:500 10px "DM Sans",sans-serif;letter-spacing:.1em;text-transform:uppercase;outline:none;cursor:pointer';
      select.innerHTML = Object.keys(PROFILES).map(r => `<option value="${r}">${PROFILES[r].label}</option>`).join('');
      select.value = profile();
      select.addEventListener('change', () => window.demoSwitchProfile(select.value));
      anchor.parentElement.insertAdjacentElement('afterend', select);
    }

    const pill = document.createElement('div');
    pill.style.cssText = 'position:fixed;left:12px;bottom:12px;z-index:99999;background:#1a1a1a;color:#98d4bb;border:1px solid rgba(152,212,187,.35);font:500 11px "DM Sans",sans-serif;letter-spacing:.04em;padding:8px 12px;border-radius:0;box-shadow:0 4px 16px rgba(0,0,0,.25);display:flex;gap:10px;align-items:center;max-width:calc(100vw - 24px)';
    pill.innerHTML = '<span>Demonstração · caderno interativo</span>';
    const rb = document.createElement('button');
    rb.type = 'button';
    rb.textContent = 'Reiniciar';
    rb.style.cssText = 'background:#f4b8c5;color:#1a1a1a;border:0;border-radius:0;padding:4px 10px;font:inherit;font-weight:600;cursor:pointer';
    rb.onclick = () => { if (confirm('Reiniciar o caderno e voltar ao estado inicial?')) window.demoReset(); };
    pill.appendChild(rb);
    document.body.appendChild(pill);
  });
})();
