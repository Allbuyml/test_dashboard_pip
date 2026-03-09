<?php 
$show_project_progress = false; 
$grid_class = $show_project_progress ? 'md:grid-cols-3' : 'md:grid-cols-2 lg:grid-cols-2 mx-auto';

// VARIABLE DE CONFIGURACIÓN GLOBAL:
$show_resolved = false; 
?>


<div class="space-y-6 font-sans relative">
    
    <div class="bg-gradient-to-br from-red-50 to-amber-50 rounded-xl shadow-md border-2 border-red-200 p-6 relative group">
        <?php if($is_admin): ?><button class="absolute top-4 right-4 text-blue-800 hover:text-blue-950 bg-white hover:bg-blue-50 p-1.5 rounded-md shadow-sm transition-colors btn-edit-card" data-card="blockers"><i data-lucide="edit-2" class="w-4 h-4"></i></button><?php endif; ?>
        
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-lg bg-red-100 flex items-center justify-center"><i data-lucide="alert-triangle" class="w-6 h-6 text-red-600"></i></div>
            <div>
                <h3 class="font-bold text-slate-900 text-lg">Items Needing Attention</h3>
                <p class="text-sm text-slate-600">Critical blockers requiring immediate action</p>
            </div>
            <div class="ml-auto flex items-center gap-3">

                <button id="btn-open-notify" class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-bold rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                    <i data-lucide="mail" class="w-4 h-4"></i> Notify Owner
                </button>

                <div id="blockers-count-badge" class="px-4 py-2 bg-red-600 text-white rounded-lg font-bold text-2xl"><?php echo count(array_filter($blockers, function($b) { return empty($b['resolved']); })); ?></div>
            </div>
        </div>

        <div class="space-y-3" id="blockers-list-container" data-show-resolved="<?php echo $show_resolved ? 'true' : 'false'; ?>">
            <?php foreach($blockers as $b_idx => $b): 
                $is_resolved = !empty($b['resolved']);
                
                // Aplicar lógica de Ocultar Resueltos en el front
                if (!$show_resolved && $is_resolved) continue;

                $style = $is_resolved ? ['border-emerald-300 opacity-75', 'bg-emerald-200 text-emerald-800'] : match($b['sev']) { 'critical'=>['border-red-300','bg-red-200 text-red-800'], 'high'=>['border-amber-300','bg-amber-200 text-amber-800'], default=>['border-blue-300','bg-blue-200 text-blue-800'] };
            ?>
            <div class="rounded-lg border-2 bg-white overflow-hidden p-0 <?php echo $style[0]; ?> blocker-card" data-idx="<?php echo $b_idx; ?>">
                
                <div class="p-4 pb-3">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1 severity-badge-container">
                                <span class="px-2 py-0.5 text-xs font-bold rounded uppercase <?php echo $style[1]; ?>"><?php echo esc_html($b['sev']); ?></span>
                                <span class="font-semibold text-slate-900"><?php echo esc_html($b['title']); ?></span>
                                <?php if($is_resolved): ?><span class="px-2 py-0.5 text-xs font-bold rounded bg-emerald-200 text-emerald-800">RESOLVED</span><?php endif; ?>
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

                                        <div class="flex items-center justify-between px-4 pb-4">
                    <button class="btn-toggle-comments flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:text-blue-800 transition-colors uppercase tracking-wide">
                        <i data-lucide="message-square" class="w-4 h-4"></i>
                        <?php echo count($b['comments']); ?> Comments <i data-lucide="chevron-right" class="w-3 h-3 transition-transform"></i>
                    </button>
                    <?php if($is_admin): ?>
                    <?php if(!$is_resolved): ?>
                    <button class="btn-resolve-blocker flex items-center gap-1.5 text-xs font-bold text-emerald-700 hover:text-emerald-900 bg-emerald-100 hover:bg-emerald-200 px-4 py-2 rounded-lg transition-colors shadow-sm">
                        <i data-lucide="check" class="w-4 h-4"></i> Mark Resolved
                    </button>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
                        
                        <div class="flex items-end gap-4 ml-4 flex-shrink-0">
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

                        <?php if(!empty($b['days_over']) && $b['days_over'] > 0 && !$is_resolved): ?>
                            <div class="text-right overdue-badge">
                                <div class="text-lg font-bold text-red-600 leading-none"><?php echo esc_html($b['days_over']); ?>d</div>
                                <div class="text-xs text-red-500 font-medium">overdue</div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3 text-sm bg-slate-50 p-3 rounded mt-2">
                        <div><span class="text-slate-500 font-medium">Owner: </span><span class="text-slate-700"><?php echo esc_html($b['owner']); ?></span></div>
                        <div><span class="text-slate-500 font-medium">Next: </span><span class="text-slate-700 dtt-linkify"><?php echo esc_html($b['next']); ?></span></div>
                    </div>
                </div>

                <div class="comments-panel hidden border-t border-slate-200 bg-slate-50 p-5">
                    <?php 
                        $total_comments = count($b['comments']);
                        $hidden_comments = [];
                        $visible_comments = [];
                        if($total_comments > 3) {
                            $hidden_comments = array_slice($b['comments'], 0, $total_comments - 3, true); // true = preserva el index
                            $visible_comments = array_slice($b['comments'], -3, null, true);
                        } else {
                            $visible_comments = array_slice($b['comments'], 0, null, true);
                        }
                    ?>

                    <?php if($total_comments > 3): ?>
                        <div class="flex justify-center mb-5">
                            <button class="btn-show-more-comments flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-blue-600 transition-colors bg-white border border-slate-200 px-3 py-1.5 rounded-full shadow-sm">
                                <i data-lucide="chevron-up" class="w-3 h-3"></i> View <?php echo count($hidden_comments); ?> previous comments
                            </button>
                        </div>
                        <div class="hidden-comments hidden space-y-4 mb-4">
                            <?php foreach($hidden_comments as $c_idx => $c): ?>
                                <div class="comment-item flex gap-3 opacity-75 hover:opacity-100 transition-opacity relative group/item" data-bidx="<?php echo $b_idx; ?>" data-cidx="<?php echo $c_idx; ?>">
                                    <div class="w-8 h-8 rounded-full flex-shrink-0 flex items-center justify-center text-xs font-bold shadow-sm <?php echo ($c['org']=='PIP') ? 'bg-slate-800 text-white' : 'bg-amber-100 text-amber-800'; ?>">
                                        <?php echo esc_html(substr(str_replace(' ', '', $c['author']), 0, 2)); ?>
                                    </div>
                                    <div class="flex-1 bg-white rounded-lg border border-slate-200 px-4 py-3 shadow-sm">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="text-sm font-bold text-slate-800"><?php echo esc_html($c['author']); ?></span>
                                            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded <?php echo ($c['org']=='PIP') ? 'bg-slate-100 text-slate-600' : 'bg-amber-100 text-amber-700'; ?>"><?php echo esc_html($c['org']); ?></span>
                                            <div class="ml-auto flex items-center gap-2">
                                                <span class="text-xs text-slate-400 font-medium"><?php echo esc_html($c['date']); ?></span>
                                                <div class="relative comment-menu-container">
                                                    <button class="btn-toggle-comment-menu p-1 text-slate-400 hover:text-blue-600 rounded bg-slate-50 hover:bg-blue-50 transition-colors opacity-0 group-hover/item:opacity-100"><i data-lucide="more-vertical" class="w-3.5 h-3.5"></i></button>
                                                    <div class="comment-menu-dropdown hidden absolute right-0 top-full mt-1 w-24 bg-white border border-slate-200 rounded-md shadow-lg z-10 overflow-hidden">
                                                        <button class="w-full text-left px-3 py-2 text-xs font-medium hover:bg-slate-50 text-slate-700 btn-edit-comment">Edit</button>
                                                        <button class="w-full text-left px-3 py-2 text-xs font-medium hover:bg-red-50 text-red-600 btn-del-comment">Delete</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="comment-content text-sm text-slate-700 dtt-linkify prose prose-sm max-w-none leading-relaxed"><?php echo wp_kses_post($c['text']); ?></div>
                                        <div class="comment-edit-form hidden mt-2">
                                            <textarea rows="3" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none resize-none"><?php 
                                                echo esc_textarea(str_replace(array("<br />\n", "<br>\n", "<br/>\n", "<br />", "<br>", "<br/>"), "\n", $c['text'])); 
                                            ?></textarea>
                                            <div class="flex gap-2 mt-2 justify-end">
                                                <button class="btn-cancel-edit-comment px-3 py-1.5 text-xs font-bold text-slate-500 hover:bg-slate-100 rounded transition-colors">Cancel</button>
                                                <button class="btn-save-edit-comment px-3 py-1.5 text-xs font-bold bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors shadow-sm">Save</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if(!empty($visible_comments)): ?>
                        <div class="space-y-4">
                        <?php foreach($visible_comments as $c_idx => $c): ?>
                            <div class="comment-item flex gap-3 relative group/item" data-bidx="<?php echo $b_idx; ?>" data-cidx="<?php echo $c_idx; ?>">
                                <div class="w-8 h-8 rounded-full flex-shrink-0 flex items-center justify-center text-xs font-bold shadow-sm <?php echo ($c['org']=='PIP') ? 'bg-slate-800 text-white' : 'bg-amber-100 text-amber-800'; ?>">
                                    <?php echo esc_html(substr(str_replace(' ', '', $c['author']), 0, 2)); ?>
                                </div>
                                <div class="flex-1 bg-white rounded-lg border border-slate-200 px-4 py-3 shadow-sm">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-sm font-bold text-slate-800"><?php echo esc_html($c['author']); ?></span>
                                        <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded <?php echo ($c['org']=='PIP') ? 'bg-slate-100 text-slate-600' : 'bg-amber-100 text-amber-700'; ?>"><?php echo esc_html($c['org']); ?></span>
                                        <div class="ml-auto flex items-center gap-2">
                                            <span class="text-xs text-slate-400 font-medium"><?php echo esc_html($c['date']); ?></span>
                                            <div class="relative comment-menu-container">
                                                <button class="btn-toggle-comment-menu p-1 text-slate-400 hover:text-blue-600 rounded bg-slate-50 hover:bg-blue-50 transition-colors opacity-0 group-hover/item:opacity-100"><i data-lucide="more-vertical" class="w-3.5 h-3.5"></i></button>
                                                <div class="comment-menu-dropdown hidden absolute right-0 top-full mt-1 w-24 bg-white border border-slate-200 rounded-md shadow-lg z-10 overflow-hidden">
                                                    <button class="w-full text-left px-3 py-2 text-xs font-medium hover:bg-slate-50 text-slate-700 btn-edit-comment">Edit</button>
                                                    <button class="w-full text-left px-3 py-2 text-xs font-medium hover:bg-red-50 text-red-600 btn-del-comment">Delete</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="comment-content text-sm text-slate-700 dtt-linkify prose prose-sm max-w-none leading-relaxed"><?php echo wp_kses_post($c['text']); ?></div>
                                    <div class="comment-edit-form hidden mt-2">
                                        <textarea rows="3" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none resize-none"><?php 
                                            echo esc_textarea(str_replace(array("<br />\n", "<br>\n", "<br/>\n", "<br />", "<br>", "<br/>"), "\n", $c['text'])); 
                                        ?></textarea>
                                        <div class="flex gap-2 mt-2 justify-end">
                                            <button class="btn-cancel-edit-comment px-3 py-1.5 text-xs font-bold text-slate-500 hover:bg-slate-100 rounded transition-colors">Cancel</button>
                                            <button class="btn-save-edit-comment px-3 py-1.5 text-xs font-bold bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors shadow-sm">Save</button>
                                        </div>
                                    </div>
                                    
                                    <?php if(!empty($c['images'])): ?>
                                    <div class="flex gap-2 mt-3 pt-3 border-t border-slate-100 overflow-x-auto pb-1">
                                        <?php foreach($c['images'] as $img_idx => $img): ?>
                                        <img src="<?php echo esc_url($img['url']); ?>" class="w-16 h-16 object-cover rounded-md border border-slate-200 cursor-pointer img-lightbox-trigger hover:opacity-80 transition-opacity" data-images='<?php echo htmlspecialchars(json_encode($c['images'])); ?>' data-index="<?php echo $img_idx; ?>">
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="comment-form-wrapper mt-4">
                        <?php if(!$is_resolved): ?>
                        <form class="frm-add-comment bg-white p-4 rounded-lg border border-slate-200 shadow-sm relative">
                            <input type="hidden" name="blocker_idx" value="<?php echo $b_idx; ?>">
                            
                            <select name="author" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-700 bg-slate-50 focus:ring-2 focus:ring-blue-500 outline-none mb-3" required>
                                <option value="">Select your name to comment...</option>
                                <optgroup label="PIP Team">
                                    <?php if(!empty($team_pip)) foreach($team_pip as $m): ?><option value="<?php echo esc_attr($m['name']); ?>" data-org="PIP"><?php echo esc_html($m['name'] . ' - ' . $m['role']); ?></option><?php endforeach; ?>
                                </optgroup>
                                <optgroup label="Client Team">
                                    <?php if(!empty($team_client)) foreach($team_client as $m): ?><option value="<?php echo esc_attr($m['name']); ?>" data-org="Client"><?php echo esc_html($m['name'] . ' - ' . $m['role']); ?></option><?php endforeach; ?>
                                </optgroup>
                            </select>
                            
                            <textarea name="text" rows="3" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-700 bg-slate-50 focus:ring-2 focus:ring-blue-500 outline-none resize-none mb-2" placeholder="Add a comment... HTML & Links supported." required></textarea>
                            
                            <div class="upload-progress-container hidden mb-3 px-1">
                                <div class="flex justify-between text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">
                                    <span>Uploading attachments...</span>
                                    <span class="upload-progress-text text-blue-600">0%</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="upload-progress-bar bg-blue-500 h-1.5 rounded-full transition-all duration-200" style="width: 0%"></div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <label class="flex items-center gap-2 cursor-pointer text-sm text-slate-500 hover:text-blue-600 transition-colors bg-slate-50 px-3 py-2 rounded-lg border border-slate-200">
                                    <i data-lucide="image" class="w-4 h-4"></i>
                                    <span class="font-medium">Attach Images</span>
                                    <input type="file" name="comment_images[]" multiple accept="image/*" class="hidden file-input-display">
                                </label>
                                <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white text-sm font-bold rounded-lg hover:bg-blue-700 transition-colors shadow-md btn-submit-comment flex items-center gap-2">
                                    Post Comment
                                </button>
                            </div>
                            <div class="text-xs text-slate-400 file-name-display empty:hidden mt-2 px-1 font-medium"></div>
                        </form>
                        <?php else: ?>
                        <div class="text-xs text-slate-400 text-center py-2 bg-slate-100 rounded-lg border border-slate-200">This blocker has been resolved. Comments are closed.</div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
            <?php endforeach; ?>
        </div>
    </div>

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

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 relative group">
        <?php if($is_admin): ?><button class="absolute top-3 right-3 text-blue-800 hover:text-blue-950 bg-blue-50 hover:bg-blue-100 p-1.5 rounded-md transition-colors btn-edit-card z-10" data-card="weekly" title="Edit Weekly Focus"><i data-lucide="edit-2" class="w-3.5 h-3.5"></i></button><?php endif; ?>
        
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 flex-shrink-0"></div>
                <h3 class="font-bold text-slate-900">This Week</h3>
                <span class="ml-auto text-xs font-medium text-slate-400"><?php echo esc_html($this_week_dates); ?></span>
            </div>
            <div class="space-y-2">
                <?php if(!empty($this_week)): foreach($this_week as $tw): ?>
                <div class="flex items-start gap-2.5 text-sm text-slate-700">
                    <i data-lucide="check" class="w-4 h-4 text-emerald-500 flex-shrink-0 mt-0.5"></i>
                    <span><?php echo esc_html($tw); ?></span>
                </div>
                <?php endforeach; else: ?>
                <div class="text-sm text-slate-400 italic">No priorities set for this week.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-2.5 h-2.5 rounded-full bg-blue-500 flex-shrink-0"></div>
                <h3 class="font-bold text-slate-900">Next Week</h3>
                <span class="ml-auto text-xs font-medium text-slate-400"><?php echo esc_html($next_week_dates); ?></span>
            </div>
            <div class="space-y-2">
                <?php if(!empty($next_week)): foreach($next_week as $nw): ?>
                <div class="flex items-start gap-2.5 text-sm text-slate-700">
                    <i data-lucide="arrow-right" class="w-4 h-4 text-blue-400 flex-shrink-0 mt-0.5"></i>
                    <span><?php echo esc_html($nw); ?></span>
                </div>
                <?php endforeach; else: ?>
                <div class="text-sm text-slate-400 italic">No priorities set for next week.</div>
                <?php endif; ?>
            </div>
        </div>
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

                    
                    <?php var_dump($timeline_dates); if($timeline_dates['show_today']): ?>
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
                            <div class="absolute h-6 rounded shadow-sm bg-blue-50 overflow-hidden flex items-center px-2 text-white transition-all duration-500 ease-out" style="left: <?php echo esc_attr($g['left_pct']); ?>%; width: <?php echo esc_attr($g['width_pct']); ?>%;">
                                <div class="absolute inset-0 bg-blue-500 opacity-90"></div>
                                <span class="relative z-10 text-[10px] font-medium truncate w-full text-left"><?php echo esc_html($g['date_str']); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

            </div>
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

<div id="delete-comment-modal" class="hidden fixed inset-0 bg-slate-900/80 z-[99999] flex items-center justify-center p-4 backdrop-blur-sm transition-opacity font-sans">
   <div class="bg-white rounded-xl shadow-2xl max-w-sm w-full p-6 text-center border border-slate-200">
       <div class="w-14 h-14 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-red-100"><i data-lucide="trash-2" class="w-6 h-6 text-red-600"></i></div>
       <h3 class="text-lg font-bold text-slate-900 mb-2">Delete Comment?</h3>
       <p class="text-sm text-slate-500 mb-6">This action cannot be undone. Any attached images will also be permanently deleted.</p>
       <div class="flex items-center justify-center gap-3">
           <button class="btn-close-del-comment px-5 py-2.5 text-sm text-slate-600 font-bold hover:bg-slate-100 rounded-lg transition-colors">Cancel</button>
           <button id="btn-confirm-del-comment" class="px-5 py-2.5 text-sm bg-red-600 text-white font-bold rounded-lg hover:bg-red-700 transition-colors shadow-md flex items-center gap-2">Yes, Delete</button>
       </div>
   </div>
</div>

<div id="notify-client-modal" class="hidden fixed inset-0 bg-slate-900/80 z-[99999] flex items-center justify-center p-4 backdrop-blur-sm transition-opacity font-sans">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden transform transition-all scale-100 border border-slate-200">
        
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-white">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-50 text-blue-600 rounded-lg"><i data-lucide="mail" class="w-5 h-5"></i></div>
                <div><h3 class="font-bold text-lg text-slate-900 leading-tight">Notify Client</h3><p class="text-xs text-slate-500">Send an update via email</p></div>
            </div>
            <button class="btn-close-notify text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors p-2 rounded-full"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>

        <div class="px-6 py-5 space-y-5 max-h-[70vh] overflow-y-auto">
            <p class="text-sm text-slate-600">Send a direct email summarizing the items that need attention to keep the project moving.</p>
            
            <div class="border border-slate-200 rounded-lg bg-slate-50 p-3 max-h-56 overflow-y-auto custom-scrollbar">
                <div class="flex items-center justify-between mb-3 pb-3 border-b border-slate-200">
                    <span class="text-xs font-bold text-slate-600 uppercase">Directory</span>
                    <label class="flex items-center gap-1.5 text-xs text-blue-600 font-bold cursor-pointer hover:text-blue-800 transition-colors">
                        <input type="checkbox" class="global-select-all w-3.5 h-3.5 text-blue-600 rounded cursor-pointer"> Select All Teams
                    </label>
                </div>
                
                <div class="space-y-4" id="notify-checkbox-list">
                    <?php 
                    $has_default_client = false;
                    if(!empty($team_client)): ?>
                    <div class="notify-group">
                        <div class="flex items-center justify-between mb-2">
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Client Team</div>
                            <label class="flex items-center gap-1 text-[10px] font-bold text-slate-500 cursor-pointer hover:text-blue-600 transition-colors">
                                <input type="checkbox" class="group-select-all w-3 h-3 text-blue-600 rounded cursor-pointer"> Select Group
                            </label>
                        </div>
                        <div class="space-y-1.5 pl-1 border-l-2 border-slate-200 ml-1">
                            <?php foreach($team_client as $m): if(!empty($m['email'])): 
                                $is_checked = false;
                                if (!$has_default_client) {
                                    $is_checked = true;
                                    $has_default_client = true;
                                }
                            ?>
                            <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer hover:bg-slate-100 p-1.5 -ml-1.5 rounded transition-colors ml-1">
                                <input type="checkbox" name="notify_to[]" value="<?php echo esc_attr($m['email']); ?>" class="notify-cb w-4 h-4 text-blue-600 rounded focus:ring-blue-500 cursor-pointer" <?php checked($is_checked); ?>>
                                <span><span class="font-medium"><?php echo esc_html($m['name']); ?></span> <span class="text-slate-400 text-xs">(<?php echo esc_html($m['email']); ?>)</span></span>
                            </label>
                            <?php endif; endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if(!empty($team_pip)): ?>
                    <div class="notify-group">
                        <div class="flex items-center justify-between mb-2 pt-2 border-t border-slate-200">
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">PIP Team</div>
                            <label class="flex items-center gap-1 text-[10px] font-bold text-slate-500 cursor-pointer hover:text-blue-600 transition-colors">
                                <input type="checkbox" class="group-select-all w-3 h-3 text-blue-600 rounded cursor-pointer"> Select Group
                            </label>
                        </div>
                        <div class="space-y-1.5 pl-1 border-l-2 border-slate-200 ml-1">
                            <?php foreach($team_pip as $m): $em = $m['slack'] ?: $m['email']; if(!empty($em)): ?>
                            <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer hover:bg-slate-100 p-1.5 -ml-1.5 rounded transition-colors ml-1">
                                <input type="checkbox" name="notify_to[]" value="<?php echo esc_attr($em); ?>" class="notify-cb w-4 h-4 text-blue-600 rounded focus:ring-blue-500 cursor-pointer">
                                <span><span class="font-medium"><?php echo esc_html($m['name']); ?></span> <span class="text-slate-400 text-xs">(<?php echo esc_html($em); ?>)</span></span>
                            </label>
                            <?php endif; endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">Custom Emails (Comma separated)</label>
                <input type="text" id="notify-custom-emails" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none mb-2" placeholder="e.g. manager@domain.com, ceo@domain.com">
                <div class="flex items-center gap-5">
                    <label class="flex items-center gap-1.5 text-sm text-slate-700 cursor-pointer">
                        <input type="radio" name="notify-mode" value="merge" checked class="w-4 h-4 text-blue-600"> Add to selection
                    </label>
                    <label class="flex items-center gap-1.5 text-sm text-slate-700 cursor-pointer">
                        <input type="radio" name="notify-mode" value="custom_only" class="w-4 h-4 text-blue-600"> Send ONLY to custom
                    </label>
                </div>
            </div>
            
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">Subject</label>
                <input type="text" id="notify-subject" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none" value="Project Update: Items Needing Attention">
            </div>
            
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">Message Body</label>
                <textarea id="notify-message" rows="12" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none resize-none">Hi team,&#10;&#10;Here is a quick summary of the items that currently need your attention to keep our timeline on track:&#10;<?php 
                    $b_count = 1;
                    foreach($blockers as $b) { 
                        if(empty($b['resolved'])) {
                            echo '&#10;' . $b_count . '. ' . esc_textarea($b['title']) . ' — ' . esc_textarea($b['next']);
                            $b_count++;
                        }
                    } 
                ?>&#10;&#10;Please let us know if you have any questions or need assistance.&#10;&#10;-- DASHBOARD ACCESS --&#10;Project Link: <?php echo esc_url($share_url ?? get_permalink($pid)); ?>&#10;Password: <?php echo esc_html($share_pass ?? ''); ?>&#10;----------------------&#10;&#10;Best regards,&#10;The PIP Team</textarea>
            </div>
            
        </div>

        <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-slate-50">
            <button class="btn-close-notify px-5 py-2.5 text-sm text-slate-600 font-bold hover:bg-slate-200 rounded-lg transition-colors">Cancel</button>
            <button id="btn-send-notify" class="px-5 py-2.5 bg-blue-600 text-white text-sm font-bold rounded-lg hover:bg-blue-700 transition-colors shadow-md flex items-center gap-2">
                <i data-lucide="send" class="w-4 h-4"></i> Send Email
            </button>
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
window.dtt_ajax_url = "<?php echo esc_url(admin_url('admin-ajax.php')); ?>";
window.dtt_pid = <?php echo intval($pid); ?>;
</script>