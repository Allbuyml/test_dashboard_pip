<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class DTT_Importer {
    public function render_page() {
        if ( isset($_POST['dtt_import_nonce']) && wp_verify_nonce($_POST['dtt_import_nonce'], 'dtt_import_action') ) {
            $this->handle_upload();
        }
        ?>
        <div class="wrap">
            <h1>Import Project Data (Q1 / V16.1)</h1>
            <div class="card" style="padding:20px; margin-top:20px; max-width:600px;">
                <p>This tool maps all JSON fields, including the new Q1 Completion Rates and Client Taxonomies.</p>
                <form method="post" enctype="multipart/form-data">
                    <?php wp_nonce_field('dtt_import_action', 'dtt_import_nonce'); ?>
                    <label><strong>Select JSON File:</strong></label><br><br>
                    <input type="file" name="dtt_json_file" accept=".json" required><br><br>
                    <input type="submit" class="button button-primary" value="Import Data">
                </form>
            </div>
        </div>
        <?php
    }

    private function handle_upload() {
        if ( ! function_exists( 'update_field' ) || empty($_FILES['dtt_json_file']['tmp_name']) ) return;
        
        $json = file_get_contents($_FILES['dtt_json_file']['tmp_name']);
        $projects = json_decode($json, true);

        if ( is_array($projects) ) {
            $count = 0;
            foreach ($projects as $proj) {
                // Comprobar si el proyecto ya existe
                $exists = get_posts(array(
                    'post_type' => 'dtt_project',
                    'title' => $proj['title'],
                    'post_status' => 'any',
                    'numberposts' => 1
                ));
                
                $pid = $exists ? $exists[0]->ID : wp_insert_post(array(
                    'post_title' => $proj['title'], 
                    'post_type' => 'dtt_project', 
                    'post_status' => 'publish'
                ));

                if ($pid) {
                    // 1. MAPA EXACTO DE ACF FIELD KEYS (Esto soluciona que los campos no se guarden al importar)
                    $acf_map = [
                        'client_name' => 'field_client_name',
                        'project_status' => 'field_proj_status',
                        'last_updated' => 'field_last_upd',
                        'comp_done' => 'field_comp_done',
                        'comp_in_progress' => 'field_comp_inprog',
                        'comp_blocked' => 'field_comp_block',
                        'comp_not_started' => 'field_comp_not',
                        'comp_primary_color' => 'field_comp_color',
                        'days_in' => 'field_d_in',
                        'days_left' => 'field_d_left',
                        'pending_deliverables' => 'field_pend_del',
                        'prog_completed' => 'field_p_comp',
                        'prog_working' => 'field_p_work',
                        'prog_upcoming' => 'field_p_up',
                        'blockers' => 'field_blockers',
                        'goals' => 'field_goals',
                        'achievements' => 'field_achievements',
                        'gantt_items' => 'field_gantt',
                        'team_client' => 'field_tm_cl',
                        'team_pip' => 'field_tm_pp',
                        'actions_list' => 'field_actions',
                        'decisions_log' => 'field_decisions',
                        'immediate_timeline' => 'field_imm_timeline',
                        'meeting_timeline' => 'field_meet_tl',
                        'doc_references' => 'field_doc_ref',
                        'lessons_learned' => 'field_lessons'
                    ];

                    // Campos extra que no requieren field_key específico
                    $extra_keys = [
                        'insight_opportunity', 'citation_opportunity', 
                        'insight_risk', 'citation_risk', 
                        'insight_action', 'citation_action'
                    ];
                    
                    // Guardar forzando la Field Key de ACF
                    foreach($acf_map as $json_key => $acf_key) { 
                        if(isset($proj[$json_key])) {
                            update_field($acf_key, $proj[$json_key], $pid); 
                            
                            // FORZAR guardado nativo de WordPress para valores simples (A prueba de fallos)
                            if (!is_array($proj[$json_key])) {
                                update_post_meta($pid, $json_key, $proj[$json_key]);
                                update_post_meta($pid, '_' . $json_key, $acf_key); // Referencia oculta de ACF
                            }
                        }
                    }

                    // Guardar los Insights
                    foreach($extra_keys as $ek) {
                        if (isset($proj[$ek])) {
                            update_post_meta($pid, $ek, $proj[$ek]);
                        }
                    }
                    
                    // 2. CREACIÓN DE TAXONOMÍA (Cliente)
                    $client_name = !empty($proj['client_name']) ? sanitize_text_field($proj['client_name']) : 'PIP';
                    $client_slug = !empty($proj['client_slug']) ? sanitize_title($proj['client_slug']) : sanitize_title($client_name);

                    // Si la taxonomía no existe, la crea dinámicamente
                    if (!term_exists($client_name, 'client')) {
                        wp_insert_term($client_name, 'client', array('slug' => $client_slug));
                    }
                    
                    // Asigna el término al proyecto
                    wp_set_object_terms($pid, $client_name, 'client');

                    $count++;
                }
            }
            echo '<div class="notice notice-success is-dismissible"><p>Successfully imported and mapped <strong>' . $count . '</strong> projects (Q1 Data perfectly synced).</p></div>';
        }
    }
}