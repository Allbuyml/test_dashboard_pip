<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class DTT_Data {

    public function get_project_data( $pid ) {
        $get_rep = function($k) use ($pid) { $v = get_field($k, $pid); return (is_array($v)) ? $v : []; };

        $to_ymd = function($d) { $ts = strtotime((string)$d); return $ts ? date('Y-m-d', $ts) : ''; };
        $fmt_short = function($d) { $ts = strtotime((string)$d); return $ts ? date('M j', $ts) : $d; };
        $fmt_long = function($d) { $ts = strtotime((string)$d); return $ts ? date('F j, Y', $ts) : $d; };

        $terms = wp_get_post_terms( $pid, 'client' );
        if ( !empty($terms) && !is_wp_error($terms) ) {
            $client_name = $terms[0]->name;
        } else {
            $client_name = 'PIP'; 
        }

        $words = explode(' ', trim($client_name));
        $client_initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
        if (empty($client_initials)) $client_initials = 'P';

        $status = get_field('project_status', $pid) ?: 'On Track';
        $raw_updated = get_field('last_updated', $pid) ?: date('Y-m-d');
        
        $now = current_time('timestamp');

        // AUTO-CÁLCULO DE MÉTRICAS
        $eng_start = get_field('eng_start_date', $pid);
        $eng_end = get_field('eng_end_date', $pid);

        if ($eng_start) {
            $start_ts = strtotime($eng_start);
            $days_in = max(0, floor(($now - $start_ts) / 86400));
        } else {
            $days_in = intval(get_field('days_in', $pid)); 
        }

        if ($eng_end) {
            $end_ts = strtotime($eng_end) + 86400 - 1; 
            $days_left = max(0, ceil(($end_ts - $now) / 86400));
        } else {
            $days_left = intval(get_field('days_left', $pid)); 
        }

        $metrics = [
            'start_date' => $eng_start,
            'end_date' => $eng_end,
            'days_in' => $days_in, 
            'days_left' => $days_left, 
            'pending' => intval(get_field('pending_deliverables', $pid)), 
            'completed' => intval(get_field('prog_completed', $pid)), 
            'working' => intval(get_field('prog_working', $pid)), 
            'upcoming' => intval(get_field('prog_upcoming', $pid))
        ];

        // THIS WEEK / NEXT WEEK
        $this_week = [];
        foreach($get_rep('this_week') as $tw) {
            if(!empty($tw['text'])) $this_week[] = $tw['text'];
        }
        $next_week = [];
        foreach($get_rep('next_week') as $nw) {
            if(!empty($nw['text'])) $next_week[] = $nw['text'];
        }

        $monday_this = date('M j', strtotime('monday this week'));
        $friday_this = date('M j', strtotime('friday this week'));
        $this_week_dates = "$monday_this - $friday_this";

        $monday_next = date('M j', strtotime('monday next week'));
        $friday_next = date('M j', strtotime('friday next week'));
        $next_week_dates = "$monday_next - $friday_next";

        // COMPLETION RATE CARD
        $comp_done = intval(get_field('comp_done', $pid));
        $comp_inprog = intval(get_field('comp_in_progress', $pid));
        $comp_block = intval(get_field('comp_blocked', $pid));
        $comp_not = intval(get_field('comp_not_started', $pid));
        $comp_color = get_field('comp_primary_color', $pid) ?: '#10b981'; 
        
        $comp_total = $comp_done + $comp_inprog + $comp_block + $comp_not;
        $comp_safe_total = max(1, $comp_total);
        $comp_pct = round(($comp_done / $comp_safe_total) * 100);

        $qStart = $eng_start ? strtotime($eng_start) : strtotime(date('Y-01-01'));
        $qEnd = $eng_end ? strtotime($eng_end) : strtotime(date('Y-03-31'));
        $qPctRaw = round((($now - $qStart) / max(1, $qEnd - $qStart)) * 100);
        $qPct = max(0, min(100, $qPctRaw));

        $completion = [
            'done' => $comp_done, 'in_progress' => $comp_inprog, 'blocked' => $comp_block,
            'not_started' => $comp_not, 'total' => $comp_total, 'pct' => $comp_pct,
            'q_pct' => $qPct, 'primary_color' => $comp_color
        ];

        // BLOCKERS, COMMENTS Y RESOLVED
        $raw_blockers = $get_rep('blockers');
        $blockers = [];
        foreach ($raw_blockers as $b) {
            if (!is_array($b)) continue; 
            
            $b['due_date_ymd'] = $to_ymd($b['due_date'] ?? '');
            if ((!isset($b['days_over']) || $b['days_over'] === '') && !empty($b['due_date_ymd'])) {
                $b['days_over'] = max(0, floor(($now - strtotime($b['due_date_ymd'])) / 86400));
            }
            
            $b['resolved'] = !empty($b['resolved']); // Booleano
            
            // Procesar comentarios enriquecidos
            $clean_comments = [];
            if(isset($b['comments']) && is_array($b['comments'])) {
                foreach($b['comments'] as $c) {
                    $c_imgs = [];
                    if(isset($c['images']) && is_array($c['images'])) {
                        foreach($c['images'] as $img) {
                            if(!empty($img['id'])) {
                                $url = wp_get_attachment_url($img['id']);
                                if($url) $c_imgs[] = ['id' => $img['id'], 'url' => $url];
                            }
                        }
                    }
                    $c['images'] = $c_imgs;
                    $clean_comments[] = $c;
                }
            }
            $b['comments'] = $clean_comments;

            $clean_links = [];
            if (isset($b['links']) && is_array($b['links'])) {
                foreach ($b['links'] as $link) { if (is_array($link)) $clean_links[] = $link; }
            }
            $b['links'] = $clean_links;

            $clean_imgs = [];
            if (isset($b['images']) && is_array($b['images'])) {
                foreach ($b['images'] as $img) {
                    if (is_array($img) && !empty($img['id'])) {
                        $url = wp_get_attachment_url($img['id']);
                        if ($url) { $img['url'] = $url; $clean_imgs[] = $img; }
                    }
                }
            }
            $b['images'] = $clean_imgs;
            $blockers[] = $b;
        }

        $sev_weights = ['critical' => 1, 'high' => 2, 'medium' => 3];
        usort($blockers, function($a, $b) use ($sev_weights) {
            // Mover los resueltos al fondo
            if ($a['resolved'] !== $b['resolved']) return $a['resolved'] ? 1 : -1;
            $wa = $sev_weights[strtolower($a['sev'])] ?? 4;
            $wb = $sev_weights[strtolower($b['sev'])] ?? 4;
            return $wa <=> $wb;
        });

        // GOALS
        $raw_goals = $get_rep('goals');
        $goals = [];
        foreach ($raw_goals as $g) {
            if (!is_array($g)) continue;
            $g['target_ymd'] = $to_ymd($g['target'] ?? '');
            $g['target_fmt'] = $fmt_long($g['target'] ?? ''); 
            if ((!isset($g['days_left']) || $g['days_left'] === '') && !empty($g['target_ymd'])) {
                $g['days_left'] = max(0, ceil((strtotime($g['target_ymd']) - $now) / 86400));
            }
            $ms = (isset($g['milestones']) && is_array($g['milestones'])) ? $g['milestones'] : [];
            $total = max(1, count($ms));
            $done = 0;
            foreach ($ms as $m) { if (is_array($m) && !empty($m['done'])) { $done++; } }
            $g['pct'] = count($ms) > 0 ? round(($done / $total) * 100) : 0;
            $g['done_ms'] = $done;
            $g['total_ms'] = count($ms);
            $g['milestones'] = $ms; 
            $goals[] = $g;
        }

        $achievements = [];
        foreach($get_rep('achievements') as $a) {
            if(!is_array($a)) continue;
            $a['date_ymd'] = $to_ymd($a['date']??''); $a['date_fmt'] = $fmt_short($a['date']??''); $achievements[] = $a;
        }
        
        $decisions = [];
        foreach($get_rep('decisions_log') as $d) {
            if(!is_array($d)) continue;
            $d['date_ymd'] = $to_ymd($d['date']??''); $d['date_fmt'] = $fmt_long($d['date']??''); $decisions[] = $d;
        }

        // GANTT
        $raw_proj_start = $eng_start ?: date('Y-m-d', strtotime('-2 weeks'));
        try { $start_dt = new DateTime($raw_proj_start); } catch (Exception $e) { $start_dt = new DateTime('-2 weeks'); }

        if ($start_dt->format('N') != 1) { $start_dt->modify('last monday'); }
        
        $p_start_ts = $start_dt->getTimestamp();
        $total_sec = 70 * 86400; // 10 semanas
        
        $timeline_labels = [];
        for ($i = 0; $i <= 10; $i++) {
            $lbl_dt = clone $start_dt;
            $lbl_dt->modify("+$i weeks");
            $timeline_labels[] = $lbl_dt->format('M j');
        }
        
        $today_pct_raw = (($now - $p_start_ts) / $total_sec) * 100;
        $today_pct = number_format(max(0, min(100, $today_pct_raw)), 4, '.', '');

        $gantt = [];
        foreach($get_rep('gantt_items') as $g) {
            if(!is_array($g)) continue;
            $item_start = $g['start'] ?? date('Y-m-d');
            $item_end = $g['end'] ?? date('Y-m-d', strtotime('+1 week'));
            try {
                $s_dt = new DateTime($item_start);
                $e_dt = new DateTime($item_end);
            } catch (Exception $e) { continue; }
            $s = $s_dt->getTimestamp();
            $e = $e_dt->getTimestamp() + 86399; 
            
            $left = (($s - $p_start_ts) / $total_sec) * 100;
            $width = (($e - $s) / $total_sec) * 100;
            $left_pct = max(0, $left);
            if ($left < 0) { $width += $left; } 
            $width_pct = max(0.5, min(100 - $left_pct, $width)); 
            
            $g['left_pct'] = number_format($left_pct, 4, '.', '');
            $g['width_pct'] = number_format($width_pct, 4, '.', '');
            $g['date_str'] = $s_dt->format('M j') . ' – ' . $e_dt->format('M j');
            $gantt[] = $g;
        }

        $team_pip = [];
        foreach($get_rep('team_pip') as $m) {
            if(!is_array($m)) continue;
            $m['resps_arr'] = !empty($m['resps']) ? array_map('trim', explode(',', $m['resps'])) : [];
            $m['pct'] = (!empty($m['total']) && $m['total'] > 0) ? round((intval($m['done'])/intval($m['total']))*100) : 0;
            $team_pip[] = $m;
        }

        return [
            'pid' => $pid,
            'client_name' => $client_name,
            'client_initials' => $client_initials, 
            'project_status' => $status,
            'updated_ymd' => $to_ymd($raw_updated),
            'updated_fmt' => $fmt_long($raw_updated),
            'completion' => $completion,
            'metrics' => $metrics,
            'this_week' => $this_week,
            'this_week_dates' => $this_week_dates,
            'next_week' => $next_week,
            'next_week_dates' => $next_week_dates,
            'blockers' => $blockers,
            'goals' => $goals,
            'achievements' => $achievements,
            'gantt' => $gantt,
            'timeline_dates' => ['labels' => $timeline_labels, 'today_pct' => $today_pct, 'show_today' => ($today_pct > 0 && $today_pct < 100)],
            'team_client' => $get_rep('team_client'),
            'team_pip' => $team_pip,
            'actions' => $get_rep('actions_list'),
            'decisions' => $decisions,
        ];
    }

    public function get_all_projects() {
        return get_posts(array('post_type' => 'dtt_project', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC'));
    }
}