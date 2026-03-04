<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class DTT_Auth {
    
    /**
     * @param string $context -> 'main', 'archive_all', 'tax', 'single'
     * @param int $id -> Post ID or Term ID
     * @return string -> 'authorized', 'locked', 'redirect_login', 'redirect_recent', 'no_permission'
     */
    public function get_context_auth($context, $id = 0) {
        $is_admin = current_user_can('manage_options');
        $logged_in = is_user_logged_in();

        // REGLAS: URL PRINCIPAL (Shortcode)
        if ($context === 'main') {
            if (!$logged_in) return 'redirect_login';
            if ($is_admin) return 'redirect_recent';
            return 'no_permission';
        }

        // REGLAS: URL /projects/ (Todos los projectos)
        if ($context === 'archive_all') {
            if (!$logged_in) return 'redirect_login';
            if ($is_admin) return 'authorized';
            return 'no_permission';
        }

        // REGLAS: Proyecto individual o Carpeta de Cliente (Requieren Auth de Cliente)
        $term_id = 0;
        if ($context === 'single') {
            $terms = wp_get_post_terms($id, 'client');
            if (!empty($terms) && !is_wp_error($terms)) {
                $term_id = $terms[0]->term_id;
            }
        } elseif ($context === 'tax') {
            $term_id = $id;
        }

        // Fallback si no tiene cliente
        if (!$term_id) {
            $term = term_exists('PIP', 'client');
            if (!$term) { $term = wp_insert_term('PIP', 'client'); }
            $term_id = is_array($term) ? $term['term_id'] : $term;
        }

        // El admin entra directo
        if ($is_admin) return 'authorized';

        // Gestión de contraseña de cliente
        $stored_pass = get_field('client_password', 'client_' . $term_id);
        if (empty($stored_pass)) {
            $stored_pass = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
            update_field('client_password', $stored_pass, 'client_' . $term_id);
        }

        // Procesar formulario de login
        if (isset($_POST['dtt_unlock_pass']) && $_POST['dtt_unlock_pass'] === $stored_pass) {
            setcookie('dtt_auth_client_' . $term_id, 'true', time() + (30 * DAY_IN_SECONDS), '/');
            $_COOKIE['dtt_auth_client_' . $term_id] = 'true';
        }

        // Comprobar Cookie
        if (isset($_COOKIE['dtt_auth_client_' . $term_id]) && $_COOKIE['dtt_auth_client_' . $term_id] === 'true') {
            return 'authorized';
        }

        return 'locked';
    }
}