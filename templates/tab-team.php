<div class="space-y-6 font-sans">
    <div class="bg-gradient-to-br from-violet-600 to-violet-700 rounded-xl p-8 text-white relative group">
        <?php if($is_admin): ?><button class="absolute top-4 right-4 bg-white/20 p-2 rounded hover:bg-white/30 btn-edit-card z-10" data-card="team"><i data-lucide="edit-2" class="w-4 h-4 text-white"></i></button><?php endif; ?>
        <h2 class="text-2xl font-bold mb-1">Team & Actions</h2>
        <p class="text-violet-100">Who's responsible, their workload status, and what needs to happen now</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 relative group">
        <?php if($is_admin): ?><button class="absolute top-3 right-3 text-slate-300 hover:text-blue-500 btn-edit-card z-10" data-card="achievements"><i data-lucide="edit-2" class="w-3 h-3"></i></button><?php endif; ?>
        <div class="flex items-center gap-2 mb-4"><i data-lucide="check" class="w-5 h-5 text-emerald-600"></i><h3 class="font-bold text-slate-900">Recent Achievements</h3></div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <?php foreach($achievements as $a): ?>
            <div class="flex items-start gap-3 p-3 bg-emerald-50 rounded-lg border border-emerald-100">
                <span class="text-xl"><?php echo esc_html($a['icon']); ?></span>
                <div><div class="font-medium text-emerald-900 text-sm"><?php echo esc_html($a['title']); ?></div><div class="text-xs text-emerald-700"><?php echo esc_html($a['desc']); ?></div><div class="text-xs text-emerald-600 mt-1"><?php echo esc_html($a['date_fmt'] ?? $a['date']); ?></div></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 relative group">
        <?php if($is_admin): ?><button class="absolute top-3 right-3 text-slate-300 hover:text-blue-500 btn-edit-card z-10" data-card="team"><i data-lucide="edit-2" class="w-3 h-3"></i></button><?php endif; ?>
        <h3 class="font-bold text-slate-900 mb-5">Team Directory</h3>
        
        <div class="mb-6">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-7 h-7 rounded bg-amber-100 flex items-center justify-center"><span class="text-amber-700 font-bold text-xs"><?php echo esc_html($client_initials ?? 'IB'); ?></span></div>
                <h4 class="font-semibold text-slate-800"><?php echo esc_html($client_name ?? 'Client'); ?> Team</h4>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <?php if(!empty($team_client)): foreach($team_client as $m): ?>
                <div class="border border-slate-200 rounded-lg p-4 bg-white">
                    <div class="font-bold text-slate-900 text-sm"><?php echo esc_html($m['name']); ?></div>
                    <div class="text-xs text-slate-500 mb-2"><?php echo esc_html($m['role']); ?></div>
                    <div class="text-xs text-slate-600 mb-3"><?php echo esc_html($m['auth']); ?></div>
                    <div class="text-xs text-slate-400 font-mono"><?php echo esc_html($m['email']); ?></div>
                </div>
                <?php endforeach; else: ?>
                <div class="text-sm text-slate-400 col-span-3 italic">No client team members added yet.</div>
                <?php endif; ?>
            </div>
        </div>
        
        <div>
            <div class="flex items-center gap-2 mb-3">
                <div class="w-7 h-7 rounded bg-slate-800 flex items-center justify-center"><span class="text-white font-bold text-xs">P</span></div>
                <h4 class="font-semibold text-slate-800">PIP Team</h4>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <?php if(!empty($team_pip)): foreach($team_pip as $m): ?>
                <div class="border border-slate-200 rounded-lg p-4 bg-white">
                    <div class="font-bold text-slate-900 text-sm"><?php echo esc_html($m['name']); ?></div>
                    <div class="text-xs text-slate-500 mb-1"><?php echo esc_html($m['role']); ?></div>
                    <div class="text-xs text-slate-400 font-mono mb-3"><?php echo esc_html($m['slack'] ?: $m['email']); ?></div>
                    
                    <div class="mb-2">
                        <div class="flex justify-between text-xs mb-1"><span class="text-slate-500">Actions</span><span class="font-medium text-slate-700"><?php echo intval($m['done']); ?>/<?php echo intval($m['total']); ?></span></div>
                        <div class="w-full bg-slate-100 rounded-full h-1.5">
                            <?php 
                            $pct = (!empty($m['total']) && $m['total'] > 0) ? round((intval($m['done'])/intval($m['total']))*100) : 0;
                            $bg = ($pct == 100) ? 'bg-emerald-500' : (($pct > 50) ? 'bg-blue-500' : 'bg-amber-400');
                            ?>
                            <div class="h-1.5 rounded-full <?php echo $bg; ?>" style="width: <?php echo $pct; ?>%"></div>
                        </div>
                    </div>
                    <?php if(!empty($m['resps_arr'])): ?>
                    <ul class="space-y-0.5">
                        <?php foreach($m['resps_arr'] as $r): ?>
                        <li class="text-xs text-slate-600">› <?php echo esc_html($r); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>
                <?php endforeach; else: ?>
                <div class="text-sm text-slate-400 col-span-4 italic">No PIP team members added yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 relative group">
        <?php if($is_admin): ?><button class="absolute top-3 right-3 z-10 bg-white p-1 rounded border shadow-sm text-slate-400 hover:text-blue-600 btn-edit-card" data-card="actions"><i data-lucide="edit-2" class="w-3 h-3"></i></button><?php endif; ?>
        
        <div class="space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                <div class="flex items-center gap-2 mb-4"><div class="w-7 h-7 rounded bg-slate-800 flex items-center justify-center"><span class="text-white font-bold text-xs">P</span></div><h3 class="font-bold text-slate-900">PIP Action Items</h3></div>
                <div class="space-y-2"><?php foreach($actions as $a): if($a['org']!=='PIP') continue; ?><div class="flex items-start gap-3 p-3 rounded-lg border bg-slate-50 border-slate-200"><div class="mt-1 w-4 h-4 border rounded"></div><div class="flex-1 min-w-0"><div class="text-sm font-medium text-slate-800"><?php echo esc_html($a['task']); ?></div><div class="text-xs text-slate-500 mt-0.5"><?php echo esc_html($a['owner']); ?></div></div><span class="text-xs font-bold px-2 py-0.5 rounded-full whitespace-nowrap <?php echo !empty($a['overdue'])?'bg-red-600 text-white':'bg-slate-200 text-slate-600'; ?>"><?php echo !empty($a['overdue'])?'⚠ ':''?><?php echo esc_html($a['deadline']); ?></span></div><?php endforeach; ?></div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                <div class="flex items-center gap-2 mb-4"><div class="w-7 h-7 rounded bg-violet-100 flex items-center justify-center"><span class="text-violet-700 font-bold text-xs">↔</span></div><h3 class="font-bold text-slate-900">Joint Actions</h3></div>
                <div class="space-y-2"><?php foreach($actions as $a): if($a['org']!=='Both') continue; ?><div class="flex items-start gap-3 p-3 rounded-lg border bg-violet-50 border-violet-100"><div class="mt-1 w-4 h-4 border rounded bg-white"></div><div class="flex-1 min-w-0"><div class="text-sm font-medium text-slate-800"><?php echo esc_html($a['task']); ?></div><div class="text-xs text-slate-500 mt-0.5"><?php echo esc_html($a['owner']); ?></div></div><span class="text-xs font-bold px-2 py-0.5 rounded-full whitespace-nowrap <?php echo !empty($a['overdue'])?'bg-red-600 text-white':'bg-white text-slate-600'; ?>"><?php echo !empty($a['overdue'])?'⚠ ':''?><?php echo esc_html($a['deadline']); ?></span></div><?php endforeach; ?></div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <div class="flex items-center gap-2 mb-4"><div class="w-7 h-7 rounded bg-amber-100 flex items-center justify-center"><span class="text-amber-700 font-bold text-xs"><?php echo esc_html($client_initials ?? 'IB'); ?></span></div><h3 class="font-bold text-slate-900">Client Action Items</h3></div>
            <div class="space-y-2">
                <?php 
                $overdue_count = 0;
                foreach($actions as $a): 
                    if($a['org']!=='Infobase') continue; 
                    if(!empty($a['overdue'])) $overdue_count++;
                    $ovr=!empty($a['overdue'])?'bg-red-50 border-red-200':'bg-slate-50 border-slate-200'; 
                ?>
                <div class="flex items-start gap-3 p-3 rounded-lg border <?php echo $ovr; ?>">
                    <div class="mt-1 w-4 h-4 border rounded bg-white"></div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-slate-800"><?php echo esc_html($a['task']); ?></div>
                        <div class="text-xs text-slate-500 mt-0.5"><?php echo esc_html($a['owner']); ?></div>
                    </div>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full whitespace-nowrap <?php echo !empty($a['overdue'])?'bg-red-600 text-white':'bg-slate-200 text-slate-600'; ?>">
                        <?php echo !empty($a['overdue'])?'⚠ ':''?><?php echo esc_html($a['deadline']); ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php if($overdue_count > 0): ?>
            <div class="mt-4 p-3 bg-amber-50 rounded-lg border border-amber-200 text-xs text-amber-800 leading-relaxed">
                <span class="font-bold">Note:</span> There are <?php echo $overdue_count; ?> client action items currently overdue. Escalation recommended.
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>