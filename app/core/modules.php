<?php
declare(strict_types=1);

/**
 * Registro dos módulos da aplicação.
 *
 * Cada app/modules/<nome>/module.php carrega os próprios arquivos e devolve:
 *
 *   'label'   => string                        Título no menu e na página.
 *   'icon'    => string                        Ícone do menu (ui/icons.php).
 *   'render'  => callable(PDO, array): string  Conteúdo da página para o usuário logado.
 *   'actions' => [nome => callable(PDO): never] Ações POST (sempre terminam com redirecionamento).
 *   'forms'   => [modal => [...]]              Formulários exibidos em modal (veja modal_forms()).
 *   'exports' => [formato => callable(PDO): never] Downloads acionados por ?export=<formato>.
 *
 * @return array<string, array<string, mixed>>
 */
function modules(): array
{
    static $modules = null;
    if ($modules === null) {
        $modules = [];
        foreach (APP_MODULES as $name) {
            $modules[$name] = load_module($name);
        }
    }
    return $modules;
}

function load_module(string $name): array
{
    $definition = require APP_PATH . "/modules/{$name}/module.php";
    if (!is_array($definition) || !isset($definition['label'], $definition['icon'], $definition['render'])) {
        throw new LogicException("Definição inválida para o módulo {$name}.");
    }
    return $definition + ['actions' => [], 'forms' => [], 'exports' => []];
}

function module_names(): array
{
    return array_keys(modules());
}

/**
 * Ações POST de todos os módulos, indexadas pelo nome da ação.
 *
 * @return array<string, callable>
 */
function module_actions(): array
{
    $actions = [];
    foreach (modules() as $module) {
        $actions += $module['actions'];
    }
    return $actions;
}

/**
 * Formulários em modal de todos os módulos. Cada formulário define:
 *
 *   'title', 'description' => textos do cabeçalho do modal;
 *   'action'               => ação POST enviada pelo formulário;
 *   'render'               => callable(PDO, ?array $record): string com os campos;
 *   'load'                 => callable(PDO, int $id): ?array (opcional) para edição.
 *
 * @return array<string, array<string, mixed>>
 */
function modal_forms(): array
{
    $forms = [];
    foreach (modules() as $module) {
        $forms += $module['forms'];
    }
    return $forms;
}
