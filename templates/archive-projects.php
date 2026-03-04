<?php
// Lógica para determinar si agrupamos o mostramos simple
$view_groups = [];
$page_title = "All Projects";

if (isset($term_id)) {
    // VISTA: Cliente Individual (Sin Agrupar)
    $term = get_term($term_id, 'client');
    if ($term && !is_wp_error($term)) {
        $page_title = $term->name . " Projects";
        $projects = get_posts([
            'post_type' => 'dtt_project',
            'numberposts' => -1,
            'post_status' => 'publish',
            'tax_query' => [['taxonomy' => 'client', 'field' => 'term_id', 'terms' => $term_id]]
        ]);
        
        $view_groups[] = [
            'name' => '', // Vacío para no mostrar título de grupo
            'initial' => '',
            'projects' => $projects
        ];
    }
} else {
    // VISTA: Global (/projects/) - Agrupado por Cliente
    $clients = get_terms(['taxonomy' => 'client', 'hide_empty' => true]);
    
    foreach ($clients as $c) {
        $projects = get_posts([
            'post_type' => 'dtt_project',
            'numberposts' => -1,
            'post_status' => 'publish',
            'tax_query' => [['taxonomy' => 'client', 'field' => 'term_id', 'terms' => $c->term_id]]
        ]);
        
        if (!empty($projects)) {
            $view_groups[] = [
                'name' => $c->name,
                'initial' => strtoupper(substr($c->name, 0, 1)),
                'projects' => $projects
            ];
        }
    }
}

// Comprobar si hay proyectos en total
$has_projects = false;
foreach($view_groups as $g) { if(!empty($g['projects'])) $has_projects = true; }
?>

<div class="py-12 px-6 font-sans">
    <div class="max-w-7xl mx-auto">
        
        <div class="flex items-center justify-between mb-10">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 mb-2"><?php echo esc_html($page_title); ?></h1>
                <p class="text-slate-500">Select a project dashboard to view details and timelines.</p>
            </div>
            <div class="w-14 h-14 bg-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-200">
                <i data-lucide="folder-kanban" class="w-7 h-7 text-white"></i>
            </div>
        </div>

        <?php if (!$has_projects): ?>
            <div class="bg-white rounded-xl border border-slate-200 p-12 text-center shadow-sm">
                <i data-lucide="folder-open" class="w-16 h-16 text-slate-300 mx-auto mb-4"></i>
                <h3 class="text-xl font-bold text-slate-700 mb-2">No projects found</h3>
                <p class="text-slate-500">There are currently no active projects available.</p>
            </div>
        <?php else: ?>
            
            <?php foreach($view_groups as $group): ?>
                
                <?php if(!empty($group['name'])): ?>
                <div class="flex items-center gap-3 mb-6 mt-12 first:mt-0 border-b border-slate-200 pb-3">
                    <div class="w-8 h-8 bg-slate-900 text-white rounded-lg flex items-center justify-center font-bold text-sm shadow-sm">
                        <?php echo esc_html($group['initial']); ?>
                    </div>
                    <h2 class="text-2xl font-bold text-slate-800"><?php echo esc_html($group['name']); ?></h2>
                    <span class="bg-slate-200 text-slate-600 text-xs font-bold px-2.5 py-1 rounded-full ml-2"><?php echo count($group['projects']); ?></span>
                </div>
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach($group['projects'] as $p): 
                        $status = get_field('project_status', $p->ID) ?: 'Discovery';
                        $score = get_field('health_score', $p->ID) ?: 0;
                        $updated = get_field('last_updated', $p->ID) ?: date('Y-m-d');
                        
                        $badge = match($status) {
                            'On Track' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                            'At Risk' => 'bg-amber-100 text-amber-700 border-amber-200',
                            'Blocked' => 'bg-red-100 text-red-700 border-red-200',
                            default => 'bg-violet-100 text-violet-700 border-violet-200'
                        };
                    ?>
                    <a href="<?php echo esc_url(get_permalink($p->ID)); ?>" class="block bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 border border-slate-200 p-6 group transform hover:-translate-y-1">
                        <div class="flex justify-between items-start mb-5">
                            <span class="px-3 py-1 rounded-full text-xs font-bold border <?php echo $badge; ?>"><?php echo esc_html($status); ?></span>
                            <div class="p-2 bg-slate-50 rounded-lg group-hover:bg-blue-50 transition-colors">
                                <i data-lucide="arrow-up-right" class="w-4 h-4 text-slate-400 group-hover:text-blue-600"></i>
                            </div>
                        </div>
                        <h2 class="text-xl font-bold text-slate-900 mb-2 group-hover:text-blue-600 transition-colors"><?php echo esc_html($p->post_title); ?></h2>
                        <p class="text-xs font-medium text-slate-400 mb-6 flex items-center gap-1.5"><i data-lucide="clock" class="w-3.5 h-3.5"></i> Updated: <?php echo date('M j, Y', strtotime($updated)); ?></p>
                        
                     
                    </a>
                    <?php endforeach; ?>
                </div>

            <?php endforeach; ?>

        <?php endif; ?>
    </div>
</div>