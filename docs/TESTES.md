# Testes de homologação — v0.3.1

1. Grupo A, política 1/substituir, apenas A no chamado: atribuir técnico X (membro de A), depois Y (**não membro de A**). Resultado esperado: só Y.
2. Mesmo cenário com Y membro de vários grupos: só Y.
3. Grupo A, política 2/substituir: atribuir X, Y, Z. Resultado: Y e Z.
4. Grupo A, política 1/bloquear: após atribuir X, tentar Y. Resultado: X permanece e Y não entra; confirmar UI e API.
5. Grupo A, política sem restrição: X, Y e Z permanecem.
6. Sem grupo técnico: X e Y permanecem, independentemente das políticas cadastradas.
7. Dois grupos técnicos A e B no chamado: X e Y permanecem (sem restrição); o plugin não deve alterar grupos.
8. Escalade troca A por B removendo A: a política de B passa a valer na PRÓXIMA nova atribuição; a troca isolada não remove técnicos.
9. Confirmar que grupo técnico é preservado após substituição de técnico.
10. Testar criação inicial, Formcreator/Advanced Forms, API, regras automáticas, notificações, permissões, bloqueio concorrente e GLPI 11 na versão exata da homologação.
11. Conferir atualização sem perda das políticas configuradas na v0.3.0.
12. Confirmar que Behaviors global `single_tech_mode` está desativado.

ATENÇÃO: no modo bloquear, testes de integração são obrigatórios para confirmar cancelamento em PRE_ITEM_ADD na versão de GLPI usada. Com atribuições simultâneas, o limite pode não ser estrito sem serialização transacional.
