/**
 * PharmaSanté - Main JavaScript
 * TP Final DAW L2 INF G 04 - UHBC
 */

/* ============================================================
   1. LIVE MEDICINE SEARCH
   ============================================================ */
function liveMedicineSearch() {
    var input = document.getElementById('searchInput');
    if (!input) return;
    var query = input.value.toLowerCase().trim();
    var cards = document.querySelectorAll('.medicine-card');
    cards.forEach(function(card) {
        var name = card.getAttribute('data-name') || '';
        card.style.display = name.indexOf(query) !== -1 ? '' : 'none';
    });
}

/* ============================================================
   2. FILTER BY CATEGORY
   ============================================================ */
function filterByCategory(cat, btn) {
    // Update active button
    document.querySelectorAll('.filter-btn').forEach(function(b) {
        b.classList.remove('active');
    });
    if (btn) btn.classList.add('active');

    var cards = document.querySelectorAll('.medicine-card');
    cards.forEach(function(card) {
        if (cat === 'tous') {
            card.style.display = '';
        } else {
            var cardCat = (card.getAttribute('data-category') || '').toLowerCase();
            card.style.display = cardCat === cat.toLowerCase() ? '' : 'none';
        }
    });
}

/* ============================================================
   3. DELETE CONFIRMATION MODAL
   ============================================================ */
var _deleteFormId = null;
var _deleteItemId = null;

function deleteConfirmation(id, formId) {
    _deleteFormId = formId;
    _deleteItemId = id;
    document.getElementById('confirmModal').classList.add('active');
}

function confirmDelete() {
    if (_deleteFormId) {
        var form = document.getElementById(_deleteFormId);
        // Set the id field (naming convention: delete-id-{suffix} or delete-id-{form split})
        var parts = _deleteFormId.split('-');
        var suffix = parts[parts.length - 1];
        var idField = document.getElementById('delete-id-' + suffix);
        if (idField) idField.value = _deleteItemId;
        if (form) form.submit();
    }
    closeModal('confirmModal');
}

/* ============================================================
   4. CALCULATE TOTAL (used inline in ajouter_vente.php)
   ============================================================ */
function calculateTotal() {
    // Implemented inline in ajouter_vente.php because it needs PHP data
}

/* ============================================================
   5. VALIDATE STOCK
   ============================================================ */
function validateStock(input, maxStock) {
    var val = parseInt(input.value) || 0;
    if (val > maxStock) {
        input.value = maxStock;
        showToast('La quantité ne peut pas dépasser le stock disponible (' + maxStock + ')!', 'warning');
    }
    if (val < 1) input.value = 1;
}

/* ============================================================
   6. PRINT LIST
   ============================================================ */
function printList() {
    window.print();
}

/* ============================================================
   7. TOGGLE MOBILE MENU
   ============================================================ */
function toggleMobileMenu() {
    var nav = document.getElementById('mainNav');
    var sidebar = document.querySelector('.sidebar');
    if (nav) nav.classList.toggle('open');
    if (sidebar) sidebar.classList.toggle('open');
}

/* ============================================================
   8. VALIDATE FORM
   ============================================================ */
function validateForm(form) {
    var inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    var valid = true;
    inputs.forEach(function(input) {
        if (!input.value.trim()) {
            input.style.borderColor = 'var(--danger)';
            valid = false;
        } else {
            input.style.borderColor = '';
        }
    });
    if (!valid) {
        showToast('Veuillez remplir tous les champs obligatoires.', 'warning');
    }
    return valid;
}

/* ============================================================
   9. CHECK PASSWORD STRENGTH
   ============================================================ */
function checkPasswordStrength(password) {
    var bar = document.getElementById('passwordStrength');
    if (!bar) return;

    if (password.length === 0) {
        bar.style.width = '0';
        bar.style.background = '';
        bar.title = '';
        return;
    }

    if (password.length >= 6) {
        bar.style.width = '100%';
        bar.style.background = '#2E7D32';
        bar.title = 'Mot de passe valide';
    } else if (password.length >= 4) {
        bar.style.width = '60%';
        bar.style.background = '#F57C00';
        bar.title = 'Trop court';
    } else {
        bar.style.width = '30%';
        bar.style.background = '#D32F2F';
        bar.title = 'Trop court';
    }
}

/* ============================================================
   10. UPDATE TIMER (for open caisse)
   ============================================================ */
function updateTimer(openTime) {
    var display = document.getElementById('timerDisplay');
    if (!display) return;
    var now = new Date();
    var diff = Math.floor((now - openTime) / 1000);
    if (diff < 0) diff = 0;
    var h = Math.floor(diff / 3600);
    var m = Math.floor((diff % 3600) / 60);
    var s = diff % 60;
    var pad = function(n) { return n < 10 ? '0' + n : n; };
    display.innerHTML = '<i class="fas fa-clock"></i> Ouverte depuis: ' + pad(h) + 'h ' + pad(m) + 'min ' + pad(s) + 's';
}

/* ============================================================
   11. CHECK PASSWORD MATCH
   ============================================================ */
function checkPasswordMatch(id1, id2, msgId) {
    var p1 = document.getElementById(id1);
    var p2 = document.getElementById(id2);
    var msg = document.getElementById(msgId);
    if (!p1 || !p2 || !msg) return;
    if (p2.value.length === 0) { msg.textContent = ''; return; }
    if (p1.value === p2.value) {
        msg.textContent = '✓ Les mots de passe correspondent';
        msg.style.color = 'var(--primary)';
    } else {
        msg.textContent = '✗ Les mots de passe ne correspondent pas';
        msg.style.color = 'var(--danger)';
    }
}

/* ============================================================
   12. LIVE TABLE FILTER (debounced)
   ============================================================ */
var _filterTimer = null;
function liveTableFilter(inputId, tableId) {
    clearTimeout(_filterTimer);
    _filterTimer = setTimeout(function() {
        var input = document.getElementById(inputId);
        if (!input) return;
        var query = input.value.toLowerCase().trim();
        var rows = document.querySelectorAll('#' + tableId + ' tbody tr');
        rows.forEach(function(row) {
            var text = row.textContent.toLowerCase();
            row.style.display = text.indexOf(query) !== -1 ? '' : 'none';
        });
    }, 200);
}

/* ============================================================
   13. COUNT UP ANIMATION
   ============================================================ */
function countUp() {
    var elements = document.querySelectorAll('.stat-value[data-target]');
    elements.forEach(function(el) {
        var target = parseInt(el.getAttribute('data-target')) || 0;
        var duration = 800;
        var start = 0;
        var step = Math.ceil(target / (duration / 16));
        if (step === 0 && target > 0) step = 1;
        var timer = setInterval(function() {
            start += step;
            if (start >= target) {
                el.textContent = target;
                clearInterval(timer);
            } else {
                el.textContent = start;
            }
        }, 16);
    });
}

/* ============================================================
   14. SHOW TOAST NOTIFICATION
   ============================================================ */
function showToast(message, type) {
    type = type || 'info';
    var toast = document.createElement('div');
    toast.className = 'toast-notification toast-' + type;
    toast.innerHTML = '<i class="fas fa-info-circle"></i> ' + message;

    var colors = {
        success: { bg: 'var(--primary)', text: '#fff' },
        danger:  { bg: 'var(--danger)', text: '#fff' },
        warning: { bg: 'var(--warning)', text: '#fff' },
        info:    { bg: '#0288D1', text: '#fff' }
    };
    var color = colors[type] || colors.info;

    toast.style.cssText = [
        'position:fixed', 'bottom:24px', 'right:24px', 'z-index:9999',
        'background:' + color.bg, 'color:' + color.text,
        'padding:12px 20px', 'border-radius:8px',
        'box-shadow:0 4px 16px rgba(0,0,0,0.2)',
        'font-size:0.9rem', 'font-weight:600',
        'display:flex', 'align-items:center', 'gap:8px',
        'max-width:360px', 'opacity:0',
        'transition:opacity 0.3s ease, transform 0.3s ease',
        'transform:translateY(20px)'
    ].join(';');

    document.body.appendChild(toast);

    setTimeout(function() {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
    }, 10);

    setTimeout(function() {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(20px)';
        setTimeout(function() { document.body.removeChild(toast); }, 300);
    }, 3500);
}

/* ============================================================
   15. LOADING SPINNER
   ============================================================ */
function loadingSpinner(show) {
    var existing = document.getElementById('globalSpinner');
    if (show) {
        if (existing) return;
        var spinner = document.createElement('div');
        spinner.id = 'globalSpinner';
        spinner.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.3);z-index:9998;display:flex;align-items:center;justify-content:center;';
        spinner.innerHTML = '<div style="width:48px;height:48px;border:5px solid rgba(255,255,255,0.3);border-top-color:#fff;border-radius:50%;animation:spin 0.8s linear infinite;"></div>';
        var style = document.createElement('style');
        style.textContent = '@keyframes spin{to{transform:rotate(360deg)}}';
        document.head.appendChild(style);
        document.body.appendChild(spinner);
    } else {
        if (existing) document.body.removeChild(existing);
    }
}

/* ============================================================
   MODAL HELPERS
   ============================================================ */
function closeModal(id) {
    var modal = document.getElementById(id);
    if (modal) modal.classList.remove('active');
}

/* ============================================================
   PASSWORD VISIBILITY TOGGLE
   ============================================================ */
function togglePasswordVisibility(inputId) {
    var input = document.getElementById(inputId);
    if (!input) return;
    input.type = input.type === 'password' ? 'text' : 'password';
}

/* ============================================================
   FORM VALIDATION REGISTRATION
   ============================================================ */
function validateRegistrationForm(form) {
    var p1 = form.querySelector('#password');
    var p2 = form.querySelector('#confirm_password');
    if (p1 && p2 && p1.value !== p2.value) {
        showToast('Les mots de passe ne correspondent pas.', 'warning');
        return false;
    }
    if (p1 && p1.value.length < 6) {
        showToast('Le mot de passe doit contenir au moins 6 caractères.', 'warning');
        return false;
    }
    return validateForm(form);
}

/* ============================================================
   CLOSE MODALS ON OVERLAY CLICK
   ============================================================ */
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('active');
    }
});

/* ============================================================
   CLOSE MODALS ON ESC
   ============================================================ */
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.active').forEach(function(m) {
            m.classList.remove('active');
        });
    }
});
