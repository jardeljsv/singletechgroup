<?php
namespace GlpiPlugin\Singletechgroup;

use CommonITILActor;

/**
 * Politicas por GRUPO TECNICO ATUAL do chamado, independentemente
 * dos grupos de pertencimento de cada usuario.
 * Nao altera glpi_groups_tickets: Escalade continua independente.
 */
final class AssignmentManager
{
    public static bool $internal = false;

    /** Retorna atribuicoes tecnicas por ordem de inclusao (id ASC). */
    public static function assignedTechnicians(int $ticket): array
    {
        global $DB;
        $actors = [];
        foreach ($DB->request([
            'SELECT' => ['id', 'users_id'],
            'FROM' => 'glpi_tickets_users',
            'WHERE' => [
                'tickets_id' => $ticket,
                'type' => CommonITILActor::ASSIGN,
            ],
            'ORDER' => ['id ASC'],
        ]) as $row) {
            $actors[] = [
                'id' => (int) $row['id'],
                'users_id' => (int) $row['users_id'],
            ];
        }
        return $actors;
    }

    /** Nao aplica restricoes quando nao ha exatamente um grupo tecnico. */
    private static function activePolicy(int $ticket): ?array
    {
        $group = Policy::soleAssignedGroup($ticket);
        if ($group === null) { return null; }
        $policy = Policy::get($group);
        return $policy['mode'] > 0 ? $policy : null;
    }

    /** Bloqueia antes da inclusao, sem alterar atribuicoes antigas. */
    public static function shouldBlock(int $ticket, int $user): bool
    {
        if ($ticket <= 0 || $user <= 0) { return false; }
        $policy = self::activePolicy($ticket);
        if ($policy === null || $policy['on_limit'] !== 'block') { return false; }
        $actors = self::assignedTechnicians($ticket);
        // Se a atribuicao ja existe, nao bloqueia a repeticao.
        foreach ($actors as $actor) {
            if ($actor['users_id'] === $user) { return false; }
        }
        return count($actors) >= $policy['mode'];
    }

    /** Remove apenas atribuicoes tecnicas excedentes; nunca grupos. */
    public static function afterAdd(int $ticket, int $user, int $newActorId): void
    {
        if ($ticket <= 0 || $user <= 0 || $newActorId <= 0) { return; }
        $policy = self::activePolicy($ticket);
        if ($policy === null || $policy['on_limit'] !== 'replace') { return; }
        $actors = self::assignedTechnicians($ticket);
        if (count($actors) <= $policy['mode']) { return; }
        // Garante que o novo responsavel nao seja eliminado por uma ordenacao inesperada.
        $newActorFound = false;
        foreach ($actors as $actor) {
            if ($actor['id'] === $newActorId && $actor['users_id'] === $user) {
                $newActorFound = true;
                break;
            }
        }
        if (!$newActorFound) { return; }
        $keep = [$newActorId => true];
        for ($i = count($actors) - 1; $i >= 0 && count($keep) < $policy['mode']; $i--) {
            $keep[$actors[$i]['id']] = true;
        }
        self::$internal = true;
        try {
            foreach ($actors as $actor) {
                if (!isset($keep[$actor['id']])) {
                    // Usa API GLPI, preservando o historico nativo.
                    (new \Ticket_User())->delete(['id' => $actor['id']]);
                }
            }
        } finally {
            self::$internal = false;
        }
    }
}
