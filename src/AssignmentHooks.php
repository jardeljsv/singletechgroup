<?php
namespace GlpiPlugin\Singletechgroup;

use CommonITILActor;
use Session;

final class AssignmentHooks
{
    public static function beforeUserAdd(\Ticket_User $item): void
    {
        if (AssignmentManager::$internal) { return; }
        $data = $item->input;
        if ((int) ($data['type'] ?? 0) !== CommonITILActor::ASSIGN) { return; }
        $ticket = (int) ($data['tickets_id'] ?? 0);
        $user = (int) ($data['users_id'] ?? 0);
        if ($ticket <= 0 || $user <= 0) { return; }
        if (AssignmentManager::shouldBlock($ticket, $user)) {
            Session::addMessageAfterRedirect(
                'A equipe atingiu o limite de técnicos por chamado. A atribuição não foi realizada.',
                false,
                ERROR
            );
            // Pre-add hooks in GLPI can cancel by emptying input.
            $item->input = [];
        }
    }

    public static function afterUserAdd(\Ticket_User $item): void
    {
        if (AssignmentManager::$internal) { return; }
        $data = $item->fields + $item->input;
        if ((int) ($data['type'] ?? 0) !== CommonITILActor::ASSIGN) { return; }
        $ticket = (int) ($data['tickets_id'] ?? 0);
        $user = (int) ($data['users_id'] ?? 0);
        if ($ticket <= 0 || $user <= 0) { return; }
        try {
            AssignmentManager::afterAdd($ticket, $user, (int) $item->getID());
        } catch (\Throwable $e) {
            // Do not break the GLPI ticket workflow if policy evaluation fails.
            error_log('[singletechgroup] Falha ao aplicar política: ' . $e->getMessage());
        }
    }
}
