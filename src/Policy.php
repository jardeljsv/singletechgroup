<?php
namespace GlpiPlugin\Singletechgroup;

use CommonITILActor;

/** Direct policy lookup by group ID; never loads the full group directory. */
final class Policy
{
    private const TABLE = 'glpi_plugin_singletechgroup_policies';

    public static function get(int $group): array
    {
        global $DB;
        if ($group <= 0) { return ['mode' => 0, 'on_limit' => 'replace']; }
        foreach ($DB->request(['FROM' => self::TABLE, 'WHERE' => ['groups_id' => $group]]) as $row) {
            $mode = (int) $row['mode'];
            return [
                'mode' => in_array($mode, [1, 2], true) ? $mode : 0,
                'on_limit' => ($row['on_limit'] ?? 'replace') === 'block' ? 'block' : 'replace',
            ];
        }
        return ['mode' => 0, 'on_limit' => 'replace'];
    }

    public static function save(int $group, int $mode, string $onLimit): bool
    {
        global $DB;
        if ($group <= 0 || !in_array($mode, [0, 1, 2], true)
            || !in_array($onLimit, ['replace', 'block'], true)) { return false; }
        $where = ['groups_id' => $group];
        if ($mode === 0) { return $DB->delete(self::TABLE, $where); }
        $data = ['mode' => $mode, 'on_limit' => $onLimit];
        foreach ($DB->request(['SELECT' => ['id'], 'FROM' => self::TABLE, 'WHERE' => $where]) as $row) {
            return $DB->update(self::TABLE, $data, ['id' => (int) $row['id']]);
        }
        return $DB->insert(self::TABLE, ['groups_id' => $group] + $data);
    }

    public static function assignedGroups(int $ticket): array
    {
        global $DB;
        $result = [];
        foreach ($DB->request(['SELECT' => ['groups_id'], 'FROM' => 'glpi_groups_tickets',
            'WHERE' => ['tickets_id' => $ticket, 'type' => CommonITILActor::ASSIGN]]) as $row) {
            $result[] = (int) $row['groups_id'];
        }
        return array_values(array_unique($result));
    }

    /**
     * A politica e aplicada somente quando ha EXATAMENTE UM grupo tecnico
     * atribuido ao chamado. Nao depende da filiacao do tecnico a grupos.
     * Retorna null para 0 ou 2+ grupos (comportamento conservador).
     */
    public static function soleAssignedGroup(int $ticket): ?int
    {
        $groups = self::assignedGroups($ticket);
        return count($groups) === 1 ? $groups[0] : null;
    }
}
