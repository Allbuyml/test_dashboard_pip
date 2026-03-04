<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class DTT_CPT {
    
    public function register_cpt() {
        register_taxonomy('client', array('dtt_project'), array(
            'hierarchical'      => true,
            'labels'            => array('name' => 'Clients', 'singular_name' => 'Client'),
            'show_ui'           => true,
            'show_admin_column' => true,
            'rewrite'           => array('slug' => 'project'), 
        ));

        register_post_type( 'dtt_project', array(
            'labels' => array('name' => 'Projects', 'singular_name' => 'Project', 'menu_name' => 'Task Tracker'),
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-chart-area',
            'supports' => array( 'title' ),
            'capability_type' => 'post',
            'has_archive' => 'projects',
            'rewrite' => array('slug' => 'project/%client%', 'with_front' => false),
        ));

        add_filter('post_type_link', array($this, 'filter_post_type_link'), 10, 2);
    }

    public function filter_post_type_link($post_link, $post) {
        if (is_object($post) && $post->post_type == 'dtt_project') {
            $terms = wp_get_object_terms($post->ID, 'client');
            if (!is_wp_error($terms) && !empty($terms) && is_object($terms[0])) {
                return str_replace('%client%', $terms[0]->slug, $post_link);
            }
            return str_replace('%client%', 'pip', $post_link);
        }
        return $post_link;
    }

    public function register_acf_fields() {
        if( ! function_exists('acf_add_local_field_group') ) return;

        acf_add_local_field_group(array(
            'key' => 'group_dtt_client_tax',
            'title' => 'Client Access Settings',
            'fields' => array(
                array('key' => 'field_client_password', 'label' => 'Client Access Password', 'name' => 'client_password', 'type' => 'text'),
            ),
            'location' => array(array(array('param' => 'taxonomy', 'operator' => '==', 'value' => 'client'))),
        ));

        acf_add_local_field_group(array(
            'key' => 'group_dtt_v15_final',
            'title' => 'Project Data (Pixel Perfect V4)',
            'fields' => array(
                array('key'=>'field_client_name', 'label'=>'Client Name Display', 'name'=>'client_name', 'type'=>'text'),
                array('key'=>'field_proj_status', 'label'=>'Status', 'name'=>'project_status', 'type'=>'select', 'choices'=>array('On Track'=>'On Track', 'At Risk'=>'At Risk', 'Blocked'=>'Blocked', 'Discovery'=>'Discovery')),
                array('key'=>'field_last_upd', 'label'=>'Updated', 'name'=>'last_updated', 'type'=>'text'),
                
                array('key'=>'field_comp_done', 'label'=>'Done', 'name'=>'comp_done', 'type'=>'number'),
                array('key'=>'field_comp_inprog', 'label'=>'In Progress', 'name'=>'comp_in_progress', 'type'=>'number'),
                array('key'=>'field_comp_block', 'label'=>'Blocked', 'name'=>'comp_blocked', 'type'=>'number'),
                array('key'=>'field_comp_not', 'label'=>'Not Started', 'name'=>'comp_not_started', 'type'=>'number'),
                array('key'=>'field_comp_color', 'label'=>'Primary Color', 'name'=>'comp_primary_color', 'type'=>'select', 'choices'=>array('#10b981'=>'Emerald (Green)', '#3b82f6'=>'Blue', '#8b5cf6'=>'Purple')),

                // NUEVOS CAMPOS: FECHAS DE ENGAGEMENT PARA AUTO-CÁLCULO
                array('key'=>'field_eng_start', 'label'=>'Engagement Start', 'name'=>'eng_start_date', 'type'=>'text'),
                array('key'=>'field_eng_end', 'label'=>'Engagement End', 'name'=>'eng_end_date', 'type'=>'text'),

                array('key'=>'field_d_in', 'label'=>'Days In (Legacy)', 'name'=>'days_in', 'type'=>'number'),
                array('key'=>'field_d_left', 'label'=>'Days Left (Legacy)', 'name'=>'days_left', 'type'=>'number'),
                array('key'=>'field_pend_del', 'label'=>'Pending', 'name'=>'pending_deliverables', 'type'=>'number'),
                array('key'=>'field_p_comp', 'label'=>'Completed', 'name'=>'prog_completed', 'type'=>'number'),
                array('key'=>'field_p_work', 'label'=>'Working', 'name'=>'prog_working', 'type'=>'number'),
                array('key'=>'field_p_up', 'label'=>'Upcoming', 'name'=>'prog_upcoming', 'type'=>'number'),
                
                array('key'=>'field_blockers', 'label'=>'Blockers', 'name'=>'blockers', 'type'=>'repeater', 'sub_fields'=>array(
                    array('key'=>'bk_sev','name'=>'sev','type'=>'select','choices'=>array('critical'=>'Critical','high'=>'High','medium'=>'Medium')),
                    array('key'=>'bk_tit','name'=>'title','type'=>'text'),
                    array('key'=>'bk_imp','name'=>'impact','type'=>'textarea'),
                    array('key'=>'bk_own','name'=>'owner','type'=>'text'),
                    array('key'=>'bk_dd','name'=>'due_date','type'=>'text'),
                    array('key'=>'bk_nxt','name'=>'next','type'=>'text'),
                    array('key'=>'bk_day','name'=>'days_over','type'=>'number'),
                    array('key'=>'bk_links','name'=>'links','type'=>'repeater','sub_fields'=>array(
                        array('key'=>'bkl_url','name'=>'url','type'=>'url'),
                        array('key'=>'bkl_lbl','name'=>'label','type'=>'text')
                    )),
                    array('key'=>'bk_imgs','name'=>'images','type'=>'repeater','sub_fields'=>array(
                        array('key'=>'bki_id','name'=>'id','type'=>'image','return_format'=>'id'),
                        array('key'=>'bki_cap','name'=>'caption','type'=>'text')
                    ))
                )),

                array('key'=>'field_goals', 'label'=>'Goals', 'name'=>'goals', 'type'=>'repeater', 'sub_fields'=>array(
                    array('key'=>'gl_nm','name'=>'goal','type'=>'text'),
                    array('key'=>'gl_st','name'=>'status','type'=>'select','choices'=>array('On Track'=>'On Track','At Risk'=>'At Risk','Blocked'=>'Blocked')),
                    array('key'=>'gl_tg','name'=>'target','type'=>'text'),
                    array('key'=>'gl_dl','name'=>'days_left','type'=>'number'),
                    array('key'=>'gl_ow','name'=>'owner','type'=>'text'),
                    array('key'=>'gl_ms','name'=>'milestones','type'=>'repeater','sub_fields'=>array(
                        array('key'=>'ms_tx','name'=>'text','type'=>'text'),
                        array('key'=>'ms_dn','name'=>'done','type'=>'true_false'),
                        array('key'=>'ms_ov','name'=>'overdue','type'=>'true_false'),
                        array('key'=>'ms_do','name'=>'days_over','type'=>'number'),
                        array('key'=>'ms_as','name'=>'assignee','type'=>'text'),
                        array('key'=>'ms_bb','name'=>'blocked_by','type'=>'text'),
                        array('key'=>'ms_bk','name'=>'blocks','type'=>'text'),
                    ))
                )),
                array('key'=>'field_achievements', 'label'=>'Achievements', 'name'=>'achievements', 'type'=>'repeater', 'sub_fields'=>array(
                    array('key'=>'ac_ic','name'=>'icon','type'=>'text'),
                    array('key'=>'ac_ti','name'=>'title','type'=>'text'),
                    array('key'=>'ac_de','name'=>'desc','type'=>'text'),
                    array('key'=>'ac_da','name'=>'date','type'=>'text'),
                )),
                array('key'=>'field_gantt', 'label'=>'Gantt', 'name'=>'gantt_items', 'type'=>'repeater', 'sub_fields'=>array(
                    array('key'=>'gt_nm','name'=>'name','type'=>'text'),
                    array('key'=>'gt_st','name'=>'start','type'=>'text'),
                    array('key'=>'gt_en','name'=>'end','type'=>'text'),
                    array('key'=>'gt_pc','name'=>'pct','type'=>'number'),
                    array('key'=>'gt_ss','name'=>'status','type'=>'select','choices'=>array('On Track'=>'On Track','At Risk'=>'At Risk','Blocked'=>'Blocked')),
                )),
                array('key'=>'field_tm_cl', 'label'=>'Client Team', 'name'=>'team_client', 'type'=>'repeater', 'sub_fields'=>array(
                    array('key'=>'tc_nm','name'=>'name','type'=>'text'),
                    array('key'=>'tc_ro','name'=>'role','type'=>'text'),
                    array('key'=>'tc_au','name'=>'auth','type'=>'text'),
                    array('key'=>'tc_em','name'=>'email','type'=>'text'),
                )),
                array('key'=>'field_tm_pp', 'label'=>'PIP Team', 'name'=>'team_pip', 'type'=>'repeater', 'sub_fields'=>array(
                    array('key'=>'tp_nm','name'=>'name','type'=>'text'),
                    array('key'=>'tp_ro','name'=>'role','type'=>'text'),
                    array('key'=>'tp_sl','name'=>'slack','type'=>'text'),
                    array('key'=>'tp_dn','name'=>'done','type'=>'number'),
                    array('key'=>'tp_tt','name'=>'total','type'=>'number'),
                    array('key'=>'tp_rs','name'=>'resps','type'=>'text'),
                )),
                array('key'=>'field_actions', 'label'=>'Actions', 'name'=>'actions_list', 'type'=>'repeater', 'sub_fields'=>array(
                    array('key'=>'al_tk','name'=>'task','type'=>'text'),
                    array('key'=>'al_ow','name'=>'owner','type'=>'text'),
                    array('key'=>'al_og','name'=>'org','type'=>'select','choices'=>array('PIP'=>'PIP','Infobase'=>'Infobase','Both'=>'Both')),
                    array('key'=>'al_dl','name'=>'deadline','type'=>'text'),
                    array('key'=>'al_ov','name'=>'overdue','type'=>'true_false'),
                )),
                array('key'=>'field_decisions', 'label'=>'Decisions', 'name'=>'decisions_log', 'type'=>'repeater', 'sub_fields'=>array(
                    array('key'=>'dl_ti','name'=>'title','type'=>'text'),
                    array('key'=>'dl_da','name'=>'date','type'=>'text'),
                    array('key'=>'dl_bd','name'=>'body','type'=>'textarea'),
                    array('key'=>'dl_by','name'=>'by','type'=>'text'),
                    array('key'=>'dl_co','name'=>'color','type'=>'select','choices'=>array('emerald'=>'Emerald','blue'=>'Blue')),
                )),
            ),
            'location' => array(array(array('param' => 'post_type', 'operator' => '==', 'value' => 'dtt_project'))),
        ));
    }
}