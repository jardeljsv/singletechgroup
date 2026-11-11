<?php
/** Writes one group's policy; no global list. */
include('../../../inc/includes.php');

use GlpiPlugin\Singletechgroup\Policy;

Session::checkRight('group', UPDATE);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Html::redirect($CFG_GLPI['root_doc'] . '/front/group.php');
}
// CSRF is validated by GLPI 11 CheckCsrfListener before this controller runs.
$gid = filter_var($_POST['groups_id'] ?? null, FILTER_VALIDATE_INT);
$mode = filter_var($_POST['mode'] ?? null, FILTER_VALIDATE_INT);
$action = (string) ($_POST['on_limit'] ?? '');
if ($gid === false || $gid === null || $gid <= 0 || !in_array($mode, [0, 1, 2], true)
    || !in_array($action, ['replace', 'block'], true)) {
    Session::addMessageAfterRedirect('Parâmetros inválidos.', false, ERROR);
} else {
    $group = new Group();
    if (!$group->getFromDB($gid) || !$group->can($gid, UPDATE)) {
        Session::addMessageAfterRedirect('Sem permissão para alterar este grupo.', false, ERROR);
    } elseif (!Policy::save($gid, $mode, $action)) {
        Session::addMessageAfterRedirect('Falha ao salvar a política.', false, ERROR);
    } else {
        Session::addMessageAfterRedirect('Política de atribuição atualizada.', false, INFO);
    }
}
Html::redirect($CFG_GLPI['root_doc'] . '/front/group.form.php?id=' . (int) $gid);
