# ✍️ RedaçãoPro

Plataforma de envio, correção e gestão de redações. Conecta o olhar clínico dos professores à dedicação dos alunos, oferecendo feedback visual estruturado e um banco de temas motivadores.

## 🚀 Funcionalidades Atuais

### 🎓 Para o Aluno

* **Envio de Redações:** Submissão de arquivos em PDF, JPG, PNG e WEBP.
* **Histórico e Espelho de Correção:** Visualização das notas por competência (modelo ENEM) e acesso ao espelho da redação com marcações visuais (SVG), apontando erros específicos no texto.
* **Banco de Temas:** Acesso a textos de apoio e imagens publicados pelos professores para auxiliar nas produções textuais.

### 👨‍🏫 Para o Professor / Avaliador

* **Fila de Correção:** Visualização de redações pendentes ou em análise.
* **Workspace de Avaliação Visual:** Ferramenta de desenho interativo sobre a imagem da redação para marcar erros, associar comentários a competências específicas e atribuir notas.
* **Gestão de Temas:** Criação, edição e exclusão de temas de redação. Suporte a upload de imagens de apoio e função de "Salvar Rascunho" antes da publicação para os alunos.

---

## 🛠️ Tecnologias Utilizadas

* **Frontend:** HTML5, CSS3, JavaScript Vanilla, Tailwind CSS (via CDN).
* **Backend:** PHP 8.2 (PDO para manipulação segura de dados).
* **Banco de Dados:** MySQL.
* **Infraestrutura:** Docker e Docker Compose (com script `entrypoint` para gerenciamento automático de permissões de pastas).

---

## 📂 Estrutura de Diretórios (Uploads)

O sistema de armazenamento de arquivos foi estruturado para evitar conflitos e organizar o disco do servidor automaticamente via Docker:

* `/api/uploads/redacoes/`: Arquivos enviados pelos alunos (sanitizados e renomeados no padrão `nome_arquivo_timestamp.ext`).
* `/api/uploads/themes/`: Imagens de apoio enviadas pelos professores durante a criação de novos temas.

---

# ⚙️ Como Rodar o Projeto Localmente

## 📋 Pré-requisitos

* **PHP** 8.2 ou superior (com as extensões `pdo_mysql` e `fileinfo` habilitadas).
* **Composer**.
* **MySQL** 5.7+ ou 8.x.

---

## 🪜 Passo a Passo

### 1. Clone o repositório

```bash
git clone https://github.com/seu-usuario/essay-plataform.git
cd essay-plataform
```

### 2. Instale as dependências do PHP

O PHPMailer é utilizado para envio de e-mails.

```bash
composer install
```

### 3. Crie o banco de dados

Você pode utilizar o script Python (recomendado) ou executar o SQL manualmente.

### Opção A — Script Python

O script cria o banco e todas as tabelas utilizando o arquivo `database/init.sql`.

```bash
pip install pymysql
python database/setup_db.py
```

O script utiliza as seguintes variáveis de ambiente:

* `DB_HOST`
* `DB_NAME`
* `DB_USER`
* `DB_PASS`

### Opção B — Manualmente

```bash
mysql -u root -p < database/init.sql
```

O arquivo `init.sql` cria automaticamente o banco `redacaopro`, suas tabelas e chaves estrangeiras.

---

### 4. Configure as credenciais do banco

O arquivo `database/db.php` utiliza as variáveis de ambiente abaixo.

Valores padrão:

* Host: `127.0.0.1`
* Banco: `redacaopro`
* Usuário: `root`
* Senha: `root`

Configure:

```bash
export DB_HOST=127.0.0.1
export DB_NAME=redacaopro
export DB_USER=root
export DB_PASS=root
```

Alternativamente, os valores podem ser configurados diretamente no arquivo `database/db.php`.

> **E-mails (opcional):** as funcionalidades de recuperação de senha dependem do SMTP,
> configurado via variáveis de ambiente (`SMTP_HOST`, `SMTP_USER`, `SMTP_PASS`,
> `SMTP_PORT`, `SMTP_FROM`, `SMTP_FROM_NAME`). Sem essas variáveis, o envio fica
> desabilitado — o cadastro e o login continuam funcionando normalmente. Nunca
> coloque credenciais reais no código; defina as variáveis também no
> `docker-compose.yml` se estiver usando Docker.

---

### 5. Configure as permissões das pastas de upload

Crie e libere as permissões das pastas utilizadas para armazenamento de arquivos:

```bash
mkdir -p api/uploads/redacoes api/uploads/themes
chmod -R 777 api/uploads
```

---

### 6. Inicie o servidor PHP

```bash
php -S localhost:8000
```

---

### 7. Acesse a plataforma

Abra o navegador:

```text
http://localhost:8000
```

---

# 🐳 Executando com Docker (Opcional)

O projeto possui suporte a Docker com:

* Apache
* PHP 8.2
* MySQL 8
* Script `entrypoint.sh` para configuração automática de permissões

O banco é criado automaticamente na primeira execução utilizando `database/init.sql`.

## Pré-requisitos

Certifique-se de possuir:

* Docker instalado.
* Docker Compose instalado.

Os arquivos abaixo devem existir na raiz do projeto:

```text
Dockerfile
docker-compose.yml
entrypoint.sh
```

Caso esses arquivos não estejam versionados, será necessário recriá-los ou restaurá-los após o clone.

---

## Subir os containers

Execute:

```bash
docker compose up -d --build
```

Acesse:

```text
http://localhost:8000
```

---

## Acesso ao MySQL pelo host

O MySQL fica exposto na porta:

```text
3307
```

Configuração:

```text
Host: localhost
Porta: 3307
Usuário: root
Senha: root
Banco: redacaopro
```

---

## Recriar o banco de dados

Caso o arquivo `database/init.sql` tenha sido alterado e seja necessário recriar o banco:

```bash
docker compose down -v
docker compose up -d --build
```

---

# 🗺️ Próximos Passos (Roadmap)

As próximas atualizações do projeto focarão na escalabilidade da gestão de usuários e nos fluxos de estudo:

* [ ] **Lógica de Turmas:** Criar a estrutura de turmas no banco de dados.
* [ ] **Vínculo Aluno-Turma:** Permitir adicionar e remanejar alunos dentro de turmas específicas.
* [ ] **Gestão Facilitada:** Desenvolver um painel administrativo/dashboard mais robusto e intuitivo.
* [ ] **Organização de Temas por Turmas:** Permitir criar temas e disponibilizá-los para turmas específicas.
* [ ] **Envio de Temas Flexível:** Permitir enviar um mesmo tema para uma ou várias turmas.
