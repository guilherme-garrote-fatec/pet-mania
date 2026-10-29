# Pet Mania

Sistema web de gestão para pet shop, implementado com PHP 8.2+, HTML semântico,
CSS e JavaScript puros. O diretório [`figma/`](./figma/) contém o protótipo
original usado como referência visual e permanece separado da aplicação PHP.

## Funcionalidades

- Autenticação por sessão com `password_hash()` / `password_verify()`.
- Agenda: cadastro, edição, pesquisa por data/status, conclusão e cancelamento.
- Clientes e pets: cadastro, atualização dos dados do tutor, pets e histórico.
- Produtos: pesquisa, estoque, preço, custo, margem, edição e remoção protegida.
- Serviços: cadastro, edição e ativação/desativação.
- Vendas: gravação transacional, baixa de estoque e lançamentos financeiros.
- Financeiro: receitas, custos, comissões, lançamentos manuais editáveis e CSV.
- Relatórios por período com indicadores calculados a partir dos registros reais.
- Proteção CSRF nos formulários, consultas preparadas via PDO e saída escapada.
- Layout responsivo para desktop, tablet e celular.

## Requisitos

- PHP 8.2 ou superior com PDO e o driver `pdo_mysql` para MySQL/MariaDB.
- MySQL 8+ ou MariaDB 10.5+.
- `pdo_sqlite` é opcional e pode ser usado para desenvolvimento local.

Não há dependências npm, etapas de compilação ou bibliotecas de frontend.

## Configuração com MySQL ou MariaDB

1. Crie um banco de dados e um usuário dedicado. Exemplo:

   ```sql
   CREATE DATABASE pet_mania CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'petmania'@'localhost' IDENTIFIED BY 'defina-uma-senha-forte';
   GRANT ALL PRIVILEGES ON pet_mania.* TO 'petmania'@'localhost';
   ```

2. Copie `.env.example` para `.env` e configure host, banco, usuário e senha.
   O `.env` é carregado pela aplicação e não deve ser publicado.
3. Importe o esquema:

   ```sh
   mysql -u petmania -p pet_mania < database/schema.sql
   ```

4. Crie a conta inicial e os dados de demonstração:

   ```sh
   php database/seed.php
   ```

   O comando imprime uma senha aleatória uma única vez. Guarde-a para entrar;
   o sistema não mantém uma senha padrão pública.
5. Inicie o servidor local:

   ```sh
   php -S localhost:8000 -t public
   ```

   Acesse `http://localhost:8000`.

Se desejar escolher o e-mail/nome do usuário inicial, passe-os ao comando de
seed: `php database/seed.php admin@petmania.local "Nome da administradora"`.

## Observações de operação

- A conclusão de um atendimento cria receita, custo e comissão uma única vez.
- Somente agendamentos pendentes (`Agendado`) podem ser editados ou cancelados.
- A venda reduz o estoque na mesma transação que grava a venda e o financeiro.
- Lançamentos financeiros automáticos são somente leitura; os manuais podem ser
  editados e removidos.
- Produtos vinculados a vendas não podem ser removidos para preservar o histórico.
- Os dados iniciais de demonstração são opcionais e podem ser alterados/excluídos
  conforme as restrições de integridade.
- Em produção, configure HTTPS e use `public/` como document root. Não exponha
  o diretório raiz do projeto ao servidor web.

## Estrutura

A aplicação é organizada em um núcleo compartilhado e em módulos por área de
negócio. Cada módulo concentra consultas, regras e telas da sua área.

```text
app/
  bootstrap.php        # Carrega o núcleo, o .env, o fuso horário e a sessão
  core/
    auth.php           # Usuário logado, login e logout
    database.php       # Conexão PDO e helpers de consulta
    env.php            # Leitura do arquivo .env
    format.php         # Escape HTML, moeda, datas e textos UTF-8
    http.php           # URLs, redirecionamentos e leitura segura da query string
    modules.php        # Registro de módulos, ações e formulários
    router.php         # Fluxo da requisição e despacho das ações POST
    session.php        # Sessão, CSRF e mensagens flash
    validation.php     # Validação dos dados enviados nos formulários
  ui/
    components.php     # Componentes HTML reutilizáveis
    icons.php          # Ícones SVG
    layout.php         # Login, layout autenticado, modal e página de erro
  modules/
    overview/          # Visão geral (painel inicial)
    schedule/          # Agenda
    clients/           # Clientes & Pets
    products/          # Produtos e estoque
    services/          # Serviços
    sales/             # Vendas
    finance/           # Financeiro
    reports/           # Relatórios e exportação CSV
database/
  schema.sql           # Esquema MySQL/MariaDB
  schema.sqlite.sql    # Esquema SQLite de desenvolvimento
  seed.php             # Criação da conta inicial e dados de demonstração
public/
  index.php            # Único ponto de entrada (document root)
  assets/css/          # Estilos responsivos
  assets/js/           # Interações Vanilla JS
tests/
  smoke.php            # Teste ponta a ponta de todos os módulos
```

Cada módulo segue o mesmo formato:

```text
app/modules/<nome>/
  module.php           # Definição: menu, página, ações, formulários e exportações
  repository.php       # Consultas ao banco usadas pelo módulo (e por outros)
  actions.php          # Ações POST: validação, gravação e redirecionamento
  views.php            # Página e campos dos formulários em modal
```

### Criando um novo módulo

1. Crie `app/modules/<nome>/module.php` devolvendo a definição do módulo:

   ```php
   <?php
   declare(strict_types=1);

   require_once __DIR__ . '/repository.php';
   require_once __DIR__ . '/actions.php';
   require_once __DIR__ . '/views.php';

   return [
       'label' => 'Fornecedores',         // título no menu e na página
       'icon' => 'box',                   // ícone de app/ui/icons.php
       'render' => 'render_suppliers',    // fn(PDO $pdo, array $user): string
       'actions' => [                     // ações POST: fn(PDO $pdo): never
           'save_supplier' => 'save_supplier_action',
       ],
       'forms' => [                       // abertos com ?modal=supplier[&id=N]
           'supplier' => [
               'title' => 'Novo fornecedor',
               'description' => 'Cadastre um fornecedor.',
               'action' => 'save_supplier',
               'render' => 'render_supplier_form', // fn(PDO $pdo, ?array $record): string
               'load' => 'find_supplier',          // fn(PDO $pdo, int $id): ?array
           ],
       ],
       'exports' => [],                   // ?export=<formato>: fn(PDO $pdo): never
   ];
   ```

2. Adicione o nome do módulo à constante `APP_MODULES` em `app/bootstrap.php`
   (a ordem da lista é a ordem do menu).

As ações recebem o token CSRF e a autenticação já verificados pelo roteador.

## Verificações

Validar a sintaxe PHP:

```sh
find app database public tests -name '*.php' -exec php -l {} \;
```

Executar o teste ponta a ponta (cria um banco SQLite temporário, executa o
seed, sobe o servidor embutido e percorre telas e ações de todos os módulos):

```sh
php tests/smoke.php
```

Para executá-lo contra MySQL/MariaDB, use um banco vazio com o esquema
importado:

```sh
SMOKE_DB_DSN="mysql:host=127.0.0.1;dbname=pet_mania_test;charset=utf8mb4" \
DB_USER=petmania DB_PASSWORD=sua-senha php tests/smoke.php
```
