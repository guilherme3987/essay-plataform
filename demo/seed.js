// Dados iniciais da demonstração RedaçãoPro.
// Use o botão "Reiniciar" para voltar a este estado.
(function () {
  // Gera uma marcação retangular (em %) sobre as linhas l1..l2 da folha de redação (imagem 720x807).
  const W = 720, H = 807, Y0 = 20, P = 25.07;
  function box(l1, l2, x1, x2) {
    const t = Y0 + (l1 - 1) * P - 11, b = Y0 + (l2 - 1) * P + 11;
    const pts = [[x1, t], [x2, t], [x2, b], [x1, b], [x1, t]]
      .map(([x, y]) => ({ x: +(x / W * 100).toFixed(2), y: +(y / H * 100).toFixed(2) }));
    return { x_pos: pts[0].x, y_pos: pts[0].y, excerpt: JSON.stringify(pts) };
  }
  const c = (id, competency, area, comment) => Object.assign({ id, correction_id: 1, competency, comment }, area);

  // Perfis exibidos na tela inicial. A pages correspondente também define DEMO_ROLE.
  window.DEMO_PROFILES = {
    student: { id: 3, label: 'Aluno', page: 'student_dashboard.html' },
    teacher: { id: 2, label: 'Professor', page: 'teacher_dashboard.html' },
    admin: { id: 1, label: 'Administrador', page: 'admin.html' }
  };

  window.DEMO_SEED = {
    users: [
      { id: 1, name: 'Administrador', email: 'admin@redacaopro.local', role: 'admin', created_at: '2026-08-01T08:00:00' },
      { id: 2, name: 'Professor Teste', email: 'professor@redacaopro.local', role: 'teacher', created_at: '2026-08-01T08:05:00' },
      { id: 3, name: 'Aluno Teste', email: 'aluno@redacaopro.local', role: 'student', created_at: '2026-08-01T08:10:00' },
      { id: 4, name: 'Ana Souza', email: 'ana@redacaopro.local', role: 'student', created_at: '2026-08-02T09:00:00' },
      { id: 5, name: 'Bruno Lima', email: 'bruno@redacaopro.local', role: 'student', created_at: '2026-08-02T09:10:00' },
      { id: 6, name: 'Carla Mendes', email: 'carla@redacaopro.local', role: 'student', created_at: '2026-08-03T10:00:00' }
    ],
    classes: [
      { id: 1, teacher_id: 2, name: '3º Ano A', created_at: '2026-08-03T09:00:00' },
      { id: 2, teacher_id: 2, name: '3º Ano B', created_at: '2026-08-03T09:05:00' }
    ],
    class_students: [
      { class_id: 1, student_id: 3 }, { class_id: 1, student_id: 4 },
      { class_id: 1, student_id: 5 }, { class_id: 2, student_id: 6 }
    ],
    themes: [
      { id: 1, created_by: 2, status: 'published', image_path: null, pdf_path: null, created_at: '2026-11-02T10:00:00',
        title: 'Invisibilidade do trabalho de cuidado realizado pela mulher no Brasil',
        content: 'Cuidar de crianças, idosos e da casa é um trabalho essencial, mas raramente remunerado ou reconhecido. No Brasil, essa tarefa recai majoritariamente sobre as mulheres e limita o acesso delas ao mercado de trabalho, à educação e ao lazer.\n\nDiscuta as causas dessa invisibilidade e proponha medidas que o poder público e a sociedade podem adotar.' },
      { id: 2, created_by: 2, status: 'published', image_path: null, pdf_path: null, created_at: '2026-10-20T10:00:00',
        title: 'Desafios para o enfrentamento da invisibilidade do registro civil no Brasil',
        content: 'Sem certidão de nascimento, uma pessoa tem dificuldade para estudar, receber benefícios e exercer direitos básicos. Ainda hoje, milhares de brasileiros vivem sem documentação, sobretudo em regiões afastadas dos centros urbanos.\n\nAnalise o problema e apresente uma proposta de intervenção.' },
      { id: 3, created_by: 2, status: 'published', image_path: null, pdf_path: null, created_at: '2026-10-05T10:00:00',
        title: 'Manipulação do comportamento do usuário pelo controle de dados na internet',
        content: 'Plataformas digitais coletam informações sobre hábitos e preferências para direcionar conteúdos e anúncios. Isso levanta dúvidas sobre liberdade de escolha e privacidade.\n\nDiscuta como o controle de dados pode influenciar o comportamento e o que fazer a respeito.' },
      { id: 4, created_by: 2, status: 'draft', image_path: null, pdf_path: null, created_at: '2026-11-18T10:00:00',
        title: 'Democratização do acesso ao cinema no Brasil',
        content: 'Rascunho: texto de apoio ainda em preparação. Este tema ainda não foi publicado para os alunos.' }
    ],
    theme_classes: [
      { theme_id: 1, class_id: 1 }, { theme_id: 2, class_id: 1 }, { theme_id: 2, class_id: 2 }, { theme_id: 3, class_id: 1 }
    ],
    essays: [
      { id: 1, user_id: 3, file_path: 'assets/demo/redacao-2.webp', status: 'corrected', created_at: '2026-11-10T14:30:00' },
      { id: 2, user_id: 3, file_path: 'assets/demo/redacao-pendente.jpg', status: 'pending', created_at: '2026-11-20T10:15:00' },
      { id: 3, user_id: 4, file_path: 'assets/demo/redacao-pendente.jpg', status: 'pending', created_at: '2026-11-22T09:40:00' }
    ],
    corrections: [
      { id: 1, essay_id: 1, teacher_id: 2, score: 920, comp1_score: 160, comp2_score: 200, comp3_score: 160, comp4_score: 200, comp5_score: 200,
        general_feedback: 'Texto muito bem estruturado, com tese clara, repertório pertinente e proposta de intervenção completa. Para chegar aos 1000, atente para a progressão das ideias no segundo desenvolvimento e revise pontuações em períodos longos.' }
    ],
    comments: [
      c(1, 2, box(1, 2, 70, 640), 'Repertório sociocultural legitimado e pertinente: o conceito de Milton Santos dialoga diretamente com o tema.'),
      c(2, 3, box(5, 6, 40, 700), 'Tese clara: anuncia os dois eixos que serão desenvolvidos (descaso governamental e falha educacional).'),
      c(3, 1, box(9, 10, 40, 700), 'Ótimo domínio da norma culta e da pontuação, mesmo em períodos de sintaxe longa.'),
      c(4, 4, box(14, 14, 40, 360), 'Bom uso de conectivo conclusivo para fechar o parágrafo e retomar a tese.'),
      c(5, 5, box(25, 26, 40, 700), 'Proposta bem estruturada: agente, ação, meio e finalidade estão presentes.'),
      c(6, 5, box(28, 29, 40, 700), 'Segunda proposta articulada, com agente e detalhamento da ação.')
    ]
  };
})();
