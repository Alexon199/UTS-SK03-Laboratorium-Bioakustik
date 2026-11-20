/* Lab Bioakustik: JavaScript ringan tanpa dependensi.
   Semua fitur bersifat tambahan; halaman tetap berfungsi penuh tanpa JavaScript. */
(function () {
    'use strict';

    var root = document.documentElement;
    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ---- Tema terang/gelap (disimpan di browser; dibungkus try/catch karena storage bisa diblokir) ---- */
    function currentTheme() {
        return root.getAttribute('data-theme') ||
            (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    }

    /* ---- Hitung naik pada angka KPI: <span data-count="61" data-decimals="0"> ---- */
    function countUp(el) {
        var target = parseFloat(el.getAttribute('data-count'));
        if (isNaN(target) || reduceMotion) { return; }
        var decimals = parseInt(el.getAttribute('data-decimals') || '0', 10);
        var duration = 1100;
        var start = null;
        var fmt = new Intl.NumberFormat('id-ID', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });

        function frame(ts) {
            if (start === null) { start = ts; }
            var t = Math.min(1, (ts - start) / duration);
            var eased = 1 - Math.pow(1 - t, 3);
            el.textContent = fmt.format(target * eased);
            if (t < 1) { requestAnimationFrame(frame); } else { el.textContent = fmt.format(target); }
        }
        el.textContent = fmt.format(0);
        requestAnimationFrame(frame);
    }
    Array.prototype.forEach.call(document.querySelectorAll('[data-count]'), countUp);

    /* ---- Toast: hilang sendiri setelah 6 detik ---- */
    function dismissToast(toast) {
        toast.classList.add('is-leaving');
        setTimeout(function () { toast.remove(); }, 320);
    }
    Array.prototype.forEach.call(document.querySelectorAll('.toast'), function (toast) {
        setTimeout(function () { dismissToast(toast); }, 6000);
    });

    /* ---- Dialog konfirmasi hapus: form dengan data-confirm="pesan" ---- */
    var dialog = document.getElementById('confirm-dialog');
    var pendingForm = null;

    document.addEventListener('submit', function (event) {
        var form = event.target;
        var message = form.getAttribute && form.getAttribute('data-confirm');
        if (!message || form.getAttribute('data-confirmed') === '1') { return; }

        event.preventDefault();
        if (!dialog || typeof dialog.showModal !== 'function') {
            if (window.confirm(message)) { form.setAttribute('data-confirmed', '1'); form.submit(); }
            return;
        }
        pendingForm = form;
        dialog.querySelector('[data-confirm-message]').textContent = message;
        dialog.showModal();
    });

    /* ---- Klik: tema, sidebar, tutup toast, dialog ---- */
    document.addEventListener('click', function (event) {
        var target = event.target;

        if (target.closest('[data-theme-toggle]')) {
            var next = currentTheme() === 'dark' ? 'light' : 'dark';
            root.setAttribute('data-theme', next);
            try { localStorage.setItem('theme', next); } catch (e) { /* storage diblokir: abaikan */ }
            return;
        }
        if (target.closest('[data-nav-toggle]')) { document.body.classList.toggle('nav-open'); return; }
        if (target.closest('.scrim')) { document.body.classList.remove('nav-open'); return; }

        var closeToast = target.closest('[data-toast-close]');
        if (closeToast) { dismissToast(closeToast.closest('.toast')); return; }

        if (target.closest('[data-confirm-cancel]') && dialog) { pendingForm = null; dialog.close(); return; }
        if (target.closest('[data-confirm-ok]') && dialog && pendingForm) {
            pendingForm.setAttribute('data-confirmed', '1');
            dialog.close();
            pendingForm.submit();
        }
    });

    /* ---- Filter: select dengan data-autosubmit langsung mengirim form ---- */
    document.addEventListener('change', function (event) {
        var el = event.target;
        if (el.hasAttribute && el.hasAttribute('data-autosubmit') && el.form) { el.form.submit(); }
    });

    /* ---- Spektrogram: sorot kotak dan baris tabel yang sama saat di-hover ---- */
    function setHot(scope, index, on) {
        Array.prototype.forEach.call(scope.querySelectorAll('[data-ann="' + index + '"]'), function (node) {
            node.classList.toggle('is-hot', on);
        });
    }
    Array.prototype.forEach.call(document.querySelectorAll('[data-ann-scope]'), function (scope) {
        scope.addEventListener('mouseover', function (e) {
            var node = e.target.closest('[data-ann]');
            if (node) { setHot(scope, node.getAttribute('data-ann'), true); }
        });
        scope.addEventListener('mouseout', function (e) {
            var node = e.target.closest('[data-ann]');
            if (node) { setHot(scope, node.getAttribute('data-ann'), false); }
        });
    });
})();
