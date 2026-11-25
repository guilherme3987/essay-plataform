#!/usr/bin/env python3
"""
Converte as páginas PHP do RedaçãoPro em HTML estático para a demo.

Uso (na pasta redacaopro-demo):
    python tools/converter.py CAMINHO/DO/PROJETO

Lê CAMINHO/DO/PROJETO/pages/*.php, grava em ./pages/*.html e copia assets/css e assets/fonts.
Pode ser executado de novo sempre que as páginas do projeto mudarem.
"""
import re
import shutil
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
PAGES = ['login', 'register', 'forgot_password', 'change_password',
         'student_dashboard', 'teacher_dashboard', 'admin', 'reset_password']
ROLES = {'student_dashboard': 'student', 'teacher_dashboard': 'teacher', 'admin': 'admin'}

STUB = """<!DOCTYPE html>
<html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{titulo} - RedaçãoPro (demo)</title>
<style>body{{font-family:system-ui,sans-serif;background:#f8fafc;color:#1e293b;display:flex;min-height:100vh;align-items:center;justify-content:center;margin:0}}
.c{{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:32px;max-width:420px;text-align:center}}
a{{color:#4f46e5;font-weight:700}}</style></head>
<body><div class="c"><h1 style="font-size:20px">{titulo}</h1>
<p>Este recurso não está disponível na versão de demonstração.</p>
<a href="{voltar}">Voltar ao login</a></div></body></html>
"""


def convert(src: str, name: str) -> str:
    s = src.replace('\r\n', '\n')
    # 1) bloco PHP do topo (proteção de rota) -> guarda em JS (mock-api.js)
    s = re.sub(r'\A\s*<\?php.*?\?>\s*', '', s, count=1, flags=re.S)
    # 2) nome do usuário e token CSRF
    s = s.replace('<?= htmlspecialchars($firstName) ?>', '<span data-first-name></span>')
    s = s.replace('<?= json_encode(csrf_token()) ?>', "''")
    # 3) links entre páginas
    s = re.sub(r'\b(' + '|'.join(PAGES) + r')\.php\b', r'\1.html', s)
    # 4) caminhos de imagem/PDF: '../api/' + x  ->  assetUrl(x)
    s = re.sub(r'"\.\./api/\$\{esc\(([^)]+)\)\}"', r'"${esc(assetUrl(\1))}"', s)
    s = re.sub(r'`\.\./api/\$\{([^}]+)\}`', r'assetUrl(\1)', s)
    s = re.sub(r"'\.\./api/'\s*\+\s*([\w.]+)", r'assetUrl(\1)', s)
    # 5) login.php: remove </form> sobrando e o código do modal de professor (elementos que não existem)
    if name == 'login':
        s = re.sub(r'</div>\s*</form>\s*<script>', '</div>\n    <script>', s)
        s = re.sub(r'\n\s*const teacherModal = .*?(?=</script>)', '\n', s, flags=re.S)
    # 6) injeta os scripts da demo no <head>
    role = ROLES.get(name, 'none')
    inject = ("<script>window.DEMO_ROLE='%s';</script>\n"
              '    <script src="../demo/seed.js"></script>\n'
              '    <script src="../demo/mock-api.js"></script>\n') % role
    s = s.replace('</head>', '    ' + inject + '</head>', 1)
    return s


def main():
    if len(sys.argv) < 2:
        sys.exit(__doc__)
    proj = Path(sys.argv[1])
    src_pages = proj / 'pages'
    if not src_pages.is_dir():
        sys.exit(f'Pasta {src_pages} não encontrada.')
    (ROOT / 'pages' / 'auth').mkdir(parents=True, exist_ok=True)

    for name in PAGES:
        php = src_pages / f'{name}.php'
        out = ROOT / 'pages' / f'{name}.html'
        if name == 'reset_password':
            out = ROOT / 'pages' / 'auth' / 'reset_password.html'
        if php.exists() and name != 'reset_password':
            html = convert(php.read_text(encoding='utf-8'), name)
            out.write_text(html, encoding='utf-8')
            print(f'[ok]   {name}.php -> {out.relative_to(ROOT)}')
            if '<?' in html:
                print(f'[ATENÇÃO] {name}: ainda há código PHP no arquivo, revise manualmente.')
        else:
            titulo = {'register': 'Criar conta', 'forgot_password': 'Recuperar senha',
                      'change_password': 'Trocar senha', 'reset_password': 'Redefinir senha'}[name]
            voltar = '../login.html' if name == 'reset_password' else 'login.html'
            out.write_text(STUB.format(titulo=titulo, voltar=voltar), encoding='utf-8')
            print(f'[stub] {out.relative_to(ROOT)} (página indisponível na demo)')

    for sub in ('css', 'fonts'):
        origem = proj / 'assets' / sub
        if origem.is_dir():
            shutil.copytree(origem, ROOT / 'assets' / sub, dirs_exist_ok=True)
            print(f'[ok]   assets/{sub} copiado')
        else:
            print(f'[ATENÇÃO] {origem} não encontrado: copie manualmente.')

    (ROOT / 'index.html').write_text(
        '<!DOCTYPE html><meta charset="UTF-8"><meta http-equiv="refresh" content="0;url=pages/login.html">'
        '<a href="pages/login.html">Entrar</a>', encoding='utf-8')


if __name__ == '__main__':
    main()
