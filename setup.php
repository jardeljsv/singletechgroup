<?php
/** Single Technician by Group - GLPI 11 */
use Glpi\Plugin\Hooks;
use GlpiPlugin\Singletechgroup\GroupPolicy;
use GlpiPlugin\Singletechgroup\AssignmentHooks;

define('PLUGIN_SINGLETECHGROUP_VERSION', '0.3.1');


function plugin_init_singletechgroup(): void
{
    global $PLUGIN_HOOKS;
    Plugin::registerClass(GroupPolicy::class, ['addtabon' => 'Group']);
    $PLUGIN_HOOKS[Hooks::ITEM_ADD]['singletechgroup'] = [
        \Ticket_User::class => [AssignmentHooks::class, 'afterUserAdd'],
    ];
    $PLUGIN_HOOKS[Hooks::PRE_ITEM_ADD]['singletechgroup'] = [
        \Ticket_User::class => [AssignmentHooks::class, 'beforeUserAdd'],
    ];
}

function plugin_version_singletechgroup(): array
{
    return [
        'name' => 'Single Technician by Group',
        'version' => PLUGIN_SINGLETECHGROUP_VERSION,
        'author' => 'Mundo ITSM',
        'license' => 'GPLv3+',
        'homepage' => 'https://mundoitsm.com.br',
        'requirements' => [
            'glpi' => ['min' => '11.0.0', 'max' => '11.99.99'],
            'php' => ['min' => '8.2'],
        ],
    ];
}
