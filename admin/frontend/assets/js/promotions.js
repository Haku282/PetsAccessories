document.addEventListener('DOMContentLoaded', () => {
  const apiBase = '/PetsAccessories/admin/backend/api/promotions';
  const tableBody = document.getElementById('promotionsTableBody');
  const pagination = document.getElementById('paginationContainer');
  const messageBox = document.getElementById('messagesContainer');
  const searchInput = document.getElementById('searchInput');
  const statusFilter = document.getElementById('statusFilter');
  const addBtn = document.getElementById('addPromotionBtn');
  const modal = document.getElementById('promotionModal');
  const form = document.getElementById('promotionForm');
  const saveBtn = document.getElementById('savePromotionBtn');
  const titleEl = document.getElementById('promotionModalTitle');

  let currentPage = 1;
  let editingId = null;

  const showMessage = (msg, type='success') => {
    messageBox.innerHTML = `<div class="message ${type}">${msg}</div>`;
    setTimeout(() => messageBox.innerHTML = '', 3000);
  };

  const money = v => new Intl.NumberFormat('vi-VN').format(Number(v || 0)) + ' ₫';

  const load = async (page = 1) => {
    currentPage = page;
    const params = new URLSearchParams({ page, search: searchInput.value.trim(), status: statusFilter.value });
    const res = await fetch(`${apiBase}/list.php?${params}`);
    const data = await res.json();
    if (!data.success) return showMessage(data.message || 'Không tải được khuyến mãi', 'error');

    tableBody.innerHTML = data.data.length ? data.data.map(p => `
      <tr>
        <td>${p.promotion_id}</td>
        <td><strong>${p.promotion_name}</strong></td>
        <td>${p.discount_percent}%</td>
        <td>${p.start_date || '-'}</td>
        <td>${p.end_date || '-'}</td>
        <td>${p.status == 1 ? 'Kích hoạt' : 'Vô hiệu'}</td>
        <td>
          <button onclick="window.__editPromotion(${p.promotion_id})">Sửa</button>
          <button onclick="window.__deletePromotion(${p.promotion_id}, '${p.promotion_name.replace(/'/g, "\\'")}')">Xóa</button>
        </td>
      </tr>`).join('') : `<tr><td colspan="7" style="text-align:center">Không có dữ liệu</td></tr>`;

    const pages = data.pagination.total_pages || 1;
    pagination.innerHTML = Array.from({length: pages}, (_, i) => i + 1).map(n => `<button ${n===data.pagination.current_page ? 'class="active"' : ''} onclick="window.__loadPromotionPage(${n})">${n}</button>`).join('');
  };

  const openModal = (promotion = null) => {
    form.reset();
    editingId = promotion?.promotion_id || null;
    titleEl.textContent = editingId ? '✏️ Sửa Khuyến Mãi' : '➕ Thêm Khuyến Mãi';
    if (promotion) {
      form.promotion_name.value = promotion.promotion_name;
      form.discount_percent.value = promotion.discount_percent;
      form.description.value = promotion.description || '';
      form.start_date.value = promotion.start_date ? promotion.start_date.slice(0, 10) : '';
      form.end_date.value = promotion.end_date ? promotion.end_date.slice(0, 10) : '';
      form.status.value = promotion.status;
    }
    modal.style.display = 'block';
  };

  window.__loadPromotionPage = load;
  window.__editPromotion = async (id) => {
    const res = await fetch(`${apiBase}/list.php?page=1&limit=1000`);
    const data = await res.json();
    const promo = data.data.find(x => x.promotion_id === id);
    if (promo) openModal(promo);
  };
  window.__deletePromotion = async (id, name) => {
    if (!confirm(`Xóa khuyến mãi "${name}"?`)) return;
    const res = await fetch(`${apiBase}/delete.php`, {method:'DELETE', headers:{'Content-Type':'application/json'}, body: JSON.stringify({promotion_id:id})});
    const data = await res.json();
    showMessage(data.message, data.success ? 'success' : 'error');
    if (data.success) load(currentPage);
  };

  addBtn?.addEventListener('click', () => openModal());
  document.getElementById('filterBtn')?.addEventListener('click', () => load(1));
  document.getElementById('resetBtn')?.addEventListener('click', () => { searchInput.value=''; statusFilter.value=''; load(1); });
  document.getElementById('closeModalBtn')?.addEventListener('click', () => modal.style.display = 'none');
  document.getElementById('cancelModalBtn')?.addEventListener('click', () => modal.style.display = 'none');
  saveBtn?.addEventListener('click', async () => {
    const payload = {
      promotion_name: form.promotion_name.value.trim(),
      description: form.description.value.trim(),
      discount_percent: parseInt(form.discount_percent.value || '0', 10),
      start_date: form.start_date.value || null,
      end_date: form.end_date.value || null,
      status: parseInt(form.status.value || '1', 10)
    };
    if (editingId) payload.promotion_id = editingId;
    const url = editingId ? `${apiBase}/update.php` : `${apiBase}/create.php`;
    const res = await fetch(url, {method: editingId ? 'PUT' : 'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload)});
    const data = await res.json();
    showMessage(data.message, data.success ? 'success' : 'error');
    if (data.success) { modal.style.display = 'none'; load(currentPage); }
  });

  load();
});
