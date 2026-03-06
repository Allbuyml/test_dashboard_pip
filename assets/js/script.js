document.addEventListener('DOMContentLoaded', () => {
    const refreshIcons = () => { if(typeof lucide !== 'undefined') lucide.createIcons(); };
    refreshIcons();

    // 1. Control de TABS
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
    // ESCAPE DEL CONTEXTO CSS (Modales Globales a Raíz)
    // ==========================================
    const notifyModal = document.getElementById('notify-client-modal');
    if (notifyModal) document.body.appendChild(notifyModal);

    const deleteCommentModal = document.getElementById('delete-comment-modal');
    if (deleteCommentModal) document.body.appendChild(deleteCommentModal);

    const lb = document.getElementById('dtt-lightbox');
    if (lb) document.body.appendChild(lb);

    // ==========================================
    // 2. AISLAMIENTO ESTRICTO DE EDICIÓN POR TARJETAS
    // ==========================================
    document.body.addEventListener('click', (e) => {
        const editBtn = e.target.closest('.btn-edit-card, .btn-edit-section');
        if(editBtn) {
            e.preventDefault();
            const card = editBtn.getAttribute('data-card') || editBtn.getAttribute('data-section'); 
            
            const ctxInput = document.getElementById('edit-context-input');
            if(ctxInput) ctxInput.value = card;

            document.querySelectorAll('.form-group, .form-section-group').forEach(g => {
                g.classList.add('hidden'); 
            });
            
            const targetForm = document.getElementById('form-card-' + card);
            if(targetForm) {
                targetForm.classList.remove('hidden'); 
            }
            
            toggleModal('edit-modal', true);
            return;
        }

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
    // 3. AUTO-LINKIFY
    // ==========================================
    const linkifyElements = document.querySelectorAll('.dtt-linkify');
    const urlPattern = /(https?:\/\/[^\s<]+)|(www\.[^\s<]+)/ig;
    
    linkifyElements.forEach(el => {
        let text = el.innerHTML;
        if (!text.includes('<a ')) {
            el.innerHTML = text.replace(urlPattern, function(url) {
                let cleanUrl = url.replace(/[.,;!?]$/, '');
                let trailing = url.slice(cleanUrl.length);
                let href = cleanUrl.match(/^https?:\/\//i) ? cleanUrl : 'https://' + cleanUrl;
                return `<a href="${href}" target="_blank" class="text-blue-600 hover:text-blue-800 hover:underline transition-colors font-semibold">${cleanUrl}</a>${trailing}`;
            });
        }
    });

    // ==========================================
    // 4. BLOCKERS (Crear, Editar, Borrar, Resolver)
    // ==========================================

    // Lógica para abrir/cerrar el menú desplegable de opciones del comentario (Edit/Delete) vía Click
    document.body.addEventListener('click', (e) => {
        const toggleMenuBtn = e.target.closest('.btn-toggle-comment-menu');
        
        if (toggleMenuBtn) {
            e.preventDefault();
            e.stopPropagation();
            
            // Cierra cualquier otro menú abierto
            document.querySelectorAll('.comment-menu-dropdown').forEach(menu => {
                if (menu !== toggleMenuBtn.nextElementSibling) {
                    menu.classList.add('hidden');
                }
            });
            
            const dropdown = toggleMenuBtn.nextElementSibling;
            if (dropdown) {
                dropdown.classList.toggle('hidden');
            }
        } else if (!e.target.closest('.comment-menu-dropdown')) {
            // Clic fuera del menú lo cierra automáticamente
            document.querySelectorAll('.comment-menu-dropdown').forEach(menu => {
                menu.classList.add('hidden');
            });
        }
    });

    document.body.addEventListener('click', (e) => {
        const toggleBtn = e.target.closest('.btn-toggle-comments');
        if(toggleBtn) {
            e.preventDefault();
            const card = toggleBtn.closest('.blocker-card');
            const panel = card.querySelector('.comments-panel');
            const icon = toggleBtn.querySelector('i.lucide-chevron-right');
            
            if(panel.classList.contains('hidden')) {
                panel.classList.remove('hidden');
                if(icon) icon.classList.add('rotate-90');
            } else {
                panel.classList.add('hidden');
                if(icon) icon.classList.remove('rotate-90');
            }
        }
    });

    document.body.addEventListener('click', (e) => {
        const showMoreBtn = e.target.closest('.btn-show-more-comments');
        if(showMoreBtn) {
            e.preventDefault();
            const panel = showMoreBtn.closest('.comments-panel');
            const hiddenDiv = panel.querySelector('.hidden-comments');
            if(hiddenDiv) {
                hiddenDiv.classList.remove('hidden');
                showMoreBtn.remove(); 
            }
        }
    });

    document.body.addEventListener('change', (e) => {
        if(e.target.classList.contains('file-input-display')) {
            const form = e.target.closest('form');
            const display = form.querySelector('.file-name-display');
            if(e.target.files.length > 0) {
                display.textContent = `${e.target.files.length} file(s) attached`;
            } else {
                display.textContent = '';
            }
        }
    });

    // Añadir Comentario Nuevo (Subida XHR + Progreso)
    document.body.addEventListener('submit', (e) => {
        if(e.target.classList.contains('frm-add-comment')) {
            e.preventDefault();
            const form = e.target;
            const btn = form.querySelector('.btn-submit-comment');
            const originalText = btn.innerHTML;
            const progressContainer = form.querySelector('.upload-progress-container');
            const progressBar = form.querySelector('.upload-progress-bar');
            const progressText = form.querySelector('.upload-progress-text');
            
            btn.disabled = true;

            const formData = new FormData(form);
            formData.append('action', 'dtt_add_comment');
            formData.append('pid', window.dtt_pid);
            
            const select = form.querySelector('select[name="author"]');
            const org = select.options[select.selectedIndex].getAttribute('data-org');
            formData.append('org', org);

            const fileInput = form.querySelector('input[type="file"]');
            const hasFiles = fileInput && fileInput.files.length > 0;
            
            if (hasFiles && progressContainer) {
                progressContainer.classList.remove('hidden');
                btn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 animate-spin"></i> Uploading...';
                if(typeof lucide !== 'undefined') lucide.createIcons();
            } else {
                btn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 animate-spin"></i> Posting...';
                if(typeof lucide !== 'undefined') lucide.createIcons();
            }

            const xhr = new XMLHttpRequest();
            xhr.open('POST', window.dtt_ajax_url, true);

            if (hasFiles && progressBar) {
                xhr.upload.onprogress = function(e) {
                    if (e.lengthComputable) {
                        const percentComplete = Math.round((e.loaded / e.total) * 100);
                        progressBar.style.width = percentComplete + '%';
                        if (progressText) progressText.innerText = percentComplete + '%';
                    }
                };
            }

            xhr.onload = function() {
                if (xhr.status >= 200 && xhr.status < 400) {
                    try {
                        const data = JSON.parse(xhr.responseText);
                        if(data.success) { 
                            window.location.href = window.location.pathname + '?t=' + new Date().getTime() + window.location.hash;
                        } else { 
                            alert('Error: ' + (data.data?.message || 'Failed to save comment.')); 
                            resetBtn(); 
                        }
                    } catch(err) {
                        alert('Server parsing error.');
                        resetBtn();
                    }
                } else {
                    alert('Connection error');
                    resetBtn();
                }
            };

            xhr.onerror = function() {
                alert('Request failed');
                resetBtn();
            };

            function resetBtn() {
                btn.innerHTML = originalText; 
                btn.disabled = false;
                if(progressContainer) progressContainer.classList.add('hidden');
                if(progressBar) progressBar.style.width = '0%';
                if(progressText) progressText.innerText = '0%';
            }

            xhr.send(formData);
        }
    });

    // -------- Lógica de Edición y Eliminación de Comentarios --------
    let commentToDelete = null;

    document.body.addEventListener('click', (e) => {
        // Toggle Edit Mode
        const editBtn = e.target.closest('.btn-edit-comment');
        if(editBtn) {
            e.preventDefault();
            const item = editBtn.closest('.comment-item');
            
            // Ocultar el menú desplegable al hacer clic en Edit
            const dropdown = item.querySelector('.comment-menu-dropdown');
            if (dropdown) dropdown.classList.add('hidden');
            
            item.querySelector('.comment-content').classList.add('hidden');
            item.querySelector('.comment-edit-form').classList.remove('hidden');
        }

        // Cancel Edit
        const cancelEditBtn = e.target.closest('.btn-cancel-edit-comment');
        if(cancelEditBtn) {
            e.preventDefault();
            const item = cancelEditBtn.closest('.comment-item');
            item.querySelector('.comment-content').classList.remove('hidden');
            item.querySelector('.comment-edit-form').classList.add('hidden');
        }

        // Save Edit
        const saveEditBtn = e.target.closest('.btn-save-edit-comment');
        if(saveEditBtn) {
            e.preventDefault();
            const item = saveEditBtn.closest('.comment-item');
            const newText = item.querySelector('textarea').value;
            const b_idx = item.getAttribute('data-bidx');
            const c_idx = item.getAttribute('data-cidx');

            const originalText = saveEditBtn.innerHTML;
            saveEditBtn.innerHTML = '<i data-lucide="loader" class="w-3 h-3 animate-spin"></i>';
            if(typeof lucide !== 'undefined') lucide.createIcons();
            saveEditBtn.disabled = true;

            const formData = new FormData();
            formData.append('action', 'dtt_edit_comment');
            formData.append('pid', window.dtt_pid);
            formData.append('b_idx', b_idx);
            formData.append('c_idx', c_idx);
            formData.append('text', newText);

            fetch(window.dtt_ajax_url, { method: 'POST', body: formData })
            .then(r => r.json())
            .then(d => {
                if(d.success) {
                    window.location.href = window.location.pathname + '?t=' + new Date().getTime() + window.location.hash;
                } else {
                    alert('Error saving comment.');
                    saveEditBtn.innerHTML = originalText;
                    saveEditBtn.disabled = false;
                }
            });
        }

        // Setup Delete Modal
        const delBtn = e.target.closest('.btn-del-comment');
        if(delBtn) {
            e.preventDefault();
            const item = delBtn.closest('.comment-item');
            
            // Ocultar el menú desplegable al hacer clic en Delete
            const dropdown = item.querySelector('.comment-menu-dropdown');
            if (dropdown) dropdown.classList.add('hidden');
            
            const b_idx = item.getAttribute('data-bidx');
            const c_idx = item.getAttribute('data-cidx');
            commentToDelete = { b_idx, c_idx };
            
            if(deleteCommentModal) deleteCommentModal.classList.remove('hidden');
        }

        // Close Delete Modal
        const btnCancelDel = e.target.closest('.btn-close-del-comment');
        if(btnCancelDel) {
            e.preventDefault();
            if(deleteCommentModal) deleteCommentModal.classList.add('hidden');
            commentToDelete = null;
        }

        // Confirm Delete
        const btnConfirmDel = e.target.closest('#btn-confirm-del-comment');
        if(btnConfirmDel) {
            e.preventDefault();
            if(!commentToDelete) return;
            
            const originalText = btnConfirmDel.innerHTML;
            btnConfirmDel.innerHTML = '<i data-lucide="loader" class="w-4 h-4 animate-spin"></i> Deleting...';
            if(typeof lucide !== 'undefined') lucide.createIcons();
            btnConfirmDel.disabled = true;

            const formData = new FormData();
            formData.append('action', 'dtt_delete_comment');
            formData.append('pid', window.dtt_pid);
            formData.append('b_idx', commentToDelete.b_idx);
            formData.append('c_idx', commentToDelete.c_idx);

            fetch(window.dtt_ajax_url, { method: 'POST', body: formData })
            .then(r => r.json())
            .then(d => {
                if(d.success) {
                    window.location.href = window.location.pathname + '?t=' + new Date().getTime() + window.location.hash;
                } else {
                    alert('Error deleting comment.');
                    btnConfirmDel.innerHTML = originalText;
                    btnConfirmDel.disabled = false;
                }
            });
        }
    });

    // Lógica Real Time de "Marcar como resuelto"
    document.body.addEventListener('click', (e) => {
        const resolveBtn = e.target.closest('.btn-resolve-blocker');
        if(resolveBtn) {
            e.preventDefault();
            const card = resolveBtn.closest('.blocker-card');
            const idx = card.getAttribute('data-idx');
            
            const originalHTML = resolveBtn.innerHTML;
            resolveBtn.innerHTML = '<i data-lucide="loader" class="w-3.5 h-3.5 animate-spin"></i> Processing...';
            if(typeof lucide !== 'undefined') lucide.createIcons();
            resolveBtn.disabled = true;

            const formData = new FormData();
            formData.append('action', 'dtt_resolve_blocker');
            formData.append('pid', window.dtt_pid);
            formData.append('idx', idx);

            fetch(window.dtt_ajax_url, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    const container = document.getElementById('blockers-list-container');
                    const showResolved = container.getAttribute('data-show-resolved') === 'true';

                    if(!showResolved) {
                        card.style.transition = 'all 0.3s ease';
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.95)';
                        setTimeout(() => card.remove(), 300);
                    } else {
                        card.className = "rounded-lg border-2 bg-white overflow-hidden p-0 border-emerald-300 opacity-75 blocker-card";
                        const badgeContainer = card.querySelector('.severity-badge-container');
                        if(badgeContainer) {
                            badgeContainer.insertAdjacentHTML('beforeend', '<span class="px-2 py-0.5 text-xs font-bold rounded bg-emerald-200 text-emerald-800">RESOLVED</span>');
                        }
                        
                        resolveBtn.remove();
                        const overdueBadge = card.querySelector('.overdue-badge');
                        if(overdueBadge) overdueBadge.remove();
                        
                        const formWrapper = card.querySelector('.comment-form-wrapper');
                        if(formWrapper) {
                            formWrapper.innerHTML = '<div class="text-xs text-slate-400 text-center py-2 bg-slate-100 rounded-lg border border-slate-200 mt-4">This blocker has been resolved. Comments are closed.</div>';
                        }
                    }

                    const countBadge = document.getElementById('blockers-count-badge');
                    if(countBadge) {
                        countBadge.innerText = Math.max(0, parseInt(countBadge.innerText) - 1);
                    }
                } else { 
                    alert('Failed to resolve'); resolveBtn.innerHTML = originalHTML; resolveBtn.disabled = false; 
                }
            });
        }
    });

    // ==========================================
    // 5. NOTIFY CLIENT (Modal + Logica de Checkboxes)
    // ==========================================
    const btnOpenNotify = document.getElementById('btn-open-notify');
    
    const syncCheckboxes = () => {
        const globalCb = notifyModal.querySelector('.global-select-all');
        if (!globalCb) return;

        let allTotal = 0;
        let allChecked = 0;

        notifyModal.querySelectorAll('.notify-group').forEach(group => {
            const cbs = group.querySelectorAll('.notify-cb');
            const groupCb = group.querySelector('.group-select-all');
            
            if (cbs.length > 0 && groupCb) {
                const totalCount = cbs.length;
                const checkedCount = Array.from(cbs).filter(cb => cb.checked).length;

                allTotal += totalCount;
                allChecked += checkedCount;

                groupCb.checked = (checkedCount === totalCount && totalCount > 0);
                groupCb.indeterminate = (checkedCount > 0 && checkedCount < totalCount);
            }
        });

        globalCb.checked = (allChecked === allTotal && allTotal > 0);
        globalCb.indeterminate = (allChecked > 0 && allChecked < allTotal);
    };

    if(btnOpenNotify && notifyModal) {
        btnOpenNotify.addEventListener('click', () => { 
            notifyModal.classList.remove('hidden'); 
            syncCheckboxes(); 
        });
        
        notifyModal.querySelectorAll('.btn-close-notify').forEach(btn => {
            btn.addEventListener('click', () => { notifyModal.classList.add('hidden'); });
        });

        notifyModal.addEventListener('change', (e) => {
            if (e.target.classList.contains('global-select-all')) {
                const isChecked = e.target.checked;
                notifyModal.querySelectorAll('.notify-cb, .group-select-all').forEach(cb => {
                    cb.checked = isChecked;
                    cb.indeterminate = false;
                });
            } 
            else if (e.target.classList.contains('group-select-all')) {
                const isChecked = e.target.checked;
                const group = e.target.closest('.notify-group');
                if (group) {
                    group.querySelectorAll('.notify-cb').forEach(cb => {
                        cb.checked = isChecked;
                    });
                }
                syncCheckboxes();
            } 
            else if (e.target.classList.contains('notify-cb')) {
                syncCheckboxes();
            }
        });

        const btnSendNotify = document.getElementById('btn-send-notify');
        if (btnSendNotify) {
            btnSendNotify.addEventListener('click', (e) => {
                e.preventDefault();
                const originalHTML = btnSendNotify.innerHTML;
                
                const to = Array.from(notifyModal.querySelectorAll('.notify-cb:checked')).map(cb => cb.value);
                const customEmails = document.getElementById('notify-custom-emails').value;
                const sendMode = document.querySelector('input[name="notify-mode"]:checked').value;
                
                if(to.length === 0 && customEmails.trim() === '') { 
                    alert('Please select recipients or enter custom email addresses.'); 
                    return; 
                }

                btnSendNotify.innerHTML = '<i data-lucide="loader" class="w-4 h-4 animate-spin"></i> Sending...';
                if(typeof lucide !== 'undefined') lucide.createIcons();
                btnSendNotify.disabled = true;
                
                const formData = new FormData();
                formData.append('action', 'dtt_notify_client');
                formData.append('pid', window.dtt_pid);
                formData.append('to', JSON.stringify(to));
                formData.append('custom_emails', customEmails);
                formData.append('send_mode', sendMode);
                formData.append('subject', document.getElementById('notify-subject').value);
                formData.append('message', document.getElementById('notify-message').value);

                fetch(window.dtt_ajax_url, { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    const fb = document.getElementById('notify-feedback');
                    fb.classList.remove('hidden');
                    if(data.success) {
                        fb.className = 'text-sm font-bold text-emerald-700 bg-emerald-100 mt-4 p-3 rounded-lg border border-emerald-200';
                        fb.innerHTML = '✅ Email sent successfully!';
                        setTimeout(() => { notifyModal.classList.add('hidden'); fb.classList.add('hidden'); }, 2000);
                    } else {
                        fb.className = 'text-sm font-bold text-red-700 bg-red-100 mt-4 p-3 rounded-lg border border-red-200';
                        fb.innerHTML = '❌ Failed to send: ' + (data.data?.message || 'Server error');
                    }
                    btnSendNotify.innerHTML = originalHTML;
                    btnSendNotify.disabled = false;
                    if(typeof lucide !== 'undefined') lucide.createIcons();
                });
            });
        }
    }

    // ==========================================
    // 6. LÓGICA DE REPETIDORES DE ADMINISTRADOR
    // ==========================================
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

    // ==========================================
    // 8. Lightbox Control
    // ==========================================
    if(lb) {
        const lbImg = document.getElementById('lb-img');
        const lbCap = document.getElementById('lb-cap');
        const lbCount = document.getElementById('lb-count');
        const lbThumbs = document.getElementById('lb-thumbs');
        const lbCopyIcon = document.getElementById('lb-copy-icon');
        let currentImages = [];
        let currentIndex = 0;

        const updateLb = () => {
            if(!currentImages.length) return;
            lbImg.src = currentImages[currentIndex].url;
            lbCap.textContent = currentImages[currentIndex].caption || '';
            lbCount.textContent = (currentIndex + 1) + ' / ' + currentImages.length;
            
            lbThumbs.innerHTML = currentImages.map((img, i) => `
                <img src="${img.url}" data-idx="${i}" class="w-16 h-16 object-cover rounded-lg cursor-pointer border-2 transition-all flex-shrink-0 snap-center ${i === currentIndex ? 'border-white scale-110 shadow-lg' : 'border-transparent opacity-50 hover:opacity-100'}">
            `).join('');
        };

        lbThumbs.addEventListener('click', (e) => {
            if(e.target.tagName === 'IMG') {
                currentIndex = parseInt(e.target.getAttribute('data-idx'));
                updateLb();
            }
        });

        document.body.addEventListener('click', (e) => {
            const trigger = e.target.closest('.img-lightbox-trigger');
            if(trigger) {
                currentImages = JSON.parse(trigger.getAttribute('data-images') || '[]');
                currentIndex = parseInt(trigger.getAttribute('data-index') || '0', 10);
                updateLb();
                lb.classList.remove('hidden');
                setTimeout(() => lb.classList.remove('opacity-0'), 10); 
            }
        });

        document.getElementById('lb-close').addEventListener('click', () => {
            lb.classList.add('opacity-0');
            setTimeout(() => lb.classList.add('hidden'), 300); 
        });
        
        document.getElementById('lb-prev').addEventListener('click', () => {
            currentIndex = (currentIndex > 0) ? currentIndex - 1 : currentImages.length - 1;
            updateLb();
        });
        
        document.getElementById('lb-next').addEventListener('click', () => {
            currentIndex = (currentIndex < currentImages.length - 1) ? currentIndex + 1 : 0;
            updateLb();
        });

        document.getElementById('lb-copy').addEventListener('click', () => {
            navigator.clipboard.writeText(currentImages[currentIndex].url).then(() => {
                const originalHTML = lbCopyIcon.innerHTML;
                lbCopyIcon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-400"><path d="M20 6 9 17l-5-5"/></svg>';
                setTimeout(() => { lbCopyIcon.innerHTML = originalHTML; }, 2000);
            });
        });
        
        document.addEventListener('keydown', (e) => {
            if(!lb.classList.contains('hidden')) {
                if(e.key === 'Escape') document.getElementById('lb-close').click();
                if(e.key === 'ArrowLeft') document.getElementById('lb-prev').click();
                if(e.key === 'ArrowRight') document.getElementById('lb-next').click();
            }
        });
    }
});