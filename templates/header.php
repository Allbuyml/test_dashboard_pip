<header class="bg-white border-b border-slate-200 sticky top-0 z-20 font-sans">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex items-center justify-between py-4">
            
            <div class="flex items-center gap-4">
                <div class="h-10 px-3 min-w-[2.5rem] bg-slate-900 rounded-lg flex items-center justify-center">
                    <span class="text-white font-bold text-sm truncate max-w-[150px]"><?php echo esc_html($client_name); ?></span>
                </div>
                <div>
                    <div class="font-bold text-slate-900 text-base"><?php echo esc_html(get_the_title($pid)); ?></div>
                    <div class="text-sm text-slate-500">
                        Project Status Dashboard · Updated <?php echo esc_html($updated_fmt); ?>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <?php if($is_admin): ?>
                <div class="relative group flex items-center">
                    <button id="btn-client-selector" class="flex items-center gap-2 px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-sm font-medium transition-colors">
                        <i data-lucide="globe" class="w-4 h-4 text-slate-500"></i>
                        <span class="truncate max-w-[200px]"><?php echo esc_html(get_the_title($pid)); ?></span>
                        <i data-lucide="chevron-down" class="w-3 h-3 text-slate-400"></i>
                    </button>
                    <button id="btn-open-new-project" class="ml-2 p-1.5 border border-slate-200 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Create New Project">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                    </button>

                    <div id="dropdown-menu" class="hidden absolute top-10 right-8 mt-2 w-64 bg-white rounded-lg shadow-lg border border-slate-200 py-1 z-30">
                        <?php foreach($all_projects as $p): ?>
                            <a href="<?php echo get_permalink($p->ID); ?>" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 truncate">
                                <?php echo esc_html(get_the_title($p->ID)); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <button id="btn-open-edit-header" class="p-2 text-slate-400 hover:text-blue-600 transition-colors btn-edit-section" data-section="header" title="Edit General Info">
                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                </button>
                <button id="btn-open-share-modal" class="p-2 text-slate-400 hover:text-emerald-600 transition-colors" title="Share">
                    <i data-lucide="share-2" class="w-4 h-4"></i>
                </button>
                <?php endif; ?>

                <?php 
                    $st = get_field('project_status', $pid) ?: 'Discovery';
                    $badge = match($st) {
                        'On Track' => 'bg-emerald-100 text-emerald-700',
                        'At Risk' => 'bg-amber-100 text-amber-700',
                        'Blocked' => 'bg-red-100 text-red-700',
                        'Discovery' => 'bg-violet-100 text-violet-700',
                        default => 'bg-slate-100 text-slate-600'
                    };
                ?>
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-full <?php echo $badge; ?>">
                    <i data-lucide="alert-circle" class="w-4 h-4"></i>
                    <span class="text-sm font-medium"><?php echo esc_html($st); ?></span>
                </div>
            </div>
        </div>

        <div class="flex gap-1 overflow-x-auto pb-1">
            <button data-tab="executive" class="tab-btn active flex items-center gap-2 px-4 py-2.5 rounded-t-lg text-sm font-medium bg-slate-100 text-slate-900 border-t-2 border-emerald-500 transition-colors">
                <i data-lucide="target" class="w-4 h-4"></i> Executive Summary
            </button>
            <button data-tab="workstreams" class="tab-btn flex items-center gap-2 px-4 py-2.5 rounded-t-lg text-sm font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-50 transition-colors">
                <i data-lucide="bar-chart-2" class="w-4 h-4"></i> Workstreams & Goals
            </button>
            <button data-tab="team" class="tab-btn flex items-center gap-2 px-4 py-2.5 rounded-t-lg text-sm font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-50 transition-colors">
                <i data-lucide="users" class="w-4 h-4"></i> Team & Actions
            </button>
            <button data-tab="archive" class="tab-btn flex items-center gap-2 px-4 py-2.5 rounded-t-lg text-sm font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-50 transition-colors">
                <i data-lucide="file-text" class="w-4 h-4"></i> Project Archive
            </button>
        </div>
    </div>
</header>