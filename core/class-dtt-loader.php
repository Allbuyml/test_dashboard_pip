<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class DTT_Loader {

    public function run() {
        require_once DTT_PATH . 'core/class-dtt-cpt.php';
        require_once DTT_PATH . 'core/class-dtt-auth.php';
        require_once DTT_PATH . 'core/class-dtt-data.php';
        require_once DTT_PATH . 'core/class-dtt-form-handler.php';
        require_once DTT_PATH . 'admin/class-dtt-admin-menu.php';
        require_once DTT_PATH . 'admin/class-dtt-importer.php';

        $cpt = new DTT_CPT();
        add_action( 'init', array( $cpt, 'register_cpt' ) );
        add_action( 'acf/init', array( $cpt, 'register_acf_fields' ) );

        $forms = new DTT_Form_Handler();
        add_action( 'admin_post_dtt_update_project', array( $forms, 'handle_update' ) );
        add_action( 'admin_post_dtt_create_project', array( $forms, 'handle_create' ) );

        $admin_menu = new DTT_Admin_Menu();
        add_action( 'admin_menu', array( $admin_menu, 'add_menu_pages' ) );

        add_shortcode( 'dashboard_task_tracker', array( $this, 'render_shortcode' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 999 );
        add_filter( 'the_content', array( $this, 'cpt_content_override' ) );
        
        add_action( 'template_redirect', array( $this, 'smart_routing' ), 9 );
    }

    public function enqueue_assets() {
        $should_load = false;
        global $post;

        if ( is_singular( 'dtt_project' ) || is_tax( 'client' ) || is_post_type_archive( 'dtt_project' ) ) $should_load = true;
        if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'dashboard_task_tracker' ) ) $should_load = true;

        if ( $should_load ) {
            wp_enqueue_script( 'tailwindcss', 'https://cdn.tailwindcss.com', array(), null, false );
            wp_enqueue_script( 'lucide-icons', 'https://unpkg.com/lucide@latest', array(), null, false );
            wp_enqueue_style( 'dtt-styles', DTT_URL . 'assets/css/styles.css', array(), DTT_VERSION );
            wp_enqueue_script( 'dtt-script', DTT_URL . 'assets/js/script.js', array('lucide-icons'), DTT_VERSION, true );
        }
    }

    public function smart_routing() {
        $pagenow = isset($GLOBALS['pagenow']) ? $GLOBALS['pagenow'] : '';
        $is_login_page = in_array($pagenow, array('wp-login.php', 'wp-register.php'));
        if ( is_admin() || wp_doing_ajax() || $is_login_page || wp_is_json_request() || (defined('REST_REQUEST') && REST_REQUEST) ) return;

        global $wp;
        $request = trim($wp->request, '/');

        if ( $request === 'project' ) {
            wp_redirect(home_url('/projects/'), 301);
            exit;
        }

        // ==========================================
        // SISTEMA ANTI-BUCLE (NUEVO)
        // ==========================================
        if ( is_404() ) {
            $parts = explode('/', $request);
            $slug = end($parts);
            if ($slug) {
                // Obtener URL actual para evitar que se redirija a sí misma infinitamente
                $current_url = rtrim(home_url($wp->request), '/');
                
                $project = get_page_by_path($slug, OBJECT, 'dtt_project');
                if ($project) { 
                    $target_url = rtrim(get_permalink($project->ID), '/');
                    if ($current_url !== $target_url) { // ¡El escudo protector!
                        wp_redirect(get_permalink($project->ID)); 
                        exit; 
                    }
                }
                
                $term = get_term_by('slug', $slug, 'client');
                if ($term) { 
                    $target_url = rtrim(get_term_link($term), '/');
                    if ($current_url !== $target_url && !is_wp_error(get_term_link($term))) {
                        wp_redirect(get_term_link($term)); 
                        exit; 
                    }
                }
            }
        }

        $is_dtt_page = is_singular('dtt_project') || is_tax('client') || is_post_type_archive('dtt_project') || is_front_page();
        if (!$is_dtt_page) {
            global $post;
            if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'dashboard_task_tracker')) {
                $is_dtt_page = true;
            }
        }
        if (!$is_dtt_page && !is_user_logged_in()) {
            wp_redirect(wp_login_url(home_url($request)));
            exit;
        }

        if ( is_post_type_archive('dtt_project') ) {
            $auth = new DTT_Auth();
            $status = $auth->get_context_auth('archive_all');
            if ($status === 'redirect_login') { wp_redirect(wp_login_url(home_url($request))); exit; }
            if ($status === 'no_permission') { $this->render_full_page('no-permission.php'); }
            if ($status === 'authorized') { $this->render_full_page('archive-projects.php'); }
        }

        if ( is_tax('client') ) {
            $term_id = get_queried_object_id();
            $auth = new DTT_Auth();
            $status = $auth->get_context_auth('tax', $term_id);
            if ($status === 'locked') { $this->render_full_page('lock-screen.php', ['term_id' => $term_id]); }
            if ($status === 'authorized') { $this->render_full_page('archive-projects.php', ['term_id' => $term_id]); }
        }
    }

    private function render_full_page($template, $args = []) {
        extract($args);
        get_header(); 
        echo '<div class="dtt-wrapper bg-slate-100 min-h-screen">';
        include DTT_PATH . 'templates/' . $template;
        echo '</div>';
        get_footer();
        exit;
    }

    public function render_shortcode($atts) {
        $auth = new DTT_Auth();
        $status = $auth->get_context_auth('main');

        if ($status === 'redirect_login') {
            return '<script>window.location.href="'.wp_login_url(home_url($_SERVER['REQUEST_URI'])).'";</script>';
        }
        if ($status === 'redirect_recent') {
            $recent = get_posts(['post_type' => 'dtt_project', 'numberposts' => 1, 'post_status' => 'publish', 'orderby' => 'date', 'order' => 'DESC']);
            if ($recent) {
                return '<script>window.location.href="'.get_permalink($recent[0]->ID).'";</script>';
            }
            return '<div class="p-10 text-center font-bold font-sans">No projects found. Create one.</div>';
        }
        if ($status === 'no_permission') {
            ob_start(); include DTT_PATH . 'templates/no-permission.php'; return ob_get_clean();
        }
    }

    public function cpt_content_override($content) {
        if ( is_admin() || wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST) ) {
            return $content; 
        }

        if (is_singular('dtt_project') && in_the_loop() && is_main_query()) {
            $pid = get_the_ID();
            $auth = new DTT_Auth();
            $status = $auth->get_context_auth('single', $pid);
            
            if ($status === 'locked') {
                $terms = wp_get_post_terms($pid, 'client');
                $term_id = (!empty($terms) && !is_wp_error($terms)) ? $terms[0]->term_id : 0;
                ob_start(); include DTT_PATH . 'templates/lock-screen.php'; return ob_get_clean();
            }
            
            $data_layer = new DTT_Data();
            $data = $data_layer->get_project_data($pid);
            
            if (empty($data)) return $content; 
            extract($data);
            
            $is_admin = current_user_can('manage_options');
            
            $share_url = get_permalink($pid);
            $share_pass = '';
            
            $terms = wp_get_post_terms($pid, 'client');
            if (!empty($terms) && !is_wp_error($terms)) {
                $term_id = $terms[0]->term_id;
                $share_pass = get_field('client_password', 'client_' . $term_id);
                
                if (empty($share_pass)) {
                    $share_pass = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
                    update_field('client_password', $share_pass, 'client_' . $term_id);
                }
            }

            $all_projects = $is_admin ? $data_layer->get_all_projects() : [];

            ob_start(); include DTT_PATH . 'templates/dashboard-main.php'; return ob_get_clean();
        }
        return $content;
    }
}