<div class="min-h-[70vh] flex items-center justify-center p-4 font-sans">
    <div class="bg-white rounded-2xl shadow-xl p-10 max-w-md w-full text-center border border-slate-200">
        <div class="w-20 h-20 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-6">
            <i data-lucide="shield-alert" class="w-10 h-10 text-red-600"></i>
        </div>
        <h2 class="text-2xl font-bold text-slate-900 mb-2">Access Denied</h2>
        <p class="text-sm text-slate-600 mb-8 leading-relaxed">
            You do not have the necessary administrative privileges to view the master dashboard. 
            Please log in with an authorized account or request a direct project link.
        </p>
        <a href="<?php echo esc_url(wp_logout_url(home_url())); ?>" class="inline-block w-full bg-slate-900 text-white font-bold py-3 px-8 rounded-lg hover:bg-slate-800 transition-colors shadow-md">
            Switch Account
        </a>
    </div>
</div>