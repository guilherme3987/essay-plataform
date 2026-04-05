```markdown
# ✍️ RedaçãoPro

Plataforma de envio, correção e gestão de redações. Conecta o olhar clínico dos professores à dedicação dos alunos, oferecendo feedback visual estruturado e um banco de temas motivadores.

## 🚀 Funcionalidades Atuais

### 🎓 Para o Aluno
* **Envio de Redações:** Submissão de arquivos em PDF, JPG, PNG e WEBP.
* **Histórico e Espelho de Correção:** Visualização das notas por competência (modelo ENEM) e acesso ao espelho da redação com marcações visuais (SVG) apontando erros exatos no texto.
* **Banco de Temas:** Acesso a textos de apoio e imagens publicados pelos professores para embasar as produções textuais.

### 👨‍🏫 Para o Professor / Avaliador
* **Fila de Correção:** Visualização de redações pendentes ou em análise.
* **Workspace de Avaliação Visual:** Ferramenta de desenho interativo sobre a imagem da redação para marcar erros, associar comentários a competências específicas e atribuir notas.
* **Gestão de Temas:** Criação, edição e exclusão de temas de redação. Suporte a upload de imagens de apoio e função de "Salvar Rascunho" antes de publicar para os alunos.

---

## 🛠️ Tecnologias Utilizadas
* **Frontend:** HTML5, CSS3, JavaScript Vanilla, Tailwind CSS (via CDN).
* **Backend:** PHP 8.2 (PDO para manipulação segura de dados).
* **Banco de Dados:** MySQL.
* **Infraestrutura:** Docker & Docker Compose (com script de `entrypoint` para gestão autônoma de permissões de pastas).

---

## 📂 Estrutura de Diretórios (Uploads)

O sistema de armazenamento de arquivos foi estruturado para evitar conflitos e organizar o disco do servidor automaticamente via Docker:

* `/api/uploads/redacoes/`: Arquivos enviados pelos alunos (sanitizados e renomeados com padrão `nome_arquivo_timestamp.ext`).
* `/api/uploads/themes/`: Imagens de apoio enviadas pelos professores ao criar novos temas.

---

## ⚙️ Como rodar o projeto localmente

1. Certifique-se de ter o **Docker** e o **Docker Compose** instalados.
2. Clone este repositório.
3. Abra o terminal na pasta raiz do projeto e execute:
   ```bash
   docker compose up -d --build
   ```
4. Acesse a plataforma no navegador: `http://localhost:8000`

*Nota: Se você fez alterações no `init.sql` e precisa resetar o banco de dados para aplicar a nova estrutura de tabelas, execute:*
```bash
docker compose down -v
docker compose up -d --build
```

---

## 🗺️ Próximos Passos (Roadmap)

As próximas atualizações do projeto focarão na escalabilidade da gestão de usuários e fluxos de estudo:

- [ ] **Lógica de Turmas:** Criar a estrutura de turmas/classes no banco de dados.
- [ ] **Vínculo Aluno-Turma:** Permitir a adição e o remanejamento de alunos dentro de turmas específicas.
- [ ] **Gestão Facilitada:** Desenvolver um painel administrativo/dashboard mais robusto e intuitivo para organizar os temas por turmas (ex: enviar um tema específico apenas para a turma do "3º Ano A"). Adicionar temas e enviar para qualquer turma
- [ ] **Filtros e Relatórios:** Permitir que o professor filtre o desempenho por turma e identifique as maiores dificuldades gerais dos alunos nas competências avaliadas.
```