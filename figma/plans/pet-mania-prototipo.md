# Plano de implementação — Protótipo web Pet Mania

## 1. Objetivo e decisões confirmadas

Construir um protótipo web navegável e responsivo para a **Pet Mania**, reunindo em uma única experiência os dois conjuntos de funcionalidades descritos no documento:

- operação do pet shop: clientes, pets, agenda e atendimentos;
- gestão comercial e financeira: produtos, serviços, vendas, receitas, custos, lucro e relatórios.

Decisões confirmadas:

- O escopo será o **protótipo completo integrado**.
- Os dados serão **simulados no navegador**, sem backend, autenticação ou banco de dados.
- O protótipo priorizará apresentação visual, navegação e demonstração dos fluxos essenciais; os dados voltarão ao estado inicial ao recarregar a página.
- A experiência representará um usuário com visão operacional e administrativa combinada, evitando criar um fluxo de login ou permissões que não foi solicitado nos requisitos.

## 2. Direção visual

Criar uma interface de gestão profissional, acolhedora e claramente ligada ao segmento pet, sem aparência infantil:

- layout de aplicação com barra lateral no desktop e navegação compacta no mobile;
- identidade “Pet Mania” com marca tipográfica e símbolo simples de pata;
- paleta clara com verde-petróleo como cor principal, coral como destaque e fundos neutros quentes;
- tipografia sans-serif de alta legibilidade, com hierarquia forte em títulos, indicadores e tabelas;
- cartões com cantos suaves, bordas discretas e sombras leves;
- status semânticos consistentes para agendado, em andamento, concluído e cancelado;
- ícones lineares consistentes por meio de `lucide-react`, adicionando a dependência ao projeto;
- gráficos compactos e elementos financeiros desenhados com CSS/SVG local, sem biblioteca pesada de visualização;
- estados de foco, hover, seleção, vazio, sucesso e validação visíveis e acessíveis.

Como o repositório não possui um design system ou biblioteca de componentes, criar um pequeno conjunto local de componentes reutilizáveis, seguindo os mesmos tokens visuais.

## 3. Arquitetura do protótipo

### Arquivos principais

- `src/App.tsx`: composição da aplicação, estado global do protótipo, navegação entre módulos, dados simulados e fluxos interativos.
- `src/index.css`: importação do Tailwind, tema visual, estilos globais, tipografia e pequenos ajustes que não sejam convenientes em classes utilitárias.
- `package.json` e `pnpm-lock.yaml`: inclusão de `lucide-react`.

Manter a implementação compacta no scaffold atual. Extrair componentes para arquivos adicionais em `src/components/` apenas se `App.tsx` ficar difícil de manter durante a implementação; nesse caso, separar somente primitives de interface e módulos maiores, sem introduzir roteador ou gerenciamento de estado externo.

### Modelo de estado

Usar estado React local com coleções simuladas relacionadas por IDs:

- clientes;
- pets vinculados a clientes;
- serviços;
- produtos;
- agendamentos vinculando cliente, pet e serviço;
- vendas de produtos;
- lançamentos financeiros vinculados a agendamentos ou vendas.

Derivar os indicadores financeiros em tempo de execução:

- receita total;
- custos de insumos;
- comissões;
- lucro líquido = receitas − custos − comissões;
- ticket médio;
- variação demonstrativa em relação ao período anterior.

## 4. Estrutura de navegação e telas

### Shell da aplicação

- Sidebar com marca Pet Mania e itens: Visão geral, Agenda, Clientes & Pets, Produtos, Serviços, Vendas, Financeiro e Relatórios.
- Cabeçalho contextual com título da seção, data/período, busca ou ação principal conforme a tela.
- Perfil compacto da usuária de demonstração (“Sônia Martins”, Administradora).
- No mobile, sidebar substituída por cabeçalho com botão de menu e painel lateral sobreposto.

### Visão geral

Será a tela inicial e a peça principal da apresentação:

- saudação e resumo da operação do dia;
- indicadores de faturamento, lucro líquido, atendimentos e ticket médio;
- mini gráfico de desempenho financeiro dos últimos meses;
- agenda do dia em formato de linha do tempo;
- distribuição dos atendimentos por status;
- lista de atividades recentes;
- ações rápidas para novo agendamento, nova venda e novo cliente.

### Agenda

- alternância visual entre “Hoje” e “Semana”;
- seleção de data e navegação entre períodos;
- cartões de horários com pet, tutor, serviço, duração, profissional e status;
- filtros por status e busca por pet ou cliente;
- fluxo “Novo agendamento” em modal/drawer com cliente, pet, serviço, data e horário;
- validação demonstrativa de conflito: impedir dois agendamentos no mesmo horário e exibir mensagem clara;
- ações para concluir ou cancelar um atendimento, atualizando indicadores e histórico.

### Clientes & Pets

- busca e lista de tutores com contato, quantidade de pets e último atendimento;
- painel de detalhes do cliente com seus animais e histórico resumido;
- cadastro demonstrativo de cliente e pet, com campos obrigatórios e validação;
- inclusão imediata do novo registro na lista em memória.

### Produtos

- tabela responsiva com produto, categoria, estoque, preço e status;
- busca e filtro de estoque;
- fluxo de cadastro/edição com nome, categoria, preço, custo e quantidade;
- exclusão com confirmação e feedback visual;
- alertas de baixo estoque.

### Serviços

- cards ou tabela com banho, tosa e pacotes, mostrando preço, custo estimado, duração e margem;
- busca e filtro por disponibilidade;
- cadastro/edição e desativação de serviço;
- cálculo e exibição da margem estimada.

### Vendas

- histórico de vendas recentes;
- ação “Nova venda” com seleção de produto, quantidade e cliente opcional;
- cálculo automático de subtotal/total;
- confirmação da venda atualizando estoque, receita, lucro e atividades recentes;
- bloqueio demonstrativo de quantidade superior ao estoque.

### Financeiro

- filtro de período visual, com opções predefinidas;
- cards de receita, custos, comissões e lucro líquido;
- gráfico comparativo mensal entre receita e despesas;
- composição das despesas;
- tabela de lançamentos financeiros com origem, data, tipo e valor;
- modal para registrar receita ou custo manual, refletindo automaticamente nos totais.

### Relatórios

- filtros de período e tipo de relatório;
- resumo financeiro com indicadores consolidados;
- tabela de desempenho por serviço;
- ranking dos serviços mais realizados;
- botão “Exportar relatório” com feedback demonstrativo de sucesso, sem gerar arquivo real;
- estado sem resultados quando um filtro não possuir dados.

## 5. Componentes e padrões reutilizáveis

Criar componentes locais coerentes para:

- `AppShell`, `Sidebar` e cabeçalho mobile;
- `PageHeader`;
- `StatCard`;
- `StatusBadge`;
- `SearchField` e filtros segmentados;
- `DataTable`/lista responsiva;
- `Modal` ou `Drawer` com fechamento por botão, clique no fundo e tecla Escape;
- campos de formulário, selects e mensagens de validação;
- `EmptyState`;
- toast/feedback temporário de sucesso ou erro;
- gráficos simples;
- confirmação de ação destrutiva.

Garantir rótulos associados aos campos, navegação por teclado, contraste adequado e textos em português do Brasil. Formatar moeda como BRL e datas no padrão brasileiro.

## 6. Dados de demonstração

Popular o protótipo com dados realistas e coerentes, incluindo:

- tutores e pets com nomes brasileiros;
- serviços como Banho Completo, Tosa Higiênica, Banho + Tosa e Hidratação;
- produtos como shampoo, condicionador, perfume e acessórios;
- agendamentos distribuídos entre os estados do fluxo;
- lançamentos de receita, insumos e comissões que fechem matematicamente com os indicadores apresentados.

Evitar imagens externas: este é um painel operacional, e avatares com iniciais/ícones oferecem melhor consistência e carregamento imediato do que fotografia.

## 7. Comportamentos e regras demonstradas

- Toda navegação da sidebar troca o conteúdo sem recarregar a página.
- Ações primárias abrem formulários funcionais; submissões válidas alteram o estado local e mostram confirmação.
- Campos obrigatórios vazios mostram erro contextual.
- Agendamentos duplicados no mesmo horário são bloqueados.
- Vendas acima do estoque são bloqueadas; vendas válidas reduzem o estoque.
- Finalizar um atendimento registra a receita associada e atualiza o financeiro.
- Receita, custos, comissões e lucro são recalculados automaticamente.
- Exclusões exigem confirmação.
- Controles sem implementação real, como exportação, exibem feedback explícito de demonstração em vez de aparentarem estar quebrados.
- Recarregar a página restaura o conjunto inicial de dados, comportamento esperado para este protótipo sem backend.

## 8. Responsividade

- Desktop: sidebar fixa, conteúdo em grade e tabelas completas.
- Tablet: sidebar reduzida/ocultável e grades com menos colunas.
- Mobile: menu em drawer, indicadores empilhados, tabelas transformadas em cartões/listas e modais em tela quase cheia.
- Não depender de larguras fixas; evitar overflow horizontal nas telas principais.

## 9. Verificação

Após a implementação:

1. Executar o formatador definido pelo projeto: `pnpm format`.
2. Executar `pnpm build`, adequado ao escopo amplo da alteração, e corrigir qualquer erro de TypeScript/Vite.
3. Revisar os fluxos principais no código/preview já ativo, sem iniciar outro servidor:
   - navegação entre todos os módulos;
   - criação de cliente/pet;
   - novo agendamento válido e conflito de horário;
   - nova venda válida e venda sem estoque suficiente;
   - conclusão de atendimento e reflexo nos indicadores;
   - lançamento financeiro manual;
   - fechamento de modal e menu mobile.
4. Conferir responsividade nas larguras aproximadas de mobile, tablet e desktop pelo preview existente.
5. Verificar que não há emojis usados como ícones, links/ações sem resposta, textos quebrados ou valores financeiros incoerentes.

## 10. Critérios de conclusão

O protótipo estará concluído quando:

- todas as oito áreas forem acessíveis pela navegação;
- o dashboard apresentar uma visão operacional e financeira convincente;
- os fluxos essenciais alterarem os dados simulados de forma coerente;
- conflitos de agenda, validações obrigatórias e limite de estoque estiverem representados;
- a interface funcionar em desktop e mobile;
- o visual estiver consistente e adequado à marca Pet Mania;
- formatação e build terminarem com sucesso.
