Aqui está o README.md completo e detalhado. Ele foi escrito como a documentação oficial do seu projeto, focando em explicar a arquitetura, o papel de cada arquivo e os pontos cruciais da lógica do código, sem mencionar processos anteriores.

Markdown
# RedaçãoPro

O **RedaçãoPro** é uma plataforma educacional desenvolvida para conectar alunos e professores no processo de envio, correção e feedback de redações. O grande diferencial do sistema é a interface de correção interativa, que permite aos avaliadores desenhar traços livres sobre a imagem da redação e ancorar comentários detalhados baseados nas competências de avaliação.

## 🛠️ Tecnologias Utilizadas

* **Backend:** PHP 8.2 Puro (Focado em performance e segurança via PDO).
* **Frontend:** HTML5, Vanilla JavaScript (Manipulação direta de DOM e SVG) e TailwindCSS (estilização via CDN).
* **Banco de Dados:** MySQL 8.0.
* **Infraestrutura:** Docker e Docker Compose.

---

## 📂 Estrutura de Diretórios e Arquivos

A aplicação segue uma arquitetura baseada em "Vanilla SPA" (Single Page Application com JavaScript puro) e rotas de API em arquivos separados, garantindo uma divisão clara entre interface e regras de negócio.

```text
App/
├── docker-compose.yml
├── Dockerfile
├── init.sql
├── README.md
└── src/
    ├── db.php
    ├── logout.php
    ├── Pages/
    │   ├── admin.php
    │   ├── login.php
    │   ├── student.php
    │   └── teacher.php
    ├── api/
    │   ├── api_add_user.php
    │   ├── api_correct_essay.php
    │   ├── api_get_essay_details.php
    │   ├── api_get_my_essays.php
    │   ├── api_get_pending_essays.php
    │   ├── api_login.php
    │   └── api_upload_essay.php
    └── uploads/
📖 Entendendo o Código (Principais Componentes)
1. Infraestrutura e Banco de Dados
docker-compose.yml & Dockerfile: Orquestram o ambiente. O Apache é configurado para rodar o PHP 8.2 com as extensões do MySQL ativadas (pdo e pdo_mysql), mapeando a pasta src/ para /var/www/html dentro do container.

init.sql: Executado automaticamente na criação do container do banco. Cria as tabelas relacionais (users, essays, corrections, comments) e insere o usuário Administrador padrão.

2. Core e Segurança
src/db.php: Ponto central de conexão com o banco de dados utilizando PDO. Utiliza variáveis de ambiente do Docker e aplica Prepared Statements por padrão para mitigar riscos de SQL Injection.

src/logout.php: Destrói a sessão atual do usuário (session_destroy()) e redireciona para a tela de login.

3. Interfaces (Pasta Pages/)
Nesta camada, o HTML constrói a estrutura visual usando Tailwind, e o JavaScript no final de cada arquivo gerencia a interatividade e o consumo das APIs (fetch).

login.php: Tela de entrada. O JavaScript captura o submit do formulário, envia as credenciais para a API e faz o redirecionamento com base na role (nível de acesso) retornada pelo servidor.

admin.php: Painel exclusivo para o Administrador cadastrar novos usuários (professores e alunos).

student.php:

Lógica Principal: Gerencia o envio de arquivos (imagens/PDFs) via Drag & Drop.

Espelho da Correção: Lê as coordenadas retornadas pela API e injeta dinamicamente elementos <polyline> dentro de uma tag <svg> sobreposta à imagem da redação, permitindo que o aluno veja exatamente onde o professor marcou o erro.

teacher.php: A interface mais complexa do sistema.

Lógica de Desenho SVG: Captura os eventos de mouse (mousedown, mousemove, mouseup) sobre a imagem da redação. Calcula a posição relativa do cursor em porcentagem (%) para garantir que o desenho seja responsivo.

Criação Dinâmica: Utiliza document.createElementNS('http://www.w3.org/2000/svg', 'polyline') para criar traços na tela em tempo real sem recarregar o DOM inteiro.

Avaliação: Agrupa notas por competência, salva o parecer geral e gerencia o status entre "rascunho" (draft) e "finalizado" (final).

4. Endpoints (Pasta api/)
Todos os arquivos nesta pasta agem como APIs RESTful consumindo requisições JSON e retornando JSON. Todos iniciam com session_start() e verificam a role do usuário logado para garantir proteção das rotas.

api_login.php: Busca o usuário no banco pelo e-mail e valida a senha criptografada usando a função nativa password_verify().

api_add_user.php: Valida os dados de cadastro, criptografa a nova senha com password_hash($password, PASSWORD_BCRYPT) e insere no banco. Restrito a administradores.

api_upload_essay.php: Processa o arquivo recebido (via FormData), valida a extensão (JPG, PNG, PDF), gera um nome único (uniqid()) e move para a pasta uploads/. Restrito a alunos.

api_get_my_essays.php: Faz um LEFT JOIN entre redações e correções para montar o histórico completo do aluno, incluindo os comentários visuais.

api_get_pending_essays.php: Retorna a fila de redações aguardando correção ou os rascunhos vinculados ao professor logado.

api_get_essay_details.php: Traz todos os detalhes de uma submissão específica, incluindo traços de rascunho se o professor já tiver iniciado a correção.

api_correct_essay.php: Recebe o payload massivo de correção. Utiliza Transações no Banco de Dados ($pdo->beginTransaction() e $pdo->commit()) para garantir que a nota geral, as notas por competência e todas as coordenadas dos comentários (x_pos, y_pos, excerpt) sejam salvas juntas com sucesso, prevenindo dados corrompidos caso algo falhe.

🚀 Como Rodar o Projeto
Suba os containers:
Na raiz do projeto (onde está o docker-compose.yml), execute:

Bash
docker-compose up -d --build
(Aguarde alguns instantes para o MySQL inicializar o script SQL na primeira execução).

Ajuste as Permissões de Upload (CRÍTICO):
O servidor web (Apache) precisa de permissão de escrita para salvar as imagens na pasta uploads/. Execute no seu terminal:

Bash
docker exec -it <NOME_DO_CONTAINER_WEB> chown -R www-data:www-data /var/www/html/uploads
(Se não souber o nome do container, digite docker ps para descobrir).

Acesse a Aplicação:
Abra no seu navegador: http://localhost:8000/Pages/login.php

🔑 Credenciais de Acesso Inicial
Para iniciar o uso da plataforma, utilize a conta de Administrador criada automaticamente:

E-mail: admin@teste.com

Senha: 123456

(Após o login, utilize o painel para criar usuários de teste com perfis de Professor e Aluno).

# Incrementar funcionalidades do Admin