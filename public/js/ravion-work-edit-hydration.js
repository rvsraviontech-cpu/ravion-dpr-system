(() => {
'use strict';
const form=document.querySelector('[data-ref-work-done-form]');
const data=window.RAVION_WORK_EDIT_RESOURCES||[];
if(!form)return;
const esc=s=>String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
function hydrate(){
 const cards=[...form.querySelectorAll('[data-ref-activity-card]')];
 cards.forEach((card,i)=>{
  const values=data[i];if(!values||card.dataset.editHydrated)return;
  const mp=card.querySelector('[data-ref-reported-materials]');
  const lp=card.querySelector('[data-ref-resource-panel]');
  const ep=card.querySelector('[data-ref-machinery-panel]');
  if(!mp?.querySelector('[data-entry-body]')||!lp?.querySelector('[data-entry-body]')||!ep?.querySelector('[data-machine-body]'))return;
  card.dataset.editHydrated='1';
  const name=n=>`works[${i}]`;
  (values.materials||[]).forEach((r,j)=>{
   const tr=document.createElement('tr');tr.dataset.entryRow='1';tr.className='border-b border-slate-200';
   const key=r.stock_key||'',q=r.quantity||0;
   tr.innerHTML=`<td class="p-2 font-medium text-slate-800">${esc(key)}<input type="hidden" data-saved-key name="works[${i}][materials_used][${j}][stock_key]" value="${esc(key)}"></td><td class="p-2 text-right">Saved</td><td class="p-2 text-right font-semibold" data-shown-qty>${esc(q)}</td><td class="p-2 text-center"><input type="hidden" data-saved-qty name="works[${i}][materials_used][${j}][quantity]" value="${esc(q)}"><button type="button" data-entry-remove class="rounded border border-red-200 px-2 py-1 text-xs text-red-700">Remove</button></td>`;
   tr.querySelector('[data-saved-key]').name=`works[${i}][materials_used][${j}][stock_key]`;
   mp.querySelector('[data-entry-body]').append(tr);
  });
  (values.labours||[]).forEach((r,j)=>{
   const key=r.labour_group_id||'',q=r.quantity||0;
   const label=(window.RAVION_WORK_LABOUR?.groups||[]).find(g=>String(g.id)===String(key))?.name||'Labour group #'+key;
   const tr=document.createElement('tr');tr.dataset.entryRow='1';tr.className='border-b border-slate-200';
   tr.innerHTML=`<td class="p-2 font-medium text-slate-800">${esc(label)}<input type="hidden" data-saved-key name="works[${i}][labours][${j}][labour_group_id]" value="${esc(key)}"></td><td class="p-2 text-right">Saved</td><td class="p-2 text-right font-semibold" data-shown-qty>${esc(q)}</td><td class="p-2 text-center"><input type="hidden" data-saved-qty name="works[${i}][labours][${j}][quantity]" value="${esc(q)}"><button type="button" data-entry-remove class="rounded border border-red-200 px-2 py-1 text-xs text-red-700">Remove</button></td>`;
   lp.querySelector('[data-entry-body]').append(tr);
  });
  (values.machinery||[]).forEach((r,j)=>{
   const tr=document.createElement('tr');tr.dataset.machineRow='1';tr.className='border-b border-slate-200';
   tr.innerHTML=`<td class="p-2 font-medium text-slate-800">${esc(r.equipment_name)}<input type="hidden" data-machine-saved-name name="works[${i}][machinery_used][${j}][equipment_name]" value="${esc(r.equipment_name)}"></td><td class="p-2 text-right">${esc(r.quantity)}<input type="hidden" data-machine-saved-qty name="works[${i}][machinery_used][${j}][quantity]" value="${esc(r.quantity)}"></td><td class="p-2 text-right">${esc(r.operating_hours??'—')}<input type="hidden" data-machine-saved-hours name="works[${i}][machinery_used][${j}][operating_hours]" value="${esc(r.operating_hours??'')}"></td><td class="p-2 text-center"><button type="button" data-machine-remove class="rounded border border-red-200 px-2 py-1 text-xs text-red-700">Remove</button></td>`;
   ep.querySelector('[data-machine-body]').append(tr);
  });
  [mp,lp,ep].forEach(p=>{if(p.querySelector('[data-entry-row], [data-machine-row]'))p.querySelector('[data-empty], [data-machine-empty]')?.classList.add('hidden');});
 });
}
const observer=new MutationObserver(hydrate);observer.observe(form,{childList:true,subtree:true});hydrate();
})();
