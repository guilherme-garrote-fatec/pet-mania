# Arquitetura do Pet Mania

## Visão geral

O projeto usa uma arquitetura front-end em camadas leves. O objetivo é manter o protótipo simples, sem esconder regras em abstrações desnecessárias, mas evitar que telas, dados e componentes compartilhados voltem a se concentrar em um único arquivo.

## Fluxo de dependências

```text
types ← data
  ↑       ↑
utils ← components/ui
  ↑       ↑
pages / features
       ↑
     App.tsx
       ↑
    main.tsx
```

As camadas inferiores não devem importar páginas nem `App.tsx`.

## Responsabilidades

### `src/App.tsx`

É o orquestrador da aplicação:

- mantém o estado compartilhado das coleções simuladas;
- calcula os totais financeiros;
- implementa operações que afetam mais de um domínio, como concluir atendimento ou registrar venda;
- controla navegação, modal, toast e sessão;
- injeta dados e callbacks nas páginas.

Evite adicionar grandes blocos de JSX de tela neste arquivo. Uma nova área deve ser criada em `src/pages`.

### `src/types/domain.ts`

É a fonte única dos modelos de domínio (`Client`, `Pet`, `Appointment`, `Product`, `Service`, `Sale` e `Transaction`). Atualize este arquivo antes de propagar uma mudança estrutural pelos dados e componentes.

### `src/data/mockData.ts`

Contém somente dados iniciais e a conta de demonstração. Dados derivados pertencem ao `App.tsx` ou à página que os apresenta.

### `src/pages`

Cada arquivo representa um dos oito módulos:

- `Overview.tsx`
- `Schedule.tsx`
- `Clients.tsx`
- `Products.tsx`
- `Services.tsx`
- `Sales.tsx`
- `Finance.tsx`
- `Reports.tsx`

As páginas recebem dados e ações por propriedades. Elas não devem acessar ou modificar diretamente o `localStorage`.

### `src/features`

Agrupa fluxos de negócio reutilizados fora de uma página específica. `forms/PrototypeForm.tsx` concentra os formulários dos modais e seus títulos.

### `src/components`

- `auth`: experiência de autenticação e persistência de usuários.
- `ui`: elementos visuais genéricos, sem regras específicas de um módulo.

Cada componente React possui exportação padrão, seguindo a convenção do projeto.

### `src/utils`

Funções puras compartilhadas. Atualmente contém formatação de moeda BRL e datas em português do Brasil.

## Estado e persistência

As coleções operacionais usam `useState` no `App.tsx`. As relações são feitas por IDs:

- agendamento → cliente, pet e serviço;
- venda → produto;
- transações são criadas por atendimentos, vendas ou lançamentos manuais.

Somente duas chaves são persistidas:

- `pet-mania-session`: sessão atual;
- `pet-mania-users`: usuários cadastrados no navegador.

## Como adicionar uma funcionalidade

1. Atualize o modelo em `src/types/domain.ts`, se necessário.
2. Ajuste dados demonstrativos em `src/data/mockData.ts`.
3. Implemente regras compartilhadas no `App.tsx`.
4. Crie ou altere a página responsável.
5. Extraia UI para `components/ui` apenas quando ela for realmente reutilizável.
6. Preserve textos em português do Brasil e formatação local.
7. Execute `pnpm format` e `pnpm build`.
