const defs = {
  products: {title:'สินค้า', entity:'products', pk:'i_ProductID', columns:[['i_ProductID','รหัส'],['c_ProductName','ชื่อสินค้า'],['i_SupplierID','ผู้จัดจำหน่าย'],['i_CategoryID','หมวดหมู่'],['c_Unit','หน่วย'],['i_Price','ราคา']], fields:[{name:'c_ProductName',label:'ชื่อสินค้า'},{name:'i_SupplierID',label:'ผู้จัดจำหน่าย',type:'select',optionsEntity:'suppliers',optionKey:'i_SupplierID',optionLabel:'c_SupplierName'},{name:'i_CategoryID',label:'หมวดหมู่',type:'select',optionsEntity:'categories',optionKey:'i_CategoryID',optionLabel:'c_CategoryName'},{name:'c_Unit',label:'หน่วยบรรจุ'},{name:'i_Price',label:'ราคา',type:'number',min:0,step:'0.01'}]},
  categories: {title:'หมวดหมู่สินค้า', entity:'categories', pk:'i_CategoryID', columns:[['i_CategoryID','รหัส'],['c_CategoryName','ชื่อหมวดหมู่'],['c_Description','รายละเอียด']], fields:[{name:'c_CategoryName',label:'ชื่อหมวดหมู่'},{name:'c_Description',label:'รายละเอียด',type:'textarea'}]},
  orders: {title:'คำสั่งซื้อ', entity:'orders', pk:'i_OrderID', columns:[['i_OrderID','เลขที่'],['c_OrderDate','วันที่'],['c_customername','ลูกค้า'],['employee','พนักงาน'],['c_ShipperName','ขนส่ง'],['total','ยอดรวม']], fields:[{name:'i_CustomerID',label:'ลูกค้า',type:'select',optionsEntity:'customers',optionKey:'i_customerid',optionLabel:'c_customername'},{name:'i_EmployeeID',label:'พนักงาน',type:'select',optionsEntity:'employees',optionKey:'i_EmployeeID',optionLabel:'c_FirstName',optionLabel2:'c_LastName'},{name:'c_OrderDate',label:'วันที่ (d/m/yyyy)',placeholder:'เช่น 8/11/2026'},{name:'i_ShipperID',label:'บริษัทขนส่ง',type:'select',optionsEntity:'shippers',optionKey:'i_ShipperID',optionLabel:'c_ShipperName'}]},
  orderdetails: {title:'รายการสินค้าในคำสั่งซื้อ', entity:'orderdetails', pk:'i_OrderDetailID', columns:[['i_OrderDetailID','รหัส'],['i_OrderID','คำสั่งซื้อ'],['c_OrderDate','วันที่'],['i_ProductID','รหัสสินค้า'],['c_ProductName','สินค้า'],['i_Quantity','จำนวน']], fields:[{name:'i_OrderID',label:'คำสั่งซื้อ',type:'select',optionsEntity:'orders',optionKey:'i_OrderID',optionLabel:'i_OrderID',optionPrefix:'#'},{name:'i_ProductID',label:'สินค้า',type:'select',optionsEntity:'products',optionKey:'i_ProductID',optionLabel:'c_ProductName'},{name:'i_Quantity',label:'จำนวน',type:'number',min:1,step:1}]},
  customers: {title:'ลูกค้า', entity:'customers', pk:'i_customerid', columns:[['i_customerid','รหัส'],['c_customername','ชื่อลูกค้า'],['c_contactname','ผู้ติดต่อ'],['c_city','เมือง'],['c_country','ประเทศ']], fields:[{name:'c_customername',label:'ชื่อลูกค้า'},{name:'c_contactname',label:'ผู้ติดต่อ'},{name:'c_address',label:'ที่อยู่'},{name:'c_city',label:'เมือง'},{name:'c_postalcode',label:'รหัสไปรษณีย์'},{name:'c_country',label:'ประเทศ'}]},
  suppliers: {title:'ผู้จัดจำหน่าย', entity:'suppliers', pk:'i_SupplierID', columns:[['i_SupplierID','รหัส'],['c_SupplierName','ชื่อบริษัท'],['c_ContactName','ผู้ติดต่อ'],['c_City','เมือง'],['c_Country','ประเทศ'],['c_Phone','โทรศัพท์']], fields:[{name:'c_SupplierName',label:'ชื่อบริษัท'},{name:'c_ContactName',label:'ผู้ติดต่อ'},{name:'c_Address',label:'ที่อยู่'},{name:'c_City',label:'เมือง'},{name:'c_PostalCode',label:'รหัสไปรษณีย์'},{name:'c_Country',label:'ประเทศ'},{name:'c_Phone',label:'โทรศัพท์'}]},
  employees: {title:'พนักงาน', entity:'employees', pk:'i_EmployeeID', columns:[['i_EmployeeID','รหัส'],['c_FirstName','ชื่อ'],['c_LastName','นามสกุล'],['c_BirthDate','วันเกิด'],['c_Photo','ไฟล์รูป']], fields:[{name:'c_FirstName',label:'ชื่อ'},{name:'c_LastName',label:'นามสกุล'},{name:'c_BirthDate',label:'วันเกิด (d/m/yyyy)'},{name:'c_Photo',label:'ไฟล์รูป'},{name:'c_Notes',label:'หมายเหตุ',type:'textarea'}]},
  shippers: {title:'บริษัทขนส่ง', entity:'shippers', pk:'i_ShipperID', columns:[['i_ShipperID','รหัส'],['c_ShipperName','ชื่อบริษัท'],['c_Phone','โทรศัพท์']], fields:[{name:'c_ShipperName',label:'ชื่อบริษัท'},{name:'c_Phone',label:'โทรศัพท์'}]}
};
const pageTitles={dashboard:'แดชบอร์ดภาพรวม',reports:'รายงานภาพรวม',monthly:'รายงานรายเดือน','top-products':'สินค้าขายดี'};
let page='dashboard', editing=null;
const app=document.querySelector('#app');
const esc=value=>String(value??'').replace(/[&<>"']/g,ch=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch]));
async function api(action,data=null,query=''){
  const response=await fetch(`api.php?action=${encodeURIComponent(action)}${query}`,{method:data?'POST':'GET',headers:data?{'Content-Type':'application/json'}:{},body:data?JSON.stringify(data):undefined,credentials:'same-origin'});
  const raw=await response.text();let payload;
  try{payload=JSON.parse(raw)}catch{throw new Error(`API returned a non-JSON response (HTTP ${response.status})`)}
  if(!response.ok)throw new Error(payload.error||'เกิดข้อผิดพลาด');return payload;
}
async function boot(){
  try{const state=await api('me');if(!state.user){document.querySelector('#login').style.display='flex';return}
    document.querySelector('#who').innerHTML=`${esc(state.user.name)} <button class="btn btn-sm btn-outline-secondary" onclick="logout()">ออกจากระบบ</button>`;render();
  }catch(error){app.innerHTML=`<div class="alert alert-danger">${esc(error.message)}<br>ตรวจสอบการตั้งค่าฐานข้อมูลและการ deploy</div>`}
}
async function login(event){
  event.preventDefault();const form=new FormData(event.target);const errorBox=document.querySelector('#loginError');
  try{await api('login',{username:form.get('username'),password:form.get('password')});document.querySelector('#login').style.display='none';errorBox.classList.add('d-none');boot()}
  catch(error){errorBox.textContent=error.message;errorBox.classList.remove('d-none')}
}
async function logout(){try{await api('logout');location.reload()}catch(error){alert(error.message)}}
document.querySelectorAll('.nav-link[data-page]').forEach(button=>button.addEventListener('click',()=>{
  document.querySelectorAll('.nav-link').forEach(item=>item.classList.remove('active'));button.classList.add('active');page=button.dataset.page;render();
}));
function heading(title,subtitle,actions=''){
  return `<div class="page-heading"><div><h1>${esc(title)}</h1><div class="text-secondary">${esc(subtitle)}</div></div><div class="heading-actions">${actions}</div></div>`;
}
function reportActions(){return '<button class="btn btn-outline-primary" onclick="downloadCsv()">ส่งออก CSV</button><button class="btn btn-outline-secondary" onclick="exportPdf()">พิมพ์ / บันทึก PDF</button>'}
function bars(rows,labelField,valueField){
  if(!rows?.length)return '<div class="empty-state">ไม่มีข้อมูล</div>';
  const max=Math.max(1,...rows.map(row=>Number(row[valueField])||0));
  return rows.map(row=>`<div class="bar-row"><span class="bar-label" title="${esc(row[labelField])}">${esc(row[labelField])}</span><span class="bar-track"><span class="bar-fill d-block" style="width:${Math.max(0,Math.min(100,100*Number(row[valueField])/max))}%"></span></span><b class="bar-value">${esc(row[valueField])}</b></div>`).join('');
}
async function getDashboard(){return api('dashboard')}
async function render(){
  const title=pageTitles[page]||defs[page]?.title||'Northwind BMS';document.querySelector('#crumb').textContent=title;
  try{
    if(page==='dashboard'||page==='reports')return renderOverview(page);
    if(page==='monthly')return renderMonthly();
    if(page==='top-products')return renderTopProducts();
    if(defs[page])return renderEntity();
  }catch(error){app.innerHTML=`<div class="alert alert-danger">${esc(error.message)}</div>`}
}
async function renderOverview(mode){
  const data=await getDashboard();
  const actions=reportActions();
  const stats=[['สินค้า',data.products],['คำสั่งซื้อ',data.orders],['ลูกค้า',data.customers],['รายได้โดยประมาณ',`฿${Number(data.revenue).toLocaleString(undefined,{maximumFractionDigits:0})}`]];
  app.innerHTML=`${heading(mode==='reports'?'รายงานภาพรวม':'แดชบอร์ดภาพรวม','สรุปข้อมูลและผลการดำเนินงาน',actions)}
  <div class="row g-3 mb-3">${stats.map(([label,value])=>`<div class="col-6 col-xl-3"><div class="card kpi-card h-100"><div class="card-body"><div class="text-secondary">${label}</div><div class="value">${value}</div></div></div></div>`).join('')}</div>
  <div class="row g-3"><div class="col-12 col-xl-6"><section class="report-card"><h2 class="h5">สินค้าขายดีตามจำนวน</h2>${bars(data.topProducts,'name','quantity')}</section></div><div class="col-12 col-xl-6"><section class="report-card"><h2 class="h5">ลูกค้าที่มีคำสั่งซื้อมาก</h2>${bars(data.topCustomers,'name','orders')}</section></div><div class="col-12"><section class="report-card"><h2 class="h5">คำสั่งซื้อตามเดือน</h2>${bars(data.monthly.map(row=>({...row,name:`${row.month}/${row.year}`})),'name','orders')}</section></div></div>`;
}
async function renderMonthly(){
  const data=await getDashboard();const rows=data.monthly.map(row=>({...row,name:`${row.month}/${row.year}`}));
  app.innerHTML=`${heading('รายงานรายเดือน','จำนวนคำสั่งซื้อแยกตามเดือน',reportActions())}<section class="report-card mb-3"><h2 class="h5">แนวโน้มคำสั่งซื้อ</h2>${bars(rows,'name','orders')}</section><section class="card"><div class="table-responsive"><table class="table table-striped table-hover align-middle"><thead><tr><th>เดือน</th><th>ปี</th><th>จำนวนคำสั่งซื้อ</th></tr></thead><tbody>${rows.map(row=>`<tr><td>${esc(row.month)}</td><td>${esc(row.year)}</td><td>${esc(row.orders)}</td></tr>`).join('')}</tbody></table></div></section>`;
}
async function renderTopProducts(){
  const data=await getDashboard();
  app.innerHTML=`${heading('สินค้าขายดี','จัดอันดับตามจำนวนหน่วยที่ขายได้',reportActions())}<section class="report-card"><h2 class="h5">อันดับสินค้าขายดี</h2>${bars(data.topProducts,'name','quantity')}</section>`;
}
async function renderEntity(){
  const def=defs[page];
  const addButton=`<button class="btn btn-primary" onclick="openForm()">＋ เพิ่มรายการ</button>`;
  app.innerHTML=`${heading(def.title,'ค้นหา เพิ่ม แก้ไข และลบข้อมูล',addButton)}<section class="card"><div class="card-body"><div class="row g-2 mb-3"><div class="col"><input id="search" class="form-control" placeholder="ค้นหา..." oninput="loadTable()"></div><div class="col-auto"><button class="btn btn-outline-secondary" onclick="loadTable()">ค้นหา</button></div></div><div id="tableArea" class="table-responsive"><div class="empty-state">กำลังโหลด...</div></div></div></section>`;
  await loadTable();
}
async function loadTable(){
  const def=defs[page],area=document.querySelector('#tableArea');if(!def||!area)return;
  try{
    const q=document.querySelector('#search')?.value||'';const rows=await api('list',null,`&entity=${encodeURIComponent(def.entity)}&q=${encodeURIComponent(q)}&limit=500`);
    if(!rows.length){area.innerHTML='<div class="empty-state">ไม่พบข้อมูล</div>';return}
    const head=def.columns.map(([,label])=>`<th>${esc(label)}</th>`).join('');
    const body=rows.map(row=>{
      const cells=def.columns.map(([key])=>`<td>${key==='total'?Number(row[key]||0).toLocaleString():esc(row[key])}</td>`).join('');
      let extra='';
      if(page==='orders')extra+=`<button class="btn btn-sm btn-outline-info me-1" onclick="orderDetail(${Number(row.i_OrderID)})">รายละเอียด</button>`;
      extra+=`<button class="btn btn-sm btn-outline-primary me-1" onclick="openForm('${encodeURIComponent(JSON.stringify(row)).replaceAll(String.fromCharCode(39),'%27')}')">แก้ไข</button><button class="btn btn-sm btn-outline-danger" onclick="removeRow(${Number(row[def.pk])})">ลบ</button>`;
      return `<tr>${cells}<td class="action-cell">${extra}</td></tr>`;
    }).join('');
    area.innerHTML=`<table class="table table-striped table-hover align-middle"><thead><tr>${head}<th>จัดการ</th></tr></thead><tbody>${body}</tbody></table>`;
  }catch(error){area.innerHTML=`<div class="alert alert-danger mb-0">${esc(error.message)}</div>`}
}
async function openForm(serialized=null){
  const def=defs[page];editing=serialized?JSON.parse(decodeURIComponent(serialized)):null;
  const fields=[];
  for(const field of def.fields){
    const value=editing?.[field.name]??'';const id=`field_${field.name}`;let control='';
    if(field.type==='select'){
      const options=await api('list',null,`&entity=${encodeURIComponent(field.optionsEntity)}&limit=500`);
      const choices=options.map(row=>{let label=String(row[field.optionLabel]??'');if(field.optionLabel2)label+=` ${row[field.optionLabel2]??''}`;if(field.optionPrefix)label=field.optionPrefix+label;return `<option value="${esc(row[field.optionKey])}" ${String(row[field.optionKey])===String(value)?'selected':''}>${esc(label)}</option>`}).join('');
      control=`<select class="form-select" id="${id}" name="${field.name}" required><option value="">เลือก${esc(field.label)}</option>${choices}</select>`;
    }else if(field.type==='textarea')control=`<textarea class="form-control" id="${id}" name="${field.name}" rows="3" required>${esc(value)}</textarea>`;
    else control=`<input class="form-control" id="${id}" name="${field.name}" type="${field.type||'text'}" ${field.min!==undefined?`min="${field.min}"`:''} ${field.step?`step="${field.step}"`:''} placeholder="${esc(field.placeholder||'')}" value="${esc(value)}" required>`;
    fields.push(`<div class="col-12 col-md-6 form-field"><label class="form-label" for="${id}">${esc(field.label)}</label>${control}</div>`);
  }
  document.querySelector('#modalTitle').textContent=editing?`แก้ไข${def.title}`:`เพิ่ม${def.title}`;
  document.querySelector('#formFields').innerHTML=fields.join('');
  document.querySelector('#formError').classList.add('d-none');
  const modal=document.querySelector('#recordModal');modal.style.display='flex';modal.setAttribute('aria-hidden','false');
}
function closeModal(){const modal=document.querySelector('#recordModal');modal.style.display='none';modal.setAttribute('aria-hidden','true');editing=null}
async function save(event){
  event.preventDefault();const def=defs[page],row={};new FormData(event.target).forEach((value,key)=>row[key]=value);
  const errorBox=document.querySelector('#formError');
  try{await api('save',{entity:def.entity,id:editing?.[def.pk],row});closeModal();await loadTable()}
  catch(error){errorBox.textContent=error.message;errorBox.classList.remove('d-none')}
}
async function removeRow(id){try{await api('delete',{entity:defs[page].entity,id});await loadTable()}catch(error){alert(error.message)}}
async function orderDetail(id){
  try{
    const order=await api('detail',null,`&id=${encodeURIComponent(id)}`);
    app.innerHTML=`${heading(`คำสั่งซื้อ #${order.i_OrderID}`,`${order.c_OrderDate} · ${order.c_customername} · ${order.employee}`,`<button class="btn btn-outline-secondary" onclick="render()">กลับ</button>`)}<section class="card"><div class="card-body"><div class="table-responsive"><table class="table table-striped align-middle"><thead><tr><th>สินค้า</th><th>ราคาต่อหน่วย</th><th>จำนวน</th><th>รวม</th></tr></thead><tbody>${order.items.map(item=>`<tr><td>${esc(item.c_ProductName)}</td><td>${Number(item.i_Price).toFixed(2)}</td><td>${esc(item.i_Quantity)}</td><td>${Number(item.line_total).toFixed(2)}</td></tr>`).join('')}</tbody></table></div></div></section>`;
  }catch(error){alert(error.message)}
}
async function downloadCsv(){
  const entity=defs[page]?.entity||'orders';const rows=await api('list',null,`&entity=${encodeURIComponent(entity)}&limit=500`);const keys=Object.keys(rows[0]||{});
  const csv=[keys.join(','),...rows.map(row=>keys.map(key=>'"'+String(row[key]??'').replaceAll('"','""')+'"').join(','))].join('\r\n');
  const url=URL.createObjectURL(new Blob(['\ufeff'+csv],{type:'text/csv;charset=utf-8'}));const link=document.createElement('a');link.href=url;link.download=`northwind-${page}.csv`;link.click();URL.revokeObjectURL(url);
}
function exportPdf(){window.print()}
boot();