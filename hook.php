<?php
/** Install/upgrade schema; never delete ticket actor records. */
function plugin_singletechgroup_install(): bool
{
    global $DB;
    $table = 'glpi_plugin_singletechgroup_policies';
    if (!$DB->tableExists($table)) {
        $sql = "CREATE TABLE `$table` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `groups_id` int unsigned NOT NULL,
            `mode` tinyint unsigned NOT NULL DEFAULT 1,
            `on_limit` varchar(12) NOT NULL DEFAULT 'replace',
            PRIMARY KEY (`id`), UNIQUE KEY `uniq_group` (`groups_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        if (!$DB->doQuery($sql)) { return false; }
    } else {
        // Upgrades v0.2.0 policies in place. No destructive migration.
        if (!$DB->fieldExists($table, 'on_limit')) {
            if (!$DB->doQuery("ALTER TABLE `$table` ADD COLUMN `on_limit` varchar(12) NOT NULL DEFAULT 'replace'")) {
                return false;
            }
        }
    }
    return true;
}

function plugin_singletechgroup_uninstall(): bool
{
    global $DB;
    $DB->dropTable('glpi_plugin_singletechgroup_policies', true);
    // Legacy v0.2 association table, if present, contains only plugin data.
    if ($DB->tableExists('glpi_plugin_singletechgroup_assignments')) {
        $DB->dropTable('glpi_plugin_singletechgroup_assignments', true);
    }
    return true;
}
