/* Ravion Work Done — canonical activity picker. Load after ravion-execution.js. */
(() => {
    'use strict';
    const form = document.querySelector('[data-ref-work-done-form]');
    if (!form) return;
    const base = form.dataset.canonicalBase || '/work-done';
    const urls = { packages: `${base}/work-packages`, sections: `${base}/work-sections`, activities: `${base}/work-activities`, search: `${base}/search-activities` };
    const cache = new Map();
    const rows = value => Array.isArray(value) ? value : Array.isArray(value?.data) ? value.data : Array.isArray(value?.activities) ? value.activities : Array.isArray(value?.packages) ? value.packages : Array.isArray(value?.sections) ? value.sections : [];
    async function fetchRows(type, params = {}) {
        const url = new URL(urls[type], location.origin);
        Object.entries(params).forEach(([k,v]) => { if (v !== '' && v !== null && v !== undefined) url.searchParams.set(k,v); });
        const key = url.toString();
        if (cache.has(key)) return cache.get(key);
        const response = await fetch(key, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
        if (!response.ok) {
            let detail = '';
            try {
                const payload = await response.json();
                detail = Object.values(payload.errors || {}).flat().join(' ')
                    || payload.message || '';
            } catch (_) { /* Preserve the HTTP status when the response is not JSON. */ }
            throw new Error(`Unable to load activities (${response.status}).${detail ? ' ' + detail : ''}`);
        }
        const data = rows(await response.json());
        cache.set(key,data);
        return data;
    }
    const text = item => item.name || item.activity_name || item.title || item.label || item.code || `Activity ${item.id}`;
    const opt = (item, selected = false) => {
        const node = document.createElement('option');
        node.value = item.id;
        node.textContent = text(item);
        node.selected = selected;
        return node;
    };
    const fill = (select, data, placeholder, selected = '') => {
        select.replaceChildren(new Option(placeholder, ''));
        data.forEach(item => select.append(opt(item, String(item.id) === String(selected))));
    };
    function init(picker) {
        if (picker.dataset.canonicalReady) return;
        picker.dataset.canonicalReady = '1';
        const card = picker.closest('[data-ref-activity-card]');
        // Legacy selectors are hidden on the canonical Create form. Disable them so
        // stale placeholder values cannot trigger integer validation errors.
        card.querySelectorAll('[data-ref-legacy-activity-field], [data-ref-legacy-division-field], [data-ref-legacy-mapping-field]').forEach(field => {
            field.disabled = true;
            field.removeAttribute('required');
        });
        const pkg = picker.querySelector('[data-canonical-package]');
        const section = picker.querySelector('[data-canonical-section]');
        const activity = picker.querySelector('[data-canonical-activity]');
        const search = picker.querySelector('[data-canonical-search]');
        const results = picker.querySelector('[data-canonical-results]');
        const id = picker.querySelector('[data-canonical-id]');
        const label = picker.querySelector('[data-canonical-selection]');
        const unit = card.querySelector('[data-ref-canonical-unit-field]');
        let requestNumber = 0;
        let selected = null;
        function select(item) {
            selected = item;
            id.value = item ? item.id : '';
            label.textContent = item ? `Selected: ${text(item)}` : 'No canonical activity selected.';
            if (item) label.classList.remove('text-red-700');
            if (item && unit) {
                const masterUnit = String(item.unit_name || item.unit || item.default_unit || '').trim();
                if (masterUnit && !Array.from(unit.options).some(option => option.value === masterUnit)) {
                    unit.add(new Option(masterUnit, masterUnit));
                }
                unit.value = masterUnit;
            }
            if (!item && unit) unit.value = '';
            results.classList.add('hidden');
            // The existing repeater listens for field changes to update summaries.
            id.dispatchEvent(new Event('change', { bubbles: true }));
            const title = card.querySelector('[data-ref-activity-title]');
            if (title && item) title.textContent = text(item);
        }
        const error = err => { label.textContent = err.message; label.classList.add('text-red-700'); };
        async function loadSections(choose = '') {
            if (!pkg.value) {
                fill(section, [], 'Choose a package first');
                return;
            }
            try { fill(section, await fetchRows('sections', { work_package_id: pkg.value }), 'Choose a section', choose); }
            catch (err) { error(err); }
        }
        async function loadActivities(choose = '') {
            if (!section.value) {
                fill(activity, [], 'Choose a section first');
                return;
            }
            try {
                const items = await fetchRows('activities', { work_section_id: section.value });
                fill(activity, items, 'Choose an activity', choose);
                if (choose && !activity.value) activity.append(opt({id: choose, name: `Activity #${choose}`}, true));
            } catch (err) { error(err); }
        }
        pkg.addEventListener('change', async () => { select(null); await loadSections(); await loadActivities(); });
        section.addEventListener('change', async () => { select(null); await loadActivities(); });
        activity.addEventListener('change', () => {
            const chosen = Array.from(activity.options).find(o => o.value === activity.value);
            const list = section.value
                ? cache.get(new URL(urls.activities + '?' + new URLSearchParams({ work_section_id: section.value }), location.origin).toString()) || []
                : [];
            select(list.find(item => String(item.id) === activity.value) || (chosen?.value ? {id:chosen.value,name:chosen.textContent} : null));
        });
        let debounce;
        search.addEventListener('input', () => {
            clearTimeout(debounce);
            const q = search.value.trim();
            if (q.length < 2) { results.classList.add('hidden'); return; }
            const request = ++requestNumber;
            debounce = setTimeout(async () => {
                try {
                    const items = await fetchRows('search', {q});
                    if (request !== requestNumber) return;
                    results.replaceChildren();
                    items.forEach(item => {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.className = 'block w-full border-b border-slate-100 px-3 py-2 text-left text-sm hover:bg-blue-50 focus:bg-blue-50';
                        button.textContent = text(item);
                        button.addEventListener('click', async () => {
                            search.value = text(item);
                            select(item);
                            const packageId = item.work_package_id || item.package_id || item.package?.id;
                            const sectionId = item.work_section_id || item.section_id || item.section?.id;
                            if (packageId) pkg.value = packageId;
                            await loadSections(sectionId || '');
                            await loadActivities(item.id);
                        });
                        results.append(button);
                    });
                    if (!items.length) results.textContent = 'No matching activity. Contact PMO to add it to the master.';
                    results.classList.remove('hidden');
                } catch (err) { error(err); }
            }, 250);
        });
        fetchRows('packages').then(items => fill(pkg, items, 'All packages')).then(async () => {
            await loadSections();
            if (id.value) {
                const existingId = id.value;
                // The catalogue API has no get-by-ID endpoint. Preserve the
                // submitted ID rather than querying a section-less endpoint.
                label.textContent = `Previously selected activity #${existingId}`;
            } else await loadActivities();
        }).catch(error);
    }
    function scan() { form.querySelectorAll('[data-canonical-picker]').forEach(init); }
    scan();
    const observer = new MutationObserver(scan);
    observer.observe(form, {childList: true, subtree: true});

    // Validate the essential fields of the current activity before adding another.
    // Existing repeater JS still owns creation, indexing and photo/material handling.
    form.addEventListener('click', event => {
        const addButton = event.target.closest('[data-ref-add-activity]');
        if (!addButton) return;
        const container = document.getElementById(addButton.dataset.refContainerId);
        const current = container?.querySelector('[data-ref-activity-card]:last-child');
        if (!current) return;
        const chosen = current.querySelector('[data-canonical-id]');
        const quantity = current.querySelector('[name$="[quantity_completed]"]');
        const reportingUnit = current.querySelector('[data-ref-canonical-unit-field]');
        if (!chosen?.value || !quantity?.value || Number(quantity.value) <= 0 || !reportingUnit?.value) {
            event.preventDefault();
            event.stopImmediatePropagation();
            const focus = !chosen?.value ? current.querySelector('[data-canonical-search]') :
                (!quantity?.value || Number(quantity.value) <= 0 ? quantity : reportingUnit);
            focus?.focus();
            const status = current.querySelector('[data-canonical-selection]');
            if (status && !chosen?.value) status.textContent = 'Choose a Work Activity before adding another.';
            return;
        }
        // The existing repeater appends the new card in its click handler.
        queueMicrotask(() => {
            const cards = [...container.querySelectorAll('[data-ref-activity-card]')];
            if (cards.length < 2) return;
            cards.slice(0,-1).forEach(card => {
                const body = card.querySelector('[data-ref-activity-body]');
                const toggle = card.querySelector('[data-ref-toggle-activity]');
                if (body && !body.classList.contains('hidden')) {
                    body.classList.add('hidden');
                    if (toggle) toggle.textContent = 'Expand';
                }
            });
            const latest = cards[cards.length-1];
            const body = latest.querySelector('[data-ref-activity-body]');
            if (body) body.classList.remove('hidden');
            const toggle = latest.querySelector('[data-ref-toggle-activity]');
            if (toggle) toggle.textContent = 'Collapse';
        });
    }, true);
    form.addEventListener('submit', event => {
        form.querySelectorAll('[data-ref-legacy-activity-field], [data-ref-legacy-division-field], [data-ref-legacy-mapping-field]').forEach(field => field.disabled = true);
        const missing = [...form.querySelectorAll('[data-canonical-picker]')].filter(p => !p.querySelector('[data-canonical-id]').value && !p.closest('[data-ref-activity-card]')?.querySelector('[data-ref-legacy-activity-field]')?.value);
        if (missing.length) {
            event.preventDefault();
            missing[0].querySelector('[data-canonical-search]').focus();
            missing[0].querySelector('[data-canonical-selection]').textContent = 'Select a Work Activity before saving.';
        }
    });
})();
