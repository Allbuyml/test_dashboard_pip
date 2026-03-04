<?php if($is_admin): ?>
<div id="share-modal" class="hidden fixed inset-0 bg-black/60 z-[100] flex items-center justify-center p-4 backdrop-blur-sm transition-opacity font-sans">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden transform transition-all scale-100">
        
        <div class="p-6 bg-slate-900 text-white flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-white/10 rounded-lg">
                    <i data-lucide="shield-check" class="w-5 h-5 text-emerald-400"></i>
                </div>
                <div>
                    <h3 class="font-bold text-lg leading-tight">Secure Sharing</h3>
                    <p class="text-xs text-slate-400">Client-wide access credentials</p>
                </div>
            </div>
            <button id="close-share-modal" class="text-slate-400 hover:text-white hover:bg-white/10 p-2 rounded-full transition-all">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <div class="p-6 space-y-6">
            
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Project Link</label>
                <div class="flex shadow-sm rounded-lg overflow-hidden group">
                    <input type="text" id="share-link-input" readonly 
                           class="w-full bg-slate-50 border border-slate-200 border-r-0 rounded-l-lg p-3 text-sm text-slate-700 font-medium select-all focus:outline-none focus:bg-white transition-colors" 
                           value="<?php echo esc_url($share_url); ?>">
                    <button class="btn-copy bg-white border border-l-0 border-slate-200 px-4 text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition-all font-medium flex items-center gap-2 rounded-r-lg" 
                            data-target="share-link-input">
                        <span class="btn-icon"><i data-lucide="link" class="w-4 h-4"></i></span>
                        <span class="text-xs">Copy</span>
                    </button>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Client Master Password</label>
                <div class="flex shadow-sm rounded-lg overflow-hidden group">
                    <input type="text" id="share-pass-input" readonly 
                           class="w-full bg-slate-50 border border-slate-200 border-r-0 rounded-l-lg p-3 text-sm text-slate-800 font-mono tracking-widest select-all focus:outline-none focus:bg-white transition-colors" 
                           value="<?php echo esc_attr($share_pass); ?>">
                    <button class="btn-copy bg-white border border-l-0 border-slate-200 px-4 text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 transition-all font-medium flex items-center gap-2 rounded-r-lg" 
                            data-target="share-pass-input">
                        <span class="btn-icon"><i data-lucide="key" class="w-4 h-4"></i></span>
                        <span class="text-xs">Copy</span>
                    </button>
                </div>
            </div>

            <div class="p-4 bg-blue-50 text-blue-800 text-sm rounded-xl flex items-start gap-3 border border-blue-100">
                <i data-lucide="info" class="w-5 h-5 mt-0.5 shrink-0 text-blue-500"></i>
                <p class="leading-relaxed text-xs">
                    This password is tied to the <strong><?php echo esc_html($client_name); ?></strong> account. Once the client logs in, they will have access to all projects under this taxonomy.
                </p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>