document.addEventListener('DOMContentLoaded', () => {
  const apiBase = '/PetsAccessories/admin/backend/api/shipping_zones';
  const tableBody = document.getElementById('shippingTableBody');
  const msgBox = document.getElementById('messagesContainer');
  const modal = document.getElementById('shippingModal');
  const form = document.getElementById('shippingForm');
  const titleEl = document.getElementById('shippingModalTitle');
  const saveBtn = document.getElementById('saveShippingBtn');
  let editId = null;

  const msg = (t, type='success') => { msgBox.innerHTML = `<div class="message ${type}">${t}</div>`; setTimeout(()=>msgBox.innerHTML='',3000); };

  async function load() {
    const res = await fetch(`${apiBase}/list.php?page=1&limit=100`);
    const data = await res.json();
    if (!data.success) return msg(data.message, 'error');
    tableBody.innerHTML = data.data.map(z => `<tr><td>${z.zone_id}</td><td>${z.zone_name}</td><td>${z.shipping_fee}</td><td>${z.estimated_delivery||'-'}</td><td>${z.status==1?'Kích hoạt':'Ẩn'}</td><td><button onclick="window.__editShipping(${z.zone_id})">Sửa</button><button onclick="window.__deleteShipping(${z.zone_id}, '${z.zone_name.replace(/'/g, "\\'")}')">Xóa</button></td></tr>`).join('');
  }

  window.__editShipping = async (id) => {
    const res = await fetch(`${apiBase}/list.php?page=1&limit=100`);
    const data = await res.json();
    const item = data.data.find(x => x.zone_id === id);
    if (!item) return;
    editId = id;
    titleEl.textContent = '✏️ Sửa Khu Vực Giao Hàng';
    form.zone_name.value = item.zone_name;
    form.shipping_fee.value = item.shipping_fee;
    form.estimated_delivery.value = item.estimated_delivery || '';
    form.status.value = item.status;
    modal.style.display = 'block';
  };
  window.__deleteShipping = async (id, name) => {
    if (!confirm(`Xóa khu vực "${name}"?`)) return;
    const res = await fetch(`${apiBase}/delete.php`, {method:'DELETE', headers:{'Content-Type':'application/json'}, body: JSON.stringify({zone_id:id})});
    const data = await res.json(); msg(data.message, data.success?'success':'error'); if(data.success) load();
  };

  document.getElementById('addShippingBtn')?.addEventListener('click', () => { editId=null; form.reset(); titleEl.textContent='➕ Thêm Khu Vực'; modal.style.display='block'; });
  document.getElementById('closeModalBtn')?.addEventListener('click', () => modal.style.display='none');
  document.getElementById('cancelModalBtn')?.addEventListener('click', () => modal.style.display='none');
  saveBtn?.addEventListener('click', async () => {
    const payload = { zone_name: form.zone_name.value.trim(), shipping_fee: parseFloat(form.shipping_fee.value || '0'), estimated_delivery: form.estimated_delivery.value.trim(), status: parseInt(form.status.value || '1', 10) };
    if (editId) payload.zone_id = editId;
    const url = editId ? `${apiBase}/update.php` : `${apiBase}/create.php`;
    const res = await fetch(url, {method: editId ? 'PUT' : 'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload)});
    const data = await res.json(); msg(data.message, data.success?'success':'error'); if(data.success){ modal.style.display='none'; load(); }
  });
  load();
});
