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

## Desenvolvimento local com SQLite (opcional)

O SQLite permite executar o sistema sem instalar um servidor de banco. Habilite
`pdo_sqlite` no PHP e adicione ao `.env`:

```dotenv
DB_DSN=sqlite:database/pet-mania.sqlite
```

O comando `php database/seed.php` cria as tabelas SQLite automaticamente na
primeira execução e inicializa os dados. Para voltar a usar MySQL, remova
`DB_DSN` e configure as variáveis MySQL descritas acima.

## Observações de operação

- A conclusão de um atendimento cria receita, custo e comissão uma única vez.
- A venda reduz o estoque na mesma transação que grava a venda e o financeiro.
- Lançamentos financeiros automáticos são somente leitura; os manuais podem ser
  editados e removidos.
- Produtos vinculados a vendas não podem ser removidos para preservar o histórico.
- Os dados iniciais de demonstração são opcionais e podem ser alterados/excluídos
  conforme as restrições de integridade.
- Em produção, configure HTTPS e use `public/` como document root. Não exponha
  o diretório raiz do projeto ao servidor web.

## Estrutura

```text
app/
  actions.php       # Validação de entrada e regras de negócio
  bootstrap.php     # Sessão, PDO, CSRF e helpers
  views.php         # Telas, layout e formulários HTML
database/
  schema.sql        # Esquema MySQL/MariaDB
  schema.sqlite.sql # Esquema SQLite de desenvolvimento
  seed.php          # Criação da conta inicial e dados de demonstração
public/
  index.php         # Entrada recomendada do servidor web
  assets/css/       # Estilos responsivos
  assets/js/        # Interações Vanilla JS
index.php           # Roteamento e composição da aplicação
```

## Verificações

Validar a sintaxe PHP:

```sh
php -l index.php
php -l app/bootstrap.php
php -l app/actions.php
php -l app/views.php
php -l database/seed.php
```