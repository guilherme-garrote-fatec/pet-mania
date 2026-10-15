# Pet Mania

Protótipo front-end navegável para gestão de um pet shop. A aplicação reúne agenda, clientes e pets, produtos, serviços, vendas, financeiro e relatórios com dados simulados no navegador.

## Tecnologias

- React 19 + TypeScript
- Vite 8
- Tailwind CSS 4
- Lucide React
- `localStorage` para a sessão e para usuários cadastrados no protótipo

## Executando o projeto

O ambiente do Figma Make já inicia o servidor de desenvolvimento. Fora dele:

```bash
pnpm install
pnpm dev
```

Comandos de qualidade:

```bash
pnpm format
pnpm build
```

## Acesso de demonstração

- E-mail: `admin@petmania.com`
- Senha: `123456`

## Estrutura

```text
src/
├── components/
│   ├── auth/          # Login e cadastro de usuários
│   └── ui/            # Componentes visuais reutilizáveis
├── data/              # Dados iniciais e conta de demonstração
├── features/
│   └── forms/         # Formulários dos fluxos de negócio
├── pages/             # Uma tela para cada módulo do sistema
├── types/             # Modelo de domínio compartilhado
├── utils/             # Formatação de moeda e datas
├── App.tsx            # Estado, regras e composição da aplicação
├── index.css          # Tema e estilos globais
└── main.tsx           # Ponto de entrada React
```

Consulte [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md) para entender os limites dos módulos e [`docs/COPILOT.md`](docs/COPILOT.md) antes de ampliar a aplicação.

## Características do protótipo

- Os dados operacionais ficam em memória e voltam ao estado inicial ao recarregar.
- A sessão e usuários criados ficam no `localStorage`.
- Não há backend, banco de dados ou integração de pagamentos.
- A navegação é controlada por estado React, sem React Router.
