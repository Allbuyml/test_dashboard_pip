<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class DTT_Form_Handler {

    public function __construct() {
        add_action('wp_ajax_dtt_add_comment', [$this, 'ajax_add_comment']);
        add_action('wp_ajax_nopriv_dtt_add_comment', [$this, 'ajax_add_comment']);
        
        add_action('wp_ajax_dtt_edit_comment', [$this, 'ajax_edit_comment']);
        add_action('wp_ajax_nopriv_dtt_edit_comment', [$this, 'ajax_edit_comment']);

        add_action('wp_ajax_dtt_delete_comment', [$this, 'ajax_delete_comment']);
        add_action('wp_ajax_nopriv_dtt_delete_comment', [$this, 'ajax_delete_comment']);

        add_action('wp_ajax_dtt_resolve_blocker', [$this, 'ajax_resolve_blocker']);
        add_action('wp_ajax_nopriv_dtt_resolve_blocker', [$this, 'ajax_resolve_blocker']);
        
        add_action('wp_ajax_dtt_notify_client', [$this, 'ajax_notify_client']);
        add_action('wp_ajax_nopriv_dtt_notify_client', [$this, 'ajax_notify_client']);
    }

    public function ajax_add_comment() {
        $pid = isset($_POST['pid']) ? intval($_POST['pid']) : 0;
        $idx = isset($_POST['blocker_idx']) ? intval($_POST['blocker_idx']) : -1;
        $author = isset($_POST['author']) ? sanitize_text_field($_POST['author']) : '';
        $org = isset($_POST['org']) ? sanitize_text_field($_POST['org']) : 'Client';
        $text = isset($_POST['text']) ? wp_kses_post($_POST['text']) : '';
        
        if(!$pid || $idx < 0 || empty($author) || empty($text)) {
            wp_send_json_error(['message' => 'Missing required data.']);
        }

        $blockers = get_field('field_blockers', $pid);
        if(!is_array($blockers) || !isset($blockers[$idx])) { 
            wp_send_json_error(['message' => 'Blocker not found']); 
        }
        
        $comment = [
            'author' => $author,
            'org' => $org,
            'text' => nl2br($text),
            'date' => current_time('M j, Y g:i A'),
            'images' => []
        ];

        if(isset($_FILES['comment_images']) && !empty($_FILES['comment_images']['name'][0])) {
            
            if(!function_exists('wp_handle_upload')) {
                require_once(ABSPATH . 'wp-admin/includes/file.php');
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                require_once(ABSPATH . 'wp-admin/includes/media.php');
            }

            $files = $_FILES['comment_images'];
            foreach($files['name'] as $key => $value) {
                if($files['name'][$key]) {
                    $file = [
                        'name' => $files['name'][$key],
                        'type' => $files['type'][$key],
                        'tmp_name' => $files['tmp_name'][$key],
                        'error' => $files['error'][$key],
                        'size' => $files['size'][$key]
                    ];
                    
                    $movefile = wp_handle_upload($file, ['test_form' => false]);
                    
                    if($movefile && !isset($movefile['error'])) {
                        $attachment = [
                            'post_mime_type' => $movefile['type'],
                            'post_title' => preg_replace('/\.[^.]+$/', '', basename($movefile['file'])),
                            'post_content' => '',
                            'post_status' => 'inherit'
                        ];
                        $attach_id = wp_insert_attachment($attachment, $movefile['file'], $pid);
                        
                        $attach_data = wp_generate_attachment_metadata($attach_id, $movefile['file']);
                        if($attach_data) {
                            wp_update_attachment_metadata($attach_id, $attach_data);
                        }
                        
                        $comment['images'][] = ['id' => $attach_id];
                    }
                }
            }
        }

        if(!isset($blockers[$idx]['comments']) || !is_array($blockers[$idx]['comments'])) {
            $blockers[$idx]['comments'] = [];
        }
        $blockers[$idx]['comments'][] = $comment;
        
        update_field('field_blockers', $blockers, $pid);
        clean_post_cache($pid); 
        
        wp_send_json_success(['message' => 'Comment saved successfully']);
    }

    public function ajax_edit_comment() {
        $pid = isset($_POST['pid']) ? intval($_POST['pid']) : 0;
        $b_idx = isset($_POST['b_idx']) ? intval($_POST['b_idx']) : -1;
        $c_idx = isset($_POST['c_idx']) ? intval($_POST['c_idx']) : -1;
        $text = isset($_POST['text']) ? wp_kses_post($_POST['text']) : '';

        if(!$pid || $b_idx < 0 || $c_idx < 0 || empty($text)) wp_send_json_error();

        $blockers = get_field('field_blockers', $pid);
        if(isset($blockers[$b_idx]['comments'][$c_idx])) {
            // Actualizar texto preservando saltos de línea
            $blockers[$b_idx]['comments'][$c_idx]['text'] = nl2br($text);
            update_field('field_blockers', $blockers, $pid);
            clean_post_cache($pid);
            wp_send_json_success();
        }
        wp_send_json_error(['message' => 'Comment not found.']);
    }

    public function ajax_delete_comment() {
        $pid = isset($_POST['pid']) ? intval($_POST['pid']) : 0;
        $b_idx = isset($_POST['b_idx']) ? intval($_POST['b_idx']) : -1;
        $c_idx = isset($_POST['c_idx']) ? intval($_POST['c_idx']) : -1;

        if(!$pid || $b_idx < 0 || $c_idx < 0) wp_send_json_error();

        $blockers = get_field('field_blockers', $pid);
        if(isset($blockers[$b_idx]['comments'][$c_idx])) {
            
            // BORRADO FÍSICO DE IMÁGENES
            if(!empty($blockers[$b_idx]['comments'][$c_idx]['images'])) {
                foreach($blockers[$b_idx]['comments'][$c_idx]['images'] as $img) {
                    if(!empty($img['id'])) {
                        wp_delete_attachment($img['id'], true); // true = force delete from server
                    }
                }
            }

            // Remover el comentario del array
            unset($blockers[$b_idx]['comments'][$c_idx]);
            
            // Reindexar el array para evitar problemas en ACF
            $blockers[$b_idx]['comments'] = array_values($blockers[$b_idx]['comments']);
            
            update_field('field_blockers', $blockers, $pid);
            clean_post_cache($pid);
            wp_send_json_success();
        }
        wp_send_json_error(['message' => 'Comment not found.']);
    }

    public function ajax_resolve_blocker() {
        $pid = isset($_POST['pid']) ? intval($_POST['pid']) : 0;
        $idx = isset($_POST['idx']) ? intval($_POST['idx']) : -1;
        
        if(!$pid || $idx < 0) wp_send_json_error();

        $blockers = get_field('field_blockers', $pid);
        if(is_array($blockers) && isset($blockers[$idx])) {
            $blockers[$idx]['resolved'] = 1;
            update_field('field_blockers', $blockers, $pid);
            clean_post_cache($pid);
            wp_send_json_success();
        }
        wp_send_json_error();
    }

    public function ajax_notify_client() {
        $pid = isset($_POST['pid']) ? intval($_POST['pid']) : 0;
        $to_json = isset($_POST['to']) ? stripslashes($_POST['to']) : '[]';
        $to = json_decode($to_json, true) ?: [];
        
        $custom_emails_raw = isset($_POST['custom_emails']) ? sanitize_text_field($_POST['custom_emails']) : '';
        $send_mode = isset($_POST['send_mode']) ? sanitize_text_field($_POST['send_mode']) : 'merge';
        
        $subject = isset($_POST['subject']) ? sanitize_text_field($_POST['subject']) : '';
        $message = isset($_POST['message']) ? sanitize_textarea_field($_POST['message']) : '';

        $custom_list = [];
        if(!empty($custom_emails_raw)) {
            $parts = explode(',', $custom_emails_raw);
            foreach($parts as $p) {
                $e = sanitize_email(trim($p));
                if(is_email($e)) $custom_list[] = $e;
            }
        }

        $final_to = [];
        if($send_mode === 'custom_only') {
            $final_to = $custom_list;
        } else {
            $final_to = array_unique(array_merge($to, $custom_list));
        }

        if(!empty($final_to) && !empty($subject) && !empty($message)) {
            $headers = ['Content-Type: text/html; charset=UTF-8'];
            $html_message = nl2br($message);
            $sent = wp_mail($final_to, $subject, $html_message, $headers);
            
            if($sent) {
                wp_send_json_success();
            } else {
                wp_send_json_error(['message' => 'Email server failed to send']);
            }
        }
        wp_send_json_error(['message' => 'Missing valid email addresses or required fields.']);
    }

    public function handle_update() {
        if ( ! current_user_can('manage_options') ) wp_die('Unauthorized');
        check_admin_referer( 'dtt_update_action', 'dtt_nonce' );
        
        $pid = intval($_POST['project_id']);
        $context = isset($_POST['edit_context']) ? sanitize_text_field($_POST['edit_context']) : 'all';

        $simple_fields = [
            'client_name'          => 'field_client_name',
            'project_status'       => 'field_proj_status',
            'last_updated'         => 'field_last_upd', 
            'comp_done'            => 'field_comp_done',
            'comp_in_progress'     => 'field_comp_inprog',
            'comp_blocked'         => 'field_comp_block',
            'comp_not_started'     => 'field_comp_not',
            'comp_primary_color'   => 'field_comp_color',
            'eng_start_date'       => 'field_eng_start',
            'eng_end_date'         => 'field_eng_end',
            'days_in'              => 'field_d_in',
            'days_left'            => 'field_d_left',
            'pending_deliverables' => 'field_pend_del',
            'prog_completed'       => 'field_p_comp',
            'prog_working'         => 'field_p_work',
            'prog_upcoming'        => 'field_p_up'
        ];

        $simple_contexts = ['header', 'completion', 'metrics', 'progress', 'all'];
        
        if( in_array($context, $simple_contexts) ) {
            foreach($simple_fields as $post_key => $acf_key) { 
                if(isset($_POST[$post_key])) {
                    update_field($acf_key, sanitize_text_field($_POST[$post_key]), $pid); 
                }
            }
            if (isset($_POST['client_name']) && trim($_POST['client_name']) !== '') {
                wp_update_post(array('ID' => $pid, 'post_title' => sanitize_text_field($_POST['client_name'])));
            }
        }

        if ($context === 'blockers' || $context === 'all') {
            $old_blockers = get_field('field_blockers', $pid);
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
                    $existing_resolved = isset($old_blockers[$b_idx]['resolved']) ? $old_blockers[$b_idx]['resolved'] : 0;
                    $existing_comments = isset($old_blockers[$b_idx]['comments']) ? $old_blockers[$b_idx]['comments'] : [];

                    $row = [
                        'sev' => sanitize_text_field($b_data['sev'] ?? 'medium'),
                        'title' => sanitize_text_field($b_data['title'] ?? ''),
                        'impact' => sanitize_textarea_field($b_data['impact'] ?? ''),
                        'owner' => sanitize_text_field($b_data['owner'] ?? ''),
                        'due_date' => sanitize_text_field($b_data['due_date'] ?? ''),
                        'days_over' => sanitize_text_field($b_data['days_over'] ?? ''),
                        'next' => sanitize_text_field($b_data['next'] ?? ''),
                        'resolved' => $existing_resolved,
                        'comments' => $existing_comments,
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
            update_field('field_blockers', $clean_blockers, $pid);

            $to_delete = array_diff($old_img_ids, $new_img_ids);
            foreach ($to_delete as $del_id) { wp_delete_attachment($del_id, true); }
        }

        $repeater_acf_keys = [
            'achievements'  => 'field_achievements', 
            'gantt_items'   => 'field_gantt',
            'team_client'   => 'field_tm_cl', 
            'team_pip'      => 'field_tm_pp', 
            'actions_list'  => 'field_actions',
            'decisions_log' => 'field_decisions',
            'this_week'     => 'field_this_week', 
            'next_week'     => 'field_next_week'
        ];
        
        $context_to_post_keys = [
            'achievements' => ['achievements'], 
            'gantt'        => ['gantt_items'], 
            'team'         => ['team_client', 'team_pip'], 
            'actions'      => ['actions_list'], 
            'archive'      => ['decisions_log'],
            'weekly'       => ['this_week', 'next_week']
        ];

        $keys_to_process = ($context === 'all') ? array_keys($repeater_acf_keys) : ($context_to_post_keys[$context] ?? []);

        foreach($keys_to_process as $post_key) {
            $acf_key = $repeater_acf_keys[$post_key];
            if (isset($_POST[$post_key]) && is_array($_POST[$post_key])) {
                $clean_rows = [];
                foreach($_POST[$post_key] as $row) { 
                    $clean_rows[] = array_map('sanitize_text_field', $row); 
                }
                update_field($acf_key, array_values($clean_rows), $pid);
            } else { 
                update_field($acf_key, [], $pid); 
            }
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
                update_field('field_goals', array_values($clean_goals), $pid);
            } else { 
                update_field('field_goals', [], $pid); 
            }
        }

        clean_post_cache($pid);
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
            
            update_field('field_client_name', $client_name, $pid);
            update_field('field_proj_status', 'Discovery', $pid);
            update_field('field_last_upd', date('Y-m-d'), $pid);
            update_field('field_comp_color', '#10b981', $pid);
            
            wp_redirect( get_permalink($pid) );
            exit;
        }
        wp_die('Error creating project.');
    }
}