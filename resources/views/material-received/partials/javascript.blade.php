<script>
document.addEventListener('DOMContentLoaded', function () {
    const body = document.getElementById('material-items-body');
    const addRowButton = document.getElementById('add-item-row');
    const projectSelect = document.getElementById('project_id');
    const blockSelect = document.getElementById('project_block_id');
    const floorSelect = document.getElementById('project_floor_id');
    const unitSelect = document.getElementById('project_unit_id');
    const photoRows = document.getElementById('photo-rows');
    const addPhotoRowButton = document.getElementById('add-photo-row');
    const receiptSource = document.getElementById('receipt_source');
    const purchaseOrderSelect = document.getElementById('purchase_order_id');
    const purchaseOrderWrap = document.getElementById('purchase-order-source-wrap');
    const loadPurchaseOrderButton = document.getElementById('load-purchase-order');

    const materialTypeOptions = @json($materialTypeOptionsForJs);
    const brandOptions = @json($brandOptionsForJs);
    const specificationOptions = @json($specificationOptionsForJs);
    const gradeOptions = @json($gradeOptionsForJs);
    const photoTypes = @json($photoTypes);
    const bibleCatalogueUrl = @json(route('materials.bible-catalogue'));
    const bibleSearchUrl = @json(route('materials.product-search'));
    const bibleDependencyTemplate = @json(route('materials.product-dependencies', ['materialType' => '__PRODUCT__']));
    const bibleCache = new Map();
    const bibleProducts = new Map();
    const bibleRequest = async (url) => {
        const response = await fetch(url, {headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}});
        if (!response.ok) throw new Error('Catalogue request failed (' + response.status + ')');
        return (await response.json()).data || [];
    };
    const bibleUrl = (level, params={}) => {
        const url = new URL(bibleCatalogueUrl, window.location.origin);
        url.searchParams.set('level',level);
        Object.entries(params).forEach(([key,value])=>{if(value)url.searchParams.set(key,value);});
        return url;
    };

    const directMode = document.getElementById('direct-entry-host') !== null;
    const entryHost = document.getElementById('direct-entry-host');
    const summaryBody = document.getElementById('direct-summary-body');
    let editingDirectRow = null;
    let rowIndex = body.querySelectorAll('.material-item-row').length;
    let photoIndex = photoRows ? photoRows.querySelectorAll('.photo-row').length : 0;

    function option(value, label, selected = false) {
        return new Option(label, value, selected, selected);
    }

    function rebuildSelect(select, placeholder, values, selectedValue = '') {
        select.innerHTML = '';
        select.add(option('', placeholder));
        values.forEach(item => select.add(option(String(item.id), item.name, String(item.id) === String(selectedValue))));
    }

    function materialById(id) {
        return bibleProducts.get(String(id)) || materialTypeOptions.find(item => String(item.id) === String(id));
    }

    function setMode(row, mode, clearOther = false) {
        const entry = row.querySelector('.entry-mode-input');
        const existingPanel = row.querySelector('.existing-panel');
        const temporaryPanel = row.querySelector('.temporary-panel');
        const existingFields = row.querySelectorAll('.existing-field');
        const temporaryFields = row.querySelectorAll('.temporary-field');
        const tempName = row.querySelector('.temporary-material-name');
        const tempUnit = row.querySelector('.temporary-unit-select');
        const typeSelect = row.querySelector('.material-type-select');

        entry.value = mode;
        existingPanel.classList.toggle('hidden', mode !== 'existing');
        temporaryPanel.classList.toggle('hidden', mode !== 'temporary');
        existingFields.forEach(el => el.classList.toggle('hidden', mode !== 'existing'));
        temporaryFields.forEach(el => el.classList.toggle('hidden', mode !== 'temporary'));

        // Direct Receipt uses explicit Add/Save validation. Hidden controls must not
        // block the browser's submit event before our handler can run.
        tempName.required = !directMode && mode === 'temporary';
        tempUnit.required = !directMode && mode === 'temporary';
        typeSelect.required = !directMode && mode === 'existing';

        if (clearOther && mode === 'temporary') {
            typeSelect.value = '';
            row.querySelector('.material-search-input').value = '';
            row.querySelector('.category-display').value = 'Pending Classification';
            row.querySelector('.material-variant-id-input').value = '';
            row.querySelectorAll('.bible-selector select').forEach(el=>el.value='');
            row.querySelector('.brand-select').innerHTML = '<option value="">Brand</option>';
            row.querySelector('.specification-select').innerHTML = '<option value="">Specification</option>';
            row.querySelector('.grade-select').innerHTML = '<option value="">Grade / Rating</option>';
            row.querySelector('.unit-id-input').value = '';
            row.querySelector('.unit-name-input').value = '';
        }

        if (clearOther && mode === 'existing') {
            tempName.value = '';
            row.querySelector('[name$="[temporary_brand]"]').value = '';
            row.querySelector('[name$="[temporary_specification]"]').value = '';
            row.querySelector('[name$="[temporary_grade]"]').value = '';
            row.querySelector('[name$="[temporary_classification_notes]"]').value = '';
            tempUnit.value = '';
            row.querySelector('.unit-id-input').value = '';
            row.querySelector('.category-display').value = '';
        }

        refreshPhotoItemOptions();
    }

    // Presentation-only relocation. Retains original inputs, names, classes and event hooks.
    function arrangeDirectReceiptRow(row) {
        if (!directMode || row.querySelector('.ravion-receipt-grid')) return;
        const productCell = row.querySelector('td:nth-child(2)');
        if (!productCell) return;
        const existing = productCell.querySelector('.existing-panel');
        const selector = existing?.querySelector('.bible-selector');
        if (!existing || !selector) return;
        const grid = document.createElement('div');
        grid.className = 'ravion-receipt-grid';
        const lines = [1,2,3,4].map(n => {
            const div = document.createElement('div');
            div.className = 'ravion-receipt-line ravion-receipt-line-' + n;
            grid.appendChild(div);
            return div;
        });
        function field(line, label, element) {
            if (!element) return;
            const wrap = document.createElement('div');
            wrap.className = 'ravion-receipt-field';
            const title = document.createElement('label');
            title.textContent = label;
            wrap.appendChild(title);
            wrap.appendChild(element);
            lines[line-1].appendChild(wrap);
        }
        field(1,'Search Product',existing.querySelector('.material-search-input'));
        const searchField = lines[0].firstElementChild;
        const results = existing.querySelector('.material-search-results');
        if (results) searchField.appendChild(results);
        field(1,'Trade Category',selector.querySelector('.bible-trade'));
        field(1,'Main Category',selector.querySelector('.bible-main'));
        field(1,'Sub Category',selector.querySelector('.bible-sub'));
        field(2,'Item / Product',selector.querySelector('.bible-product'));
        field(2,'Sub Item',selector.querySelector('.bible-subitem'));
        field(2,'Size / Variant',selector.querySelector('.bible-variant'));
        const specCell = row.querySelector('td:nth-child(3)');
        // One aligned column with Specification and Grade side by side.
        // Both original select elements are moved, retaining their names and listeners.
        const specificationSelect = specCell?.querySelector('.specification-select');
        const gradeSelect = specCell?.querySelector('.grade-select');
        if (specificationSelect || gradeSelect) {
            const pair = document.createElement('div');
            pair.className = 'ravion-spec-grade-pair';
            if (specificationSelect) pair.appendChild(specificationSelect);
            if (gradeSelect) pair.appendChild(gradeSelect);
            field(2, 'Specification / Grade', pair);
        }
        field(2,'Brand',row.querySelector('td:nth-child(4) .brand-select'));
        const quantities = [
            ['Receive Now','.quantity-input'],['Unit','.transaction-unit-select'],
            ['Accepted','.accepted-input'],['Short','.short-input'],
            ['Damaged','.damaged-input'],['Rejected','.rejected-input']
        ];
        quantities.forEach(([label,selector])=>field(3,label,row.querySelector(selector)));
        field(4,'Remarks',row.querySelector('input[name$="[remarks]"]'));
        const status = existing.querySelector('.bible-status');
        if (status) grid.appendChild(status);
        // Keep temporary panel and hidden backing fields in their original cells.
        productCell.appendChild(grid);
    }

    function initializeRow(row) {
        arrangeDirectReceiptRow(row);
        const searchInput = row.querySelector('.material-search-input');
        const searchResults = row.querySelector('.material-search-results');
        const typeSelect = row.querySelector('.material-type-select');
        const category = row.querySelector('.category-display');
        const brand = row.querySelector('.brand-select');
        const specification = row.querySelector('.specification-select');
        const grade = row.querySelector('.grade-select');
        const unitId = row.querySelector('.unit-id-input');
        const unitName = row.querySelector('.unit-name-input');
        const temporaryUnit = row.querySelector('.temporary-unit-select');
        const quantity = row.querySelector('.quantity-input');
        const transactionUnit = row.querySelector('.transaction-unit-select');
        const accepted = row.querySelector('.accepted-input');
        const shortQty = row.querySelector('.short-input');
        const damaged = row.querySelector('.damaged-input');
        const rejected = row.querySelector('.rejected-input');

        const initialMode = row.querySelector('.entry-mode-input').value || 'existing';
        const initialTypeId = typeSelect.value;
        const initialBrandId = @json(null);
        const initialSpecId = @json(null);
        const initialGradeId = @json(null);

        // Preserve server-rendered selected values before rebuilding filtered selects.
        const serverBrand = row.querySelector('[name$="[brand_master_id]"] option:checked')?.value || '';
        const serverSpec = row.querySelector('[name$="[material_specification_id]"] option:checked')?.value || '';
        const serverGrade = row.querySelector('[name$="[material_grade_id]"] option:checked')?.value || '';

        function updateDependencies(preserve = {}) {
            const selected = materialById(typeSelect.value);
            category.value = selected?.group || '';
            // Product Master unit is only the default. Never overwrite a saved/PO transaction unit.
            unitName.value = selected?.unit_name || '';
            if (transactionUnit) {
                if (!transactionUnit.value) {
                    transactionUnit.value = selected?.unit_id || '';
                }
                unitId.value = transactionUnit.value || '';
            } else {
                unitId.value = selected?.unit_id || '';
            }

            rebuildSelect(brand, 'Brand', brandOptions.filter(x => String(x.material_type_id) === String(typeSelect.value)), preserve.brandId || '');
            rebuildSelect(specification, 'Specification', specificationOptions.filter(x => String(x.material_type_id) === String(typeSelect.value)), preserve.specificationId || '');
            rebuildSelect(grade, 'Grade / Rating', gradeOptions.filter(x => String(x.material_type_id) === String(typeSelect.value)), preserve.gradeId || '');

            if (selected) searchInput.value = selected.name;
            refreshPhotoItemOptions();
        }

        // Direct Receipt: approved Bible search and category navigation. PO remains unchanged.
        if (directMode) {
            const trade = row.querySelector('.bible-trade');
            const main = row.querySelector('.bible-main');
            const sub = row.querySelector('.bible-sub');
            const product = row.querySelector('.bible-product');
            const subItem = row.querySelector('.bible-subitem');
            const variant = row.querySelector('.bible-variant');
            const variantId = row.querySelector('.material-variant-id-input');
            const status = row.querySelector('.bible-status');
            let requestSeq = 0;
            let searchTimer;
            const message = text => {if(status) status.textContent=text;};
            const fill = (select,placeholder,values,selected='') => {
                if (!select) return;
                select.replaceChildren(new Option(placeholder,''));
                values.forEach(v=>select.add(new Option(v.name,String(v.id),false,String(v.id)===String(selected))));
            };
            const fetchLevel = async (level,params) => {
                const key=JSON.stringify([level,params]);
                if (!bibleCache.has(key)) bibleCache.set(key,bibleRequest(bibleUrl(level,params)).catch(e=>{bibleCache.delete(key);throw e;}));
                return bibleCache.get(key);
            };
            const clearProduct = () => {
                ++requestSeq; // Invalidate any pending product-detail response.
                typeSelect.value=''; variantId.value='';
                row._bibleVariants = [];
                category.value = '';
                unitName.value = '';
                if (transactionUnit) transactionUnit.value = '';
                unitId.value = '';
                searchInput.value = '';
                searchResults.classList.add('hidden');
                brand.replaceChildren(new Option('Brand',''));
                specification.replaceChildren(new Option('Specification',''));
                grade.replaceChildren(new Option('Grade / Rating',''));
                [brand, specification, grade].forEach(el => { el.disabled = true; });
                fill(subItem,'Sub Item',[]); fill(variant,'Size / Variant',[]);
                refreshPhotoItemOptions();
            };
            // The server renders global master options for legacy/PO compatibility.
            // In DIRECT mode, never expose those options before a product is chosen.
            if (!initialTypeId || initialMode !== 'existing') {
                brand.replaceChildren(new Option('Brand',''));
                specification.replaceChildren(new Option('Specification',''));
                grade.replaceChildren(new Option('Grade / Rating',''));
                [brand, specification, grade].forEach(el => { el.disabled = true; });
            }
            // Resolve the selected Bible product back to its classification path.
            // Await each level so the selected value exists before assigning it.
            const syncBiblePath = async (details, seq) => {
                const mappings = details.bible_mappings || [];
                const mapping = mappings.find(m => m.sub_category && (m.trade || m.trade_category || m.main_category))
                    || mappings[0];
                if (!mapping) {
                    fill(trade,'Trade Category',[]);
                    fill(main,'Main Category',[]);
                    fill(sub,'Sub Category',[]);
                    fill(product,'Item / Product',[]);
                    return;
                }
                const getId = (...values) => values.find(v => v !== undefined && v !== null && v !== '') || '';
                const tradeId = getId(mapping.trade_id, mapping.trade_category_id, mapping.trade?.id, mapping.trade_category?.id,
                    mapping.main_category?.trade_id, mapping.sub_category?.main_category?.trade_id);
                const mainId = getId(mapping.main_category_id, mapping.main_id, mapping.main_category?.id,
                    mapping.sub_category?.main_category_id, mapping.sub_category?.main_category?.id);
                const subId = getId(mapping.sub_category_id, mapping.sub_id, mapping.sub_category?.id);
                const alive = () => seq === requestSeq && String(typeSelect.value) === String(details.id);
                const trades = await fetchLevel('trades',{});
                if (!alive()) return;
                fill(trade,'Trade Category',trades,tradeId);
                // Some Bible mappings expose only a subcategory. Recover the parent
                // using the catalogue hierarchy instead of displaying a wrong path.
                let selectedTrade = tradeId;
                let selectedMain = mainId;
                if (!selectedTrade && selectedMain) {
                    for (const t of trades) {
                        const mains = await fetchLevel('main_categories',{trade_id:t.id});
                        if (!alive()) return;
                        if (mains.some(m => String(m.id) === String(selectedMain))) { selectedTrade=t.id; break; }
                    }
                    trade.value = String(selectedTrade || '');
                }
                if (!selectedTrade) {
                    fill(main,'Main Category',[]);
                    fill(sub,'Sub Category',[]);
                    fill(product,'Item / Product',[]);
                    message('Product selected, but its Bible trade mapping could not be resolved.');
                    return;
                }
                const mains = await fetchLevel('main_categories',{trade_id:selectedTrade});
                if (!alive()) return;
                if (!selectedMain && subId) {
                    for (const m of mains) {
                        const subs = await fetchLevel('sub_categories',{main_category_id:m.id});
                        if (!alive()) return;
                        if (subs.some(x => String(x.id) === String(subId))) { selectedMain=m.id; break; }
                    }
                }
                fill(main,'Main Category',mains,selectedMain);
                if (!selectedMain) {
                    fill(sub,'Sub Category',[]); fill(product,'Item / Product',[]);
                    return;
                }
                const subs = await fetchLevel('sub_categories',{main_category_id:selectedMain});
                if (!alive()) return;
                fill(sub,'Sub Category',subs,subId);
                if (!subId) { fill(product,'Item / Product',[]); return; }
                const products = await fetchLevel('products',{sub_category_id:subId});
                if (!alive()) return;
                fill(product,'Item / Product',products,details.id);
                if (!product.value) {
                    // Do not leave a different product visible when the mapping is stale.
                    product.add(new Option(details.name,String(details.id),true,true));
                }
            };
            const loadDetails = async (id,preserve=false) => {
                if (!id) return;
                const seq=++requestSeq;
                message('Loading product specifications...');
                try {
                    const details=await bibleRequest(bibleDependencyTemplate.replace('__PRODUCT__',encodeURIComponent(id)));
                    if(seq!==requestSeq || String(typeSelect.value)!==String(id))return;
                    const current={brand:preserve?brand.value:'',spec:preserve?specification.value:'',grade:preserve?grade.value:'',variant:preserve?variantId.value:''};
                    const normalized={id:details.id,name:details.name,group:details.bible_mappings?.map(x=>x.sub_category?.name).filter(Boolean).join(' / ')||'',unit_id:details.unit?.id,unit_name:details.unit?.name};
                    bibleProducts.set(String(id),normalized);
                    searchInput.value=details.name;
                    category.value=normalized.group;
                    try { await syncBiblePath(details,seq); }
                    catch (pathError) { message('Product loaded, but category mapping could not be loaded: ' + pathError.message); }
                    if (seq !== requestSeq) return;
                    fill(brand,'Brand',details.brands||[],current.brand);
                    fill(specification,'Specification',details.specifications||[],current.spec);
                    fill(grade,'Grade / Rating',details.grades||[],current.grade);
                    [brand, specification, grade].forEach(el => { el.disabled = false; });
                    const variants=details.variants||[];
                    row._bibleVariants=variants;
                    const subs=[...new Set(variants.map(v=>v.sub_item).filter(Boolean))].map((name,i)=>({id:name,name}));
                    fill(subItem,'Sub Item',subs);
                    const chosen=variants.find(v=>String(v.id)===String(current.variant));
                    if(chosen?.sub_item)subItem.value=chosen.sub_item;
                    const refreshVariants=()=>{
                        const filtered=variants.filter(v=>!subItem.value || v.sub_item===subItem.value);
                        fill(variant,'Size / Variant',filtered.map(v=>({id:v.id,name:v.size_specification||v.name})),current.variant);
                    };
                    refreshVariants();
                    if(!preserve)variantId.value='';
                    if(transactionUnit && !transactionUnit.value)transactionUnit.value=chosen?.unit?.id||details.unit?.id||'';
                    unitId.value=transactionUnit?.value||details.unit?.id||'';
                    unitName.value=details.unit?.name||'';
                    message('Approved Bible product selected. Choose sub-item, size, specification and brand as applicable.');
                    refreshPhotoItemOptions();
                } catch(e) {message('Unable to load product details. '+e.message);}
            };
            const choose = item => {
                // Reset product-dependent fields before requesting new definitions.
                brand.replaceChildren(new Option('Brand',''));
                specification.replaceChildren(new Option('Specification',''));
                grade.replaceChildren(new Option('Grade / Rating',''));
                [brand, specification, grade].forEach(el => { el.disabled = true; });
                if(!Array.from(typeSelect.options).some(o=>String(o.value)===String(item.id)))typeSelect.add(new Option(item.name,String(item.id)));
                typeSelect.value=String(item.id);
                searchInput.value=item.name;
                variantId.value='';
                if(transactionUnit)transactionUnit.value='';
                fill(trade,'Trade Category',[]);
                fill(main,'Main Category',[]);
                fill(sub,'Sub Category',[]);
                fill(product,'Item / Product',[]);
                searchResults.classList.add('hidden');
                loadDetails(item.id);
            };
            const search = async () => {
                const term=searchInput.value.trim();
                if(term.length<2){searchResults.classList.add('hidden');return;}
                const seq=++requestSeq;
                message('Searching approved Bible...');
                try {
                    const url=new URL(bibleSearchUrl,window.location.origin);
                    url.searchParams.set('q',term);url.searchParams.set('catalogue_only','1');
                    const matches=await bibleRequest(url);
                    if(seq!==requestSeq)return;
                    searchResults.replaceChildren();
                    if(!matches.length){const empty=document.createElement('div');empty.className='p-3 text-xs text-slate-500';empty.textContent='No approved Bible product found.';searchResults.appendChild(empty);}
                    matches.slice(0,30).forEach(item=>{
                        const btn=document.createElement('button');btn.type='button';btn.className='block w-full border-b px-3 py-2 text-left text-sm hover:bg-blue-50';
                        btn.textContent=item.name||item.material_type_name||item.label||('Product #'+item.id);
                        btn.addEventListener('click',()=>choose({id:item.id,name:btn.textContent}));searchResults.appendChild(btn);
                    });
                    searchResults.classList.remove('hidden');message(matches.length+' matching Bible products');
                }catch(e){message('Search unavailable: '+e.message);}
            };
            searchInput.addEventListener('input',()=>{
                // Typed text must not silently retain a previously selected product.
                const typed = searchInput.value;
                clearProduct();
                fill(trade,'Trade Category',[]);
                fill(main,'Main Category',[]);
                fill(sub,'Sub Category',[]);
                fill(product,'Item / Product',[]);
                fetchLevel('trades',{}).then(data=>{if(!typeSelect.value)fill(trade,'Trade Category',data);});
                searchInput.value = typed;
                clearTimeout(searchTimer);searchTimer=setTimeout(search,250);
            });
            searchInput.addEventListener('focus',()=>{if(searchInput.value.trim().length>=2 && !typeSelect.value)search();});
            trade?.addEventListener('change',async()=>{
                clearProduct();fill(main,'Main Category',[]);fill(sub,'Sub Category',[]);fill(product,'Item / Product',[]);
                if(!trade.value)return;
                try{fill(main,'Main Category',await fetchLevel('main_categories',{trade_id:trade.value}));}catch(e){message(e.message);}
            });
            main?.addEventListener('change',async()=>{
                clearProduct();fill(sub,'Sub Category',[]);fill(product,'Item / Product',[]);
                if(!main.value)return;
                try{fill(sub,'Sub Category',await fetchLevel('sub_categories',{main_category_id:main.value}));}catch(e){message(e.message);}
            });
            sub?.addEventListener('change',async()=>{
                clearProduct();fill(product,'Item / Product',[]);
                if(!sub.value)return;
                try{fill(product,'Item / Product',await fetchLevel('products',{sub_category_id:sub.value}));}catch(e){message(e.message);}
            });
            product?.addEventListener('change',()=>{
                const selected=product.selectedOptions[0];
                if(selected?.value)choose({id:selected.value,name:selected.textContent});else clearProduct();
            });
            subItem?.addEventListener('change',()=>{
                variantId.value='';
                const variants=(row._bibleVariants||[]).filter(v=>!subItem.value||v.sub_item===subItem.value);
                fill(variant,'Size / Variant',variants.map(v=>({id:v.id,name:v.size_specification||v.name})));
            });
            variant?.addEventListener('change',()=>{
                variantId.value=variant.value;
                const selected=(row._bibleVariants||[]).find(v=>String(v.id)===String(variant.value));
                if(selected?.unit?.id && transactionUnit && !transactionUnit.value){transactionUnit.value=selected.unit.id;unitId.value=selected.unit.id;}
            });
            fetchLevel('trades',{}).then(data=>{ if (!typeSelect.value) fill(trade,'Trade Category',data); }).catch(e=>message(e.message));
            if(initialTypeId && initialMode === 'existing'){
                // Historical draft products remain editable even when no longer in the current catalogue.
                loadDetails(initialTypeId,true);
            }
        } else {
            // PO products are inherited from their issued purchase orders.
            typeSelect.addEventListener('change',()=>updateDependencies());
        }
        document.addEventListener('click',event=>{
            if(!row.contains(event.target))searchResults.classList.add('hidden');
        });

        row.querySelector('.enable-temporary-material').addEventListener('click', () => {
            setMode(row, 'temporary', true);
            row.querySelector('.temporary-material-name').focus();
        });

        row.querySelector('.use-existing-material').addEventListener('click', () => {
            setMode(row, 'existing', true);
            searchInput.focus();
        });

        temporaryUnit.addEventListener('change', () => { unitId.value = temporaryUnit.value; if (transactionUnit) transactionUnit.value = temporaryUnit.value; });
        transactionUnit?.addEventListener('change', () => unitId.value = transactionUnit.value);

        let acceptedAuto = !accepted?.value;
        accepted?.addEventListener('input', () => acceptedAuto = false);
        quantity?.addEventListener('input', () => {
            if (accepted && acceptedAuto) accepted.value = quantity.value;
        });

        row.querySelector('.temporary-material-name').addEventListener('input', refreshPhotoItemOptions);

        row.querySelector('.remove-item-row').addEventListener('click', function () {
            if (directMode) { removeDirectRow(row); return; }
            if (body.querySelectorAll('.material-item-row').length <= 1) {
                alert('At least one material row is required.');
                return;
            }
            row.remove();
            renumberMaterialRows();
            refreshPhotoItemOptions();
        });

        document.addEventListener('click', event => {
            if (!row.contains(event.target)) searchResults.classList.add('hidden');
        });

        setMode(row, initialMode, false);
        if (transactionUnit?.value) unitId.value = transactionUnit.value;

        if (initialMode === 'temporary') {
            temporaryUnit.value = unitId.value || '';
            category.value = 'Pending Classification';
        } else if (initialTypeId && !directMode) {
            updateDependencies({
                brandId: serverBrand,
                specificationId: serverSpec,
                gradeId: serverGrade,
            });
        }
    }

    function buildNewRow(index) {
        const row = (body.querySelector('.material-item-row') || entryHost?.querySelector('.material-item-row')).cloneNode(true);
        row.dataset.rowIndex = index;

        row.querySelectorAll('input, select').forEach(field => {
            if (field.classList.contains('entry-mode-input')) {
                field.value = 'existing';
            } else if (field.tagName === 'SELECT') {
                field.selectedIndex = 0;
            } else if (field.type !== 'button') {
                field.value = '';
            }
            field.required = false;
            field.disabled = false;
        });

        row.querySelectorAll('.bible-selector select').forEach(el=>el.replaceChildren(new Option(el.options[0]?.textContent||'Select','')));
        row.querySelector('.material-search-results').innerHTML = '';
        row.querySelector('.material-search-results').classList.add('hidden');
        return row;
    }

    function renumberMaterialRows() {
        body.querySelectorAll('.material-item-row').forEach((row, index) => {
            row.dataset.rowIndex = index;
            row.querySelector('.row-number').textContent = index + 1;
            row.querySelectorAll('[name^="items["]').forEach(field => {
                field.name = field.name.replace(/^items\[\d+\]/, `items[${index}]`);
            });
        });
        rowIndex = body.querySelectorAll('.material-item-row').length;
    }

    function materialItemOptions() {
        return Array.from(body.querySelectorAll('.material-item-row')).map((row, index) => {
            const mode = row.querySelector('.entry-mode-input').value;
            let label = 'Material not selected';
            if (mode === 'temporary') {
                label = row.querySelector('.temporary-material-name').value.trim() || 'Temporary material';
                label += ' — Pending Classification';
            } else {
                const type = materialById(row.querySelector('.material-type-select').value);
                if (type) label = type.name;
                else label = row.querySelector('.material-search-input').value.trim() || label;
            }
            return {value: String(index), label: `Item ${index + 1} — ${label}`};
        });
    }

    function refreshPhotoItemOptions() {
        if (!photoRows) return;
        const options = materialItemOptions();
        photoRows.querySelectorAll('.photo-item-select').forEach(select => {
            const selected = select.value !== '' ? select.value : (select.dataset.selected || '');
            select.innerHTML = '';
            select.add(new Option('General / Whole Receipt', ''));
            options.forEach(item => select.add(new Option(item.label, item.value, false, String(item.value) === String(selected))));
            select.dataset.selected = select.value;
        });
    }

    function filterLocationSelect(select, predicates) {
        if (!select) return;
        Array.from(select.options).forEach((opt, i) => {
            if (i === 0) return;
            opt.hidden = !predicates.every(([key, value]) => !value || String(opt.dataset[key] || '') === String(value));
        });
        if (select.selectedOptions[0]?.hidden) select.value = '';
    }

    function refreshLocations() {
        const project = projectSelect?.value || '';
        const block = blockSelect?.value || '';
        const floor = floorSelect?.value || '';
        filterLocationSelect(blockSelect, [['project', project]]);
        filterLocationSelect(floorSelect, [['project', project], ['block', block]]);
        filterLocationSelect(unitSelect, [['project', project], ['block', block], ['floor', floor]]);
    }

    [projectSelect, blockSelect, floorSelect].forEach(select => select?.addEventListener('change', refreshLocations));

    body.querySelectorAll('.material-item-row').forEach(initializeRow);
    if (directMode) setupDirectReceipt();

    addRowButton.addEventListener('click', function () {
        if (directMode) return;
        const row = buildNewRow(rowIndex++);
        body.appendChild(row);
        renumberMaterialRows();
        initializeRow(row);
        row.querySelector('.material-search-input').focus();
        refreshPhotoItemOptions();
    });

    if (photoRows && addPhotoRowButton) {
        addPhotoRowButton.addEventListener('click', function () {
            const first = photoRows.querySelector('.photo-row');
            if (!first) return;
            const row = first.cloneNode(true);
            row.querySelectorAll('input, select').forEach(field => {
                if (field.type === 'file' || field.type === 'text') field.value = '';
                if (field.tagName === 'SELECT') field.selectedIndex = 0;
                if (field.name) field.name = field.name.replace(/^photos\[\d+\]/, `photos[${photoIndex}]`);
            });
            row.querySelector('.photo-row-number')?.replaceChildren(document.createTextNode(photoIndex + 1));
            photoRows.appendChild(row);
            photoIndex++;
            refreshPhotoItemOptions();
        });
    }

    function setupDirectReceipt() {
        const initialRows = Array.from(body.querySelectorAll('.material-item-row'));
        // The default blank row becomes the entry form; old() rows stay committed.
        const initialIsBlank = initialRows.length === 1 && !initialRows[0].querySelector('.material-type-select').value && !initialRows[0].querySelector('.temporary-material-name').value.trim();
        if (initialIsBlank) {
            entryHost.appendChild(initialRows[0]);
            clearDirectDraftRequired();
        } else createDirectDraft();
        refreshDirectSummary();
        document.getElementById('commit-direct-item').addEventListener('click', commitDirectItem);
        document.getElementById('cancel-direct-edit').addEventListener('click', () => {
            if (!editingDirectRow) return;
            // Editing is performed in-place; cancel returns the row to the list.
            body.appendChild(editingDirectRow);
            editingDirectRow = null;
            renumberMaterialRows();
            createDirectDraft();
            refreshDirectSummary();
        });
    }

    function clearDirectDraftRequired() {
        // The entry row is a draft, not a receipt item. Browser validation
        // happens before the submit event, so a hidden required draft input
        // otherwise blocks Save without letting our submit handler run.
        entryHost?.querySelectorAll('.material-item-row [required]').forEach(field => {
            field.required = false;
        });
    }

    function createDirectDraft() {
        const template = body.querySelector('.material-item-row') || entryHost.querySelector('.material-item-row');
        if (!template) return;
        const draft = buildNewRow(rowIndex++);
        draft.querySelector('.short-input').value = '0';
        draft.querySelector('.damaged-input').value = '0';
        draft.querySelector('.rejected-input').value = '0';
        entryHost.replaceChildren(draft);
        initializeRow(draft);
        clearDirectDraftRequired();
        draft.querySelector('.material-search-input').focus();
        document.getElementById('commit-direct-item').textContent = '+ Add to Receipt';
        document.getElementById('cancel-direct-edit').classList.add('hidden');
        document.getElementById('direct-entry-status').textContent = 'Choose a product and enter its receipt details';
    }

    function validateDirectEntry(row) {
        const mode = row.querySelector('.entry-mode-input').value;
        if (mode === 'temporary' ? !row.querySelector('.temporary-material-name').value.trim() : !row.querySelector('.material-type-select').value) return 'Select a product or enter the Material Not Found name.';
        const received = Number(row.querySelector('.quantity-input').value);
        const unit = row.querySelector('.unit-id-input').value;
        if (!(received > 0)) return 'Enter a Receive Now quantity greater than zero.';
        if (!unit) return 'Select a unit.';
        const accepted = Number(row.querySelector('.accepted-input').value || 0);
        const damaged = Number(row.querySelector('.damaged-input').value || 0);
        const rejected = Number(row.querySelector('.rejected-input').value || 0);
        if ([accepted, damaged, rejected, Number(row.querySelector('.short-input').value || 0)].some(x => x < 0)) return 'Quantities cannot be negative.';
        if (Math.abs(received - accepted - damaged - rejected) > 0.0005) return 'Receive Now must equal Accepted + Damaged + Rejected.';
        return '';
    }

    function commitDirectItem() {
        const row = entryHost.querySelector('.material-item-row');
        if (!row) return;
        const error = validateDirectEntry(row);
        if (error) { alert(error); return; }
        body.appendChild(row);
        editingDirectRow = null;
        renumberMaterialRows();
        createDirectDraft();
        refreshDirectSummary();
        refreshPhotoItemOptions();
    }

    function editDirectRow(row) {
        if (editingDirectRow) { alert('Update the material currently being edited first.'); return; }
        const draft = entryHost.querySelector('.material-item-row');
        if (draft) draft.remove();
        editingDirectRow = row;
        entryHost.appendChild(row);
        document.getElementById('commit-direct-item').textContent = 'Update Material';
        document.getElementById('cancel-direct-edit').classList.remove('hidden');
        document.getElementById('direct-entry-status').textContent = 'Editing an item already in this receipt';
        document.getElementById('direct-entry-card').scrollIntoView({behavior:'smooth',block:'start'});
    }

    function removeDirectRow(row) {
        if (editingDirectRow === row) { alert('Finish editing this material first.'); return; }
        if (body.querySelectorAll('.material-item-row').length <= 1 && !confirm('Remove the last material? You must add another before saving.')) return;
        const removedIndex = Array.from(body.querySelectorAll('.material-item-row')).indexOf(row);
        row.remove();
        // Photos previously associated with a removed item become general photos;
        // subsequent item indices shift down to follow their original material.
        photoRows?.querySelectorAll('.photo-item-select').forEach(select => {
            if (select.value === '') return;
            const value = Number(select.value);
            select.value = value === removedIndex ? '' : String(value > removedIndex ? value - 1 : value);
            select.dataset.selected = select.value;
        });
        renumberMaterialRows();
        refreshDirectSummary();
        refreshPhotoItemOptions();
    }

    function refreshDirectSummary() {
        if (!directMode) return;
        summaryBody.replaceChildren();
        const rows = Array.from(body.querySelectorAll('.material-item-row'));
        document.getElementById('direct-item-count').textContent = `(${rows.length})`;
        document.getElementById('direct-empty-message').classList.toggle('hidden', rows.length > 0);
        rows.forEach((row, index) => {
            const mode = row.querySelector('.entry-mode-input').value;
            const product = mode === 'temporary' ? row.querySelector('.temporary-material-name').value + ' — Pending Classification' : row.querySelector('.material-search-input').value;
            const spec = mode === 'temporary' ? row.querySelector('[name$="[temporary_specification]"]').value : row.querySelector('.specification-select').selectedOptions[0]?.textContent;
            const grade = mode === 'temporary' ? row.querySelector('[name$="[temporary_grade]"]').value : row.querySelector('.grade-select').selectedOptions[0]?.textContent;
            const brand = mode === 'temporary' ? row.querySelector('[name$="[temporary_brand]"]').value : row.querySelector('.brand-select').selectedOptions[0]?.textContent;
            const unit = row.querySelector('.transaction-unit-select').selectedOptions[0]?.textContent || '';
            const tr = document.createElement('tr');
            const cell = (value, cls='') => { const td = document.createElement('td'); td.className = 'px-3 py-3 ' + cls; td.textContent = value || '—'; tr.appendChild(td); return td; };
            cell(String(index + 1));
            const detail = [spec,grade,brand].filter(v => v && !['Specification','Grade / Rating','Brand'].includes(v)).join(' · ');
            const productCell = cell(product); productCell.classList.add('font-semibold','text-slate-800');
            if (detail) { const small = document.createElement('div'); small.className = 'mt-1 text-xs font-normal text-slate-500'; small.textContent = detail; productCell.appendChild(small); }
            cell(`${row.querySelector('.quantity-input').value} ${unit}`, 'text-right');
            cell(row.querySelector('.accepted-input').value, 'text-right');
            cell(row.querySelector('[name$="[remarks]"]').value);
            const actions = document.createElement('td'); actions.className = 'whitespace-nowrap px-3 py-3';
            const edit = document.createElement('button'); edit.type='button'; edit.className='mr-3 font-semibold text-blue-700 hover:underline'; edit.textContent='Edit'; edit.addEventListener('click',()=>editDirectRow(row));
            const remove = document.createElement('button'); remove.type='button'; remove.className='font-semibold text-red-700 hover:underline'; remove.textContent='Remove'; remove.addEventListener('click',()=>removeDirectRow(row));
            actions.append(edit,remove); tr.appendChild(actions); summaryBody.appendChild(tr);
        });
    }

    function refreshReceiptSource() {
        const isPo = receiptSource?.value === 'PO';
        purchaseOrderWrap?.classList.toggle('hidden', !isPo);
        if (!isPo && purchaseOrderSelect) purchaseOrderSelect.value = '';
    }

    receiptSource?.addEventListener('change', refreshReceiptSource);
    loadPurchaseOrderButton?.addEventListener('click', function () {
        if (!purchaseOrderSelect?.value) { alert('Select a Purchase Order first.'); return; }
        const url = new URL(window.location.href);
        url.searchParams.set('purchase_order_id', purchaseOrderSelect.value);
        window.location.href = url.toString();
    });

    document.getElementById('material-receipt-form')?.addEventListener('submit', function (event) {
        let valid = true;
        body.querySelectorAll('.material-item-row').forEach((row, index) => {
            if (directMode) {
                const entryError = validateDirectEntry(row);
                if (entryError) {
                    alert(`Row ${index + 1}: ${entryError}`);
                    valid = false;
                    return;
                }
            }
            const received = Number(row.querySelector('.quantity-input')?.value || 0);
            const acceptedQty = Number(row.querySelector('.accepted-input')?.value || 0);
            const damagedQty = Number(row.querySelector('.damaged-input')?.value || 0);
            const rejectedQty = Number(row.querySelector('.rejected-input')?.value || 0);
            const shortValue = Number(row.querySelector('.short-input')?.value || 0);
            if (Math.abs(received - (acceptedQty + damagedQty + rejectedQty)) > 0.0005) {
                alert(`Row ${index + 1}: Receive Now must equal Accepted + Damaged + Rejected.`);
                valid = false;
                return;
            }
            const pendingEl = row.querySelector('.po-pending-quantity');
            if (pendingEl) {
                const pending = Number((pendingEl.textContent || '0').replace(/,/g, ''));
                if ((received + shortValue) - pending > 0.0005) {
                    alert(`Row ${index + 1}: Receive Now + Short exceeds Pending quantity.`);
                    valid = false;
                }
            }
        });
        if (directMode) {
            if (editingDirectRow) { event.preventDefault(); alert('Click Update Material before saving the receipt.'); return; }
            if (!body.querySelector('.material-item-row')) { event.preventDefault(); alert('Add at least one material to the receipt.'); return; }
            // Uncommitted entry inputs are outside the submitted item list.
            entryHost.querySelectorAll('[name^="items["]').forEach(field => field.disabled = true);
        }
        if (!valid) { event.preventDefault(); if (directMode) entryHost.querySelectorAll('[name^="items["]').forEach(field => field.disabled = false); }
    });

    refreshReceiptSource();
    refreshLocations();
    refreshPhotoItemOptions();
});
</script>
