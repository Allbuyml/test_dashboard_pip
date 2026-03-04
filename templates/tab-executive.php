<?php 
// CONDICIONAL: Cambia a "true" si alguna vez deseas mostrar de nuevo la 3ra tarjeta.
$show_project_progress = false; 

// Grid dinámico (2 columnas o 3 columnas)
$grid_class = $show_project_progress ? 'md:grid-cols-3' : 'md:grid-cols-2 lg:grid-cols-2 mx-auto';
?>

<div class="space-y-6 font-sans relative">
    
    <div class="grid grid-cols-1 <?php echo $grid_class; ?> gap-6">
        
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 relative group">
            <?php if($is_admin): ?><button class="absolute top-3 right-3 text-blue-800 hover:text-blue-950 bg-blue-50 hover:bg-blue-100 p-1.5 rounded-md transition-colors btn-edit-card" data-card="completion" title="Edit Completion Rate"><i data-lucide="edit-2" class="w-3.5 h-3.5"></i></button><?php endif; ?>
            <div class="flex items-center justify-between mb-4"><h3 class="font-semibold text-slate-900">Q1 Completion Rate</h3><span class="text-xs text-slate-400 font-medium"><?php echo $completion['done']; ?>/<?php echo $completion['total']; ?> deliverables</span></div>
            <div class="flex items-center gap-5">
                <div class="relative flex-shrink-0 w-24 h-24">
                    <svg width="96" height="96" viewBox="0 0 104 104" class="overflow-visible">
                        <?php 
                        $r = 40; $cx = 52; $cy = 52; $stroke = 10;
                        $circ = 2 * M_PI * $r;
                        $segments = [
                            ['value' => $completion['done'],        'color' => $completion['primary_color'], 'label' => 'Done'],
                            ['value' => $completion['in_progress'], 'color' => '#3b82f6',                    'label' => 'In Progress'],
                            ['value' => $completion['blocked'],     'color' => '#ef4444',                    'label' => 'Blocked'],
                            ['value' => $completion['not_started'], 'color' => '#e2e8f0',                    'label' => 'Not Started'],
                        ];
                        $offset = 0;
                        foreach ($segments as $s): 
                            if ($s['value'] == 0) continue;
                            $len = ($s['value'] / max(1, $completion['total'])) * $circ;
                            $dasharray = "{$len} " . ($circ - $len);
                            $dashoffset = -$offset;
                            $offset += $len;
                        ?>
                        <circle cx="<?php echo $cx; ?>" cy="<?php echo $cy; ?>" r="<?php echo $r; ?>" fill="none" stroke="<?php echo $s['color']; ?>" stroke-width="<?php echo $stroke; ?>" stroke-dasharray="<?php echo $dasharray; ?>" stroke-dashoffset="<?php echo $dashoffset; ?>" stroke-linecap="butt" style="transform: rotate(-90deg); transform-origin: <?php echo $cx; ?>px <?php echo $cy; ?>px;" class="transition-all duration-1000 ease-out" />
                        <?php endforeach; ?>
                        <text x="<?php echo $cx; ?>" y="<?php echo $cy - 2; ?>" text-anchor="middle" style="font-size: 22px; font-weight: 700; fill: #0f172a;"><?php echo $completion['pct']; ?>%</text>
                        <text x="<?php echo $cx; ?>" y="<?php echo $cy + 14; ?>" text-anchor="middle" style="font-size: 10px; fill: #94a3b8; font-weight: 500;">complete</text>
                    </svg>
                </div>
                <div class="flex-1 space-y-1.5">
                    <?php foreach ($segments as $s): if($s['value'] > 0): ?>
                    <div class="flex items-center justify-between text-xs"><div class="flex items-center gap-1.5"><div class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background: <?php echo $s['color']; ?>"></div><span class="text-slate-600 font-medium"><?php echo $s['label']; ?></span></div><span class="font-bold text-slate-800"><?php echo $s['value']; ?></span></div>
                    <?php endif; endforeach; ?>
                    <div class="pt-2.5 border-t border-slate-100 mt-2">
                        <div class="flex justify-between text-[10px] text-slate-400 mb-1 font-semibold uppercase"><div class="flex items-center gap-1"><span>Quarter elapsed</span></div><span><?php echo $completion['q_pct']; ?>%</span></div>
                        <div class="relative w-full bg-slate-100 rounded-full h-2"><div class="absolute top-0 left-0 h-2 rounded-full transition-all duration-1000 ease-out" style="width: <?php echo $completion['pct']; ?>%; background: <?php echo $completion['primary_color']; ?>;"></div><div class="absolute top-[-3px] h-3.5 w-[2px] bg-slate-400 rounded shadow-sm" style="left: <?php echo $completion['q_pct']; ?>%;"></div></div>
                        <div class="flex justify-between text-[10px] mt-1 font-bold"><span style="color: <?php echo $completion['primary_color']; ?>;"><?php echo $completion['pct']; ?>% done</span><?php $behind = $completion['pct'] < $completion['q_pct']; ?><span class="<?php echo $behind ? 'text-red-500' : 'text-emerald-500'; ?>"><?php echo $behind ? ($completion['q_pct'] - $completion['pct']) . '% behind pace' : 'On pace'; ?></span></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 relative group">
            <?php if($is_admin): ?><button class="absolute top-3 right-3 text-blue-800 hover:text-blue-950 bg-blue-50 hover:bg-blue-100 p-1.5 rounded-md transition-colors btn-edit-card" data-card="metrics"><i data-lucide="edit-2" class="w-3.5 h-3.5"></i></button><?php endif; ?>
            <h3 class="font-semibold text-slate-900 mb-4">Engagement Metrics</h3>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between"><span>Days in Engagement</span><span class="font-bold text-slate-900"><?php echo esc_html($metrics['days_in']); ?></span></div>
                <div class="flex justify-between border-t border-slate-100 pt-2"><span>Days Remaining (Q1)</span><span class="font-bold text-slate-900"><?php echo esc_html($metrics['days_left']); ?></span></div>
                <div class="flex justify-between border-t border-slate-100 pt-2"><span class="text-slate-500">Deliverables Pending</span><span class="font-bold text-amber-600"><?php echo esc_html($metrics['pending']); ?></span></div>
            </div>
        </div>

        <?php if($show_project_progress): ?>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 relative group">
            <?php if($is_admin): ?><button class="absolute top-3 right-3 text-blue-800 hover:text-blue-950 bg-blue-50 hover:bg-blue-100 p-1.5 rounded-md transition-colors btn-edit-card" data-card="progress"><i data-lucide="edit-2" class="w-3.5 h-3.5"></i></button><?php endif; ?>
            <h3 class="font-semibold text-slate-900 mb-4">Project Progress</h3>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between"><span class="text-slate-500">Completed This Month</span><span class="font-bold text-emerald-600"><?php echo esc_html($metrics['completed']); ?></span></div>
                <div class="flex justify-between border-t border-slate-100 pt-2"><span class="text-slate-500">Currently Working On</span><span class="font-bold text-blue-600"><?php echo esc_html($metrics['working']); ?></span></div>
                <div class="flex justify-between border-t border-slate-100 pt-2"><span class="text-slate-500">Coming Up This Month</span><span class="font-bold text-slate-900"><?php echo esc_html($metrics['upcoming']); ?></span></div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 relative group">
            <?php if($is_admin): ?><button class="absolute top-3 right-3 text-blue-800 hover:text-blue-950 bg-blue-50 hover:bg-blue-100 p-1.5 rounded-md transition-colors btn-edit-card" data-card="achievements"><i data-lucide="edit-2" class="w-3.5 h-3.5"></i></button><?php endif; ?>
            
            <div class="flex items-center gap-2 mb-4">
                <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600"></i>
                <h3 class="font-bold text-slate-900">Latest Updates</h3>
            </div>
            
            <div class="space-y-3">
                <?php foreach($achievements as $a): ?>
                <div class="flex items-start gap-3 p-3 bg-emerald-50 rounded-lg border border-emerald-100">
                    <span class="text-2xl"><?php echo esc_html($a['icon']); ?></span>
                    <div>
                        <div class="font-medium text-emerald-900 text-sm"><?php echo esc_html($a['title']); ?></div>
                        <div class="text-xs text-emerald-700 mt-0.5"><?php echo esc_html($a['desc']); ?></div>
                        <div class="text-xs text-emerald-600 mt-1"><?php echo esc_html($a['date_fmt']); ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 relative group flex flex-col">
            <?php if($is_admin): ?><button class="absolute top-3 right-3 text-blue-800 hover:text-blue-950 bg-blue-50 hover:bg-blue-100 p-1.5 rounded-md transition-colors btn-edit-card" data-card="gantt"><i data-lucide="edit-2" class="w-3.5 h-3.5"></i></button><?php endif; ?>
            
            <div class="flex items-center gap-2 mb-6">
                <i data-lucide="bar-chart" class="w-5 h-5 text-blue-600"></i>
                <h3 class="font-bold text-slate-900">Timeline Overview</h3>
            </div>
            
            <div class="relative flex flex-col w-full">
                
                <div class="flex text-xs text-slate-500 mb-2">
                    <div class="w-32 flex-shrink-0"></div> <div class="flex-1 relative h-4">
                        <?php foreach($timeline_dates['labels'] as $idx => $l): ?>
                            <div class="absolute top-0 text-[10px] font-medium text-slate-500 whitespace-nowrap" style="left: <?php echo $idx * 10; ?>%; transform: translateX(-50%);">
                                <?php echo esc_html($l); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="absolute inset-0 left-32 pointer-events-none z-0 mt-6">
                    <?php for($i=0; $i<=10; $i++): ?>
                        <div class="absolute top-0 bottom-0 border-l border-slate-100" style="left: <?php echo $i * 10; ?>%;"></div>
                    <?php endfor; ?>
                    
                    <?php if($timeline_dates['show_today']): ?>
                        <div class="absolute top-0 bottom-[-20px] w-[2px] bg-blue-600 z-20" style="left: <?php echo esc_attr($timeline_dates['today_pct']); ?>%;">
                            <div class="absolute -top-1 -left-1 w-2.5 h-2.5 rounded-full bg-blue-600"></div>
                            <div class="absolute -bottom-5 left-1/2 -translate-x-1/2 text-[10px] font-bold text-blue-600 uppercase bg-white px-1">Today</div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="space-y-2 relative z-10 pb-6">
                    <?php foreach($gantt as $g): 
                        $txt_col = match($g['status']) { 'On Track'=>'text-emerald-600', 'At Risk'=>'text-amber-600', default=>'text-red-600' }; 
                    ?>
                    <div class="flex items-center h-8">
                        <div class="w-32 flex-shrink-0 pr-4">
                            <div class="text-xs font-medium text-slate-900 truncate" title="<?php echo esc_attr($g['name']); ?>"><?php echo esc_html($g['name']); ?></div>
                            <div class="text-[10px] <?php echo $txt_col; ?> font-medium"><?php echo $g['pct']; ?>%</div>
                        </div>
                        <div class="flex-1 relative h-full flex items-center">
                            <div class="absolute h-6 rounded shadow-sm bg-blue-500 overflow-hidden flex items-center px-2 text-white transition-all duration-500 ease-out" style="left: <?php echo esc_attr($g['left_pct']); ?>%; width: <?php echo esc_attr($g['width_pct']); ?>%;">
                                <span class="text-[10px] font-medium truncate w-full text-left"><?php echo esc_html($g['date_str']); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

            </div>
        </div>

    </div>

    <div class="bg-gradient-to-br from-red-50 to-amber-50 rounded-xl shadow-md border-2 border-red-200 p-6 relative group">
        <?php if($is_admin): ?><button class="absolute top-4 right-4 text-blue-800 hover:text-blue-950 bg-white hover:bg-blue-50 p-1.5 rounded-md shadow-sm transition-colors btn-edit-card" data-card="blockers"><i data-lucide="edit-2" class="w-4 h-4"></i></button><?php endif; ?>
        
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-lg bg-red-100 flex items-center justify-center"><i data-lucide="alert-triangle" class="w-6 h-6 text-red-600"></i></div>
            <div>
                <h3 class="font-bold text-slate-900 text-lg">Items Needing Attention</h3>
                <p class="text-sm text-slate-600">Critical blockers requiring immediate action</p>
            </div>
            <div class="ml-auto px-4 py-2 bg-red-600 text-white rounded-lg font-bold text-2xl"><?php echo count($blockers); ?></div>
        </div>

        <div class="space-y-3">
            <?php foreach($blockers as $b): 
                $style = match($b['sev']) { 'critical'=>['border-red-300','bg-red-200 text-red-800'], 'high'=>['border-amber-300','bg-amber-200 text-amber-800'], default=>['border-blue-300','bg-blue-200 text-blue-800'] };
            ?>
            <div class="rounded-lg border-2 bg-white overflow-hidden p-4 <?php echo $style[0]; ?>">
                <div class="flex items-start justify-between mb-2">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 text-xs font-bold rounded uppercase <?php echo $style[1]; ?>"><?php echo esc_html($b['sev']); ?></span>
                            <span class="font-semibold text-slate-900"><?php echo esc_html($b['title']); ?></span>
                        </div>
                        
                        <div class="text-sm text-slate-600 mb-2 dtt-linkify"><?php echo esc_html($b['impact']); ?></div>
                        
                        <?php if(!empty($b['links'])): ?>
                            <div class="flex flex-wrap gap-2 mt-3">
                                <?php foreach($b['links'] as $link): ?>
                                    <a href="<?php echo esc_url($link['url']); ?>" target="_blank" class="flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-2.5 py-1.5 rounded transition-colors">
                                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i> <?php echo esc_html($link['label'] ?: 'View Link'); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="flex flex-col items-end gap-3 ml-4 flex-shrink-0">
                        <?php if(!empty($b['days_over']) && $b['days_over'] > 0): ?>
                        <div class="text-right">
                            <div class="text-lg font-bold text-red-600 leading-none"><?php echo esc_html($b['days_over']); ?>d</div>
                            <div class="text-xs text-red-500 font-medium">overdue</div>
                        </div>
                        <?php endif; ?>

                        <?php if(!empty($b['images'])): ?>
                            <div class="flex items-center justify-end -space-x-2">
                                <?php 
                                $img_count = count($b['images']); 
                                foreach(array_slice($b['images'], 0, 3) as $idx => $img): 
                                ?>
                                    <?php if($img_count > 3 && $idx === 2): ?>
                                        <div class="relative w-10 h-10 rounded-lg bg-slate-800 overflow-hidden cursor-pointer img-lightbox-trigger shadow-md ring-2 ring-white hover:z-10" data-images='<?php echo htmlspecialchars(json_encode($b['images'])); ?>' data-index="0">
                                            <img src="<?php echo esc_url($img['url']); ?>" class="w-full h-full object-cover opacity-40">
                                            <div class="absolute inset-0 flex items-center justify-center font-bold text-white text-xs">+<?php echo $img_count - 3; ?></div>
                                        </div>
                                    <?php else: ?>
                                        <div class="w-10 h-10 rounded-lg bg-slate-100 overflow-hidden cursor-pointer img-lightbox-trigger shadow-md ring-2 ring-white hover:z-10 hover:-translate-y-1 transition-transform" data-images='<?php echo htmlspecialchars(json_encode($b['images'])); ?>' data-index="<?php echo $idx; ?>">
                                            <img src="<?php echo esc_url($img['url']); ?>" class="w-full h-full object-cover">
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-3 text-sm bg-slate-50 p-3 rounded mt-2">
                    <div><span class="text-slate-500 font-medium">Owner: </span><span class="text-slate-700"><?php echo esc_html($b['owner']); ?></span></div>
                    <div><span class="text-slate-500 font-medium">Next: </span><span class="text-slate-700 dtt-linkify"><?php echo esc_html($b['next']); ?></span></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 relative group">
        <?php if($is_admin): ?><button class="absolute top-3 right-3 text-blue-800 hover:text-blue-950 bg-blue-50 hover:bg-blue-100 p-1.5 rounded-md transition-colors btn-edit-card" data-card="goals"><i data-lucide="edit-2" class="w-3.5 h-3.5"></i></button><?php endif; ?>
        
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center"><i data-lucide="target" class="w-5 h-5 text-blue-600"></i></div>
            <h3 class="font-bold text-slate-900">Progress Against Goals</h3>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <?php foreach($goals as $g): 
                $bar_col = match($g['status']) { 'On Track'=>'bg-emerald-500', 'At Risk'=>'bg-amber-500', default=>'bg-red-500' }; 
                $badge_cls = match($g['status']) { 'On Track'=>'bg-emerald-100 text-emerald-700 border-emerald-200', 'At Risk'=>'bg-amber-100 text-amber-700 border-amber-200', default=>'bg-red-100 text-red-700 border-red-200' }; 
            ?>
            <div class="border border-slate-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between mb-3">
                    <span class="px-2 py-1 rounded text-xs font-medium border <?php echo $badge_cls; ?> border-opacity-20"><?php echo esc_html($g['status']); ?></span>
                    <span class="text-xs text-slate-500"><?php echo esc_html($g['days_left']); ?>d</span>
                </div>
                <div class="font-semibold text-slate-900 text-sm mb-2 truncate" title="<?php echo esc_attr($g['goal']); ?>"><?php echo esc_html($g['goal']); ?></div>
                <div class="w-full bg-slate-100 rounded-full h-2 mb-1">
                    <div class="h-2 rounded-full <?php echo $bar_col; ?>" style="width: <?php echo $g['pct']; ?>%"></div>
                </div>
                <div class="text-xs text-slate-500"><?php echo $g['done_ms']; ?>/<?php echo $g['total_ms']; ?> complete</div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div id="dtt-lightbox" class="hidden fixed inset-0 z-[99999] bg-slate-900/95 flex flex-col justify-between p-4 backdrop-blur-md opacity-0 transition-opacity duration-300">
    
    <div class="flex justify-end items-center p-2 gap-4">
        <button id="lb-copy" class="text-white/60 hover:text-white transition-colors bg-white/10 hover:bg-white/20 p-2 rounded-full flex items-center gap-2" title="Copy Image Link">
            <span id="lb-copy-icon"><i data-lucide="link" class="w-5 h-5"></i></span>
            <span class="text-xs font-bold uppercase tracking-wider pr-1">Copy Link</span>
        </button>
        <button id="lb-close" class="text-white/60 hover:text-white transition-colors bg-white/10 hover:bg-white/20 p-2 rounded-full">
            <i data-lucide="x" class="w-6 h-6"></i>
        </button>
    </div>
    
    <div class="flex-1 relative flex items-center justify-center overflow-hidden px-10">
        <button id="lb-prev" class="absolute left-4 text-white/50 hover:text-white transition-colors p-2 bg-black/20 hover:bg-black/40 rounded-full"><i data-lucide="chevron-left" class="w-8 h-8"></i></button>
        <div class="flex flex-col items-center justify-center w-full h-full">
            <img id="lb-img" src="" class="max-h-full max-w-full object-contain rounded-lg shadow-2xl transition-transform duration-300 select-none">
            <p id="lb-cap" class="text-white text-center text-sm font-medium bg-black/60 px-6 py-2.5 rounded-full backdrop-blur mt-4 empty:hidden"></p>
        </div>
        <button id="lb-next" class="absolute right-4 text-white/50 hover:text-white transition-colors p-2 bg-black/20 hover:bg-black/40 rounded-full"><i data-lucide="chevron-right" class="w-8 h-8"></i></button>
    </div>

    <div class="mt-4 pb-2">
        <div id="lb-count" class="text-white/50 text-xs text-center font-mono font-bold tracking-widest mb-3"></div>
        <div id="lb-thumbs" class="flex gap-3 overflow-x-auto justify-center max-w-full px-4 snap-x"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const lb = document.getElementById('dtt-lightbox');
    if(!lb) return;
    
    document.body.appendChild(lb);

    const lbImg = document.getElementById('lb-img');
    const lbCap = document.getElementById('lb-cap');
    const lbCount = document.getElementById('lb-count');
    const lbThumbs = document.getElementById('lb-thumbs');
    const lbCopyIcon = document.getElementById('lb-copy-icon');
    let currentImages = [];
    let currentIndex = 0;

    const updateLb = () => {
        if(!currentImages.length) return;
        lbImg.src = currentImages[currentIndex].url;
        lbCap.textContent = currentImages[currentIndex].caption || '';
        lbCount.textContent = (currentIndex + 1) + ' / ' + currentImages.length;
        
        lbThumbs.innerHTML = currentImages.map((img, i) => `
            <img src="${img.url}" data-idx="${i}" class="w-16 h-16 object-cover rounded-lg cursor-pointer border-2 transition-all flex-shrink-0 snap-center ${i === currentIndex ? 'border-white scale-110 shadow-lg' : 'border-transparent opacity-50 hover:opacity-100'}">
        `).join('');
    };

    lbThumbs.addEventListener('click', (e) => {
        if(e.target.tagName === 'IMG') {
            currentIndex = parseInt(e.target.getAttribute('data-idx'));
            updateLb();
        }
    });

    document.body.addEventListener('click', (e) => {
        const trigger = e.target.closest('.img-lightbox-trigger');
        if(trigger) {
            currentImages = JSON.parse(trigger.getAttribute('data-images') || '[]');
            currentIndex = parseInt(trigger.getAttribute('data-index') || '0', 10);
            updateLb();
            lb.classList.remove('hidden');
            setTimeout(() => lb.classList.remove('opacity-0'), 10); 
        }
    });

    document.getElementById('lb-close').addEventListener('click', () => {
        lb.classList.add('opacity-0');
        setTimeout(() => lb.classList.add('hidden'), 300); 
    });
    
    document.getElementById('lb-prev').addEventListener('click', () => {
        currentIndex = (currentIndex > 0) ? currentIndex - 1 : currentImages.length - 1;
        updateLb();
    });
    
    document.getElementById('lb-next').addEventListener('click', () => {
        currentIndex = (currentIndex < currentImages.length - 1) ? currentIndex + 1 : 0;
        updateLb();
    });

    document.getElementById('lb-copy').addEventListener('click', () => {
        navigator.clipboard.writeText(currentImages[currentIndex].url).then(() => {
            const originalHTML = lbCopyIcon.innerHTML;
            lbCopyIcon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-400"><path d="M20 6 9 17l-5-5"/></svg>';
            setTimeout(() => { lbCopyIcon.innerHTML = originalHTML; }, 2000);
        });
    });
    
    document.addEventListener('keydown', (e) => {
        if(!lb.classList.contains('hidden')) {
            if(e.key === 'Escape') document.getElementById('lb-close').click();
            if(e.key === 'ArrowLeft') document.getElementById('lb-prev').click();
            if(e.key === 'ArrowRight') document.getElementById('lb-next').click();
        }
    });
});
</script>