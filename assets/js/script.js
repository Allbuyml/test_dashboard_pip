document.addEventListener('DOMContentLoaded', () => {
    // 1. Iniciar Iconos
    const refreshIcons = () => { if(typeof lucide !== 'undefined') lucide.createIcons(); };
    refreshIcons();

    // 2. Control de TABS
    const tabs = document.querySelectorAll('.tab-btn');
    const views = document.querySelectorAll('.view-section');

    if(tabs.length > 0) {
        tabs.forEach(btn => {
            btn.addEventListener('click', () => {
                tabs.forEach(t => { 
                    t.classList.remove('active', 'bg-slate-100', 'text-slate-900', 'border-t-2', 'border-emerald-500'); 
                    t.classList.add('text-slate-500', 'hover:text-slate-700', 'hover:bg-slate-50'); 
                });
                
                btn.classList.remove('text-slate-500', 'hover:text-slate-700', 'hover:bg-slate-50'); 
                btn.classList.add('active', 'bg-slate-100', 'text-slate-900', 'border-t-2', 'border-emerald-500');

                views.forEach(v => v.classList.add('hidden'));
                const targetView = document.getElementById('view-' + btn.getAttribute('data-tab'));
                if(targetView) targetView.classList.remove('hidden');
            });
        });
    }

    const toggleModal = (id, show) => { 
        const el = document.getElementById(id); 
        if(el) { show ? el.classList.remove('hidden') : el.classList.add('hidden'); }
    };

    // ==========================================
    // 3. AISLAMIENTO ESTRICTO DE EDICIÓN POR TARJETAS
    // ==========================================
    document.body.addEventListener('click', (e) => {
        // A. Clic en Botón de Editar cualquier Tarjeta
        const editBtn = e.target.closest('.btn-edit-card');
        if(editBtn) {
            e.preventDefault();
            const card = editBtn.getAttribute('data-card'); 
            
            const ctxInput = document.getElementById('edit-context-input');
            if(ctxInput) ctxInput.value = card;

            // OCULTAR ABSOLUTAMENTE TODOS LOS FORMULARIOS (Incluido el Header)
            document.querySelectorAll('.form-group, .form-section-group').forEach(g => {
                g.classList.add('hidden'); 
            });
            
            // MOSTRAR SOLO EL FORMULARIO DE LA TARJETA SELECCIONADA
            const targetForm = document.getElementById('form-card-' + card);
            if(targetForm) {
                targetForm.classList.remove('hidden'); 
            }
            
            toggleModal('edit-modal', true);
            return;
        }

        // B. Clic en Editar Header (Excepción)
        const headEditBtn = e.target.closest('#btn-open-edit-header');
        if(headEditBtn) {
            e.preventDefault();
            const ctxInput = document.getElementById('edit-context-input');
            if(ctxInput) ctxInput.value = 'header';
            
            document.querySelectorAll('.form-group, .form-section-group').forEach(g => g.classList.add('hidden')); 
            const targetForm = document.getElementById('form-card-header');
            if(targetForm) targetForm.classList.remove('hidden');
            
            toggleModal('edit-modal', true);
            return;
        }
    });

    const closeEdit = document.getElementById('close-edit-modal');
    if(closeEdit) closeEdit.addEventListener('click', (e) => { e.preventDefault(); toggleModal('edit-modal', false); });

    // ==========================================
    // 4. AUTO-LINKIFY (Convierte textos en enlaces)
    // ==========================================
    const linkifyElements = document.querySelectorAll('.dtt-linkify');
    // Detecta http://, https://, o www.dominio.com
    const urlPattern = /(https?:\/\/[^\s]+)|(www\.[^\s]+)/ig;
    
    linkifyElements.forEach(el => {
        let text = el.innerHTML;
        // Evita romper si ya hay código HTML dentro
        if (!text.includes('<a ')) {
            el.innerHTML = text.replace(urlPattern, function(url) {
                // Eliminar puntuación al final del link (ej: "www.google.com." -> "www.google.com")
                let cleanUrl = url.replace(/[.,;!?]$/, '');
                let trailing = url.slice(cleanUrl.length);
                
                // Forzar HTTPS si solo escribieron www.
                let href = cleanUrl.match(/^https?:\/\//i) ? cleanUrl : 'https://' + cleanUrl;
                
                return `<a href="${href}" target="_blank" class="text-blue-600 hover:text-blue-800 hover:underline transition-colors font-semibold">${cleanUrl}</a>${trailing}`;
            });
        }
    });

    // 5. Lógica Repeater: Eliminar Fila
    document.body.addEventListener('click', (e) => {
        const delBtn = e.target.closest('.btn-del-row');
        if(delBtn) {
            e.preventDefault();
            if(confirm('Delete this item?')) {
                const row = delBtn.closest('.repeater-row');
                if(row) row.remove();
            }
        }
    });

    // 6. Lógica Repeater: Añadir Fila
    document.body.addEventListener('click', (e) => {
        const addBtn = e.target.closest('.btn-add-row');
        if(addBtn) {
            e.preventDefault();
            const targetId = addBtn.getAttribute('data-target');
            const tplId = addBtn.getAttribute('data-template');
            
            const container = document.getElementById(targetId);
            const tpl = document.getElementById(tplId);
            
            if(container && tpl) {
                const index = Date.now(); 
                let html = tpl.innerHTML.replace(/{i}/g, index);
                container.insertAdjacentHTML('beforeend', html);
                refreshIcons();
            }
        }

        // Añadir Milestone anidado
        const addNestedBtn = e.target.closest('.btn-add-nested');
        if(addNestedBtn) {
            e.preventDefault();
            const row = addNestedBtn.closest('.goal-row');
            const container = row.querySelector('.milestones-container');
            const tpl = document.getElementById(addNestedBtn.dataset.template);
            const parentName = addNestedBtn.dataset.parentName; 
            
            if(container && tpl) {
                const j = Date.now();
                let html = tpl.innerHTML.replace(/{parentName}/g, parentName).replace(/{j}/g, j);
                container.insertAdjacentHTML('beforeend', html);
                refreshIcons();
            }
        }
    });

    // 7. Modales Varios y Copiar
    const btnShare = document.getElementById('btn-open-share-modal');
    if(btnShare) btnShare.addEventListener('click', () => toggleModal('share-modal', true));
    
    const closeShare = document.getElementById('close-share-modal');
    if(closeShare) closeShare.addEventListener('click', () => toggleModal('share-modal', false));

    const btnClient = document.getElementById('btn-client-selector');
    const menuClient = document.getElementById('dropdown-menu');
    if(btnClient && menuClient) { 
        btnClient.addEventListener('click', (e) => { 
            e.stopPropagation(); 
            menuClient.classList.toggle('hidden'); 
        }); 
        document.addEventListener('click', () => {
            if(!menuClient.classList.contains('hidden')) menuClient.classList.add('hidden');
        }); 
    }

    document.querySelectorAll('.btn-copy').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const input = document.getElementById(btn.getAttribute('data-target'));
            if(input) {
                navigator.clipboard.writeText(input.value).then(() => {
                    const iconSpan = btn.querySelector('.btn-icon');
                    if(iconSpan) {
                        const originalIcon = iconSpan.innerHTML;
                        iconSpan.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-emerald-600"><path d="M20 6 9 17l-5-5"/></svg>';
                        setTimeout(() => { iconSpan.innerHTML = originalIcon; }, 2000);
                    }
                });
            }
        });
    });

    // NEW PROJECT MODAL
    const btnNewProj = document.getElementById('btn-open-new-project');
    if(btnNewProj) {
        btnNewProj.addEventListener('click', (e) => {
            e.preventDefault();
            toggleModal('new-project-modal', true);
        });
    }
    const closeNewProj = document.getElementById('close-new-project-modal');
    if(closeNewProj) {
        closeNewProj.addEventListener('click', (e) => {
            e.preventDefault();
            toggleModal('new-project-modal', false);
        });
    }

    // TAXONOMY SELECT TOGGLE
    document.body.addEventListener('change', (e) => {
        if(e.target.id === 'new_project_client_select') {
            const wrapper = document.getElementById('new_client_name_wrapper');
            const textInput = document.getElementById('new_project_client_text');
            if(wrapper && textInput) {
                if(e.target.value === 'new') {
                    wrapper.classList.remove('hidden');
                    textInput.setAttribute('required', 'required');
                } else {
                    wrapper.classList.add('hidden');
                    textInput.removeAttribute('required');
                }
            }
        }
    });
});