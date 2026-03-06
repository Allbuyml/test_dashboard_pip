<?php 
$in = " border border-slate-300 rounded px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all font-sans";
$sel = "w-full border border-slate-300 rounded px-3 py-2 text-sm text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all font-sans";
$lb = "block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1";
$grp = "border border-slate-200 rounded-lg p-5 bg-slate-50 mb-4 relative group repeater-row transition-all hover:border-slate-300";
$btn_del = '<button type="button" class="absolute top-2 right-2 p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-md transition-colors btn-del-row" title="Delete"><i data-lucide="trash-2" class="w-4 h-4"></i></button>';

$clients = get_terms(array('taxonomy' => 'client', 'hide_empty' => false));
?>

<div id="new-project-modal" class="hidden fixed inset-0 bg-black/60 z-[60] flex items-center justify-center p-4 backdrop-blur-sm font-sans">
    <div class="bg-white rounded-xl shadow-2xl max-w-sm w-full">
        <div class="p-5 border-b border-slate-100 flex justify-between items-center">
            <h3 class="font-bold text-lg text-slate-900">Create New Project</h3>
            <button id="close-new-project-modal" class="p-1.5 text-slate-400 hover:bg-slate-100 rounded-md transition-colors"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" class="p-5 space-y-4">
            <input type="hidden" name="action" value="dtt_create_project">
            <?php wp_nonce_field('dtt_create_action', 'dtt_nonce'); ?>
            
            <div><label class="block text-xs font-bold text-slate-500 uppercase mb-1">Project Name</label><input type="text" name="new_project_name" required class="w-full border border-slate-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="e.g. Website Redesign"></div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Client Selection</label>
                <select id="new_project_client_select" name="new_project_client_select" required class="w-full border border-slate-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                    <option value="" disabled selected>-- Select Client --</option>
                    <?php if(!is_wp_error($clients)): foreach($clients as $c): ?>
                        <option value="<?php echo esc_attr($c->slug); ?>"><?php echo esc_html($c->name); ?></option>
                    <?php endforeach; endif; ?>
                    <option value="new" class="font-bold text-blue-600">+ Add New Client...</option>
                </select>
            </div>
            <div id="new_client_name_wrapper" class="hidden mt-2 p-3 bg-blue-50 border border-blue-100 rounded"><label class="block text-xs font-bold text-blue-700 uppercase mb-1">New Client Full Name</label><input type="text" id="new_project_client_text" name="new_project_client_text" class="w-full border border-blue-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Type new client name..."></div>
            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2.5 rounded hover:bg-blue-700 transition-colors flex items-center justify-center gap-2 mt-2"><i data-lucide="plus-circle" class="w-4 h-4"></i> Create Project</button>
        </form>
    </div>
</div>

<div id="edit-modal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-xl shadow-2xl max-w-5xl w-full max-h-[95vh] overflow-y-auto flex flex-col font-sans">
        <div class="sticky top-0 bg-white border-b border-slate-100 p-5 flex justify-between items-center z-20 shadow-sm">
            <div><h3 class="font-bold text-xl text-slate-900">Edit Data</h3><p class="text-sm text-slate-500">Modifying: <?php echo esc_html($client_name); ?></p></div>
            <button id="close-edit-modal" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-full transition-colors"><i data-lucide="x" class="w-6 h-6"></i></button>
        </div>
        <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" enctype="multipart/form-data" class="p-6">
            <input type="hidden" name="action" value="dtt_update_project"><input type="hidden" name="project_id" value="<?php echo esc_attr($pid); ?>"><input type="hidden" name="edit_context" id="edit-context-input" value="all">
            <?php wp_nonce_field('dtt_update_action', 'dtt_nonce'); ?>

            <div id="form-card-header" class="form-group hidden space-y-5">
                <div class="grid grid-cols-3 gap-5">
                    <div><label class="<?php echo $lb; ?>">Client Name</label><input type="text" name="client_name" value="<?php echo esc_attr($client_name); ?>" class="<?php echo $in; ?>"></div>
                    <div><label class="<?php echo $lb; ?>">Status</label><select name="project_status" class="<?php echo $sel; ?>"><option <?php selected($project_status,'On Track'); ?>>On Track</option><option <?php selected($project_status,'At Risk'); ?>>At Risk</option><option <?php selected($project_status,'Blocked'); ?>>Blocked</option><option <?php selected($project_status,'Discovery'); ?>>Discovery</option></select></div>
                    <div><label class="<?php echo $lb; ?>">Last Updated</label><input type="date" name="last_updated" value="<?php echo esc_attr($updated_ymd); ?>" class="<?php echo $in; ?>"></div>
                </div>
            </div>

            <div id="form-card-completion" class="form-group hidden space-y-5">
                <h4 class="text-slate-800 font-bold text-sm uppercase border-b pb-2">Q1 Completion Rate</h4>
                <div class="grid grid-cols-5 gap-4">
                    <div><label class="<?php echo $lb; ?> text-emerald-600">Done</label><input type="number" name="comp_done" value="<?php echo esc_attr($completion['done']); ?>" class="<?php echo $in; ?>"></div>
                    <div><label class="<?php echo $lb; ?> text-blue-600">In Progress</label><input type="number" name="comp_in_progress" value="<?php echo esc_attr($completion['in_progress']); ?>" class="<?php echo $in; ?>"></div>
                    <div><label class="<?php echo $lb; ?> text-red-600">Blocked</label><input type="number" name="comp_blocked" value="<?php echo esc_attr($completion['blocked']); ?>" class="<?php echo $in; ?>"></div>
                    <div><label class="<?php echo $lb; ?> text-slate-400">Not Started</label><input type="number" name="comp_not_started" value="<?php echo esc_attr($completion['not_started']); ?>" class="<?php echo $in; ?>"></div>
                    <div>
                        <label class="<?php echo $lb; ?>">Primary Color</label>
                        <select name="comp_primary_color" class="<?php echo $sel; ?>">
                            <option value="#10b981" <?php selected($completion['primary_color'], '#10b981'); ?>>Green (Default)</option>
                            <option value="#3b82f6" <?php selected($completion['primary_color'], '#3b82f6'); ?>>Blue</option>
                            <option value="#8b5cf6" <?php selected($completion['primary_color'], '#8b5cf6'); ?>>Purple</option>
                        </select>
                    </div>
                </div>
            </div>

            <div id="form-card-metrics" class="form-group hidden space-y-5">
                <h4 class="text-slate-800 font-bold text-sm uppercase mb-3 border-b pb-2">Engagement Metrics</h4>
                <div class="grid grid-cols-3 gap-5">
                    <div><label class="<?php echo $lb; ?>">Start Date</label><input type="date" name="eng_start_date" value="<?php echo esc_attr($metrics['start_date']); ?>" class="<?php echo $in; ?>"></div>
                    <div><label class="<?php echo $lb; ?>">End Date</label><input type="date" name="eng_end_date" value="<?php echo esc_attr($metrics['end_date']); ?>" class="<?php echo $in; ?>"></div>
                    <div style="display:none;"><label class="<?php echo $lb; ?>">Pending Deliverables</label><input type="number" name="pending_deliverables" value="<?php echo esc_attr($metrics['pending']); ?>" class="<?php echo $in; ?>"></div>
                </div>
                <p class="text-xs text-slate-500 italic">"Days in Engagement" and "Days Remaining" are auto-calculated from these dates.</p>
            </div>

            <div id="form-card-progress" class="form-group hidden space-y-5">
                <div class="grid grid-cols-3 gap-5">
                    <div><label class="<?php echo $lb; ?>">Completed</label><input type="number" name="prog_completed" value="<?php echo esc_attr($metrics['completed']); ?>" class="<?php echo $in; ?>"></div>
                    <div><label class="<?php echo $lb; ?>">Working</label><input type="number" name="prog_working" value="<?php echo esc_attr($metrics['working']); ?>" class="<?php echo $in; ?>"></div>
                    <div><label class="<?php echo $lb; ?>">Upcoming</label><input type="number" name="prog_upcoming" value="<?php echo esc_attr($metrics['upcoming']); ?>" class="<?php echo $in; ?>"></div>
                </div>
            </div>

            <div id="form-card-weekly" class="form-group hidden space-y-5">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-slate-800 font-bold text-sm uppercase border-b pb-2 mb-3 text-emerald-600">This Week</h4>
                        <div id="list-this-week">
                            <?php if(!empty($this_week)): foreach($this_week as $i => $tw): ?>
                            <div class="flex gap-2 items-center relative pr-8 bg-slate-50 border border-slate-200 p-2 rounded repeater-row mb-2">
                                <input type="text" name="this_week[<?php echo $i; ?>][text]" value="<?php echo esc_attr($tw); ?>" class="<?php echo $in; ?> w-full">
                                <button type="button" class="absolute right-2 text-slate-400 hover:text-red-500 btn-del-row">✖</button>
                            </div>
                            <?php endforeach; endif; ?>
                        </div>
                        <button type="button" class="btn-add-row mt-2 py-1.5 px-3 bg-slate-100 text-slate-700 rounded text-xs font-bold flex items-center gap-1" data-target="list-this-week" data-template="tpl-this-week"><i data-lucide="plus" class="w-3 h-3"></i> Add Item</button>
                    </div>
                    <div>
                        <h4 class="text-slate-800 font-bold text-sm uppercase border-b pb-2 mb-3 text-blue-600">Next Week</h4>
                        <div id="list-next-week">
                            <?php if(!empty($next_week)): foreach($next_week as $i => $nw): ?>
                            <div class="flex gap-2 items-center relative pr-8 bg-slate-50 border border-slate-200 p-2 rounded repeater-row mb-2">
                                <input type="text" name="next_week[<?php echo $i; ?>][text]" value="<?php echo esc_attr($nw); ?>" class="<?php echo $in; ?> w-full">
                                <button type="button" class="absolute right-2 text-slate-400 hover:text-red-500 btn-del-row">✖</button>
                            </div>
                            <?php endforeach; endif; ?>
                        </div>
                        <button type="button" class="btn-add-row mt-2 py-1.5 px-3 bg-slate-100 text-slate-700 rounded text-xs font-bold flex items-center gap-1" data-target="list-next-week" data-template="tpl-next-week"><i data-lucide="plus" class="w-3 h-3"></i> Add Item</button>
                    </div>
                </div>
            </div>

            <div id="form-card-team" class="form-group hidden space-y-5">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-slate-800 font-bold text-sm uppercase border-b pb-2 mb-3">Client Team</h4>
                        <div id="list-team-client">
                            <?php if(!empty($team_client)): foreach($team_client as $i => $tc): ?>
                            <div class="<?php echo $grp; ?> pr-8 relative">
                                <?php echo $btn_del; ?>
                                <div class="space-y-2">
                                    <input type="text" name="team_client[<?php echo $i; ?>][name]" value="<?php echo esc_attr($tc['name']); ?>" class="<?php echo $in; ?> w-full" placeholder="Name">
                                    <input type="text" name="team_client[<?php echo $i; ?>][role]" value="<?php echo esc_attr($tc['role']); ?>" class="<?php echo $in; ?> w-full" placeholder="Role">
                                    <input type="text" name="team_client[<?php echo $i; ?>][email]" value="<?php echo esc_attr($tc['email']); ?>" class="<?php echo $in; ?> w-full" placeholder="Email">
                                    <input type="text" name="team_client[<?php echo $i; ?>][auth]" value="<?php echo esc_attr($tc['auth']); ?>" class="<?php echo $in; ?> w-full" placeholder="Authority / Focus">
                                </div>
                            </div>
                            <?php endforeach; endif; ?>
                        </div>
                        <button type="button" class="btn-add-row mt-2 py-1.5 px-3 bg-slate-100 text-slate-700 rounded text-xs font-bold flex items-center gap-1" data-target="list-team-client" data-template="tpl-team-client"><i data-lucide="plus" class="w-3 h-3"></i> Add Client Member</button>
                    </div>
                    <div>
                        <h4 class="text-slate-800 font-bold text-sm uppercase border-b pb-2 mb-3">PIP Team</h4>
                        <div id="list-team-pip">
                            <?php if(!empty($team_pip)): foreach($team_pip as $i => $tp): ?>
                            <div class="<?php echo $grp; ?> pr-8 relative">
                                <?php echo $btn_del; ?>
                                <div class="space-y-2">
                                    <input type="text" name="team_pip[<?php echo $i; ?>][name]" value="<?php echo esc_attr($tp['name']); ?>" class="<?php echo $in; ?> w-full" placeholder="Name">
                                    <input type="text" name="team_pip[<?php echo $i; ?>][role]" value="<?php echo esc_attr($tp['role']); ?>" class="<?php echo $in; ?> w-full" placeholder="Role">
                                    <input type="text" name="team_pip[<?php echo $i; ?>][slack]" value="<?php echo esc_attr($tp['slack']); ?>" class="<?php echo $in; ?> w-full" placeholder="Email / Slack">
                                    <div class="grid grid-cols-2 gap-2">
                                        <input type="number" name="team_pip[<?php echo $i; ?>][done]" value="<?php echo esc_attr($tp['done']); ?>" class="<?php echo $in; ?> w-full" placeholder="Tasks Done">
                                        <input type="number" name="team_pip[<?php echo $i; ?>][total]" value="<?php echo esc_attr($tp['total']); ?>" class="<?php echo $in; ?> w-full" placeholder="Total Tasks">
                                    </div>
                                    <input type="text" name="team_pip[<?php echo $i; ?>][resps]" value="<?php echo esc_attr($tp['resps']); ?>" class="<?php echo $in; ?> w-full" placeholder="Responsibilities (comma separated)">
                                </div>
                            </div>
                            <?php endforeach; endif; ?>
                        </div>
                        <button type="button" class="btn-add-row mt-2 py-1.5 px-3 bg-slate-100 text-slate-700 rounded text-xs font-bold flex items-center gap-1" data-target="list-team-pip" data-template="tpl-team-pip"><i data-lucide="plus" class="w-3 h-3"></i> Add PIP Member</button>
                    </div>
                </div>
            </div>

            <div id="form-card-actions" class="form-group hidden space-y-5">
                <h4 class="text-slate-800 font-bold text-sm uppercase border-b pb-2 mb-3">Action Items</h4>
                <div id="list-actions">
                    <?php if(!empty($actions)): foreach($actions as $i => $al): ?>
                    <div class="<?php echo $grp; ?> flex gap-3 pr-8 relative items-start">
                        <?php echo $btn_del; ?>
                        <div class="w-32">
                            <label class="<?php echo $lb; ?>">Owner Org</label>
                            <select name="actions_list[<?php echo $i; ?>][org]" class="<?php echo $sel; ?>">
                                <option value="PIP" <?php selected($al['org'], 'PIP'); ?>>PIP</option>
                                <option value="Infobase" <?php selected($al['org'], 'Infobase'); ?>>Client</option>
                                <option value="Both" <?php selected($al['org'], 'Both'); ?>>Both</option>
                            </select>
                        </div>
                        <div class="flex-1 space-y-2">
                            <div><label class="<?php echo $lb; ?>">Task</label><input type="text" name="actions_list[<?php echo $i; ?>][task]" value="<?php echo esc_attr($al['task']); ?>" class="<?php echo $in; ?> w-full"></div>
                            <div class="grid grid-cols-2 gap-3">
                                <div><label class="<?php echo $lb; ?>">Owner Name</label><input type="text" name="actions_list[<?php echo $i; ?>][owner]" value="<?php echo esc_attr($al['owner']); ?>" class="<?php echo $in; ?> w-full"></div>
                                <div><label class="<?php echo $lb; ?>">Deadline</label><input type="text" name="actions_list[<?php echo $i; ?>][deadline]" value="<?php echo esc_attr($al['deadline']); ?>" class="<?php echo $in; ?> w-full"></div>
                            </div>
                        </div>
                        <div class="w-24 flex items-center justify-center pt-6">
                            <label class="flex items-center gap-1.5 text-xs text-red-600 font-bold cursor-pointer">
                                <input type="checkbox" name="actions_list[<?php echo $i; ?>][overdue]" value="1" <?php checked(!empty($al['overdue'])); ?> class="w-4 h-4"> Overdue
                            </label>
                        </div>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
                <button type="button" class="btn-add-row mt-2 py-2 px-4 bg-slate-100 text-slate-700 rounded-lg text-sm font-bold flex items-center gap-2" data-target="list-actions" data-template="tpl-action"><i data-lucide="plus" class="w-4 h-4"></i> Add Action Item</button>
            </div>

            <div id="form-card-blockers" class="form-group hidden">
                <div id="list-blockers">
                    <?php if($blockers): foreach($blockers as $i => $b): ?>
                        <div class="<?php echo $grp; ?> border-l-4 border-l-red-400">
                            <?php echo $btn_del; ?>
                            <div class="grid grid-cols-12 gap-4 mb-3 pr-8">
                                <div class="col-span-2"><label class="<?php echo $lb; ?>">Severity</label><select name="blockers[<?php echo $i; ?>][sev]" class="<?php echo $sel; ?>"><option value="critical" <?php selected($b['sev'],'critical'); ?>>Critical</option><option value="high" <?php selected($b['sev'],'high'); ?>>High</option><option value="medium" <?php selected($b['sev'],'medium'); ?>>Medium</option></select></div>
                                <div class="col-span-4"><label class="<?php echo $lb; ?>">Title</label><input type="text" name="blockers[<?php echo $i; ?>][title]" value="<?php echo esc_attr($b['title']); ?>" class="<?php echo $in; ?>"></div>
                                <div class="col-span-2"><label class="<?php echo $lb; ?>">Owner</label><input type="text" name="blockers[<?php echo $i; ?>][owner]" value="<?php echo esc_attr($b['owner']); ?>" class="<?php echo $in; ?>"></div>
                                <div class="col-span-2"><label class="<?php echo $lb; ?>">Due Date</label><input type="date" name="blockers[<?php echo $i; ?>][due_date]" value="<?php echo esc_attr($b['due_date_ymd']); ?>" class="<?php echo $in; ?>"></div>
                                <div class="col-span-2"><label class="<?php echo $lb; ?>">Days Over</label><input type="number" name="blockers[<?php echo $i; ?>][days_over]" value="<?php echo esc_attr($b['days_over']); ?>" class="<?php echo $in; ?>" placeholder="Auto"></div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div><label class="<?php echo $lb; ?>">Impact</label><textarea name="blockers[<?php echo $i; ?>][impact]" rows="1" class="<?php echo $in; ?> w-full"><?php echo esc_textarea($b['impact']); ?></textarea></div>
                                <div><label class="<?php echo $lb; ?>">Next Step</label><input type="text" name="blockers[<?php echo $i; ?>][next]" value="<?php echo esc_attr($b['next']); ?>" class="<?php echo $in; ?> w-full"></div>
                            </div>
                            
                            <div class="mt-4 pt-4 border-t border-slate-200 grid grid-cols-2 gap-6">
                                <div>
                                    <label class="<?php echo $lb; ?> text-blue-500 mb-2">Resource Links</label>
                                    <div class="space-y-2">
                                        <?php if(!empty($b['links'])): foreach($b['links'] as $l_idx => $link): ?>
                                            <div class="flex gap-2 items-center relative pr-8 bg-white border border-slate-200 p-2 rounded repeater-row">
                                                <input type="text" name="blockers[<?php echo $i; ?>][links][<?php echo $l_idx; ?>][label]" value="<?php echo esc_attr($link['label']); ?>" class="w-full border border-slate-300 rounded px-2 py-1.5 text-xs focus:ring-blue-500 outline-none" placeholder="Label">
                                                <input type="text" name="blockers[<?php echo $i; ?>][links][<?php echo $l_idx; ?>][url]" value="<?php echo esc_attr($link['url']); ?>" class="w-full border border-slate-300 rounded px-2 py-1.5 text-xs focus:ring-blue-500 outline-none" placeholder="www.domain.com">
                                                <button type="button" class="absolute right-2 text-slate-400 hover:text-red-500 btn-del-row">✖</button>
                                            </div>
                                        <?php endforeach; endif; ?>
                                    </div>
                                    <button type="button" class="mt-2 text-xs font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1 btn-add-bk-link" data-bidx="<?php echo $i; ?>">+ Add Link</button>
                                </div>
                                <div>
                                    <label class="<?php echo $lb; ?> text-emerald-500 mb-2">Images & Evidence</label>
                                    <div class="space-y-2">
                                        <?php if(!empty($b['images'])): foreach($b['images'] as $img_idx => $img): ?>
                                            <div class="flex gap-2 items-start relative pr-8 bg-white border border-slate-200 p-2 rounded repeater-row">
                                                <input type="hidden" name="blockers[<?php echo $i; ?>][images][<?php echo $img_idx; ?>][id]" value="<?php echo esc_attr($img['id']); ?>">
                                                <img src="<?php echo esc_url($img['url']); ?>" class="w-10 h-10 object-cover rounded shadow-sm border border-slate-100 flex-shrink-0">
                                                <div class="flex-1 space-y-1">
                                                    <div class="text-[10px] text-emerald-600 font-bold uppercase pt-1">Stored Image</div>
                                                    <input type="text" name="blockers[<?php echo $i; ?>][images][<?php echo $img_idx; ?>][caption]" value="<?php echo esc_attr($img['caption']); ?>" class="w-full border border-slate-300 rounded px-2 py-1 text-xs focus:ring-blue-500 outline-none" placeholder="Caption">
                                                </div>
                                                <button type="button" class="absolute right-2 top-2 text-slate-400 hover:text-red-500 btn-del-row">✖</button>
                                            </div>
                                        <?php endforeach; endif; ?>
                                    </div>
                                    <button type="button" class="mt-2 text-xs font-bold text-emerald-600 hover:text-emerald-800 flex items-center gap-1 btn-add-bk-img" data-bidx="<?php echo $i; ?>">+ Batch Upload Images</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
                <button type="button" class="btn-add-row mt-2 py-2 px-4 bg-slate-100 text-slate-700 rounded-lg text-sm font-bold flex items-center gap-2" data-target="list-blockers" data-template="tpl-blocker"><i data-lucide="plus" class="w-4 h-4"></i> Add Blocker</button>
            </div>

            <div id="form-card-goals" class="form-group hidden"><div id="list-goals"><?php if($goals): foreach($goals as $i => $g): ?><div class="<?php echo $grp; ?> border-l-4 border-l-blue-500 goal-row"><?php echo $btn_del; ?><div class="grid grid-cols-12 gap-4 mb-4 pb-4 border-b border-slate-200 pr-8"><div class="col-span-4"><label class="<?php echo $lb; ?>">Goal Name</label><input type="text" name="goals[<?php echo $i; ?>][goal]" value="<?php echo esc_attr($g['goal']); ?>" class="<?php echo $in; ?> font-bold text-base w-full"></div><div class="col-span-2"><label class="<?php echo $lb; ?>">Status</label><select name="goals[<?php echo $i; ?>][status]" class="<?php echo $sel; ?>"><option <?php selected($g['status'],'On Track'); ?>>On Track</option><option <?php selected($g['status'],'At Risk'); ?>>At Risk</option><option <?php selected($g['status'],'Blocked'); ?>>Blocked</option></select></div><div class="col-span-2"><label class="<?php echo $lb; ?>">Owner</label><input type="text" name="goals[<?php echo $i; ?>][owner]" value="<?php echo esc_attr($g['owner']); ?>" class="<?php echo $in; ?> w-full"></div><div class="col-span-2"><label class="<?php echo $lb; ?>">Target</label><input type="date" name="goals[<?php echo $i; ?>][target]" value="<?php echo esc_attr($g['target_ymd']); ?>" class="<?php echo $in; ?> w-full"></div><div class="col-span-2"><label class="<?php echo $lb; ?>">Days Left</label><input type="number" name="goals[<?php echo $i; ?>][days_left]" value="<?php echo esc_attr($g['days_left']); ?>" class="<?php echo $in; ?> w-full" placeholder="Auto"></div></div><div class="bg-white p-4 rounded-lg border border-slate-200"><label class="<?php echo $lb; ?> text-blue-500">Milestones</label><div class="milestones-container space-y-2"><?php if(isset($g['milestones']) && is_array($g['milestones'])): foreach($g['milestones'] as $j => $m): $m_text = isset($m['text']) ? $m['text'] : ''; $m_ass = isset($m['assignee']) ? $m['assignee'] : ''; ?><div class="flex gap-3 items-center repeater-row bg-slate-50 p-2 rounded border border-slate-100 relative pr-10"><input type="text" name="goals[<?php echo $i; ?>][milestones][<?php echo $j; ?>][text]" value="<?php echo esc_attr($m_text); ?>" class="<?php echo $in; ?> flex-1" placeholder="Task Name"><input type="text" name="goals[<?php echo $i; ?>][milestones][<?php echo $j; ?>][assignee]" value="<?php echo esc_attr($m_ass); ?>" class="<?php echo $in; ?> w-32" placeholder="Assignee"><div class="flex items-center gap-4 px-3 bg-white border border-slate-200 rounded h-10"><label class="flex items-center gap-1.5 text-xs text-emerald-600 font-bold"><input type="checkbox" name="goals[<?php echo $i; ?>][milestones][<?php echo $j; ?>][done]" value="1" <?php checked(!empty($m['done'])); ?>> Done</label><label class="flex items-center gap-1.5 text-xs text-red-500 font-bold"><input type="checkbox" name="goals[<?php echo $i; ?>][milestones][<?php echo $j; ?>][overdue]" value="1" <?php checked(!empty($m['overdue'])); ?>> Late</label></div><button type="button" class="absolute right-2 text-slate-400 hover:text-red-500 btn-del-row"><i data-lucide="x" class="w-5 h-5"></i></button></div><?php endforeach; endif; ?></div><button type="button" class="mt-3 py-1.5 px-3 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded text-xs font-bold flex items-center gap-1 btn-add-nested transition-colors" data-parent-name="goals[<?php echo $i; ?>][milestones]" data-template="tpl-milestone"><i data-lucide="plus" class="w-3 h-3"></i> Add Milestone</button></div></div><?php endforeach; endif; ?></div><button type="button" class="btn-add-row mt-2 py-2 px-4 bg-slate-100 text-slate-700 rounded-lg text-sm font-bold flex items-center gap-2" data-target="list-goals" data-template="tpl-goal"><i data-lucide="plus" class="w-4 h-4"></i> Add Goal</button></div>
            <div id="form-card-achievements" class="form-group hidden"><div id="list-achievements"><?php if($achievements): foreach($achievements as $i => $a): ?><div class="<?php echo $grp; ?> flex gap-4 items-start pr-8 repeater-row"><?php echo $btn_del; ?><div class="w-16"><input type="text" name="achievements[<?php echo $i; ?>][icon]" value="<?php echo esc_attr($a['icon']); ?>" class="<?php echo $in; ?> text-center text-xl w-full"></div><div class="flex-1 space-y-2"><div><input type="text" name="achievements[<?php echo $i; ?>][title]" value="<?php echo esc_attr($a['title']); ?>" class="<?php echo $in; ?> font-bold text-sm w-full"></div><div><input type="text" name="achievements[<?php echo $i; ?>][desc]" value="<?php echo esc_attr($a['desc']); ?>" class="<?php echo $in; ?> w-full"></div></div><div class="w-40"><input type="date" name="achievements[<?php echo $i; ?>][date]" value="<?php echo esc_attr($a['date_ymd']); ?>" class="<?php echo $in; ?> w-full"></div></div><?php endforeach; endif; ?></div><button type="button" class="btn-add-row mt-2 py-2 px-4 bg-slate-100 text-slate-700 rounded-lg text-sm font-bold flex items-center gap-2" data-target="list-achievements" data-template="tpl-achievement"><i data-lucide="plus" class="w-4 h-4"></i> Add Achievement</button></div>
            <div id="form-card-gantt" class="form-group hidden"><div id="list-gantt"><?php if($gantt): foreach($gantt as $i => $g): ?><div class="<?php echo $grp; ?> grid grid-cols-12 gap-4 pr-8 items-end repeater-row"><?php echo $btn_del; ?><div class="col-span-4"><label class="<?php echo $lb; ?>">Name</label><input type="text" name="gantt_items[<?php echo $i; ?>][name]" value="<?php echo esc_attr($g['name']); ?>" class="<?php echo $in; ?> w-full"></div><div class="col-span-3"><label class="<?php echo $lb; ?>">Start Date</label><input type="date" name="gantt_items[<?php echo $i; ?>][start]" value="<?php echo esc_attr($g['start']); ?>" class="<?php echo $in; ?> w-full"></div><div class="col-span-3"><label class="<?php echo $lb; ?>">End Date</label><input type="date" name="gantt_items[<?php echo $i; ?>][end]" value="<?php echo esc_attr($g['end']); ?>" class="<?php echo $in; ?> w-full"></div><div class="col-span-2"><label class="<?php echo $lb; ?>">Status</label><select name="gantt_items[<?php echo $i; ?>][status]" class="<?php echo $sel; ?>"><option <?php selected($g['status'],'On Track'); ?>>On Track</option><option <?php selected($g['status'],'At Risk'); ?>>At Risk</option><option <?php selected($g['status'],'Blocked'); ?>>Blocked</option></select></div></div><?php endforeach; endif; ?></div><button type="button" class="btn-add-row mt-2 py-2 px-4 bg-slate-100 text-slate-700 rounded-lg text-sm font-bold flex items-center gap-2" data-target="list-gantt" data-template="tpl-gantt"><i data-lucide="plus" class="w-4 h-4"></i> Add Item</button></div>
            <div class="pt-5 border-t mt-6 sticky bottom-0 bg-white shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] pb-2 z-10">
                <button type="submit" class="w-full bg-slate-900 text-white font-bold py-3.5 rounded-lg hover:bg-slate-800 shadow-lg text-sm uppercase flex items-center justify-center gap-2"><i data-lucide="save" class="w-4 h-4"></i> Save Changes</button>
            </div>
        </form>
    </div>

    <template id="tpl-this-week"><div class="flex gap-2 items-center relative pr-8 bg-slate-50 border border-slate-200 p-2 rounded repeater-row mb-2"><input type="text" name="this_week[{i}][text]" class="<?php echo $in; ?> w-full"><button type="button" class="absolute right-2 text-slate-400 hover:text-red-500 btn-del-row">✖</button></div></template>
    <template id="tpl-next-week"><div class="flex gap-2 items-center relative pr-8 bg-slate-50 border border-slate-200 p-2 rounded repeater-row mb-2"><input type="text" name="next_week[{i}][text]" class="<?php echo $in; ?> w-full"><button type="button" class="absolute right-2 text-slate-400 hover:text-red-500 btn-del-row">✖</button></div></template>

    <template id="tpl-team-client"><div class="<?php echo $grp; ?> pr-8 relative"><?php echo $btn_del; ?><div class="space-y-2"><input type="text" name="team_client[{i}][name]" class="<?php echo $in; ?> w-full" placeholder="Name"><input type="text" name="team_client[{i}][role]" class="<?php echo $in; ?> w-full" placeholder="Role"><input type="text" name="team_client[{i}][email]" class="<?php echo $in; ?> w-full" placeholder="Email"><input type="text" name="team_client[{i}][auth]" class="<?php echo $in; ?> w-full" placeholder="Authority / Focus"></div></div></template>
    <template id="tpl-team-pip"><div class="<?php echo $grp; ?> pr-8 relative"><?php echo $btn_del; ?><div class="space-y-2"><input type="text" name="team_pip[{i}][name]" class="<?php echo $in; ?> w-full" placeholder="Name"><input type="text" name="team_pip[{i}][role]" class="<?php echo $in; ?> w-full" placeholder="Role"><input type="text" name="team_pip[{i}][slack]" class="<?php echo $in; ?> w-full" placeholder="Email / Slack"><div class="grid grid-cols-2 gap-2"><input type="number" name="team_pip[{i}][done]" class="<?php echo $in; ?> w-full" placeholder="Tasks Done"><input type="number" name="team_pip[{i}][total]" class="<?php echo $in; ?> w-full" placeholder="Total Tasks"></div><input type="text" name="team_pip[{i}][resps]" class="<?php echo $in; ?> w-full" placeholder="Responsibilities (comma separated)"></div></div></template>
    <template id="tpl-action"><div class="<?php echo $grp; ?> flex gap-3 pr-8 relative items-start"><?php echo $btn_del; ?><div class="w-32"><label class="<?php echo $lb; ?>">Owner Org</label><select name="actions_list[{i}][org]" class="<?php echo $sel; ?>"><option value="PIP">PIP</option><option value="Infobase">Client</option><option value="Both">Both</option></select></div><div class="flex-1 space-y-2"><div><label class="<?php echo $lb; ?>">Task</label><input type="text" name="actions_list[{i}][task]" class="<?php echo $in; ?> w-full"></div><div class="grid grid-cols-2 gap-3"><div><label class="<?php echo $lb; ?>">Owner Name</label><input type="text" name="actions_list[{i}][owner]" class="<?php echo $in; ?> w-full"></div><div><label class="<?php echo $lb; ?>">Deadline</label><input type="text" name="actions_list[{i}][deadline]" class="<?php echo $in; ?> w-full"></div></div></div><div class="w-24 flex items-center justify-center pt-6"><label class="flex items-center gap-1.5 text-xs text-red-600 font-bold cursor-pointer"><input type="checkbox" name="actions_list[{i}][overdue]" value="1" class="w-4 h-4"> Overdue</label></div></div></template>

    <template id="tpl-blocker">
        <div class="<?php echo $grp; ?> border-l-4 border-l-slate-300">
            <?php echo $btn_del; ?>
            <div class="grid grid-cols-12 gap-4 mb-3 pr-8"><div class="col-span-2"><select name="blockers[{i}][sev]" class="<?php echo $sel; ?>"><option value="medium">Medium</option><option value="high">High</option><option value="critical">Critical</option></select></div><div class="col-span-4"><input type="text" name="blockers[{i}][title]" class="<?php echo $in; ?> w-full"></div><div class="col-span-2"><input type="text" name="blockers[{i}][owner]" class="<?php echo $in; ?> w-full"></div><div class="col-span-2"><input type="date" name="blockers[{i}][due_date]" class="<?php echo $in; ?> w-full"></div><div class="col-span-2"><input type="number" name="blockers[{i}][days_over]" class="<?php echo $in; ?> w-full" placeholder="Auto"></div></div><div class="grid grid-cols-2 gap-4"><div><textarea name="blockers[{i}][impact]" rows="1" class="<?php echo $in; ?> w-full"></textarea></div><div><input type="text" name="blockers[{i}][next]" class="<?php echo $in; ?> w-full"></div></div>
            <div class="mt-4 pt-4 border-t border-slate-200 grid grid-cols-2 gap-6">
                <div><label class="<?php echo $lb; ?> text-blue-500 mb-2">Resource Links</label><div class="space-y-2"></div><button type="button" class="mt-2 text-xs font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1 btn-add-bk-link" data-bidx="{i}">+ Add Link</button></div>
                <div><label class="<?php echo $lb; ?> text-emerald-500 mb-2">Images & Evidence</label><div class="space-y-2"></div><button type="button" class="mt-2 text-xs font-bold text-emerald-600 hover:text-emerald-800 flex items-center gap-1 btn-add-bk-img" data-bidx="{i}">+ Batch Upload Images</button></div>
            </div>
        </div>
    </template>
    
    <template id="tpl-achievement"><div class="<?php echo $grp; ?> flex gap-4 items-start pr-8 repeater-row"><?php echo $btn_del; ?><div class="w-16"><input type="text" name="achievements[{i}][icon]" class="<?php echo $in; ?> w-full" value="✅"></div><div class="flex-1 space-y-2"><div><input type="text" name="achievements[{i}][title]" class="<?php echo $in; ?> w-full"></div><div><input type="text" name="achievements[{i}][desc]" class="<?php echo $in; ?> w-full"></div></div><div class="w-40"><input type="date" name="achievements[{i}][date]" class="<?php echo $in; ?> w-full"></div></div></template>
    <template id="tpl-gantt"><div class="<?php echo $grp; ?> grid grid-cols-12 gap-4 pr-8 items-end repeater-row"><?php echo $btn_del; ?><div class="col-span-4"><input type="text" name="gantt_items[{i}][name]" class="<?php echo $in; ?> w-full"></div><div class="col-span-3"><input type="date" name="gantt_items[{i}][start]" class="<?php echo $in; ?> w-full"></div><div class="col-span-3"><input type="date" name="gantt_items[{i}][end]" class="<?php echo $in; ?> w-full"></div><div class="col-span-2"><select name="gantt_items[{i}][status]" class="<?php echo $sel; ?>"><option>On Track</option><option>At Risk</option><option>Blocked</option></select></div></div></template>
    <template id="tpl-goal"><div class="<?php echo $grp; ?> border-l-4 border-l-blue-200 goal-row"><?php echo $btn_del; ?><div class="grid grid-cols-12 gap-4 mb-4 pb-4 border-b border-slate-200 pr-8"><div class="col-span-4"><input type="text" name="goals[{i}][goal]" class="<?php echo $in; ?> w-full"></div><div class="col-span-2"><select name="goals[{i}][status]" class="<?php echo $sel; ?>"><option>On Track</option><option>At Risk</option><option>Blocked</option></select></div><div class="col-span-2"><input type="text" name="goals[{i}][owner]" class="<?php echo $in; ?> w-full"></div><div class="col-span-2"><input type="date" name="goals[{i}][target]" class="<?php echo $in; ?> w-full"></div><div class="col-span-2"><input type="number" name="goals[{i}][days_left]" class="<?php echo $in; ?> w-full" placeholder="Auto"></div></div><div class="bg-white p-4 rounded-lg border border-slate-200"><div class="milestones-container space-y-2"></div><button type="button" class="mt-3 py-1.5 px-3 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded text-xs font-bold flex items-center gap-1 btn-add-nested transition-colors" data-parent-name="goals[{i}][milestones]" data-template="tpl-milestone"><i data-lucide="plus" class="w-3 h-3"></i> Add Milestone</button></div></div></template>
    <template id="tpl-milestone"><div class="flex gap-3 items-center repeater-row bg-slate-50 p-2 rounded border border-slate-100 relative pr-10"><input type="text" name="{parentName}[{j}][text]" class="<?php echo $in; ?> flex-1" placeholder="Task Name"><input type="text" name="{parentName}[{j}][assignee]" class="<?php echo $in; ?> w-32" placeholder="Assignee"><div class="flex items-center gap-4 px-3 bg-white border border-slate-200 rounded h-10"><label class="flex items-center gap-1.5 text-xs text-emerald-600 font-bold"><input type="checkbox" name="{parentName}[{j}][done]" value="1" class="w-4 h-4"> Done</label><label class="flex items-center gap-1.5 text-xs text-red-500 font-bold"><input type="checkbox" name="{parentName}[{j}][overdue]" value="1" class="w-4 h-4"> Late</label></div><button type="button" class="absolute right-2 text-slate-400 hover:text-red-500 btn-del-row"><i data-lucide="x" class="w-5 h-5"></i></button></div></template>
</div>

<script>
document.addEventListener('click', function(e) {
    const addLink = e.target.closest('.btn-add-bk-link');
    if (addLink) {
        e.preventDefault();
        const bidx = addLink.getAttribute('data-bidx');
        const j = Date.now();
        const container = addLink.previousElementSibling;
        const html = `
            <div class="flex gap-2 items-center relative pr-8 bg-white border border-slate-200 p-2 rounded repeater-row mt-2">
                <input type="text" name="blockers[${bidx}][links][${j}][label]" class="w-full border border-slate-300 rounded px-2 py-1.5 text-xs focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Label">
                <input type="text" name="blockers[${bidx}][links][${j}][url]" class="w-full border border-slate-300 rounded px-2 py-1.5 text-xs focus:ring-2 focus:ring-blue-500 outline-none" placeholder="www.domain.com">
                <button type="button" class="absolute right-2 text-slate-400 hover:text-red-500 btn-del-row">✖</button>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
    }

    const addImg = e.target.closest('.btn-add-bk-img');
    if (addImg) {
        e.preventDefault();
        const bidx = addImg.getAttribute('data-bidx');
        const container = addImg.previousElementSibling;

        const fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.multiple = true;
        fileInput.accept = 'image/*';
        
        fileInput.onchange = (ev) => {
            const files = ev.target.files;
            Array.from(files).forEach((file) => {
                const j = Date.now() + Math.floor(Math.random() * 1000); 
                const reader = new FileReader();
                
                reader.onload = (rev) => {
                    const html = `
                        <div class="flex gap-2 items-start relative pr-8 bg-white border border-slate-200 p-2 rounded repeater-row mt-2">
                            <img src="${rev.target.result}" class="w-10 h-10 object-cover rounded shadow-sm border border-slate-100 flex-shrink-0">
                            <div class="flex-1 space-y-1">
                                <input type="file" name="blocker_new_file_${bidx}_${j}" class="hidden dtt-hidden-file">
                                <input type="text" name="blockers[${bidx}][images][${j}][caption]" class="w-full border border-slate-300 rounded px-2 py-1 text-xs focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Caption (Optional)">
                            </div>
                            <button type="button" class="absolute right-2 top-2 text-slate-400 hover:text-red-500 btn-del-row">✖</button>
                        </div>
                    `;
                    container.insertAdjacentHTML('beforeend', html);
                    
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    const inputs = container.querySelectorAll('.dtt-hidden-file');
                    inputs[inputs.length - 1].files = dt.files;
                };
                reader.readAsDataURL(file);
            });
        };
        fileInput.click();
    }
});
</script>