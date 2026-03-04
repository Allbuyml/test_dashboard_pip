<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
class DTT_Admin_Menu {
    public function add_menu_pages() {
        add_submenu_page('edit.php?post_type=dtt_project', 'Import Projects', 'Import Data', 'manage_options', 'dtt-import', array($this, 'render_import'));
    }
    public function render_import() { $importer = new DTT_Importer(); $importer->render_page(); }
}