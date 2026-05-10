document.addEventListener('DOMContentLoaded', () => {
  const apiBase = '/PetsAccessories/admin/backend/api';
  const tableBody = document.getElementById('reviewsTableBody');
  const reportBody = document.getElementById('reportsTableBody');
  const messageBox = document.getElementById('messagesContainer');
  const searchInput = document.getElementById('searchInput');
  const statusFilter = document.getElementById('statusFilter');
  const reportStatusFilter = document.getElementById('reportStatusFilter');

  let page = 1;
  let reportPage = 1;

  const msg = (t, type='success') => { messageBox.innerHTML = `<div class="message ${type}">${t}</div>`; setTimeout(()=>messageBox.innerHTML='',3000); };
  const statusLabel = v => v == 1 ? 'Hiển thị' : 'Ẩn';

  async function loadReviews(p=1) {
    page = p;
    const params = new URLSearchParams({ page: p, search: searchInput.value.trim(), status: statusFilter.value });
    const res = await fetch(`${apiBase}/reviews/list.php?${params}`);
    const data = await res.json();
    if (!data.success) return msg(data.message, 'error');
    tableBody.innerHTML = data.data.length ? data.data.map(r => `<tr><td>${r.review_id}</td><td>${r.product_name}</td><td>${r.user_name}</td><td>${r.rating}</td><td>${(r.comment||'').substring(0,80)}</td><td>${statusLabel(r.status)}</td><td><button onclick="window.__toggleReview(${r.review_id}, ${r.status})">${r.status==1?'Ẩn':'Hiện'}</button><button onclick="window.__deleteReview(${r.review_id})">Xóa</button></td></tr>`).join('') : `<tr><td colspan="7" style="text-align:center">Không có dữ liệu</td></tr>`;
  }

  async function loadReports(p=1) {
    reportPage = p;
    const params = new URLSearchParams({ page: p, status: reportStatusFilter.value });
    const res = await fetch(`${apiBase}/review_reports/list.php?${params}`);
    const data = await res.json();
    if (!data.success) return msg(data.message, 'error');
    reportBody.innerHTML = data.data.length ? data.data.map(r => `<tr><td>${r.report_id}</td><td>${r.product_name||'-'}</td><td>${r.reporter_name||'-'}</td><td>${r.reason}</td><td>${r.status}</td><td><button onclick="window.__resolveReport(${r.report_id})">Đánh dấu xử lý</button></td></tr>`).join('') : `<tr><td colspan="6" style="text-align:center">Không có báo cáo</td></tr>`;
  }

  window.__toggleReview = async (id, current) => {
    const res = await fetch(`${apiBase}/reviews/toggle-status.php`, {method:'PUT', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ review_id:id, status: current==1 ? 0 : 1 })});
    const data = await res.json(); msg(data.message, data.success?'success':'error'); if(data.success) loadReviews(page);
  };
  window.__deleteReview = async (id) => {
    if (!confirm('Xóa bình luận này?')) return;
    const res = await fetch(`${apiBase}/reviews/delete.php`, {method:'DELETE', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ review_id:id })});
    const data = await res.json(); msg(data.message, data.success?'success':'error'); if(data.success) loadReviews(page);
  };
  window.__resolveReport = async (id) => {
    const res = await fetch(`${apiBase}/review_reports/update-status.php`, {method:'PUT', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ report_id:id, status:1, action:'hide_review' })});
    const data = await res.json(); msg(data.message, data.success?'success':'error'); if(data.success) loadReports(reportPage);
  };

  document.getElementById('filterBtn')?.addEventListener('click', () => loadReviews(1));
  document.getElementById('filterReportsBtn')?.addEventListener('click', () => loadReports(1));
  document.getElementById('resetBtn')?.addEventListener('click', () => { searchInput.value=''; statusFilter.value=''; loadReviews(1); });
  document.getElementById('resetReportsBtn')?.addEventListener('click', () => { reportStatusFilter.value=''; loadReports(1); });

  loadReviews();
  loadReports();
});
