(function () {
    'use strict';
    document.addEventListener('DOMContentLoaded', function () {
        var choose = document.getElementById('ga-logo-select'), clear = document.getElementById('ga-logo-clear'), field = document.getElementById('ga-brand_logo');
        if (!choose || !clear || !field) return;
        var frame;
        choose.addEventListener('click', function () {
            if (!window.wp || !wp.media) return;
            if (!frame) {
                frame = wp.media({ title: choose.textContent, button: { text: choose.textContent }, library: { type: 'image' }, multiple: false });
                frame.on('select', function () { var image = frame.state().get('selection').first().toJSON(); field.value = image.url; field.dispatchEvent(new Event('change', { bubbles: true })); });
            }
            frame.open();
        });
        clear.addEventListener('click', function () { field.value = ''; field.dispatchEvent(new Event('change', { bubbles: true })); field.focus(); });
    });
}());
