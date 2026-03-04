<div class="fixed inset-0 z-50 bg-slate-100 flex items-center justify-center p-4 font-sans text-slate-900">
    <div class="bg-white p-10 rounded-2xl shadow-2xl max-w-md w-full border border-slate-200">
        <div class="text-center mb-6">
            <div class="w-14 h-14 bg-slate-900 rounded-xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                <span class="text-white font-bold text-xl">PIP</span>
            </div>
            <h2 class="text-2xl font-bold text-slate-900">Protected Dashboard</h2>
            <p class="text-slate-500 mt-2 font-medium"><?php echo esc_html($client_name); ?></p>
        </div>
        <?php if($error_msg): ?>
            <div class="mb-6 p-4 bg-red-50 text-red-600 text-sm font-medium rounded-lg border border-red-100 flex items-center gap-2 justify-center">
                <span>⚠️</span> <?php echo esc_html($error_msg); ?>
            </div>
        <?php endif; ?>
        <form method="post" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">Access Password</label>
                <input type="password" name="dtt_unlock_pass" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-4 text-slate-900 focus:ring-2 focus:ring-blue-500 outline-none transition-all" placeholder="Enter secure code..." required>
            </div>
            <button type="submit" class="w-full bg-slate-900 text-white font-bold py-4 rounded-lg hover:bg-slate-800 transition-all shadow-md">Unlock Dashboard</button>
        </form>
    </div>
</div>