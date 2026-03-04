<div class="dtt-wrapper min-h-screen bg-slate-100 font-sans text-slate-900 relative">
    <?php include DTT_PATH . 'templates/header.php'; ?>
    <main class="max-w-7xl mx-auto px-6 py-8">
        <div id="view-executive" class="view-section animate-fade-in"><?php include DTT_PATH . 'templates/tab-executive.php'; ?></div>
        <div id="view-workstreams" class="view-section hidden animate-fade-in"><?php include DTT_PATH . 'templates/tab-workstreams.php'; ?></div>
        <div id="view-team" class="view-section hidden animate-fade-in"><?php include DTT_PATH . 'templates/tab-team.php'; ?></div>
        <div id="view-archive" class="view-section hidden animate-fade-in"><?php include DTT_PATH . 'templates/tab-archive.php'; ?></div>
    </main>
    <?php include DTT_PATH . 'templates/modal-share.php'; ?>
    <?php if($is_admin) include DTT_PATH . 'templates/modal-edit-v15.php'; ?>
</div>