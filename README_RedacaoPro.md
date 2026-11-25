# RedaçãoPro

> Plataforma SaaS para gestão, submissão e correção de redações,
> conectando professores e alunos em um fluxo pedagógico digital.

## Sobre o projeto

O **RedaçãoPro** é uma plataforma tecnológica voltada à organização do
processo de envio, correção e devolução de redações. A solução foi
concebida para professores autônomos, cursinhos preparatórios e
instituições de ensino que desejam substituir fluxos dispersos --- como
papel, e-mail, WhatsApp, pastas no Drive e fotos --- por um ambiente
centralizado.

O sistema busca reduzir o esforço administrativo do professor e oferecer
ao aluno um feedback visual e detalhado sobre seu desempenho.

-   **Área:** EdTech (Tecnologia Educacional)
-   **Modelo de negócio:** Software as a Service (SaaS)
-   **Tipo de solução:** ferramenta de produtividade para corretores e
    ambiente de aprendizagem para alunos
-   **Estágio descrito no projeto:** MVP, com funcionalidades principais
    de submissão, correção e gestão de temas implementadas

## Problema

A preparação para exames como o ENEM e concursos exige prática constante
de escrita e correções detalhadas. Entretanto, muitos professores ainda
recebem redações por diferentes canais, organizam arquivos manualmente e
devolvem comentários sem um histórico centralizado.

Esse cenário pode causar:

-   Perda de arquivos e confusão entre versões;
-   Tempo excessivo gasto com tarefas administrativas;
-   Dificuldade para localizar e contextualizar os erros no texto;
-   Feedbacks pouco padronizados;
-   Falta de visibilidade sobre a evolução do aluno.

### Pergunta orientadora

> Como podemos digitalizar o processo de correção de redações de modo
> que o professor reduza seu tempo de gestão e o aluno receba feedbacks
> visuais e precisos sobre suas competências linguísticas?

### Escopo

O foco inicial está nas etapas de **envio, correção e feedback**. A
proposta não contempla, nesta etapa, produção assistida por IA em tempo
real nem gestão financeira ou administrativa da instituição. A avaliação
permanece sob responsabilidade do professor.

## Proposta de valor

O RedaçãoPro oferece gestão de turmas e temas, submissão de redações,
fila de correção e um espaço de trabalho visual. O professor pode marcar
o ponto específico do erro diretamente sobre a imagem da redação,
associá-lo a uma competência, atribuir notas e escrever um parecer
final.

O aluno recebe um espelho de correção com as marcações, as notas por
competência e o parecer do professor.

**Benefícios pretendidos:**

-   Centralização dos envios e das correções;
-   Redução do esforço administrativo;
-   Organização do fluxo de trabalho docente;
-   Feedback contextualizado e visual;
-   Histórico que pode apoiar o acompanhamento da evolução do aluno;
-   Profissionalização do serviço de correção.

O produto **não promete correção automática por IA nem garantia de
aprovação**. Seu foco é tornar mais eficiente o processo de correção
humana.

## Público-alvo

### Segmento prioritário

Professores autônomos de redação, especialmente aqueles que atendem
vários alunos e precisam organizar o próprio serviço.

### Outros segmentos

-   Professores particulares;
-   Freelancers que corrigem redações para alunos de diferentes origens;
-   Cursinhos preparatórios;
-   Instituições de ensino médio com foco em vestibulares.

O mercado inicial considerado é o de preparação para o ENEM, com
possibilidade de expansão futura para concursos públicos, redações
corporativas e ensino de línguas estrangeiras.

## Persona de referência

**Prof. Ricardo, 34 anos --- professor autônomo de redação.**

Atualmente, recebe fotos de redações por WhatsApp e e-mail, organiza
arquivos em pastas, corrige manualmente e devolve as correções aos
alunos. Entre suas principais dificuldades estão a perda de arquivos, a
confusão entre versões e o tempo gasto para elaborar feedbacks.

> "Eu gasto mais tempo organizando quem enviou o quê do que corrigindo a
> redação em si."

Sua necessidade central é receber os textos em um único ambiente e
apontar os erros diretamente na redação, sem precisar descrever cada
localização por mensagens longas.

## Funcionalidades do MVP

  -----------------------------------------------------------------------
  Funcionalidade          Descrição               Usuário principal
  ----------------------- ----------------------- -----------------------
  Gestão de usuários e    Login e autenticação    Todos
  acessos                 por perfil, como        
                          `student`, `teacher` e  
                          `admin`.                

  Banco de temas          Criação de temas com    Professor
                          título, texto de apoio, 
                          imagens e PDFs,         
                          vinculados a turmas.    

  Submissão de redações   Envio de arquivos nos   Aluno
                          formatos JPG, PNG e     
                          PDF.                    

  Fila de correção        Visualização das        Professor
                          redações pendentes,     
                          organizadas por data e  
                          aluno.                  

  Workspace de correção   Exibição da redação,    Professor
  visual                  marcações sobre a       
                          imagem, associação dos  
                          erros a competências,   
                          atribuição de notas e   
                          parecer final.          

  Espelho da correção     Exibição das marcações, Aluno
                          notas, erros apontados  
                          e parecer final.        
  -----------------------------------------------------------------------

### Fluxo principal de utilização

1.  O professor cria um tema e o vincula a uma turma.
2.  O aluno acessa o tema, produz sua redação e envia o arquivo.
3.  A redação aparece na fila de correção do professor.
4.  O professor abre o texto, marca os pontos que precisam de atenção,
    associa os erros às competências e atribui a nota.
5.  O aluno acessa o espelho da correção e visualiza o feedback.

### Prioridades da primeira versão

-   Garantir o fluxo completo: **tema → submissão → correção →
    espelho**;
-   Oferecer marcações visuais com coordenadas X/Y sobre a imagem da
    redação.

## Diferencial competitivo

O RedaçãoPro pretende oferecer uma ferramenta de produtividade para quem
corrige, em vez de vender diretamente a correção como serviço final ao
aluno.

Seu diferencial está na integração entre gestão de turmas e temas e um
workspace de correção visual. As marcações preservam a imagem e a
caligrafia original do estudante, aproximando a experiência da correção
manual.

O projeto cita como aplicações correlatas:

-   Corrija-me;
-   Imaginie;
-   Redação Online;
-   Pontue;
-   Plataforma Redigir.

Essas soluções são apresentadas no projeto como referências de serviços
de correção voltados ao aluno. O posicionamento pretendido para o
RedaçãoPro é atender à rotina operacional do professor.

### Possíveis evoluções

As funcionalidades abaixo são possibilidades futuras, não devem ser
entendidas como necessariamente implementadas no MVP:

-   Banco de comentários rápidos;
-   Integração aprimorada com tablets e canetas digitais;
-   Pré-correção assistida por IA, mantendo a autoria da avaliação com o
    professor;
-   Painel de evolução individual do aluno;
-   Analisador de desempenho da turma;
-   Recursos de gamificação;
-   Tutor de reescrita.

## Modelo de negócio

O modelo preliminar é baseado em assinaturas mensais, com valores
relacionados à quantidade de alunos administrados pelo professor.

  ------------------------------------------------------------------------
  Plano                               Preço proposto Limite / público
  --------------------- ---------------------------- ---------------------
  Starter                              R\$ 29,90/mês Até 10 alunos

  Professional                         R\$ 59,90/mês Até 50 alunos

  Institucional                         Sob consulta Instituições ou
                                                     operações com
                                                     quantidade ilimitada
                                                     de alunos
  ------------------------------------------------------------------------

> Os preços são hipóteses iniciais e ainda precisam ser validados com
> potenciais clientes.

A cobrança por quantidade de alunos deverá ser comparada com
alternativas, como o pagamento por redação corrigida.

### Business Model Canvas --- síntese

  -----------------------------------------------------------------------
  Componente                          Hipótese inicial
  ----------------------------------- -----------------------------------
  Segmentos de clientes               Professores autônomos, professores
                                      particulares e cursinhos
                                      preparatórios.

  Proposta de valor                   Organização e profissionalização do
                                      trabalho de correção, com feedback
                                      visual para os alunos.

  Canais                              Marketing digital, redes sociais,
                                      influenciadores da educação,
                                      demonstrações, parcerias e
                                      indicações.

  Relacionamento                      Autoatendimento, suporte por chat,
                                      orientações iniciais, coleta de
                                      sugestões e comunicação de
                                      atualizações.

  Fontes de receita                   Assinaturas mensais por quantidade
                                      de alunos e plano institucional.

  Recursos principais                 Equipe de desenvolvimento,
                                      código-fonte, banco de dados,
                                      servidores, armazenamento de
                                      arquivos e conhecimentos de UX.

  Atividades principais               Desenvolvimento, manutenção,
                                      infraestrutura, suporte, divulgação
                                      e evolução do produto.

  Parcerias principais                Professores influentes, cursinhos,
                                      profissionais da educação, usuários
                                      iniciais e fornecedores
                                      tecnológicos.

  Estrutura de custos                 Hospedagem, armazenamento,
                                      desenvolvimento, manutenção,
                                      suporte e divulgação.
  -----------------------------------------------------------------------

O Canvas é preliminar. As estratégias comerciais e financeiras dependem
de testes com potenciais clientes e poderão ser revistas conforme os
resultados.

## Tecnologias e recursos

O projeto apresenta como tecnologias e recursos previstos:

-   PHP;
-   Python;
-   MySQL;
-   Docker;
-   Tailwind CSS;
-   Servidor web;
-   Armazenamento de arquivos;
-   Arquivos de redação em PDF, JPG e PNG.

A proposta técnica considera uma arquitetura cliente-servidor,
autenticação e armazenamento de arquivos. As marcações visuais utilizam
coordenadas X/Y para associar o feedback a regiões da imagem da redação.

> Este documento de projeto não especifica comandos de instalação,
> variáveis de ambiente, versões de dependências ou configuração de
> execução. Consulte os arquivos de implementação do repositório para
> essas instruções.

## Riscos e pontos de atenção

-   **Adesão:** professores podem resistir à mudança dos hábitos
    baseados em papel e WhatsApp.
-   **Usabilidade:** as ferramentas de marcação precisam ser fluidas e
    não tornar a correção mais lenta.
-   **Segurança:** uploads devem ser tratados de forma segura para
    reduzir riscos relacionados a arquivos maliciosos.
-   **Infraestrutura:** o crescimento do número de usuários e de
    arquivos pode elevar os custos de hospedagem e armazenamento.
-   **Viabilidade comercial:** os preços e a disposição de pagamento
    ainda precisam ser investigados.

## Plano de investigação e validação

O projeto propõe as seguintes atividades iniciais:

1.  **Entrevistas com usuários:** conversar com cinco professores de
    redação.
2.  **Teste de usabilidade:** comparar o tempo de correção utilizando
    papel e RedaçãoPro.
3.  **Validação de valor:** disponibilizar o MVP para três professores e
    coletar feedback.
4.  **Ajuste da persona:** refinar a persona com base nos relatos reais.
5.  **Revisão do Canvas:** atualizar o modelo de negócio conforme as
    evidências coletadas.

### Indicadores iniciais

-   Número de redações corrigidas por hora por professor;
-   Tempo entre o recebimento do arquivo e a entrega da nota;
-   Retenção de alunos que acessam o espelho de correção;
-   Percepção de usabilidade;
-   Avaliação dos usuários sobre a utilidade da plataforma.

Uma hipótese de validação apresentada é verificar se professores
conseguem reduzir em pelo menos **30%** o tempo entre receber a redação
e entregar a nota. Essa meta ainda precisa ser testada.

### Hipóteses a validar

1.  Professores autônomos representam um segmento com demanda
    suficiente?
2.  Os potenciais clientes reconhecem valor em uma solução
    especializada?
3.  Os preços dos planos Starter e Professional são compatíveis com a
    disposição de pagamento?
4.  A assinatura por quantidade de alunos é mais atrativa que a cobrança
    por redação?
5.  Os canais de divulgação conseguem atrair clientes a um custo viável?
6.  Os custos de hospedagem e armazenamento permanecem sustentáveis com
    o crescimento?
7.  Cursinhos e instituições demonstram interesse no plano
    institucional?
8.  As funcionalidades complementares correspondem a necessidades reais
    dos professores?
9.  As marcações com mouse são suficientes ou será necessário aprimorar
    o suporte a tablets e canetas digitais?

## Equipe e contexto acadêmico

Projeto empreendedor desenvolvido no contexto do curso de **Bacharelado
em Sistemas de Informação** da **Universidade do Estado da Bahia
(UNEB)**, Departamento de Ciências Exatas e da Terra (DCET), Campus I.

-   **Docente:** Prof. Dr. José Gileá de Souza
-   **Discentes:** Guilherme, Iago Freitas e Eric

## Status do projeto

O documento de referência descreve o RedaçãoPro como um MVP com
funcionalidades centrais de gestão de temas, submissão, fila de
correção, correção visual e espelho para o aluno.

A próxima etapa indicada é validar o problema, a usabilidade e a
proposta de valor com professores reais, utilizando os resultados para
ajustar o produto e o modelo de negócio.

------------------------------------------------------------------------

*README elaborado a partir do documento "Projeto Empreendedor:
RedaçãoPro". As funcionalidades futuras, preços e componentes do modelo
de negócio foram mantidos como propostas ou hipóteses quando assim
caracterizados no projeto.*
