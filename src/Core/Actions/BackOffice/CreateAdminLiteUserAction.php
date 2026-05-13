<?php

namespace AkyosUpdates\Core\Actions;

use AkyosUpdates\Service\BrandaService;

final class CreateAdminLiteUserAction implements ActionInterface
{
    public function getId(): string
    {
        return 'backoffice.create_admin_lite_user';
    }

    public function run(array $payload = []): ActionResult
    {
        $email = sanitize_email((string) ($payload['email'] ?? ''));
        if ($email === '' || ! is_email($email)) {
            return ActionResult::failure('Email invalide.');
        }

        $displayName = sanitize_text_field((string) ($payload['displayName'] ?? ''));
        if ($displayName === '') {
            return ActionResult::failure('Le nom est obligatoire.');
        }

        if (email_exists($email)) {
            return ActionResult::failure('Cet email est déjà utilisé.');
        }

        BrandaService::ensureAdminLiteRoleExists();

        $local = strstr($email, '@', true);
        $local = $local !== false ? $local : $email;
        $baseLogin = sanitize_user((string) $local, true);
        if ($baseLogin === '') {
            $baseLogin = 'admin_lite';
        }

        $login = $baseLogin;
        $i = 1;
        while (username_exists($login)) {
            $login = $baseLogin . $i;
            $i++;
        }

        $password = wp_generate_password(24);

        $userId = wp_insert_user([
            'user_login' => $login,
            'user_pass' => $password,
            'user_email' => $email,
            'display_name' => $displayName,
            'nickname' => $displayName,
            'role' => BrandaService::ROLE_ADMIN_LITE,
        ]);

        if (is_wp_error($userId)) {
            return ActionResult::failure($userId->get_error_message());
        }

        wp_new_user_notification($userId, null, 'both');

        $users = BrandaService::getUsersWithAdminLiteRole();

        return ActionResult::success(sprintf('Utilisateur %s créé (rôle admin lite). Identifiant : %s', $email, $login), [
            'hasAdminLiteUser' => true,
            'hasRoleDefinition' => get_role(BrandaService::ROLE_ADMIN_LITE) !== null,
            'userCount' => count($users),
            'usersPreview' => array_slice(array_map(static function ($u): array {
                return [
                    'id' => (int) $u->ID,
                    'email' => (string) $u->user_email,
                    'name' => (string) $u->display_name,
                ];
            }, $users), 0, 5),
        ]);
    }
}
