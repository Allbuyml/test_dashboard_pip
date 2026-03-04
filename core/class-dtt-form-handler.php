<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class DTT_Form_Handler {

    public function handle_update() {
        if ( ! current_user_can('manage_options') ) wp_die('Unauthorized');
        check_admin_referer( 'dtt_update_action', 'dtt_nonce' );
        
        $pid = intval($_POST['project_id']);
        $context = isset($_POST['edit_context']) ? sanitize_text_field($_POST['edit_context']) : 'all';

        $simple_contexts = ['header', 'completion', 'metrics', 'progress', 'all'];
        if( in_array($context, $simple_contexts) ) {
            $fields = ['client_name', 'project_status', 'last_updated', 
                       'comp_done', 'comp_in_progress', 'comp_blocked', 'comp_not_started', 'comp_primary_color',
                       'eng_start_date', 'eng_end_date', // <- CAMPOS DE FECHA AÑADIDOS
                       'days_in', 'days_left', 'pending_deliverables', 'prog_completed', 'prog_working', 'prog_upcoming'];
            foreach($fields as $f) { if(isset($_POST[$f])) update_field($f, sanitize_text_field($_POST[$f]), $pid); }
            if (isset($_POST['client_name']) && trim($_POST['client_name']) !== '') {
                wp_update_post(array('ID' => $pid, 'post_title' => sanitize_text_field($_POST['client_name'])));
            }
        }

        if ($context === 'blockers' || $context === 'all') {
            $old_blockers = get_field('blockers', $pid);
            $old_img_ids = [];
            if(is_array($old_blockers)) {
                foreach($old_blockers as $ob) {
                    if(!empty($ob['images']) && is_array($ob['images'])) {
                        foreach($ob['images'] as $oimg) {
                            if(!empty($oimg['id'])) $old_img_ids[] = intval($oimg['id']);
                        }
                    }
                }
            }

            $clean_blockers = [];
            $new_img_ids = [];

            if (isset($_POST['blockers']) && is_array($_POST['blockers'])) {
                if ( ! function_exists( 'wp_handle_upload' ) ) {
                    require_once( ABSPATH . 'wp-admin/includes/file.php' );
                    require_once( ABSPATH . 'wp-admin/includes/image.php' );
                }

                foreach ($_POST['blockers'] as $b_idx => $b_data) {
                    $row = [
                        'sev' => sanitize_text_field($b_data['sev'] ?? 'medium'),
                        'title' => sanitize_text_field($b_data['title'] ?? ''),
                        'impact' => sanitize_textarea_field($b_data['impact'] ?? ''),
                        'owner' => sanitize_text_field($b_data['owner'] ?? ''),
                        'due_date' => sanitize_text_field($b_data['due_date'] ?? ''),
                        'days_over' => sanitize_text_field($b_data['days_over'] ?? ''),
                        'next' => sanitize_text_field($b_data['next'] ?? ''),
                        'links' => [],
                        'images' => []
                    ];

                    if (isset($b_data['links']) && is_array($b_data['links'])) {
                        foreach ($b_data['links'] as $l) {
                            $raw_url = trim($l['url'] ?? '');
                            if (!empty($raw_url)) {
                                if (!preg_match("~^(?:f|ht)tps?://~i", $raw_url)) { $raw_url = "https://" . $raw_url; }
                                $row['links'][] = ['url' => esc_url_raw($raw_url), 'label' => sanitize_text_field($l['label'] ?? '')];
                            }
                        }
                    }

                    if (isset($b_data['images']) && is_array($b_data['images'])) {
                        foreach ($b_data['images'] as $img_idx => $img_data) {
                            $img_row = [ 'caption' => sanitize_text_field($img_data['caption'] ?? '') ];
                            
                            if (!empty($img_data['id'])) {
                                $img_row['id'] = intval($img_data['id']);
                                $new_img_ids[] = $img_row['id'];
                            }

                            $file_key = 'blocker_new_file_' . $b_idx . '_' . $img_idx;
                            if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK) {
                                $uploaded_file = $_FILES[$file_key];
                                $file_type = wp_check_filetype(basename($uploaded_file['name']), null);
                                
                                if (strpos($file_type['type'], 'image/') === 0) {
                                    $movefile = wp_handle_upload($uploaded_file, array('test_form' => false));
                                    if ($movefile && !isset($movefile['error'])) {
                                        $attachment = array(
                                            'post_mime_type' => $movefile['type'],
                                            'post_title'     => preg_replace('/\.[^.]+$/', '', basename($movefile['file'])),
                                            'post_content'   => '',
                                            'post_status'    => 'inherit'
                                        );
                                        $attach_id = wp_insert_attachment($attachment, $movefile['file'], $pid);
                                        $attach_data = wp_generate_attachment_metadata($attach_id, $movefile['file']);
                                        wp_update_attachment_metadata($attach_id, $attach_data);
                                        
                                        $img_row['id'] = $attach_id;
                                        $new_img_ids[] = $attach_id;
                                    }
                                }
                            }

                            if (!empty($img_row['id'])) { $row['images'][] = $img_row; }
                        }
                    }
                    $clean_blockers[] = $row;
                }
            }
            update_field('blockers', $clean_blockers, $pid);

            $to_delete = array_diff($old_img_ids, $new_img_ids);
            foreach ($to_delete as $del_id) { wp_delete_attachment($del_id, true); }
        }

        $repeater_acf_keys = [
            'achievements' => 'achievements', 'gantt_items' => 'gantt_items',
            'team_client' => 'team_client', 'team_pip' => 'team_pip', 'actions_list' => 'actions_list',
            'decisions_log' => 'decisions_log'
        ];
        $context_to_post_keys = [
            'achievements' => ['achievements'], 'gantt' => ['gantt_items'], 
            'team' => ['team_client', 'team_pip', 'actions_list'], 'archive' => ['decisions_log']
        ];

        $keys_to_process = ($context === 'all') ? array_keys($repeater_acf_keys) : ($context_to_post_keys[$context] ?? []);

        foreach($keys_to_process as $post_key) {
            $acf_key = $repeater_acf_keys[$post_key];
            if (isset($_POST[$post_key]) && is_array($_POST[$post_key])) {
                $clean_rows = [];
                foreach($_POST[$post_key] as $row) { $clean_rows[] = array_map('sanitize_text_field', $row); }
                update_field($acf_key, array_values($clean_rows), $pid);
            } else { update_field($acf_key, [], $pid); }
        }
        
        if( ($context === 'goals' || $context === 'all') ) {
            if(isset($_POST['goals']) && is_array($_POST['goals'])) {
                $clean_goals = [];
                foreach($_POST['goals'] as $g) {
                    $goal = array_map('sanitize_text_field', $g);
                    $goal['milestones'] = []; 
                    if(isset($g['milestones']) && is_array($g['milestones'])) {
                        foreach($g['milestones'] as $m) { 
                            if (isset($m['text']) && trim($m['text']) !== '') {
                                $goal['milestones'][] = array_map('sanitize_text_field', $m); 
                            }
                        }
                    }
                    $clean_goals[] = $goal;
                }
                update_field('goals', array_values($clean_goals), $pid);
            } else { update_field('goals', [], $pid); }
        }

        wp_redirect( get_permalink($pid) );
        exit;
    }

    public function handle_create() {
        if ( ! current_user_can('manage_options') ) wp_die('Unauthorized');
        check_admin_referer( 'dtt_create_action', 'dtt_nonce' );
        
        $title = sanitize_text_field($_POST['new_project_name']);
        $pid = wp_insert_post(array('post_title' => $title, 'post_type' => 'dtt_project', 'post_status' => 'publish'));
        
        if ($pid) {
            $client_select = sanitize_text_field($_POST['new_project_client_select'] ?? '');
            $client_name = 'PIP'; 
            if ($client_select === 'new' && !empty($_POST['new_project_client_text'])) {
                $new_client_name = sanitize_text_field($_POST['new_project_client_text']);
                $term = term_exists($new_client_name, 'client');
                if (!$term) { $term = wp_insert_term($new_client_name, 'client'); }
                if (!is_wp_error($term)) { wp_set_object_terms($pid, intval($term['term_id']), 'client'); }
                $client_name = $new_client_name;
            } elseif (!empty($client_select) && $client_select !== 'new') {
                $term = term_exists($client_select, 'client');
                if ($term) { 
                    wp_set_object_terms($pid, intval($term['term_id']), 'client'); 
                    $term_obj = get_term($term['term_id']);
                    if($term_obj) $client_name = $term_obj->name;
                }
            } else {
                $term = term_exists('PIP', 'client');
                if (!$term) { $term = wp_insert_term('PIP', 'client'); }
                wp_set_object_terms($pid, intval($term['term_id']), 'client');
            }
            update_field('client_name', $client_name, $pid);
            update_field('project_status', 'Discovery', $pid);
            update_field('last_updated', date('Y-m-d'), $pid);
            update_field('comp_primary_color', '#10b981', $pid);
            
            wp_redirect( get_permalink($pid) );
            exit;
        }
        wp_die('Error creating project.');
    }
}