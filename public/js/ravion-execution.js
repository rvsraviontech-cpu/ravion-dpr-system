(() => {
    'use strict';

    const REF = {
        materialCache: new Map(),

        init() {
            document.querySelectorAll('[data-ref-repeater]')
                .forEach((repeater) => this.initRepeater(repeater));

            document.querySelectorAll('[data-ref-activity-card]')
                .forEach((card) => this.initActivityCard(card));

            this.bindHeaderControls();
            this.refreshAll();
            this.refreshMaterials();
        },

        bindHeaderControls() {
            const project = document.querySelector('[data-ref-project-field]');
            const date = document.querySelector('[data-ref-work-date-field]');

            [project, date].filter(Boolean).forEach((field) => {
                field.addEventListener('change', () => {
                    document.querySelectorAll('[data-ref-activity-card]')
                        .forEach((card) => this.filterLocationOptions(card));

                    this.refreshMaterials(true);
                    this.refreshSummary();
                });
            });
        },

        initRepeater(repeater) {
            if (repeater.dataset.refInitialized === '1') {
                return;
            }

            repeater.dataset.refInitialized = '1';

            repeater.querySelectorAll('[data-ref-add-activity]')
                .forEach((button) => {
                    button.addEventListener('click', () => this.addActivity(button));
                });
        },

        initActivityCard(card) {
            if (!card || card.dataset.refInitialized === '1') {
                return;
            }

            card.dataset.refInitialized = '1';

            const toggle = card.querySelector('[data-ref-toggle-activity]');
            const body = card.querySelector('[data-ref-activity-body]');
            const remove = card.querySelector('[data-ref-remove-activity]');

            toggle?.addEventListener('click', () => {
                const opening = body.classList.contains('hidden');
                body.classList.toggle('hidden');
                toggle.textContent = opening ? 'Collapse' : 'Expand';
            });

            remove?.addEventListener('click', () => {
                const container = card.closest('[data-ref-activity-container]');
                if (!container) return;

                const cards = container.querySelectorAll('[data-ref-activity-card]');

                if (cards.length <= 1) {
                    window.alert('At least one Work Activity is required.');
                    return;
                }

                if (!window.confirm('Remove this Work Activity?')) {
                    return;
                }

                card.remove();
                this.renumberActivities(container);
                this.refreshMaterialDuplicateState();
                this.refreshSummary();
            });

            this.initLocationCascade(card);
            this.initActivityCascade(card);
            this.initPhotoUploader(card);

            card.addEventListener('input', () => {
                this.refreshCardHeader(card);
                this.refreshSummary();
            });

            card.addEventListener('change', (event) => {
                if (event.target.matches('[data-ref-material-link]')) {
                    this.refreshMaterialDuplicateState();
                }

                this.refreshCardHeader(card);
                this.refreshSummary();
            });

            this.filterLocationOptions(card);
            this.filterActivityMappings(card, true);
            this.syncActivityAndUnit(card);
            this.refreshCardHeader(card);
        },

        addActivity(button) {
            const template = document.getElementById(button.dataset.refTemplateId);
            const container = document.getElementById(button.dataset.refContainerId);

            if (!template || !container) {
                console.warn('REF activity template/container missing.');
                return;
            }

            const index = container.querySelectorAll('[data-ref-activity-card]').length;

            const wrapper = document.createElement('div');
            wrapper.innerHTML = template.innerHTML
                .replaceAll('__INDEX__', String(index))
                .replaceAll('__NUMBER__', String(index + 1));

            const fragment = document.createDocumentFragment();
            Array.from(wrapper.children).forEach((child) => fragment.appendChild(child));
            container.appendChild(fragment);

            const cards = container.querySelectorAll('[data-ref-activity-card]');
            const card = cards[cards.length - 1];

            this.initActivityCard(card);
            this.renumberActivities(container);
            this.renderCachedMaterialsIntoCard(card);
            this.refreshMaterialDuplicateState();
            this.refreshSummary();

            card?.scrollIntoView({
                behavior: 'smooth',
                block: 'start',
            });

            document.dispatchEvent(new CustomEvent('ref:activity-added', {
                detail: { index, card },
            }));
        },

        renumberActivities(container) {
            container.querySelectorAll('[data-ref-activity-card]')
                .forEach((card, index) => {
                    card.dataset.refIndex = String(index);

                    const badge = card.querySelector('[data-ref-activity-number]');
                    if (badge) badge.textContent = String(index + 1);

                    card.querySelectorAll('[name]')
                        .forEach((field) => {
                            field.name = field.name.replace(
                                /works\[\d+\]/g,
                                `works[${index}]`
                            );
                        });

                    card.querySelectorAll('[data-ref-work-index]')
                        .forEach((element) => {
                            element.dataset.refWorkIndex = String(index);
                        });

                    this.renumberPhotos(card);
                    this.refreshCardHeader(card);
                });
        },

        initLocationCascade(card) {
            const block = card.querySelector('[data-ref-location-block]');
            const floor = card.querySelector('[data-ref-location-floor]');
            const unit = card.querySelector('[data-ref-location-unit]');
            const room = card.querySelector('[data-ref-location-room]');
            const subspace = card.querySelector('[data-ref-location-subspace]');

            block?.addEventListener('change', () => {
                this.clearSelection(floor);
                this.clearSelection(unit);
                this.clearSelection(room);
                this.clearSelection(subspace);
                this.filterLocationOptions(card);
            });

            floor?.addEventListener('change', () => {
                this.clearSelection(unit);
                this.clearSelection(room);
                this.clearSelection(subspace);
                this.filterLocationOptions(card);
            });

            unit?.addEventListener('change', () => {
                this.clearSelection(room);
                this.clearSelection(subspace);
                this.filterLocationOptions(card);
            });

            room?.addEventListener('change', () => {
                this.clearSelection(subspace);
                this.filterLocationOptions(card);
            });
        },

        filterLocationOptions(card) {
            const projectId = document.querySelector('[data-ref-project-field]')?.value || '';

            const block = card.querySelector('[data-ref-location-block]');
            const floor = card.querySelector('[data-ref-location-floor]');
            const unit = card.querySelector('[data-ref-location-unit]');
            const room = card.querySelector('[data-ref-location-room]');
            const subspace = card.querySelector('[data-ref-location-subspace]');

            if (!block) return;

            this.filterSelectOptions(block, (option) => {
                return !option.value
                    || (
                        projectId
                        && String(option.dataset.project) === String(projectId)
                    );
            });

            const blockId = block.value;

            this.filterSelectOptions(floor, (option) => {
                if (!option.value) return true;

                return Boolean(projectId)
                    && String(option.dataset.project) === String(projectId)
                    && (
                        !blockId
                        || String(option.dataset.block) === String(blockId)
                    );
            });

            const floorId = floor?.value || '';

            this.filterSelectOptions(unit, (option) => {
                if (!option.value) return true;

                return Boolean(projectId)
                    && String(option.dataset.project) === String(projectId)
                    && (
                        !blockId
                        || String(option.dataset.block) === String(blockId)
                    )
                    && (
                        !floorId
                        || String(option.dataset.floor) === String(floorId)
                    );
            });

            const unitId = unit?.value || '';

            this.filterSelectOptions(room, (option) => {
                return !option.value
                    || (
                        unitId
                        && String(option.dataset.unit) === String(unitId)
                    );
            });

            const roomId = room?.value || '';

            this.filterSelectOptions(subspace, (option) => {
                return !option.value
                    || (
                        roomId
                        && String(option.dataset.room) === String(roomId)
                    );
            });

            this.refreshCardHeader(card);
        },

        filterSelectOptions(select, predicate) {
            if (!select) return;

            Array.from(select.options).forEach((option) => {
                const visible = predicate(option);
                option.hidden = !visible;
                option.disabled = !visible && Boolean(option.value);

                if (!visible && option.selected) {
                    select.value = '';
                }
            });
        },

        clearSelection(select) {
            if (select) select.value = '';
        },

        initActivityCascade(card) {
            const division = card.querySelector('[data-ref-activity-division-field]');
            const mapping = card.querySelector('[data-ref-activity-mapping-field]');
            const activity = card.querySelector('[data-ref-activity-field]');

            division?.addEventListener('change', () => {
                this.filterActivityMappings(card, false);
                this.syncActivityAndUnit(card);
            });

            mapping?.addEventListener('change', () => {
                this.syncActivityAndUnit(card);
            });

            activity?.addEventListener('change', () => {
                this.syncActivityAndUnit(card, false);
            });
        },

        filterActivityMappings(card, preserve = true) {
            const division = card.querySelector('[data-ref-activity-division-field]');
            const mapping = card.querySelector('[data-ref-activity-mapping-field]');

            if (!mapping) return;

            const divisionId = division?.value || '';
            const current = preserve ? mapping.value : '';

            Array.from(mapping.options).forEach((option) => {
                if (!option.value) {
                    option.hidden = false;
                    option.disabled = false;
                    return;
                }

                const visible =
                    !divisionId
                    || String(option.dataset.division) === String(divisionId);

                option.hidden = !visible;
                option.disabled = !visible;
            });

            if (current) {
                const currentOption = Array.from(mapping.options)
                    .find((option) => String(option.value) === String(current));

                mapping.value = currentOption && !currentOption.disabled
                    ? current
                    : '';
            } else if (!preserve) {
                mapping.value = '';
            }
        },

        syncActivityAndUnit(card, fromMapping = true) {
            const mapping = card.querySelector('[data-ref-activity-mapping-field]');
            const activity = card.querySelector('[data-ref-activity-field]');
            const unit = card.querySelector('[data-ref-unit-field]');

            const mappingOption = mapping?.value
                ? mapping.options[mapping.selectedIndex]
                : null;

            if (fromMapping && mappingOption?.dataset.activity && activity) {
                activity.value = mappingOption.dataset.activity;
            }

            const activityOption = activity?.value
                ? activity.options[activity.selectedIndex]
                : null;

            if (unit) {
                unit.value =
                    mappingOption?.dataset.unit
                    || activityOption?.dataset.unit
                    || '';
            }

            this.refreshCardHeader(card);
        },

        initPhotoUploader(card) {
            const uploader = card.querySelector('[data-ref-photo-uploader]');
            if (!uploader || uploader.dataset.refInitialized === '1') return;

            uploader.dataset.refInitialized = '1';

            uploader.querySelector('[data-ref-add-photo]')
                ?.addEventListener('click', () => {
                    const body = uploader.querySelector('[data-ref-photo-body]');
                    const template = uploader.querySelector('[data-ref-photo-template]');

                    if (!body || !template) return;

                    const index = body.querySelectorAll('[data-ref-photo-row]').length;
                    const html = template.innerHTML
                        .replaceAll('__PHOTO_INDEX__', String(index));

                    body.insertAdjacentHTML('beforeend', html);

                    this.bindPhotoRows(uploader);
                    this.renumberPhotos(card);
                    this.refreshSummary();
                });

            this.bindPhotoRows(uploader);
        },

        bindPhotoRows(uploader) {
            uploader.querySelectorAll('[data-ref-photo-row]')
                .forEach((row) => {
                    if (row.dataset.refInitialized === '1') return;
                    row.dataset.refInitialized = '1';

                    const file = row.querySelector('[data-ref-photo-file]');
                    const remove = row.querySelector('[data-ref-remove-photo]');

                    file?.addEventListener('change', () => {
                        this.previewPhoto(row);
                        this.refreshSummary();
                    });

                    remove?.addEventListener('click', () => {
                        const body = uploader.querySelector('[data-ref-photo-body]');
                        const rows = body?.querySelectorAll('[data-ref-photo-row]') || [];

                        if (rows.length <= 1) {
                            row.querySelectorAll('input, select, textarea')
                                .forEach((field) => {
                                    if (field.type === 'file') {
                                        field.value = '';
                                    } else if (field.tagName === 'SELECT') {
                                        field.selectedIndex = 0;
                                    } else {
                                        field.value = '';
                                    }
                                });

                            this.clearPhotoPreview(row);
                        } else {
                            row.remove();
                        }

                        const card = uploader.closest('[data-ref-activity-card]');
                        this.renumberPhotos(card);
                        this.refreshSummary();
                    });
                });
        },

        previewPhoto(row) {
            const fileInput = row.querySelector('[data-ref-photo-file]');
            const file = fileInput?.files?.[0];
            const wrap = row.querySelector('[data-ref-photo-preview-wrap]');
            const image = row.querySelector('[data-ref-photo-preview]');
            const name = row.querySelector('[data-ref-photo-name]');
            const size = row.querySelector('[data-ref-photo-size]');

            if (!file) {
                this.clearPhotoPreview(row);
                return;
            }

            if (file.size > 10 * 1024 * 1024) {
                window.alert('Each Work Done photo must be 10 MB or smaller.');
                fileInput.value = '';
                this.clearPhotoPreview(row);
                return;
            }

            const objectUrl = URL.createObjectURL(file);

            if (image) {
                image.src = objectUrl;
                image.onload = () => URL.revokeObjectURL(objectUrl);
            }

            if (name) name.textContent = file.name;
            if (size) size.textContent = `${(file.size / 1024 / 1024).toFixed(2)} MB`;
            wrap?.classList.remove('hidden');
        },

        clearPhotoPreview(row) {
            row.querySelector('[data-ref-photo-preview-wrap]')?.classList.add('hidden');

            const image = row.querySelector('[data-ref-photo-preview]');
            if (image) image.removeAttribute('src');

            const name = row.querySelector('[data-ref-photo-name]');
            const size = row.querySelector('[data-ref-photo-size]');

            if (name) name.textContent = '';
            if (size) size.textContent = '';
        },

        renumberPhotos(card) {
            if (!card) return;

            const workIndex = Number(card.dataset.refIndex || 0);

            card.querySelectorAll('[data-ref-photo-row]')
                .forEach((row, photoIndex) => {
                    row.querySelectorAll('[name]')
                        .forEach((field) => {
                            field.name = field.name
                                .replace(/works\[\d+\]/g, `works[${workIndex}]`)
                                .replace(
                                    /\[photos\]\[\d+\]/g,
                                    `[photos][${photoIndex}]`
                                );
                        });
                });
        },

        async refreshMaterials(force = false) {
            const form = document.querySelector('[data-ref-work-done-form]');
            const projectId = document.querySelector('[data-ref-project-field]')?.value || '';
            const workDate = document.querySelector('[data-ref-work-date-field]')?.value || '';
            const endpoint = form?.dataset.refMaterialsUrl || '';

            if (!projectId || !workDate || !endpoint) {
                document.querySelectorAll('[data-ref-material-selector]')
                    .forEach((selector) => {
                        selector.querySelector('[data-ref-material-list]').innerHTML = '';
                        const empty = selector.querySelector('[data-ref-material-empty]');
                        if (empty) {
                            empty.textContent = 'Select Project and Work Date to load Material Consumed records.';
                            empty.classList.remove('hidden');
                        }
                    });
                return;
            }

            const cacheKey = `${projectId}|${workDate}`;

            if (!force && this.materialCache.has(cacheKey)) {
                this.renderMaterials(this.materialCache.get(cacheKey));
                return;
            }

            document.querySelectorAll('[data-ref-material-loading]')
                .forEach((el) => el.classList.remove('hidden'));

            try {
                const url = new URL(endpoint, window.location.origin);
                url.searchParams.set('project_id', projectId);
                url.searchParams.set('work_date', workDate);

                const response = await fetch(url.toString(), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error(`Material lookup failed (${response.status})`);
                }

                const payload = await response.json();
                const materials = Array.isArray(payload.data) ? payload.data : [];

                this.materialCache.set(cacheKey, materials);
                this.renderMaterials(materials);
            } catch (error) {
                console.error(error);

                document.querySelectorAll('[data-ref-material-selector]')
                    .forEach((selector) => {
                        selector.querySelector('[data-ref-material-list]').innerHTML = '';

                        const empty = selector.querySelector('[data-ref-material-empty]');
                        if (empty) {
                            empty.textContent = 'Unable to load Material Consumed records. You can save Work Done and link materials later.';
                            empty.classList.remove('hidden');
                        }
                    });
            } finally {
                document.querySelectorAll('[data-ref-material-loading]')
                    .forEach((el) => el.classList.add('hidden'));
            }
        },

        renderCachedMaterialsIntoCard(card) {
            const projectId = document.querySelector('[data-ref-project-field]')?.value || '';
            const workDate = document.querySelector('[data-ref-work-date-field]')?.value || '';
            const key = `${projectId}|${workDate}`;

            if (this.materialCache.has(key)) {
                this.renderMaterials(
                    this.materialCache.get(key),
                    card.querySelector('[data-ref-material-selector]')
                );
            }
        },

        renderMaterials(materials, onlySelector = null) {
            const selectors = onlySelector
                ? [onlySelector]
                : Array.from(document.querySelectorAll('[data-ref-material-selector]'));

            selectors.filter(Boolean).forEach((selector) => {
                const list = selector.querySelector('[data-ref-material-list]');
                const empty = selector.querySelector('[data-ref-material-empty]');
                const card = selector.closest('[data-ref-activity-card]');
                const workIndex = Number(card?.dataset.refIndex || 0);

                let selected = [];

                try {
                    selected = JSON.parse(selector.dataset.refSelectedMaterials || '[]')
                        .map(String);
                } catch (_) {
                    selected = [];
                }

                const checkedNow = Array.from(
                    selector.querySelectorAll('[data-ref-material-link]:checked')
                ).map((input) => String(input.value));

                selected = Array.from(new Set([...selected, ...checkedNow]));

                list.innerHTML = '';

                if (!materials.length) {
                    if (empty) {
                        empty.textContent = 'No eligible Material Consumed records are available for the selected Project and Date.';
                        empty.classList.remove('hidden');
                    }
                    return;
                }

                empty?.classList.add('hidden');

                materials.forEach((record) => {
                    const label = document.createElement('label');
                    label.className = 'flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 bg-white p-4 hover:bg-gray-50';
                    label.dataset.refMaterialRecord = '1';

                    const checked = selected.includes(String(record.id));

                    const input = document.createElement('input');
                    input.type = 'checkbox';
                    input.name = `works[${workIndex}][material_consumed_ids][]`;
                    input.value = record.id;
                    input.checked = checked;
                    input.className = 'mt-1 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500';
                    input.dataset.refMaterialLink = '1';

                    const content = document.createElement('div');
                    content.className = 'min-w-0 flex-1';

                    const title = document.createElement('div');
                    title.className = 'font-semibold text-gray-800';
                    title.textContent = `Material Consumption #${record.id}`;

                    const details = document.createElement('div');
                    details.className = 'mt-1 text-sm text-gray-700';
                    details.textContent = record.display_text || 'Material details not available';

                    const meta = document.createElement('div');
                    meta.className = 'mt-1 text-xs text-gray-500';
                    meta.textContent = [
                        record.consumed_date || '',
                        record.consumed_time || '',
                        record.status || '',
                    ].filter(Boolean).join(' • ');

                    content.append(title, details, meta);
                    label.append(input, content);
                    list.appendChild(label);
                });
            });

            this.refreshMaterialDuplicateState();
            this.refreshSummary();
        },

        refreshMaterialDuplicateState() {
            const allChecked = Array.from(
                document.querySelectorAll('[data-ref-material-link]:checked')
            );

            const selectedByCard = new Map();

            allChecked.forEach((input) => {
                const card = input.closest('[data-ref-activity-card]');
                if (!card) return;

                if (!selectedByCard.has(card)) {
                    selectedByCard.set(card, new Set());
                }

                selectedByCard.get(card).add(String(input.value));
            });

            document.querySelectorAll('[data-ref-material-link]')
                .forEach((input) => {
                    const card = input.closest('[data-ref-activity-card]');
                    const value = String(input.value);

                    const usedElsewhere = Array.from(selectedByCard.entries())
                        .some(([otherCard, values]) => {
                            return otherCard !== card && values.has(value);
                        });

                    if (!input.checked) {
                        input.disabled = usedElsewhere;
                        input.closest('[data-ref-material-record]')
                            ?.classList.toggle('opacity-50', usedElsewhere);
                    }
                });
        },

        refreshCardHeader(card) {
            const activity = card.querySelector('[data-ref-activity-field]');
            const subspace = card.querySelector('[data-ref-subspace-field]');
            const room = card.querySelector('[data-ref-room-field]');
            const unit = card.querySelector('[data-ref-unit-location-field]');
            const floor = card.querySelector('[data-ref-floor-field]');
            const block = card.querySelector('[data-ref-block-field]');
            const statusField = card.querySelector('[data-ref-execution-status-field]');

            const title = card.querySelector('[data-ref-activity-title]');
            const location = card.querySelector('[data-ref-activity-location]');
            const status = card.querySelector('[data-ref-activity-status]');

            const activityText = this.selectedText(activity);

            if (title) {
                title.textContent =
                    activityText
                    || `Work Activity ${Number(card.dataset.refIndex || 0) + 1}`;
            }

            const locationText =
                this.selectedText(subspace)
                || this.selectedText(room)
                || this.selectedText(unit)
                || this.selectedText(floor)
                || this.selectedText(block)
                || 'Location not selected';

            if (location) location.textContent = locationText;

            const statusText = this.selectedText(statusField);

            if (status) {
                status.textContent = statusText || '';
                status.classList.toggle('hidden', !statusText);
                status.classList.toggle('inline-flex', Boolean(statusText));
            }
        },

        selectedText(select) {
            if (!select || !select.value) return '';
            return select.options[select.selectedIndex]?.text?.trim() || '';
        },

        refreshSummary() {
            const cards = document.querySelectorAll('[data-ref-activity-card]');

            let materialTotal = 0;
            let photoTotal = 0;

            cards.forEach((card) => {

                materialTotal += card.querySelectorAll(
                    '[data-ref-material-link]:checked'
                ).length;

                card.querySelectorAll('[data-ref-photo-file]')
                    .forEach((field) => {
                        photoTotal += field.files?.length || 0;
                    });
            });

            this.setSummary('activities', cards.length);
            this.setSummary('materials', materialTotal);
            this.setSummary('photos', photoTotal);
        },

        setSummary(key, value) {
            document.querySelectorAll(`[data-ref-summary="${key}"]`)
                .forEach((element) => {
                    element.textContent = String(value);
                });
        },

        refreshAll() {
            document.querySelectorAll('[data-ref-activity-container]')
                .forEach((container) => {
                    this.renumberActivities(container);
                });

            document.querySelectorAll('[data-ref-activity-card]')
                .forEach((card) => {
                    this.initActivityCard(card);
                });

            this.refreshSummary();
        },
    };

    window.RavionExecution = REF;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => REF.init());
    } else {
        REF.init();
    }
})();
