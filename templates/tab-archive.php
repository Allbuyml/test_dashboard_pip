<div class="space-y-6 font-sans">
    <div class="bg-gradient-to-br from-slate-600 to-slate-700 rounded-xl p-8 text-white relative">
        <?php if($is_admin): ?><button class="absolute top-4 right-4 bg-white/20 p-2 rounded hover:bg-white/30 btn-edit-section" data-section="decisions"><i data-lucide="edit-2" class="w-4 h-4 text-white"></i></button><?php endif; ?>
        <h2 class="text-2xl font-bold mb-1">Project Archive</h2>
        <p class="text-slate-100">Key decisions, meeting summaries, and project history</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <h3 class="font-bold text-slate-900 mb-4">Decision Log</h3>
        <div class="space-y-3">
            <?php foreach($decisions as $d): $col = ($d['color']=='emerald')?'border-emerald-500 bg-emerald-50':'border-blue-500 bg-blue-50'; ?>
            <div class="border-l-4 <?php echo $col; ?> p-4 rounded-r-lg">
                <div class="flex items-center justify-between mb-2"><span class="font-semibold text-slate-900"><?php echo esc_html($d['title']); ?></span><span class="text-sm text-slate-500"><?php echo esc_html($d['date']); ?></span></div>
                <div class="text-sm text-slate-700 mb-1"><?php echo esc_html($d['body']); ?></div>
                <div class="text-xs text-slate-500">Decided by: <?php echo esc_html($d['by']); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 relative">
        <?php if($is_admin): ?><button class="absolute top-3 right-3 text-slate-300 hover:text-blue-500 btn-edit-section" data-section="meetings"><i data-lucide="edit-2" class="w-3 h-3"></i></button><?php endif; ?>
        <h3 class="font-bold text-slate-900 mb-4">Meeting Timeline</h3>
        <?php foreach($meetings as $m): ?>
        <div class="flex gap-4">
            <div class="flex flex-col items-center"><div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center"><i data-lucide="message-square" class="w-5 h-5 text-blue-600"></i></div><div class="w-0.5 flex-1 bg-slate-200 mt-2"></div></div>
            <div class="flex-1 pb-6">
                <div class="font-semibold text-slate-900"><?php echo esc_html($m['title']); ?></div>
                <div class="text-sm text-slate-500 mb-2"><?php echo esc_html($m['date']); ?> · <?php echo esc_html($m['duration']); ?></div>
                <div class="text-sm text-slate-700 mb-2"><?php echo esc_html($m['desc']); ?></div>
                <div class="text-xs text-slate-500"><?php echo esc_html($m['outcomes']); ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 relative">
            <?php if($is_admin): ?><button class="absolute top-3 right-3 text-slate-300 hover:text-blue-500 btn-edit-section" data-section="docs"><i data-lucide="edit-2" class="w-3 h-3"></i></button><?php endif; ?>
            <h3 class="font-bold text-slate-900 mb-4">Document References</h3>
            <div class="space-y-2"><?php foreach($docs as $doc): ?><div class="flex items-center gap-3 p-3 bg-slate-50 rounded-lg hover:bg-slate-100 cursor-pointer border border-slate-200"><i data-lucide="file-text" class="w-5 h-5 text-slate-400"></i><div><div class="font-medium text-slate-900 text-sm"><?php echo esc_html($doc['title']); ?></div><div class="text-xs text-slate-500"><?php echo esc_html($doc['desc']); ?></div></div></div><?php endforeach; ?></div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 relative">
            <?php if($is_admin): ?><button class="absolute top-3 right-3 text-slate-300 hover:text-blue-500 btn-edit-section" data-section="lessons"><i data-lucide="edit-2" class="w-3 h-3"></i></button><?php endif; ?>
            <h3 class="font-bold text-slate-900 mb-4">Lessons Learned</h3>
            <div class="space-y-3 text-sm text-slate-600">
                <?php foreach($lessons as $l): $col = $l['color']=='amber'?'bg-amber-50 border-amber-100':'bg-blue-50 border-blue-100'; ?>
                <div class="flex gap-3 p-3 border rounded-lg <?php echo $col; ?>"><span class="text-lg">📌</span><div><span class="font-medium text-slate-800"><?php echo esc_html($l['label']); ?>:</span> <?php echo esc_html($l['text']); ?></div></div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>