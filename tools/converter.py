#!/usr/bin/env python3
"""
Converte os painéis PHP do RedaçãoPro em HTML para a branch demo.

Uso (na pasta do projeto):
    python tools/converter.py CAMINHO/DO/PROJETO

Esta demo não tem login nem banco. Apenas os três painéis são convertidos;
as telas de autenticação foram removidas e o index.html (seletor de perfil)
é preservado. Reexecutar o script atualiza os painéis a partir do projeto principal.
"""
import re
import shutil
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
PAGES = ['student_dashboard', 'teacher_dashboard', 'admin']
ROLES = {'student_dashboard': 'student', 'teacher_dashboard': 'teacher', 'admin': 'admin'}

DEFAULT_INDEX = """<!DOCTYPE html>
<html lang="pt-BR"><head><meta charset="UTF-8"><title>RedaçãoPro · Demonstração</title>
<link rel="stylesheet" href="assets/css/tailwind.css">
<link rel="stylesheet" href="assets/css/notebook.css">
<script src="demo/seed.js"></script><script src="demo/mock-api.js"></script></head>
<body class="nb-root"><main class="nb-wrap nb-section">
<h1 class="nb-display">RedaçãoPro · Demonstração</h1>
<p class="nb-muted mt-4">Escolha um perfil:</p>
<p class="mt-6 flex flex-wrap gap-3">
<a class="nb-btn nb-btn--accent" href="pages/student_dashboard.html">Aluno</a>
<a class="nb-btn nb-btn--accent" href="pages/teacher_dashboard.html">Professor</a>
<a class="nb-btn nb-btn--accent" href="pages/admin.html">Administrador</a>
</p></main></body></html>
"""


def add_notebook_theme(s: str) -> str:
    """Anexa a camada visual Notebook Tabs aos painéis convertidos."""
    s = s.replace(
        '<link rel="stylesheet" href="../assets/css/tailwind.css">',
        '<link rel="stylesheet" href="../assets/css/tailwind.css">\n'
        '    <link rel="stylesheet" href="../assets/css/notebook.css">',
        1,
    )
    m = re.search(r'<body\b([^>]*)>', s)
    if not m:
        return s
    attrs = m.group(1)
    cm = re.search(r'class="([^"]*)"', attrs)
    if cm:
        classes = (cm.group(1).strip() + ' notebook-app').strip()
        attrs = attrs[:cm.start(1)] + classes + attrs[cm.end(1):]
    else:
        attrs += ' class="notebook-app"'
    return s[:m.start(1)] + attrs + s[m.end(1):]


def convert(src: str, name: str) -> str:
    s = src.replace('\r\n', '\n')

    # Remove a proteção de rota do PHP; a demo troca autenticação por seleção de perfil.
    s = re.sub(r'\A\s*<\?php.*?\?>\s*', '', s, count=1, flags=re.S)

    # Nome do usuário e token CSRF do projeto principal.
    s = s.replace('<?= htmlspecialchars($firstName) ?>', '<span data-first-name></span>')
    s = s.replace('<?= json_encode(csrf_token()) ?>', "''")

    # Remove o wrapper de CSRF: não há servidor nem sessão na demonstração.
    s = re.sub(r'\s*// Segurança: token CSRF injetado pelo servidor\s*const CSRF_TOKEN = [^\n]*;\s*', '\n', s)
    s = re.sub(r'\s*const originalFetch = window\.fetch;.*?return originalFetch\(url, opts\);\s*\};\s*', '\n', s, flags=re.S)

    # Links internos e saída -> troca de perfil.
    s = re.sub(r'\b[A-Za-z_]+\.php\b', lambda m: m.group(0).replace('.php', '.html'), s)
    s = s.replace('../api/logout.html', '../index.html').replace('../api/logout.php', '../index.html')
    s = re.sub(r'(<a href="\.\./index\.html"[^>]*>)[^<]*(</a>)', r'\1TROCAR PERFIL\2', s)

    # Caminhos de imagem/PDF: '../api/' + x  ->  assetUrl(x)
    s = re.sub(r'"\.\./api/\$\{esc\(([^)]+)\)\}"', r'"${esc(assetUrl(\1))}"', s)
    s = re.sub(r'`\.\./api/\$\{([^}]+)\}`', r'assetUrl(\1)', s)
    s = re.sub(r"'\.\./api/'\s*\+\s*([\w.]+)", r'assetUrl(\1)', s)

    # Injeta os dados e a camada de API simulada no <head>.
    role = ROLES.get(name, 'student')
    inject = ("<script>window.DEMO_ROLE='%s';</script>\n"
              '    <script src="../demo/seed.js"></script>\n'
              '    <script src="../demo/mock-api.js"></script>\n') % role
    s = s.replace('</head>', '    ' + inject + '</head>', 1)
    s = add_notebook_theme(s)
    return s


def main():
    if len(sys.argv) < 2:
        sys.exit(__doc__)
    proj = Path(sys.argv[1])
    src_pages = proj / 'pages'
    if not src_pages.is_dir():
        sys.exit(f'Pasta {src_pages} não encontrada.')

    for name in PAGES:
        php = src_pages / f'{name}.php'
        out = ROOT / 'pages' / f'{name}.html'
        if not php.exists():
            print(f'[aviso] {php} não encontrado; mantendo {out.name} como está.')
            continue
        html = convert(php.read_text(encoding='utf-8'), name)
        out.write_text(html, encoding='utf-8')
        print(f'[ok]   {name}.php -> {out.relative_to(ROOT)}')
        if '<?' in html:
            print(f'[ATENÇÃO] {name}: ainda há código PHP no arquivo, revise manualmente.')

    index_path = ROOT / 'index.html'
    if index_path.exists():
        print('[ok]   index.html mantido (seletor de perfil).')
    else:
        index_path.write_text(DEFAULT_INDEX, encoding='utf-8')
        print('[ok]   index.html criado com seleção direta de perfil.')

    for sub in ('css', 'fonts'):
        origem = proj / 'assets' / sub
        if origem.is_dir():
            shutil.copytree(origem, ROOT / 'assets' / sub, dirs_exist_ok=True)
            print(f'[ok]   assets/{sub} copiado')
        else:
            print(f'[ATENÇÃO] {origem} não encontrado: copie manualmente.')

    # Ajustes de fonte para o caminho da branch demo (assets/css -> assets/fonts).
    css_path = ROOT / 'assets' / 'css' / 'tailwind.css'
    if css_path.exists():
        css = css_path.read_text(encoding='utf-8')
        css = css.replace('../assets/fonts/', '../fonts/')
        css = css.replace('outfit-v15-latin-600.woff2', 'outfit-v15-latin-700.woff2')
        for weight, arquivo in ((700, 'outfit-v15-latin-700.woff2'), (800, 'outfit-v15-latin-800.woff2')):
            if f'font-weight: {weight};' not in css:
                css += (f"\n@font-face {{ font-family: 'Outfit'; font-style: normal; "
                        f"font-weight: {weight}; src: url('../fonts/{arquivo}') format('woff2'); }}\n")
        css_path.write_text(css, encoding='utf-8')
        print('[ok]   fontes locais ajustadas para assets/fonts')


if __name__ == '__main__':
    main()
