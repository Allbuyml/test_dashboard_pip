<div class="space-y-6 font-sans">
    <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl p-8 text-white relative group">
        <?php if($is_admin): ?><button class="absolute top-4 right-4 bg-white/20 p-2 rounded hover:bg-white/30 btn-edit-card" data-card="goals"><i data-lucide="edit-2" class="w-4 h-4 text-white"></i></button><?php endif; ?>
        <h2 class="text-2xl font-bold mb-1">Workstreams & Goals</h2>
        <p class="text-blue-100">Detailed milestone tracking, ownership, and dependency mapping</p>
    </div>

    <div class="flex flex-wrap gap-4 px-1 text-sm text-slate-600">
        <div class="flex items-center gap-2"><div class="w-4 h-4 rounded bg-emerald-500"></div><span>Completed</span></div>
        <div class="flex items-center gap-2"><div class="w-4 h-4 rounded bg-red-400"></div><span>Overdue / Blocked</span></div>
        <div class="flex items-center gap-2"><div class="w-4 h-4 rounded border-2 border-slate-300 bg-white"></div><span>Not started</span></div>
        <div class="flex items-center gap-2"><i data-lucide="arrow-right" class="w-4 h-4 text-amber-500"></i><span>Blocks downstream</span></div>
    </div>

    <div class="space-y-5">
        <?php foreach($goals as $idx => $g): 
            $status_cls = match($g['status']) { 'On Track' => 'bg-emerald-100 text-emerald-700 border-emerald-200', 'At Risk' => 'bg-amber-100 text-amber-700 border-amber-200', default => 'bg-red-100 text-red-700 border-red-200' };
            $bar_col = match($g['status']) { 'On Track' => 'bg-emerald-500', 'At Risk' => 'bg-amber-500', default => 'bg-red-500' };
        ?>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden accordion-item">
            <button class="w-full text-left p-6 hover:bg-slate-50 transition-colors flex items-center justify-between accordion-trigger" onclick="this.closest('.accordion-item').querySelector('.accordion-body').classList.toggle('hidden'); this.querySelector('.chevron').classList.toggle('rotate-90');">
                <div class="flex items-center gap-4">
                    <div>
                        <div class="text-lg font-bold text-slate-900"><?php echo esc_html($g['goal']); ?></div>
                        <div class="text-sm text-slate-500 mt-0.5">Owner: <?php echo esc_html($g['owner']); ?> · Target: <?php echo esc_html($g['target_fmt']); ?></div> </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-right hidden md:block">
                        <div class="text-xs text-slate-500 mb-1"><?php echo $g['done_ms']; ?>/<?php echo $g['total_ms']; ?> milestones</div>
                        <div class="w-32 bg-slate-100 rounded-full h-2">
                            <div class="h-2 rounded-full <?php echo $bar_col; ?>" style="width: <?php echo $g['pct']; ?>%"></div>
                        </div>
                    </div>
                    <span class="px-3 py-1.5 rounded-full text-sm font-medium border <?php echo $status_cls; ?>"><?php echo esc_html($g['status']); ?></span>
                    <div class="text-right">
                        <div class="text-xl font-bold text-slate-900"><?php echo esc_html($g['days_left']); ?></div>
                        <div class="text-xs text-slate-500">days left</div>
                    </div>
                    <div class="w-7 h-7 rounded-full flex items-center justify-center bg-slate-100 transition-transform chevron">
                        <i data-lucide="chevron-right" class="w-4 h-4 text-slate-500"></i>
                    </div>
                </div>
            </button>

            <div class="accordion-body border-t border-slate-200 px-6 pb-6 pt-4 hidden">
                <div class="space-y-3">
                    <?php foreach($g['milestones'] as $m): 
                        $is_blocked = !empty($m['blocked_by']);
                        $is_blocker = !empty($m['blocks']);
                        $row_cls = 'border-slate-200 bg-slate-50';
                        $icon = 'circle'; 
                        $icon_col = 'text-slate-300';

                        if($m['done']) {
                            $row_cls = 'border-emerald-200 bg-emerald-50';
                            $icon = 'check-circle'; $icon_col = 'text-emerald-500';
                        } elseif ($m['overdue']) {
                            $row_cls = 'border-red-300 bg-red-50';
                            $icon = 'alert-circle'; $icon_col = 'text-red-500';
                        } elseif ($is_blocked) {
                            $row_cls = 'border-amber-200 bg-amber-50 opacity-75';
                            $icon = 'clock'; $icon_col = 'text-amber-500';
                        }
                    ?>
                    <div class="rounded-lg border-2 p-4 <?php echo $row_cls; ?>">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 mt-0.5">
                                <?php if($m['done']): ?><div class="w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center"><i data-lucide="check" class="w-4 h-4 text-white"></i></div>
                                <?php elseif($m['overdue']): ?><div class="w-6 h-6 rounded-full bg-red-500 flex items-center justify-center"><i data-lucide="alert-triangle" class="w-3 h-3 text-white"></i></div>
                                <?php else: ?><div class="w-6 h-6 rounded-full border-2 border-slate-300 bg-white"></div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <span class="font-medium text-sm text-slate-800 <?php echo $m['done']?'line-through text-slate-400':''; ?>"><?php echo esc_html($m['text']); ?></span>
                                        <?php if($m['overdue'] && !$m['done']): ?><span class="ml-2 px-2 py-0.5 text-xs font-bold bg-red-200 text-red-800 rounded">OVERDUE <?php echo esc_html($m['days_over'] ?? ''); ?>d</span><?php endif; ?>
                                        <?php if($is_blocked && !$m['done']): ?><span class="ml-2 px-2 py-0.5 text-xs font-bold bg-amber-200 text-amber-800 rounded">WAITING</span><?php endif; ?>
                                    </div>
                                    <div class="text-xs text-slate-500 whitespace-nowrap font-medium"><?php echo esc_html($m['assignee']); ?></div>
                                </div>
                                <?php if($is_blocked || $is_blocker): ?>
                                <div class="mt-2 flex flex-wrap gap-3 text-xs">
                                    <?php if($is_blocked): ?>
                                    <div class="flex items-center gap-1 text-amber-700"><i data-lucide="clock" class="w-3 h-3"></i><span>Waiting on: <span class="font-medium"><?php echo esc_html($m['blocked_by']); ?></span></span></div>
                                    <?php endif; ?>
                                    <?php if($is_blocker): ?>
                                    <div class="flex items-center gap-1 text-slate-600"><i data-lucide="arrow-right" class="w-3 h-3 text-amber-500"></i><span>Blocks: <span class="font-medium"><?php echo esc_html($m['blocks']); ?></span></span></div>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>