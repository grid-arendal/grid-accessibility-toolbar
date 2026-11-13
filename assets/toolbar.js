/* GRID Accessibility Toolbar — GPL-2.0-or-later. No third-party services. */
(function () {
    'use strict';
    function init() {
        var host = document.getElementById('grid-a11y-root');
        if (!host || host.shadowRoot || !host.attachShadow) return;
        var config;
        try { config = JSON.parse(host.dataset.config); } catch (e) { return; }
        var settings = config.settings;
        var labels = config.labels;
        // Input capabilities, not viewport width: small desktop windows and
        // touch laptops with a mouse should retain their pointer controls.
        var touchOnly = window.matchMedia('(any-pointer: coarse)').matches &&
            !(window.matchMedia('(any-pointer: fine)').matches && window.matchMedia('(any-hover: hover)').matches);
        var pointerTools = ['cursor', 'mask', 'guide', 'tooltips'];
        var tools = settings.tools.filter(function (key) { return !touchOnly || pointerTools.indexOf(key) === -1; });
        var combinedFonts = tools.indexOf('font') !== -1 && tools.indexOf('dyslexia') !== -1;
        var aidModes = ['cursor', 'mask', 'guide'].filter(function (key) { return tools.indexOf(key) !== -1; });
        var combinedAids = aidModes.length > 1;
        var aidControlKey = aidModes[0];
        var body = document.body;
        var editorSelector = '.fl-builder-edit, .fl-builder-ui, .fl-builder-ui-pinned, .elementor-editor-active, .et-fb, .vc_editor, [contenteditable="true"][data-builder]';
        function editing() {
            var params = new URLSearchParams(window.location.search);
            return ['fl_builder', 'fl_builder_ui', 'fl_builder_preview', 'elementor-preview', 'et_fb', 'vc_editable', 'ct_builder'].some(function (key) { return params.has(key); }) || !!document.querySelector(editorSelector);
        }
        if (editing()) { host.remove(); return; }
        var empty = function () { return { text: '100', contrast: 'normal', lineheight: 'normal', align: 'normal', saturation: 'normal', spacing: false, font: false, dyslexia: false, links: false, motion: false, cursor: false, mask: false, maskheight: 140, maskopacity: 70, guide: false, images: false, tooltips: false, structure: false, focus: false }; };
        var choices = { text: ['100', '115', '130', '150'], contrast: ['normal', 'dark', 'light'], lineheight: ['normal', 'comfortable', 'relaxed', 'loose'], align: ['normal', 'left', 'center', 'right'], saturation: ['normal', 'muted', 'gray', 'vivid'] };
        function validate(value) {
            var clean = empty();
            if (!value || typeof value !== 'object') return clean;
            tools.forEach(function (key) {
                if (choices[key] && choices[key].indexOf(value[key]) !== -1) clean[key] = value[key];
                else if (typeof clean[key] === 'boolean') clean[key] = value[key] === true;
            });
            if (clean.dyslexia) clean.font = false;
            if (tools.indexOf('mask') !== -1) {
                if (Number.isInteger(value.maskheight) && value.maskheight >= 80 && value.maskheight <= 240 && value.maskheight % 20 === 0) clean.maskheight = value.maskheight;
                if (Number.isInteger(value.maskopacity) && value.maskopacity >= 40 && value.maskopacity <= 85 && value.maskopacity % 5 === 0) clean.maskopacity = value.maskopacity;
            }
            var selectedAid = aidModes.filter(function (key) { return clean[key]; })[0];
            aidModes.forEach(function (key) { clean[key] = key === selectedAid; });
            return clean;
        }
        var state = empty();
        if (settings.remember) {
            try { state = validate(JSON.parse(localStorage.getItem(config.storageKey))); } catch (e) { /* Storage is optional. */ }
        } else {
            try { localStorage.removeItem(config.storageKey); } catch (e) { /* Private browsing. */ }
        }
        var shadow = host.attachShadow({ mode: 'open' });
        host.hidden = true;
        host.dataset.side = settings.side;
        host.style.setProperty('--ga-bottom', settings.bottom + 'px');
        host.style.setProperty('--ga-brand', settings.color);
        var hex = settings.color.slice(1);
        if (hex.length === 3) hex = hex.split('').map(function (c) { return c + c; }).join('');
        var channels = [0, 2, 4].map(function (i) {
            var c = parseInt(hex.slice(i, i + 2), 16) / 255;
            return c <= 0.04045 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
        });
        var luminance = channels[0] * 0.2126 + channels[1] * 0.7152 + channels[2] * 0.0722;
        host.style.setProperty('--ga-on-brand', luminance > 0.179 ? '#000000' : '#ffffff');
        var sheet = document.createElement('link');
        sheet.rel = 'stylesheet'; sheet.href = config.css;
        sheet.onload = function () { if (!stopped) host.hidden = false; };
        sheet.onerror = function () { teardown(); };
        shadow.appendChild(sheet);
        function node(tag, text, attrs) {
            var el = document.createElement(tag);
            if (text) el.textContent = text;
            Object.keys(attrs || {}).forEach(function (key) { el.setAttribute(key, attrs[key]); });
            return el;
        }
        // Original line icons, built from fixed geometry rather than remote icon fonts.
        function icon(name) {
            var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            var attrs = { viewBox: '0 0 24 24', width: '28', height: '28', fill: 'none', stroke: 'currentColor', 'stroke-width': '1.7', 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'aria-hidden': 'true', focusable: 'false' };
            Object.keys(attrs).forEach(function (key) { svg.setAttribute(key, attrs[key]); });
            var shapes = {
                accessibility: [['circle', { cx: '12', cy: '4.5', r: '2' }], ['path', { d: 'M4 9l8 2 8-2M12 11v5m0 0-5 6m5-6 5 6' }]],
                text: [['path', { d: 'M3 19 8.5 5 14 19M5 14h7m4-3h5m-2.5 0v8' }]],
                spacing: [['path', { d: 'M3 5v14M21 5v14M6 12h12M9 9l-3 3 3 3m6-6 3 3-3 3' }]],
                font: [['path', { d: 'M12 6C9 4 6 4 3 5v14c3-1 6-1 9 1m0-14c3-2 6-2 9-1v14c-3-1-6-1-9 1V6Z' }]],
                dyslexia: [['path', { d: 'M4 5h4a7 7 0 0 1 0 14H4V5Zm11 14 3-9 3 9m-5-3h4' }]],
                contrast: [['circle', { cx: '12', cy: '12', r: '9' }], ['path', { d: 'M12 3a9 9 0 0 1 0 18Z', fill: 'currentColor', stroke: 'none' }]],
                links: [['path', { d: 'm10 13 4-4m-6 6-1 1a4 4 0 0 1-6-6l4-4a4 4 0 0 1 6 0m2 2 1-1a4 4 0 0 1 6 6l-4 4a4 4 0 0 1-6 0', transform: 'translate(1 1)' }]],
                motion: [['circle', { cx: '12', cy: '12', r: '9' }], ['path', { d: 'M9 8v8m6-8v8' }]],
                cursor: [['path', { d: 'm5 3 14 12-7 1-4 6L5 3Z' }]],
                guide: [['path', { d: 'M5 4h14M5 8h10M5 16h14M5 20h10' }], ['rect', { x: '2', y: '10.5', width: '20', height: '3', rx: '1' }]],
                mask: [['rect', { x: '3', y: '3', width: '18', height: '18', rx: '3' }], ['path', { d: 'M3 8h18M3 16h18M8 12h8' }]],
                lineheight: [['path', { d: 'M10 5h11M10 12h11M10 19h11M4 3v18M2 5l2-2 2 2M2 19l2 2 2-2' }]],
                align: [['path', { d: 'M3 5h18M3 10h12M3 15h18M3 20h12' }]],
                saturation: [['path', { d: 'M12 2s-7 8-7 13a7 7 0 0 0 14 0c0-5-7-13-7-13ZM9 16a3 3 0 0 0 3 3' }]],
                images: [['rect', { x: '3', y: '3', width: '18', height: '18', rx: '3' }], ['path', { d: 'm3 17 5-5 4 4 3-3 6 6M3 3l18 18' }]],
                tooltips: [['path', { d: 'M5 3h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-7l-5 4v-4H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z' }], ['path', { d: 'M9.5 8a2.5 2.5 0 1 1 4.5 1.5L12 11v1m0 2h.01' }]],
                structure: [['path', { d: 'M9 4h12M9 12h12M9 20h12M3 4h1M3 12h1M3 20h1M3 4v16' }]],
                focus: [['path', { d: 'M8 3H3v5m13-5h5v5M3 16v5h5m13-5v5h-5' }], ['circle', { cx: '12', cy: '12', r: '4' }]],
                reset: [['path', { d: 'M3 4v6h6M3 10a9 9 0 1 1 1 8' }]],
                close: [['path', { d: 'm6 6 12 12M18 6 6 18' }]]
            };
            (shapes[name] || []).forEach(function (shape) {
                var part = document.createElementNS('http://www.w3.org/2000/svg', shape[0]);
                Object.keys(shape[1]).forEach(function (key) { part.setAttribute(key, shape[1][key]); });
                svg.appendChild(part);
            });
            return svg;
        }
        var launcher = node('button', '', { type: 'button', id: 'launcher', 'aria-label': settings.label, 'aria-expanded': 'false', 'aria-controls': 'panel', 'aria-haspopup': 'dialog' });
        launcher.appendChild(icon('accessibility'));
        launcher.appendChild(node('span', settings.label, { class: 'launcher-label', 'aria-hidden': 'true' }));
        var panel = node('section', '', { id: 'panel', role: 'dialog', 'aria-labelledby': 'ga-title', hidden: '' });
        var header = node('header');
        var identity = node('div', '', { class: 'identity' });
        if (config.brand && config.brand.logo) {
            identity.appendChild(node('img', '', { class: 'grid-brand-logo', src: config.brand.logo, alt: '', width: '40', height: '40' }));
        } else {
            var brandIcon = node('span', '', { class: 'brand-icon' });
            brandIcon.appendChild(icon('accessibility')); identity.appendChild(brandIcon);
        }
        var heading = node('div', '', { class: 'header-copy' });
        heading.appendChild(node('h2', settings.title, { id: 'ga-title' }));
        var credit = node('p', labels.credit + ' ', { class: 'brand-credit' });
        credit.appendChild(node('a', 'GRID-Arendal', { href: config.brand.url }));
        heading.appendChild(credit);
        identity.appendChild(heading); header.appendChild(identity);
        var close = node('button', '', { type: 'button', class: 'close', 'aria-label': labels.close });
        close.appendChild(icon('close'));
        header.appendChild(close); panel.appendChild(header);
        var content = node('div', '', { class: 'panel-content' });
        var translation = host.querySelector('#grid-a11y-translation');
        var translationObserver = null;
        var translationTimer = 0;
        if (translation) {
            var translationSlot = node('slot', '', { name: 'translation' });
            content.appendChild(translationSlot);
            translation.hidden = false;
            var languageReset = translation.querySelector('#grid-a11y-language-reset');
            // Use the official selector's original-language option so GTranslate
            // restores the page and its remembered choice through its own handler.
            function originalLanguageOption(select) {
                return Array.prototype.find.call(select.options, function (option) {
                    var pair = option.value.split('|');
                    return pair.length === 2 && pair[0] && pair[0] === pair[1];
                });
            }
            if (languageReset) languageReset.addEventListener('click', function () {
                var select = translation.querySelector('select.gt_selector');
                var original = select && originalLanguageOption(select);
                if (!original) return;
                select.value = original.value;
                select.dispatchEvent(new Event('change', { bubbles: true }));
            });
            function readyTranslation() {
                var select = translation.querySelector('select.gt_selector');
                if (!select) return;
                select.id = 'grid-a11y-language';
                if (languageReset) languageReset.disabled = !originalLanguageOption(select);
                select.setAttribute('aria-describedby', 'grid-a11y-translation-note');
                var loading = translation.querySelector('#grid-a11y-translation-status');
                if (loading.textContent) loading.textContent = '';
                loading.hidden = true;
                if (translationTimer) { clearTimeout(translationTimer); translationTimer = 0; }
            }
            translationObserver = new MutationObserver(readyTranslation);
            translationObserver.observe(translation, { childList: true, subtree: true });
            translationTimer = setTimeout(function () {
                var loading = translation.querySelector('#grid-a11y-translation-status');
                if (!translation.querySelector('select.gt_selector')) loading.textContent = labels.translationUnavailable;
            }, 15000);
            readyTranslation();
        }
        var profileSelect = null;
        var profileNote = null;
        if (config.profiles && Object.keys(config.profiles).length) {
            var profileBlock = node('div', '', { class: 'profiles' });
            profileBlock.appendChild(node('label', labels.profiles, { for: 'ga-profile' }));
            profileSelect = node('select', '', { id: 'ga-profile', 'aria-describedby': 'ga-profile-note' });
            profileSelect.appendChild(node('option', labels.custom, { value: '' }));
            Object.keys(config.profiles).forEach(function (key) { profileSelect.appendChild(node('option', config.profiles[key].label, { value: key })); });
            profileNote = node('p', labels.profileHint, { id: 'ga-profile-note', class: 'note' });
            profileBlock.appendChild(profileSelect); profileBlock.appendChild(profileNote); content.appendChild(profileBlock);
            profileSelect.addEventListener('change', function () {
                var selected = config.profiles[profileSelect.value];
                if (!selected) return;
                state = validate(selected.preferences); profileNote.textContent = selected.description;
                apply(); save(); status.textContent = labels.profileApplied;
            });
        }
        function customProfile() {
            if (profileSelect) { profileSelect.value = ''; profileNote.textContent = labels.profileHint; }
        }
        var maskOptions = node('section', '', { class: 'mask-options', hidden: '', 'aria-label': labels.tools.mask });
        var maskRanges = {};
        var maskOutputs = {};
        ['maskheight', 'maskopacity'].forEach(function (key) {
            var row = node('div', '', { class: 'mask-range' });
            row.appendChild(node('label', key === 'maskheight' ? labels.maskHeight : labels.maskDimming, { for: 'ga-' + key }));
            var range = node('input', '', { type: 'range', id: 'ga-' + key, min: key === 'maskheight' ? '80' : '40', max: key === 'maskheight' ? '240' : '85', step: key === 'maskheight' ? '20' : '5' });
            var output = node('output', '', { for: 'ga-' + key });
            maskRanges[key] = range; maskOutputs[key] = output;
            range.addEventListener('input', function () {
                state[key] = Number(range.value); output.textContent = range.value + (key === 'maskheight' ? ' px' : '%'); customProfile(); updateReadingAids(); save();
            });
            range.addEventListener('change', function () { status.textContent = labels.updated; });
            row.appendChild(range); row.appendChild(output); maskOptions.appendChild(row);
        });
        content.appendChild(maskOptions);
        var outline = node('section', '', { class: 'outline', hidden: '', 'aria-labelledby': 'ga-outline-title' });
        var outlineHeader = node('div', '', { class: 'outline-header' });
        var outlineTitle = node('h3', labels.tools.structure, { id: 'ga-outline-title', tabindex: '-1' });
        outlineHeader.appendChild(outlineTitle);
        var outlineClose = node('button', '', { type: 'button', class: 'outline-close', 'aria-label': labels.closeOutline });
        outlineClose.appendChild(icon('close')); outlineHeader.appendChild(outlineClose); outline.appendChild(outlineHeader);
        outline.appendChild(node('p', labels.outlineHint, { class: 'note' }));
        var outlineList = node('ul'); outline.appendChild(outlineList); content.appendChild(outline);
        outlineClose.addEventListener('click', function () { state.structure = false; customProfile(); apply(); save(); if (controls.structure) controls.structure.focus(); });
        var grid = node('div', '', { class: 'controls' });
        content.appendChild(grid); panel.appendChild(content);
        var controls = {};
        var indicators = {};
        tools.forEach(function (key) {
            if (combinedFonts && key === 'font') return;
            if (combinedAids && aidModes.indexOf(key) !== -1 && key !== aidControlKey) return;
            if (!Object.prototype.hasOwnProperty.call(state, key)) return;
            var row = node('div', '', { class: 'control' });
            var el;
            if (choices[key]) {
                row.classList.add('control-select');
                row.appendChild(icon(key));
                row.appendChild(node('label', labels.tools[key], { for: 'ga-' + key }));
                el = node('select', '', { id: 'ga-' + key });
                choices[key].forEach(function (value) {
                    el.appendChild(node('option', key === 'text' ? value + '%' : labels[value], { value: value }));
                });
            } else {
                el = node('button', '', { type: 'button', 'aria-pressed': 'false' });
                el.appendChild(icon(key));
                el.appendChild(node('span', combinedFonts && key === 'dyslexia' ? labels.fontStyle : combinedAids && key === aidControlKey ? labels.readingAids : labels.tools[key], { class: 'control-label' }));
                indicators[key] = node('span', labels.off, { class: 'state-indicator', 'aria-hidden': 'true' });
                el.appendChild(indicators[key]);
                if (combinedFonts && key === 'dyslexia') {
                    el.setAttribute('aria-describedby', 'ga-font-cycle-hint');
                    row.appendChild(node('span', labels.fontCycleHint, { id: 'ga-font-cycle-hint', class: 'sr-only' }));
                }
                if (combinedAids && key === aidControlKey) {
                    el.setAttribute('aria-describedby', 'ga-reading-aids-hint');
                    row.appendChild(node('span', labels.readingAidsHint, { id: 'ga-reading-aids-hint', class: 'sr-only' }));
                }
            }
            el.addEventListener(choices[key] ? 'change' : 'click', function () {
                if (combinedAids && key === aidControlKey) {
                    var active = aidModes.filter(function (mode) { return state[mode]; })[0];
                    var next = active ? aidModes[aidModes.indexOf(active) + 1] : aidModes[0];
                    aidModes.forEach(function (mode) { state[mode] = mode === next; });
                } else if (combinedFonts && key === 'dyslexia') {
                    if (state.dyslexia) { state.dyslexia = false; state.font = true; }
                    else if (state.font) { state.font = false; }
                    else { state.dyslexia = true; }
                } else {
                    state[key] = el.tagName === 'SELECT' ? el.value : !state[key];
                    if (key === 'dyslexia' && state.dyslexia) state.font = false;
                    if (key === 'font' && state.font) state.dyslexia = false;
                }
                customProfile(); apply(); save(); status.textContent = labels.updated;
                if (key === 'structure' && state.structure) outlineTitle.focus();
            });
            controls[key] = el; row.appendChild(el); grid.appendChild(row);
        });
        var reset = node('button', '', { type: 'button', class: 'reset' });
        reset.appendChild(icon('reset')); reset.appendChild(node('span', labels.reset));
        content.appendChild(reset);
        if (settings.statement) {
            // Defense in depth for filtered/imported settings. Never allow executable URL schemes.
            try {
                var url = new URL(settings.statement);
                if (url.protocol === 'https:' || url.protocol === 'http:') content.appendChild(node('a', labels.statement, { href: url.href, class: 'statement' }));
            } catch (e) { /* Invalid optional URL. */ }
        }
        var about = node('details', '', { class: 'about' });
        about.appendChild(node('summary', labels.about));
        about.appendChild(node('p', labels.note, { class: 'note' }));
        if (touchOnly) about.appendChild(node('p', labels.touchNotice, { class: 'note' }));
        if (tools.indexOf('dyslexia') !== -1) about.appendChild(node('p', labels.dyslexiaNote, { class: 'note' }));
        about.appendChild(node('p', labels.motionNote, { class: 'note' }));
        about.appendChild(node('p', labels.imagesNote, { class: 'note' }));
        if (config.translation) about.appendChild(node('p', labels.translationPrivacy, { class: 'note' }));
        var storageNote = node('p', labels.storage, { class: 'note storage' });
        about.appendChild(storageNote); content.appendChild(about);
        var status = node('p', '', { role: 'status', 'aria-live': 'polite', class: 'sr-only' });
        panel.appendChild(status);
        shadow.appendChild(panel); shadow.appendChild(launcher);
        function openPanel() {
            panel.hidden = false; launcher.setAttribute('aria-expanded', 'true'); close.focus();
        }
        function closePanel(restore) {
            if (panel.hidden) return;
            panel.hidden = true; launcher.setAttribute('aria-expanded', 'false');
            if (restore) launcher.focus();
        }
        launcher.addEventListener('click', function () { if (panel.hidden) openPanel(); else closePanel(true); });
        close.addEventListener('click', function () { closePanel(true); });
        function onKey(event) {
            if (event.key === 'Escape' && tooltipRecord) { dismissedTooltip = tooltipRecord.el; hideTooltip(); event.preventDefault(); return; }
            if (event.key === 'Escape' && !panel.hidden) { event.preventDefault(); closePanel(true); }
        }
        function onOutside(event) {
            if (!event.composedPath().includes(host)) closePanel(false);
        }
        document.addEventListener('keydown', onKey);
        document.addEventListener('pointerdown', onOutside);
        reset.addEventListener('click', function () {
            state = empty(); customProfile(); apply();
            try { localStorage.removeItem(config.storageKey); storageNote.textContent = labels.storage; } catch (e) { storageNote.textContent = labels.unsaved; about.open = true; }
            status.textContent = labels.cleared;
        });
        function save() {
            if (!settings.remember) return;
            try { localStorage.setItem(config.storageKey, JSON.stringify(state)); storageNote.textContent = labels.storage; }
            catch (e) { storageNote.textContent = labels.unsaved; about.open = true; }
        }

        // Never replace HTML or alter Beaver Builder's stored content. Sizes are measured
        // before any overrides, including nested spans with pixel-sized typography.
        var fonts = new Map();
        var ignore = '#grid-a11y-root, #wpadminbar, [data-ga-a11y-ignore], [contenteditable]:not([contenteditable="false"]), .fl-builder-ui, .fl-builder-bar, script, style, noscript, svg, canvas, iframe, pre, code, [aria-hidden="true"], .dashicons, .fa, .fas, .far, .fab, .material-icons';
        var temporaryFocus = new Map();
        function restoreFocusTarget(el) {
            if (!temporaryFocus.has(el)) return;
            if (el.getAttribute('tabindex') === '-1') el.removeAttribute('tabindex');
            temporaryFocus.delete(el);
        }
        function renderOutline() {
            outline.hidden = !state.structure;
            if (!state.structure) return;
            var activeTarget = shadow.activeElement && shadow.activeElement._gaTarget;
            outlineList.textContent = '';
            var count = 0;
            body.querySelectorAll('h1, h2, h3, h4, h5, h6, main, nav, [role="main"], [role="navigation"]').forEach(function (target) {
                if (count >= 80 || target.closest(ignore) || !target.getClientRects().length || getComputedStyle(target).visibility === 'hidden') return;
                var isHeading = /^H[1-6]$/.test(target.tagName);
                var text = isHeading ? target.textContent.trim().replace(/\s+/g, ' ') : (target.getAttribute('aria-label') || (target.matches('nav, [role="navigation"]') ? labels.navigation : labels.main));
                if (!text) return;
                var label = (isHeading ? target.tagName + ' · ' : '') + text.slice(0, 140);
                var item = node('li');
                var jump = node('button', label, { type: 'button' });
                jump._gaTarget = target;
                jump.addEventListener('click', function () {
                    if (!target.isConnected) { renderOutline(); return; }
                    if (!target.hasAttribute('tabindex') && target.tabIndex < 0) {
                        temporaryFocus.set(target, true); target.setAttribute('tabindex', '-1');
                        target.addEventListener('blur', function () { restoreFocusTarget(target); }, { once: true });
                    }
                    closePanel(false); target.focus({ preventScroll: true }); target.scrollIntoView({ block: 'start', behavior: 'auto' });
                });
                item.appendChild(jump); outlineList.appendChild(item); count++;
                if (activeTarget === target) jump.focus({ preventScroll: true });
            });
            if (!count) outlineList.appendChild(node('li', labels.outlineEmpty));
        }
        var tooltip = node('div', '', { id: 'grid-a11y-tooltip', role: 'tooltip', 'data-ga-a11y-ignore': '', hidden: '' });
        body.appendChild(tooltip);
        var tooltipRecord = null;
        var dismissedTooltip = null;
        function hideTooltip() {
            if (tooltipRecord) {
                var record = tooltipRecord;
                if (!record.el.hasAttribute('title')) record.el.setAttribute('title', record.title);
                var described = record.el.getAttribute('aria-describedby');
                if (described === record.appliedDescription) {
                    if (record.description === null) record.el.removeAttribute('aria-describedby');
                    else record.el.setAttribute('aria-describedby', record.description);
                }
            }
            tooltipRecord = null; tooltip.hidden = true; tooltip.textContent = '';
        }
        function showTooltip(target) {
            if (!state.tooltips || !target || target === dismissedTooltip || target.closest(ignore)) return;
            var title = target.getAttribute('title');
            if (!title || !title.trim()) return;
            if (tooltipRecord && tooltipRecord.el === target) return;
            hideTooltip();
            var description = target.getAttribute('aria-describedby');
            var applied = ((description || '') + ' grid-a11y-tooltip').trim();
            tooltipRecord = { el: target, title: title, description: description, appliedDescription: applied };
            target.removeAttribute('title'); target.setAttribute('aria-describedby', applied);
            tooltip.textContent = title.slice(0, 1000); tooltip.hidden = false;
            var rect = target.getBoundingClientRect();
            var tip = tooltip.getBoundingClientRect();
            tooltip.style.left = Math.max(8, Math.min(rect.left, window.innerWidth - tip.width - 8)) + 'px';
            tooltip.style.top = Math.max(8, rect.bottom + tip.height + 12 < window.innerHeight ? rect.bottom + 8 : rect.top - tip.height - 8) + 'px';
        }
        function onTooltipPointer(event) {
            if (!state.tooltips || !(event.target instanceof Element) || tooltip.contains(event.target)) return;
            var candidate = event.target.closest('[title]');
            if (tooltipRecord && tooltipRecord.el.contains(event.target)) return;
            showTooltip(candidate);
        }
        function onTooltipLeave(event) {
            if (dismissedTooltip && event.target instanceof Element && dismissedTooltip.contains(event.target) && (!event.relatedTarget || !dismissedTooltip.contains(event.relatedTarget))) dismissedTooltip = null;
            if (!tooltipRecord || !(event.target instanceof Element)) return;
            var next = event.relatedTarget;
            if (next && (tooltipRecord.el.contains(next) || tooltip.contains(next))) return;
            if (document.activeElement === tooltipRecord.el) return;
            if (tooltipRecord.el.contains(event.target) || tooltip.contains(event.target)) { hideTooltip(); dismissedTooltip = null; }
        }
        function onTooltipFocus(event) { if (event.target instanceof Element) { dismissedTooltip = null; showTooltip(event.target.closest('[title]')); } }
        function onTooltipBlur(event) {
            if (dismissedTooltip && dismissedTooltip.contains(event.target)) dismissedTooltip = null;
            if (tooltipRecord && tooltipRecord.el.contains(event.target)) { hideTooltip(); dismissedTooltip = null; }
        }
        function onScroll() { hideTooltip(); }
        function onPlay(event) {
            if (state.motion && event.target instanceof HTMLVideoElement && !event.target.closest(ignore)) event.target.pause();
        }
        document.addEventListener('pointerover', onTooltipPointer);
        document.addEventListener('pointerout', onTooltipLeave);
        document.addEventListener('focusin', onTooltipFocus);
        document.addEventListener('focusout', onTooltipBlur);
        document.addEventListener('scroll', onScroll, true);
        document.addEventListener('play', onPlay, true);
        function restoreFonts() {
            fonts.forEach(function (record, el) {
                if (el.style.getPropertyValue('font-size') === record.applied && el.style.getPropertyPriority('font-size') === 'important') {
                    if (record.value) el.style.setProperty('font-size', record.value, record.priority);
                    else el.style.removeProperty('font-size');
                }
            });
            fonts.clear();
        }
        function resizeText() {
            restoreFonts();
            if (state.text === '100') return;
            var measured = [];
            body.querySelectorAll('*').forEach(function (el) {
                if (el.closest(ignore) || !el.getClientRects().length) return;
                var directText = Array.prototype.some.call(el.childNodes, function (n) { return n.nodeType === 3 && n.textContent.trim(); });
                if (!directText && !el.matches('input, textarea, select')) return;
                var size = parseFloat(getComputedStyle(el).fontSize);
                if (Number.isFinite(size) && size > 0) measured.push([el, size]);
            });
            measured.forEach(function (entry) {
                var el = entry[0];
                var record = { value: el.style.getPropertyValue('font-size'), priority: el.style.getPropertyPriority('font-size'), applied: (entry[1] * Number(state.text) / 100).toFixed(3) + 'px' };
                fonts.set(el, record); el.style.setProperty('font-size', record.applied, 'important');
                // CSSOM canonicalizes e.g. 30.000px to 30px. Compare canonical
                // values on restore so repeated changes never compound sizes.
                record.applied = el.style.getPropertyValue('font-size');
            });
        }
        var guide = node('div', '', { id: 'grid-a11y-guide', 'aria-hidden': 'true', 'data-ga-a11y-ignore': '' });
        guide.hidden = true; body.appendChild(guide);
        var maskTop = node('div', '', { id: 'grid-a11y-mask-top', 'aria-hidden': 'true', 'data-ga-a11y-ignore': '', hidden: '' });
        var maskBottom = node('div', '', { id: 'grid-a11y-mask-bottom', 'aria-hidden': 'true', 'data-ga-a11y-ignore': '', hidden: '' });
        body.appendChild(maskTop); body.appendChild(maskBottom);
        var readingY = window.innerHeight / 2;
        function updateReadingAids() {
            var height = Math.min(state.maskheight, Math.max(40, window.innerHeight - 16));
            var top = Math.max(0, Math.min(readingY - height / 2, window.innerHeight - height));
            maskTop.hidden = maskBottom.hidden = !state.mask;
            maskTop.style.height = top + 'px'; maskBottom.style.top = (top + height) + 'px';
            maskTop.style.setProperty('--ga-mask-alpha', String(state.maskopacity / 100));
            maskBottom.style.setProperty('--ga-mask-alpha', String(state.maskopacity / 100));
            guide.hidden = !state.guide;
            guide.style.top = Math.max(0, Math.min(readingY + 14, window.innerHeight - 6)) + 'px';
        }
        function onPointer(event) {
            if ((!state.guide && !state.mask) || event.composedPath().indexOf(host) !== -1) return;
            readingY = event.clientY; updateReadingAids();
        }
        function onReadingFocus(event) {
            if ((!state.guide && !state.mask) || !(event.target instanceof Element) || event.target.closest(ignore)) return;
            var rect = event.target.getBoundingClientRect(); readingY = rect.top + rect.height / 2; updateReadingAids();
        }
        document.addEventListener('pointermove', onPointer, { passive: true });
        document.addEventListener('focusin', onReadingFocus);
        var stopped = false;
        var frame = 0;
        function pageClasses(enabled) {
            ['spacing', 'font', 'dyslexia', 'links', 'motion', 'cursor', 'mask', 'guide', 'images', 'focus'].forEach(function (key) { body.classList.toggle('ga-a11y-' + key, enabled && !!state[key]); });
            ['dark', 'light'].forEach(function (mode) { body.classList.toggle('ga-a11y-' + mode, enabled && state.contrast === mode); });
            ['lineheight', 'align', 'saturation'].forEach(function (key) {
                choices[key].filter(function (value) { return value !== 'normal'; }).forEach(function (value) {
                    body.classList.toggle('ga-a11y-' + key + '-' + value, enabled && state[key] === value);
                });
            });
        }
        function apply() {
            pageClasses(true);
            if (!state.tooltips) hideTooltip();
            if (state.motion) body.querySelectorAll('video').forEach(function (video) { if (!video.closest(ignore) && !video.paused) video.pause(); });
            renderOutline();
            updateReadingAids();
            maskOptions.hidden = !state.mask;
            ['maskheight', 'maskopacity'].forEach(function (key) { maskRanges[key].value = String(state[key]); maskOutputs[key].textContent = state[key] + (key === 'maskheight' ? ' px' : '%'); });
            Object.keys(controls).forEach(function (key) {
                var el = controls[key];
                if (el.tagName === 'SELECT') el.value = state[key];
                else if (combinedAids && key === aidControlKey) {
                    var activeAid = aidModes.filter(function (mode) { return state[mode]; })[0];
                    var aidLabel = activeAid ? labels.tools[activeAid] : labels.off;
                    el.setAttribute('aria-pressed', String(!!activeAid));
                    el.setAttribute('aria-label', labels.readingAids + ': ' + aidLabel);
                    indicators[key].textContent = aidLabel;
                }
                else if (combinedFonts && key === 'dyslexia') {
                    var fontLabel = state.dyslexia ? labels.openDyslexic : state.font ? labels.arial : labels.siteFont;
                    el.setAttribute('aria-pressed', String(state.dyslexia || state.font));
                    el.setAttribute('aria-label', labels.fontStyle + ': ' + fontLabel);
                    indicators[key].textContent = fontLabel;
                }
                else {
                    el.setAttribute('aria-pressed', String(state[key]));
                    indicators[key].textContent = state[key] ? labels.on : labels.off;
                }
            });
            resizeText();
        }
        function schedule() {
            if (!frame && !stopped) frame = requestAnimationFrame(function () { frame = 0; if (!stopped) { resizeText(); updateReadingAids(); if (state.structure) renderOutline(); } });
        }
        var observer = new MutationObserver(function (records) {
            if (editing()) { teardown(); return; }
            if ((state.text !== '100' || state.structure) && records.some(function (r) { return (r.type === 'childList' || r.type === 'attributes') && r.target !== host && !host.contains(r.target) && !(r.target instanceof Element && r.target.closest('[data-ga-a11y-ignore]')); })) schedule();
        });
        observer.observe(body, { subtree: true, childList: true, attributes: true, attributeFilter: ['class'] });
        function teardown() {
            stopped = true;
            if (frame) cancelAnimationFrame(frame);
            observer.disconnect(); restoreFonts();
            if (translationObserver) translationObserver.disconnect();
            if (translationTimer) clearTimeout(translationTimer);
            pageClasses(false); hideTooltip();
            temporaryFocus.forEach(function (_, target) { restoreFocusTarget(target); });
            guide.remove(); maskTop.remove(); maskBottom.remove(); tooltip.remove(); host.remove();
            document.removeEventListener('keydown', onKey);
            document.removeEventListener('pointerdown', onOutside);
            document.removeEventListener('pointermove', onPointer);
            document.removeEventListener('focusin', onReadingFocus);
            document.removeEventListener('pointerover', onTooltipPointer);
            document.removeEventListener('pointerout', onTooltipLeave);
            document.removeEventListener('focusin', onTooltipFocus);
            document.removeEventListener('focusout', onTooltipBlur);
            document.removeEventListener('scroll', onScroll, true);
            document.removeEventListener('play', onPlay, true);
            window.removeEventListener('resize', schedule);
        }
        window.addEventListener('resize', schedule, { passive: true });
        // A second frame captures responsive theme styles after the toolbar mounts.
        apply(); schedule();
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, { once: true });
    else init();
}());
