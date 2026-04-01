// ============================================================
// NoiThat.vn - Main JavaScript
// ============================================================

document.addEventListener('DOMContentLoaded', function () {

    // ---- Mobile menu ----
    const menuToggle = document.querySelector('.menu-toggle');
    const navMain    = document.querySelector('.nav-main');
    if (menuToggle && navMain) {
        menuToggle.addEventListener('click', () => navMain.classList.toggle('open'));
    }

    // ---- Scroll to top ----
    const scrollBtn = document.createElement('button');
    scrollBtn.className = 'scroll-top';
    scrollBtn.innerHTML = '↑';
    scrollBtn.title = 'Lên đầu trang';
    document.body.appendChild(scrollBtn);
    window.addEventListener('scroll', () => {
        scrollBtn.classList.toggle('visible', window.scrollY > 300);
    });
    scrollBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

    // ---- Quantity buttons (product detail) ----
    const qtyInput = document.querySelector('.qty-input');
    const qtyMinus = document.querySelector('.qty-minus');
    const qtyPlus  = document.querySelector('.qty-plus');
    if (qtyInput) {
        const max = parseInt(qtyInput.dataset.max) || 99;
        if (qtyMinus) qtyMinus.addEventListener('click', () => {
            if (parseInt(qtyInput.value) > 1) qtyInput.value = parseInt(qtyInput.value) - 1;
        });
        if (qtyPlus) qtyPlus.addEventListener('click', () => {
            if (parseInt(qtyInput.value) < max) qtyInput.value = parseInt(qtyInput.value) + 1;
        });
    }

    // ---- Cart quantity AJAX ----
    document.querySelectorAll('.cart-qty-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const masp = this.dataset.masp;
            const action = this.dataset.action; // 'plus' | 'minus'
            fetch('?action=update_cart', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `ma_sp=${masp}&action=${action}`
            }).then(r => r.json()).then(d => {
                if (d.success) location.reload();
            });
        });
    });

    // ---- Payment method toggle ----
    const bankInfo = document.getElementById('bankInfo');
    document.querySelectorAll('input[name="hinh_thuc_tt"]').forEach(radio => {
        radio.addEventListener('change', function () {
            if (bankInfo) bankInfo.style.display = this.value === 'chuyen_khoan' ? 'block' : 'none';
        });
    });

    // ---- Address radio toggle (checkout) ----
    const addrExisting = document.getElementById('addr_existing');
    const addrNew = document.getElementById('addr_new');
    const existingFields = document.getElementById('existingFields');
    const newFields = document.getElementById('newFields');
    if (addrExisting && addrNew) {
        function toggleAddr() {
            if (addrExisting.checked) {
                if (existingFields) existingFields.style.display = 'block';
                if (newFields)      newFields.style.display      = 'none';
            } else {
                if (existingFields) existingFields.style.display = 'none';
                if (newFields)      newFields.style.display      = 'block';
            }
        }
        addrExisting.addEventListener('change', toggleAddr);
        addrNew.addEventListener('change', toggleAddr);
        toggleAddr();
    }

    // ---- Flash message auto hide ----
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(a => {
        setTimeout(() => {
            a.style.transition = 'opacity 0.5s';
            a.style.opacity = '0';
            setTimeout(() => a.remove(), 500);
        }, 4000);
    });

    // ---- Advanced search toggle ----
    const advBtn = document.getElementById('advancedToggle');
    const advArea = document.getElementById('advancedFields');
    if (advBtn && advArea) {
        advBtn.addEventListener('click', () => {
            const open = advArea.style.display !== 'none';
            advArea.style.display = open ? 'none' : 'flex';
            advBtn.textContent = open ? '+ Tìm nâng cao' : '− Tìm nâng cao';
        });
    }

    // ---- Delete confirm ----
    document.querySelectorAll('.confirm-delete').forEach(el => {
        el.addEventListener('click', function (e) {
            if (!confirm('Bạn có chắc chắn muốn xóa?')) e.preventDefault();
        });
    });
});
