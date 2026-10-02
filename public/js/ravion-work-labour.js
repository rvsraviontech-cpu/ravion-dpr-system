(() => {
'use strict';
const form = document.querySelector('[data-ref-work-done-form]');
if (!form) return;
const endpoint = form.dataset.refLabourUrl;
let available = [];
const groups = window.RAVION_WORK_LABOUR?.groups || [];
const roles = window.RAVION_WORK_LABOUR?.roles || [];
const contractors = window.RAVION_WORK_LABOUR?.contractors || [];
const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const opts = (arr, placeholder, selected='') => '<option value="">'+esc(placeholder)+'</option>'+arr.map(x=>'<option value="'+esc(x.id)+'"'+(String(x.id)===String(selected)?' selected':'')+'>'+esc(x.name)+'</option>').join('');
const prefix = panel => panel.closest('[data-ref-activity-card]')?.querySelector('[data-canonical-id]')?.name.match(/^works\[(\d+)\]/)?.[1];
function row(panel, preset={}) {
  const holder=panel.querySelector('[data-ref-labour-rows]');
  const el=document.createElement('div'); el.dataset.refLabourRow='1'; el.className='rounded-lg border border-slate-200 bg-slate-50 p-3';
  const input='w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm';
  el.innerHTML=`<div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
  <label class="text-xs font-semibold">Labour source<select class="${input}" data-l-source><option value="ravion">Ravion attendance</option><option value="contractor">Contractor manpower</option></select></label>
  <label class="text-xs font-semibold" data-l-attendance-wrap>Approved attendance / worker<select class="${input}" data-l-attendance></select></label>
  <label class="text-xs font-semibold" data-l-group-wrap>Labour group<select class="${input}" data-l-group>${opts(groups,'Select group')}</select></label>
  <label class="text-xs font-semibold" data-l-role-wrap>Designation<select class="${input}" data-l-role>${opts(roles,'Select designation')}</select></label>
  <label class="text-xs font-semibold" data-l-contractor-wrap>Contractor<select class="${input}" data-l-contractor>${opts(contractors,'Select contractor')}</select></label>
  <label class="text-xs font-semibold">Workers deployed<input class="${input}" type="number" min="1" max="1000" step="1" value="1" data-l-quantity></label>
  <label class="text-xs font-semibold">Hours per worker<input class="${input}" type="number" min="0.01" max="24" step="0.01" placeholder="e.g. 4" data-l-hours></label>
  <label class="text-xs font-semibold">Remarks<input class="${input}" maxlength="500" data-l-remarks placeholder="Optional"></label>
  </div><div class="mt-2 flex items-center justify-between gap-2"><span class="text-xs text-slate-500" data-l-remaining></span><button type="button" data-ref-remove-labour class="text-xs font-semibold text-red-700">Remove Labour</button></div>`;
  holder.appendChild(el);
  el.querySelector('[data-l-source]').value=preset.source||'ravion';
  el.querySelector('[data-l-attendance]').dataset.selected=preset.attendance_detail_id||'';
  el.querySelector('[data-l-group]').value=preset.labour_group_id||'';
  el.querySelector('[data-l-role]').value=preset.designation_role_id||'';
  el.querySelector('[data-l-contractor]').value=preset.contractor_id||'';
  el.querySelector('[data-l-quantity]').value=preset.quantity||1;
  el.querySelector('[data-l-hours]').value=preset.allocated_hours||'';
  el.querySelector('[data-l-remarks]').value=preset.remarks||'';
  update(el); renumber(panel); return el;
}
function update(el) {
 const source=el.querySelector('[data-l-source]').value;
 const ravion=source==='ravion';
 el.querySelector('[data-l-attendance-wrap]').hidden=!ravion;
 for(const field of ['group','role','contractor']) el.querySelector(`[data-l-${field}-wrap]`).hidden=ravion;
 const select=el.querySelector('[data-l-attendance]'); const current=select.value||select.dataset.selected||'';
 select.innerHTML=opts(available.map(a=>({id:a.id,name:`${a.name} (${a.group||a.designation||'Labour'}) — ${a.remaining_hours} hrs left`})), 'Select approved worker',current);
 select.value=current; delete select.dataset.selected;
 el.querySelector('[data-l-quantity]').readOnly=ravion;
 if(ravion) el.querySelector('[data-l-quantity]').value='1';
 const found=available.find(a=>String(a.id)===select.value);
 el.querySelector('[data-l-remaining]').textContent=ravion?(found?`Available normal hours: ${found.remaining_hours} (of ${found.normal_hours})`:'Only approved present attendance is listed'):'Contractor manpower is entered manually; no attendance deduction.';
}
function renumber(panel) {
 const p=prefix(panel); if(p===undefined) return;
 panel.querySelectorAll('[data-ref-labour-row]').forEach((el,i)=>{
   for(const [key,sel] of Object.entries({source:'[data-l-source]',attendance_detail_id:'[data-l-attendance]',labour_group_id:'[data-l-group]',designation_role_id:'[data-l-role]',contractor_id:'[data-l-contractor]',quantity:'[data-l-quantity]',allocated_hours:'[data-l-hours]',remarks:'[data-l-remarks]'})) {
     const control=el.querySelector(sel); control.name=`works[${p}][labours][${i}][${key}]`;
     control.disabled=(key==='attendance_detail_id' && el.querySelector('[data-l-source]').value!=='ravion') || (['labour_group_id','designation_role_id','contractor_id'].includes(key) && el.querySelector('[data-l-source]').value==='ravion');
   }
 });
}
async function load() {
 const project=form.querySelector('[data-ref-project-field]')?.value;
 const date=form.querySelector('[data-ref-work-date-field]')?.value;
 available=[];
 if(project&&date){
  try{const url=new URL(endpoint,window.location.origin);url.searchParams.set('project_id',project);url.searchParams.set('work_date',date);
   const res=await fetch(url,{headers:{Accept:'application/json'}});if(!res.ok)throw Error('Attendance lookup failed ('+res.status+')');
   available=(await res.json()).data||[];
  }catch(e){form.querySelectorAll('[data-ref-labour-message]').forEach(n=>n.textContent=e.message);}
 }
 form.querySelectorAll('[data-ref-resource-panel]').forEach(panel=>{
  panel.querySelectorAll('[data-ref-labour-row]').forEach(update);
  const msg=panel.querySelector('[data-ref-labour-message]');if(msg)msg.textContent=project&&date?`${available.length} approved attendance entries available. Contractor manpower may be entered manually.`:'Select a Project and Work Date to load approved attendance.';
 });
}
form.addEventListener('click',e=>{
 const add=e.target.closest('[data-ref-add-labour]');if(add){row(add.closest('[data-ref-resource-panel]'));return;}
 const remove=e.target.closest('[data-ref-remove-labour]');if(remove){const panel=remove.closest('[data-ref-resource-panel]');remove.closest('[data-ref-labour-row]').remove();renumber(panel);}
});
form.addEventListener('change',e=>{
 if(e.target.matches('[data-ref-project-field],[data-ref-work-date-field]'))load();
 const el=e.target.closest('[data-ref-labour-row]');if(el){if(e.target.matches('[data-l-source],[data-l-attendance]'))update(el);renumber(el.closest('[data-ref-resource-panel]'));}
});
form.addEventListener('submit',()=>form.querySelectorAll('[data-ref-resource-panel]').forEach(renumber));
load();
})();