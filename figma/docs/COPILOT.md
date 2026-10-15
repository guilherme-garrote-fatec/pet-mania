# Contexto para agentes de código e GitHub Copilot

Leia este arquivo, o `AGENTS.md` da raiz e `docs/ARCHITECTURE.md` antes de alterar o projeto.

## Produto

**Pet Mania** é um protótipo acadêmico completo de gestão para pet shop. Ele deve continuar demonstrável sem backend e cobrir oito módulos: Visão geral, Agenda, Clientes & Pets, Produtos, Serviços, Vendas, Financeiro e Relatórios.

## Restrições importantes

- Use React, TypeScript, Vite e Tailwind CSS v4 já instalados.
- Não adicione backend, banco, roteador ou gerenciador de estado sem solicitação explícita.
- O servidor de desenvolvimento do Figma Make já está ativo; não inicie outro.
- Preserve a identidade verde-petróleo/coral e a tipografia DM Sans + Manrope.
- Use `lucide-react` para ícones; não use emojis como ícones.
- Mantenha textos da interface em português do Brasil.
- Componentes React devem ter exportação padrão.
- Não duplique tipos de domínio fora de `src/types/domain.ts`.
- Não coloque dados simulados dentro de páginas; use `src/data/mockData.ts`.
- Não mova regras que alteram vários módulos para componentes visuais; mantenha-as no `App.tsx`.
- A aplicação é intencionalmente client-side. Dados operacionais reiniciam no reload.

## Padrões de implementação

- Uma página por módulo em `src/pages`.
- Componentes compartilhados pequenos em `src/components/ui`.
- Fluxos de negócio maiores e reutilizáveis em `src/features`.
- Funções puras e sem JSX em `src/utils`.
- Prefira propriedades tipadas e os modelos existentes.
- Reaproveite `Button`, `Modal`, `PageHeader`, `StatCard`, `StatusBadge`, `SearchBox`, `Field` e `EmptyState`.
- Formate dinheiro com `money()` e datas compactas com `shortDate()`.
- Mostre feedback de sucesso por `notify()` e validações no próprio formulário.

## Regras de negócio existentes

- Não permitir dois agendamentos ativos na mesma data e horário.
- Concluir atendimento gera receita, custo de insumos e comissão.
- Venda reduz estoque e gera receita e custo da mercadoria.
- Venda acima do estoque deve ser bloqueada.
- Totais financeiros são derivados das transações.
- Cadastro e login demonstrativos usam `localStorage`.

## Checklist antes de finalizar

1. O fluxo alterado continua navegável?
2. As relações por ID continuam válidas?
3. Mobile, tablet e desktop não ganharam overflow?
4. Estados vazio, erro e sucesso continuam claros?
5. Os textos permanecem em português do Brasil?
6. `pnpm format` conclui com sucesso?
7. `pnpm build` conclui com sucesso?
