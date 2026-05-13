document.addEventListener('DOMContentLoaded', () => {
  const apiBase = '/PetsAccessories/admin/backend/api';
  const msgBox = document.getElementById('messagesContainer');

  const pageBody = document.getElementById('pagesTableBody');
  const postBody = document.getElementById('postsTableBody');

  const pageSearch = document.getElementById('pageSearch');
  const postSearch = document.getElementById('postSearch');

  const pageModal = document.getElementById('pageModal');
  const postModal = document.getElementById('postModal');

  const pageId = document.getElementById('pageId');
  const pageTitle = document.getElementById('pageTitle');
  const pageSlug = document.getElementById('pageSlug');
  const pageContent = document.getElementById('pageContent');
  const pageModalTitle = document.getElementById('pageModalTitle');
  const postId = document.getElementById('postId');
  const postTitle = document.getElementById('postTitle');
  const postSlug = document.getElementById('postSlug');
  const postCategory = document.getElementById('postCategory');
  const postStatus = document.getElementById('postStatus');
  const postThumbnail = document.getElementById('postThumbnail');
  const postThumbnailFile = document.getElementById('postThumbnailFile');
  const postThumbnailPreview = document.getElementById('postThumbnailPreview');
  const postThumbnailName = document.getElementById('postThumbnailName');
  const postContent = document.getElementById('postContent');
  const postModalTitle = document.getElementById('postModalTitle');
  let pages = [];
  let posts = [];
  let editingPageId = null;
  let editingPostId = null;

  const escapeHtml = (value) => String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');

  const jsString = (value) => JSON.stringify(String(value ?? ''));

  const msg = (text, type = 'success') => {
    if (!msgBox) return;
    msgBox.innerHTML = `<div class="message ${type}">${escapeHtml(text)}</div>`;
    setTimeout(() => {
      if (msgBox) msgBox.innerHTML = '';
    }, 3000);
  };

  const openModal = (modal) => {
    if (modal) modal.style.display = 'block';
  };

  const closeModal = (modal) => {
    if (modal) modal.style.display = 'none';
  };

  // Preview thumbnail when file selected
  postThumbnailFile?.addEventListener('change', (e) => {
    const file = e.target.files?.[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = (event) => {
        postThumbnailPreview.src = event.target.result;
        postThumbnailPreview.style.display = 'block';
        postThumbnailName.textContent = file.name;
        postThumbnailName.style.display = 'block';
      };
      reader.readAsDataURL(file);
    }
  });

  async function loadPages() {
    if (!pageBody) return;
    const params = new URLSearchParams({ search: pageSearch?.value.trim() || '', page: 1, limit: 100 });
    const res = await fetch(`${apiBase}/pages/list.php?${params}`);
    const data = await res.json();
    if (!data.success) return msg(data.message || 'Không tải được trang', 'error');
    pages = data.data || [];
    if (!pages.length) {
      pageBody.innerHTML = '<tr><td colspan="4" style="text-align:center">Không có dữ liệu</td></tr>';
      return;
    }
    pageBody.innerHTML = pages.map(p => `
      <tr>
        <td>${p.page_id}</td>
        <td>${escapeHtml(p.page_title)}</td>
        <td>${escapeHtml(p.page_slug)}</td>
        <td>
          <div class="actions-cell">
            <button class="action-btn edit" onclick="window.__editPage(${p.page_id})">✏️ Sửa</button>
            <button class="action-btn delete" onclick="window.__deletePage(${p.page_id}, ${jsString(p.page_title)})">🗑️ Xóa</button>
          </div>
        </td>
      </tr>
    `).join('');
  }

  async function loadPosts() {
    if (!postBody) return;
    const params = new URLSearchParams({ search: postSearch?.value.trim() || '', page: 1, limit: 100 });
    const res = await fetch(`${apiBase}/posts/list.php?${params}`);
    const data = await res.json();
    if (!data.success) return msg(data.message || 'Không tải được bài viết', 'error');
    posts = data.data || [];
    if (!posts.length) {
      postBody.innerHTML = '<tr><td colspan="6" style="text-align:center">Không có dữ liệu</td></tr>';
      return;
    }
    postBody.innerHTML = posts.map(p => `
      <tr>
        <td>${p.post_id}</td>
        <td>
          <img src="/PetsAccessories/admin/backend/uploads/posts/${escapeHtml(p.thumbnail || 'default.jpg')}" alt="${escapeHtml(p.title)}" style="max-width: 100px; max-height: 80px; border-radius: 4px; object-fit: cover;">
        </td>
        <td>${escapeHtml(p.title)}</td>
        <td>${escapeHtml(p.category)}</td>
        <td>${p.status == 1 ? 'Hiện' : 'Ẩn'}</td>
        <td>
          <div class="actions-cell">
            <button class="action-btn edit" onclick="window.__editPost(${p.post_id})">✏️ Sửa</button>
            <button class="action-btn delete" onclick="window.__deletePost(${p.post_id}, ${jsString(p.title)})">🗑️ Xóa</button>
          </div>
        </td>
      </tr>
    `).join('');
  }

  window.__editPage = (id) => {
    const page = pages.find(item => item.page_id === id);
    if (!page) return msg('Không tìm thấy trang', 'error');
    editingPageId = id;
    pageId.value = String(id);
    pageTitle.value = page.page_title || '';
    pageSlug.value = page.page_slug || '';
    pageContent.value = page.page_content || '';
    pageModalTitle.textContent = '✏️ Sửa Trang';
    openModal(pageModal);
  };

  window.__editPost = (id) => {
    const post = posts.find(item => item.post_id === id);
    if (!post) return msg('Không tìm thấy bài viết', 'error');
    editingPostId = id;
    postId.value = String(id);
    postTitle.value = post.title || '';
    postSlug.value = post.slug || '';
    postCategory.value = post.category || 'blog';
    postStatus.value = String(post.status ?? 1);
    postThumbnail.value = post.thumbnail || '';
    postContent.value = post.content || '';
    
    // Show old thumbnail preview when editing
    if (post.thumbnail) {
      postThumbnailPreview.src = `/PetsAccessories/admin/backend/uploads/posts/${post.thumbnail}`;
      postThumbnailPreview.style.display = 'block';
      postThumbnailName.textContent = post.thumbnail;
      postThumbnailName.style.display = 'block';
    } else {
      postThumbnailPreview.style.display = 'none';
      postThumbnailName.style.display = 'none';
    }
    
    postModalTitle.textContent = '✏️ Sửa Bài Viết';
    openModal(postModal);
  };

  window.__deletePage = async (id, name) => {
    if (!confirm(`Xóa trang "${name}"?`)) return;
    const res = await fetch(`${apiBase}/pages/delete.php`, {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ page_id: id })
    });
    const data = await res.json();
    msg(data.message, data.success ? 'success' : 'error');
    if (data.success) loadPages();
  };

  window.__deletePost = async (id, name) => {
    if (!confirm(`Xóa bài viết "${name}"?`)) return;
    const res = await fetch(`${apiBase}/posts/delete.php`, {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ post_id: id })
    });
    const data = await res.json();
    msg(data.message, data.success ? 'success' : 'error');
    if (data.success) loadPosts();
  };

  const savePage = async () => {
    const payload = {
      page_title: pageTitle.value.trim(),
      page_slug: pageSlug.value.trim(),
      page_content: pageContent.value.trim()
    };
    if (editingPageId) payload.page_id = editingPageId;
    const res = await fetch(`${apiBase}/pages/${editingPageId ? 'update.php' : 'create.php'}`, {
      method: editingPageId ? 'PUT' : 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    msg(data.message, data.success ? 'success' : 'error');
    if (data.success) {
      closeModal(pageModal);
      editingPageId = null;
      if (pageId) pageId.value = '';
      loadPages();
    }
  };

  const savePost = async () => {
    // Validate title, slug, content
    if (!postTitle.value.trim()) {
      msg('Vui lòng nhập tiêu đề', 'error');
      return;
    }
    if (!postSlug.value.trim()) {
      msg('Vui lòng nhập slug', 'error');
      return;
    }
    if (!postContent.value.trim()) {
      msg('Vui lòng nhập nội dung', 'error');
      return;
    }
    
    // Validate thumbnail: required for new post, optional for edit
    const file = postThumbnailFile?.files?.[0];
    const hasExistingThumbnail = postThumbnail.value.trim();
    
    if (!editingPostId && !file && !hasExistingThumbnail) {
      msg('Vui lòng chọn ảnh bài viết', 'error');
      return;
    }
    
    const payload = {
      title: postTitle.value.trim(),
      slug: postSlug.value.trim(),
      content: postContent.value.trim(),
      category: postCategory.value,
      status: parseInt(postStatus.value || '1', 10)
    };
    
    // Handle thumbnail upload
    let thumbnailFilename = postThumbnail.value.trim();
    
    if (file) {
      // Upload thumbnail file
      const formData = new FormData();
      formData.append('image', file);
      const uploadRes = await fetch(`${apiBase}/posts/upload-image.php`, {
        method: 'POST',
        body: formData
      });
      const uploadData = await uploadRes.json();
      if (!uploadData.success) {
        msg('Lỗi upload ảnh: ' + (uploadData.message || 'Không xác định'), 'error');
        return;
      }
      thumbnailFilename = uploadData.filename;
    }
    
    if (thumbnailFilename) payload.thumbnail = thumbnailFilename;
    if (editingPostId) payload.post_id = editingPostId;
    
    const res = await fetch(`${apiBase}/posts/${editingPostId ? 'update.php' : 'create.php'}`, {
      method: editingPostId ? 'POST' : 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    msg(data.message, data.success ? 'success' : 'error');
    if (data.success) {
      closeModal(postModal);
      editingPostId = null;
      if (postId) postId.value = '';
      if (postThumbnailFile) postThumbnailFile.value = '';
      loadPosts();
    }
  };

  document.getElementById('addPageBtn')?.addEventListener('click', () => {
    editingPageId = null;
    if (pageId) pageId.value = '';
    if (pageTitle) pageTitle.value = '';
    if (pageSlug) pageSlug.value = '';
    if (pageContent) pageContent.value = '';
    pageModalTitle.textContent = '➕ Thêm Trang';
    openModal(pageModal);
  });

  document.getElementById('addPostBtn')?.addEventListener('click', () => {
    editingPostId = null;
    if (postId) postId.value = '';
    if (postTitle) postTitle.value = '';
    if (postSlug) postSlug.value = '';
    if (postCategory) postCategory.value = 'blog';
    if (postStatus) postStatus.value = '1';
    if (postThumbnail) postThumbnail.value = '';
    if (postThumbnailFile) postThumbnailFile.value = '';
    postThumbnailPreview.style.display = 'none';
    postThumbnailName.style.display = 'none';
    if (postContent) postContent.value = '';
    postModalTitle.textContent = '➕ Thêm Bài Viết';
    openModal(postModal);
  });

  document.getElementById('pageForm')?.addEventListener('submit', (e) => {
    e.preventDefault();
    savePage();
  });

  document.getElementById('postForm')?.addEventListener('submit', (e) => {
    e.preventDefault();
    savePost();
  });

  document.getElementById('closePageModalBtn')?.addEventListener('click', () => closeModal(pageModal));
  document.getElementById('cancelPageBtn')?.addEventListener('click', () => closeModal(pageModal));
  document.getElementById('closePostModalBtn')?.addEventListener('click', () => closeModal(postModal));
  document.getElementById('cancelPostBtn')?.addEventListener('click', () => closeModal(postModal));

  document.getElementById('filterPagesBtn')?.addEventListener('click', loadPages);
  document.getElementById('filterPostsBtn')?.addEventListener('click', loadPosts);

  document.getElementById('resetPagesBtn')?.addEventListener('click', () => {
    if (pageSearch) pageSearch.value = '';
    loadPages();
  });

  document.getElementById('resetPostsBtn')?.addEventListener('click', () => {
    if (postSearch) postSearch.value = '';
    loadPosts();
  });

  loadPages();
  loadPosts();
});
