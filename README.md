# Single Technician by Group v0.3.1 — GLPI 11

## Regra operacional SES-DF
- O **Escalade** controla a troca de grupos e a remoção de grupos técnicos anteriores. Este plugin **não adiciona, altera nem remove grupos**.
- Ao incluir um **técnico** (`Ticket_User`, type = ASSIGN), consulta os grupos **atualmente atribuídos** (`glpi_groups_tickets`, type = ASSIGN).
- **Exatamente um grupo técnico**: consulta exclusivamente a política desse grupo, independentemente dos grupos de que o técnico é membro.
- **Nenhum ou mais de um grupo técnico**: não bloqueia nem remove técnicos (fail-open intencional).
- Grupo sem política: GLPI permanece normal. Os limites possíveis são 1 ou 2 técnicos.
- `replace`: após a inclusão preserva o novo técnico e os mais recentemente atribuídos até o limite; remove os anteriores via API nativa `Ticket_User::delete`.
- `block`: antes da inclusão, ao alcançar o limite, impede nova atribuição. Precisa ser validado no GLPI 11 exato, especialmente na UI e API.
- Mudanças no grupo responsável **não removem automaticamente técnicos antigos**. A regra é avaliada **na próxima inclusão de técnico**.
- Técnicos podem pertencer a várias equipes; não há necessidade de associá-los ao grupo dentro do chamado.

## Interface
- Apenas guia **Política de atribuição** em Administração > Grupos > grupo escolhido.
- Nenhuma lista geral de grupos; nenhuma guia/campo novo em chamados; nenhuma auditoria própria.
- Histórico nativo do GLPI permanece sujeito ao comportamento das APIs usadas.

## Instalação em HOMOLOGAÇÃO
1. Backup da pasta do plugin e banco de dados.
2. Desabilitar a opção global `Single technician and group` do Behaviors.
3. Desativar versão anterior no GLPI; extrair `singletechgroup` em `<glpi>/plugins`, substituindo **apenas arquivos deste plugin**.
4. Atualizar/ativar no gerenciamento de plugins; limpar cache.
5. Abrir Administração > Grupos > grupo > Política de atribuição.

**Não validado em um servidor GLPI real.** Lint local não substitui teste de integração: validar hooks, permissões, criação por formulários, regras, API e concorrência; testar primeiro usando chamados e grupos fictícios.

## Nota de atualização
- Atualização de v0.3.0 para v0.3.1: **sem alteração de esquema**; as políticas continuam no mesmo banco.
- Tabelas de atribuição legadas anteriores não são utilizadas nem apagadas durante a atualização.
- A desinstalação apaga as tabelas próprias, conforme comportamento anterior.
