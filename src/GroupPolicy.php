<?php
namespace GlpiPlugin\Singletechgroup;

use CommonDBTM;
use CommonGLPI;
use Group;
use Html;
use Session;

/** A single per-group tab. Never renders the global group list. */
class GroupPolicy extends CommonDBTM
{
    public static $rightname = 'group';

    public static function getIcon()
    {
        return 'ti ti-users-group';
    }

    public static function getTypeName($nb = 0): string
    {
        return 'Política de atribuição';
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($item instanceof Group && (int) $item->getID() > 0 && Session::haveRight('group', READ)) {
            return self::createTabEntry('Política de atribuição');
        }
        return '';
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0): bool
    {
        if (!$item instanceof Group || (int) $item->getID() <= 0 || !Session::haveRight('group', READ)) {
            return false;
        }
        $id = (int) $item->getID();
        $policy = Policy::get($id);
        $editable = Session::haveRight('group', UPDATE);
        $CFG_GLPI = $GLOBALS['CFG_GLPI'];
        $action = htmlspecialchars(rtrim($CFG_GLPI['root_doc'], '/') . '/plugins/singletechgroup/front/policy.form.php', ENT_QUOTES, 'UTF-8');
        echo '<div class="card m-3"><div class="card-body">';
        echo '<h3>Política de atribuição — ' . htmlspecialchars($item->getName(), ENT_QUOTES, 'UTF-8') . '</h3>';
        echo '<p>Aplica-se aos chamados que tenham exclusivamente este grupo como grupo técnico responsável. Os grupos aos quais o técnico pertence não interferem. Chamados com vários grupos técnicos não sofrem alterações automáticas.</p>';
        echo '<form method="post" action="' . $action . '">';
        echo '<input type="hidden" name="groups_id" value="' . $id . '">';
        echo '<input type="hidden" name="_glpi_csrf_token" value="' . htmlspecialchars(Session::getNewCSRFToken(), ENT_QUOTES, 'UTF-8') . '">';
        echo '<label class="form-label" for="stg_mode">Limite de técnicos desta equipe por chamado</label>';
        echo '<select class="form-select mb-3" id="stg_mode" name="mode"' . ($editable ? '' : ' disabled') . '>';
        foreach ([0 => 'Sem restrição (padrão)', 1 => '1 técnico', 2 => '2 técnicos'] as $val => $label) {
            echo '<option value="' . $val . '"' . ($policy['mode'] === $val ? ' selected' : '') . '>' . $label . '</option>';
        }
        echo '</select><label class="form-label" for="stg_action">Ao ultrapassar o limite</label>';
        echo '<select class="form-select mb-3" id="stg_action" name="on_limit"' . ($editable ? '' : ' disabled') . '>';
        foreach (['replace' => 'Substituir técnico mais antigo', 'block' => 'Bloquear nova atribuição'] as $val => $label) {
            echo '<option value="' . $val . '"' . ($policy['on_limit'] === $val ? ' selected' : '') . '>' . $label . '</option>';
        }
        echo '</select>';
        if ($editable) { echo '<button class="btn btn-primary" type="submit" name="save" value="1">Salvar</button>'; }
        echo '</form></div></div>';
        return true;
    }
}
