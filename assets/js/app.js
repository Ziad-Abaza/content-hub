/**
 * Marketing Content Hub - Client-side Application Logic
 * Pure Vanilla JS + Navigator Clipboard API
 */

let state = {
    posts: window.INITIAL_POSTS || [],
    campaigns: window.INITIAL_CAMPAIGNS || [],
    currentChannel: 'instagram', // instagram, tiktok, linkedin, twitter
    filterCampaignId: null,
    filterStatus: '',
    searchQuery: '',
    selectedAssets: new Set(),
    modalChannel: 'instagram'
};

// Character limits per platform
const PLATFORM_LIMITS = {
    instagram: 2200,
    tiktok: 2200,
    linkedin: 3000,
    twitter: 280
};

// Initialize app on DOM Load
document.addEventListener('DOMContentLoaded', () => {
    renderPosts();
    updateBatchBtn();
});

// Toast notification helper
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    const isSuccess = type === 'success';
    toast.className = `pointer-events-auto flex items-center gap-2.5 px-4 py-3 rounded-xl shadow-2xl text-xs font-medium border transition-all duration-300 transform translate-y-2 opacity-0 ${
        isSuccess 
        ? 'bg-slate-900 border-emerald-500/40 text-emerald-300 shadow-emerald-950/40' 
        : 'bg-slate-900 border-rose-500/40 text-rose-300 shadow-rose-950/40'
    }`;
    
    toast.innerHTML = `
        <i data-lucide="${isSuccess ? 'check-circle' : 'alert-circle'}" class="w-4 h-4"></i>
        <span>${message}</span>
    `;

    container.appendChild(toast);
    lucide.createIcons();

    // Trigger enter animation
    requestAnimationFrame(() => {
        toast.classList.remove('translate-y-2', 'opacity-0');
    });

    // Remove after 3 seconds
    setTimeout(() => {
        toast.classList.add('translate-y-2', 'opacity-0');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Copy to Clipboard Core Function
async function copyToClipboard(text, successMessage = 'Copied to clipboard!', postId = null) {
    if (!text) {
        showToast('Nothing to copy', 'error');
        return;
    }

    try {
        await navigator.clipboard.writeText(text);
        showToast(successMessage, 'success');

        // Track copy event on backend if post ID is provided
        if (postId) {
            trackCopyEvent(postId);
        }
    } catch (err) {
        // Fallback for older browsers
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        document.body.appendChild(textarea);
        textarea.focus();
        textarea.select();
        try {
            document.execCommand('copy');
            showToast(successMessage, 'success');
            if (postId) trackCopyEvent(postId);
        } catch (e) {
            showToast('Failed to copy to clipboard', 'error');
        }
        document.body.removeChild(textarea);
    }
}

// Track copy analytics
async function trackCopyEvent(postId) {
    try {
        const res = await fetch(`api/posts/${postId}/track-copy`, { method: 'POST' });
        const json = await res.json();
        if (json.status === 'success') {
            const countElem = document.getElementById(`copy-count-${postId}`);
            if (countElem) {
                countElem.textContent = json.copy_count;
            }
            // Update local state
            const p = state.posts.find(item => item.id == postId);
            if (p) p.copy_count = json.copy_count;
        }
    } catch (e) {
        console.error('Analytics track error:', e);
    }
}

// Format caption + hashtags for a specific channel
function getFormattedPost(post, channel) {
    const channelCaps = post.channel_captions || {};
    let caption = channelCaps[channel] || post.primary_caption || '';
    const tags = Array.isArray(post.hashtags) ? post.hashtags : [];
    const tagString = tags.join(' ');

    if (tagString) {
        return `${caption}\n\n${tagString}`;
    }
    return caption;
}

// Quick Copy Actions
function copyCaptionOnly(postId) {
    const post = state.posts.find(p => p.id == postId);
    if (!post) return;
    const channelCaps = post.channel_captions || {};
    const text = channelCaps[state.currentChannel] || post.primary_caption;
    copyToClipboard(text, `Copied ${capitalize(state.currentChannel)} caption!`, postId);
}

function copyHashtagsOnly(postId) {
    const post = state.posts.find(p => p.id == postId);
    if (!post) return;
    const tags = Array.isArray(post.hashtags) ? post.hashtags : [];
    if (tags.length === 0) {
        showToast('No hashtags found for this post', 'error');
        return;
    }
    copyToClipboard(tags.join(' '), 'Copied hashtags bundle!', postId);
}

function copyFullPost(postId) {
    const post = state.posts.find(p => p.id == postId);
    if (!post) return;
    const text = getFormattedPost(post, state.currentChannel);
    copyToClipboard(text, `Copied full ${capitalize(state.currentChannel)} post!`, postId);
}

// Filter Actions
function setGlobalChannel(channel) {
    state.currentChannel = channel;
    
    // Update active tab buttons
    document.querySelectorAll('.global-tab').forEach(btn => {
        btn.classList.remove('active-global-tab', 'text-white', 'bg-slate-800');
        btn.classList.add('text-slate-400');
    });

    const activeBtn = event.currentTarget;
    if (activeBtn) {
        activeBtn.classList.add('active-global-tab', 'text-white', 'bg-slate-800');
        activeBtn.classList.remove('text-slate-400');
    }

    renderPosts();
}

function filterByCampaign(campaignId) {
    state.filterCampaignId = campaignId;

    document.querySelectorAll('.camp-filter-btn').forEach(btn => {
        btn.classList.remove('active-filter', 'text-white', 'bg-slate-800/80');
        btn.classList.add('text-slate-400');
    });

    const activeBtn = event.currentTarget;
    if (activeBtn) {
        activeBtn.classList.add('active-filter', 'text-white', 'bg-slate-800/80');
        activeBtn.classList.remove('text-slate-400');
    }

    renderPosts();
}

function filterByStatus(status) {
    state.filterStatus = status;

    document.querySelectorAll('.status-filter-btn').forEach(btn => {
        btn.classList.remove('active-filter', 'text-white', 'bg-slate-800/80');
        btn.classList.add('text-slate-400');
    });

    const activeBtn = event.currentTarget;
    if (activeBtn) {
        activeBtn.classList.add('active-filter', 'text-white', 'bg-slate-800/80');
        activeBtn.classList.remove('text-slate-400');
    }

    renderPosts();
}

function handleSearch(query) {
    state.searchQuery = query.toLowerCase().trim();
    renderPosts();
}

// Render Posts to Grid
function renderPosts() {
    const grid = document.getElementById('posts-grid');
    const emptyState = document.getElementById('empty-state');
    const countDisplay = document.getElementById('post-count');
    if (!grid) return;

    const filtered = state.posts.filter(post => {
        // Campaign filter
        if (state.filterCampaignId && post.campaign_id != state.filterCampaignId) {
            return false;
        }
        // Status filter
        if (state.filterStatus && post.status !== state.filterStatus) {
            return false;
        }
        // Search filter
        if (state.searchQuery) {
            const matchTitle = post.title && post.title.toLowerCase().includes(state.searchQuery);
            const matchCap = post.primary_caption && post.primary_caption.toLowerCase().includes(state.searchQuery);
            const matchCampaign = post.campaign_title && post.campaign_title.toLowerCase().includes(state.searchQuery);
            const matchTags = Array.isArray(post.hashtags) && post.hashtags.some(t => t.toLowerCase().includes(state.searchQuery));
            if (!matchTitle && !matchCap && !matchCampaign && !matchTags) {
                return false;
            }
        }
        return true;
    });

    if (countDisplay) countDisplay.textContent = filtered.length;

    if (filtered.length === 0) {
        grid.innerHTML = '';
        emptyState.classList.remove('hidden');
        return;
    }

    emptyState.classList.add('hidden');

    grid.innerHTML = filtered.map(post => {
        const channelCaps = post.channel_captions || {};
        const currentCap = channelCaps[state.currentChannel] || post.primary_caption;
        const charLimit = PLATFORM_LIMITS[state.currentChannel] || 2200;
        const charCount = currentCap.length;
        const isOverLimit = charCount > charLimit;
        const tags = Array.isArray(post.hashtags) ? post.hashtags : [];

        // Primary media preview
        const firstMedia = post.media && post.media.length > 0 ? post.media[0] : null;
        const hasMultipleMedia = post.media && post.media.length > 1;

        return `
        <div class="glass-card rounded-2xl overflow-hidden flex flex-col justify-between border border-slate-800 hover:border-slate-700 bg-slate-900/60" id="post-card-${post.id}">
            
            <div>
                <!-- Card Header -->
                <div class="p-5 pb-3 border-b border-slate-800/60 flex items-start justify-between gap-3">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            ${post.campaign_title ? `
                                <span class="px-2 py-0.5 rounded-md bg-brand-500/10 text-brand-400 text-[10px] font-semibold uppercase tracking-wider">
                                    ${escapeHtml(post.campaign_title)}
                                </span>
                            ` : ''}
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold uppercase tracking-wider ${
                                post.status === 'ready' ? 'bg-emerald-500/10 text-emerald-400' :
                                post.status === 'review' ? 'bg-amber-500/10 text-amber-400' : 'bg-slate-800 text-slate-400'
                            }">
                                ${post.status}
                            </span>
                        </div>
                        <h3 class="font-bold text-white text-base leading-snug">${escapeHtml(post.title)}</h3>
                    </div>

                    <!-- Post Menu & Copy Count -->
                    <div class="flex items-center gap-1.5 text-xs text-slate-500">
                        <span title="Times copied to clipboard" class="flex items-center gap-1 px-2 py-0.5 rounded-full bg-slate-950/80 border border-slate-800 text-[11px] font-mono text-slate-400">
                            <i data-lucide="copy" class="w-3 h-3 text-brand-400"></i>
                            <span id="copy-count-${post.id}">${post.copy_count || 0}</span>
                        </span>
                        <button onclick="deletePost(${post.id})" class="p-1 rounded-lg text-slate-500 hover:text-rose-400 hover:bg-slate-800/60 transition-colors" title="Delete Post">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>

                <!-- Media Preview Area (if exists) -->
                ${firstMedia ? `
                    <div class="relative bg-slate-950 aspect-video overflow-hidden group border-b border-slate-800">
                        <img src="${firstMedia.file_path}" alt="${escapeHtml(firstMedia.original_name)}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300 cursor-pointer" onclick="openLightbox('${firstMedia.file_path}', '${escapeHtml(firstMedia.original_name)}', '${firstMedia.width || 0}x${firstMedia.height || 0}', '${formatBytes(firstMedia.file_size)}', ${firstMedia.id})">
                        
                        <!-- Aspect Ratio & Media Count Badges -->
                        <div class="absolute top-3 left-3 flex items-center gap-1.5 pointer-events-none">
                            <span class="px-2 py-0.5 rounded-lg bg-black/70 backdrop-blur-md text-[10px] font-mono font-bold text-white border border-white/10">
                                ${firstMedia.aspect_ratio || '1:1'}
                            </span>
                            ${hasMultipleMedia ? `
                                <span class="px-2 py-0.5 rounded-lg bg-brand-600/80 backdrop-blur-md text-[10px] font-semibold text-white">
                                    +${post.media.length - 1} more
                                </span>
                            ` : ''}
                        </div>

                        <!-- Multi-select checkbox for batch download -->
                        <label class="absolute top-3 right-3 p-1.5 rounded-lg bg-black/70 backdrop-blur-md border border-white/10 cursor-pointer flex items-center gap-1 hover:bg-black/90">
                            <input type="checkbox" onchange="toggleAssetSelection(${firstMedia.id}, this)" class="w-3.5 h-3.5 rounded text-brand-600 focus:ring-0 cursor-pointer" ${state.selectedAssets.has(firstMedia.id) ? 'checked' : ''}>
                        </label>

                        <!-- Hover Quick Download -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end justify-between p-3 pointer-events-none">
                            <span class="text-xs text-white font-medium truncate">${escapeHtml(firstMedia.original_name)}</span>
                            <a href="download?id=${firstMedia.id}" class="pointer-events-auto p-1.5 rounded-lg bg-white/20 hover:bg-white/40 text-white backdrop-blur-md transition-colors" title="Download Asset">
                                <i data-lucide="download" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </div>
                ` : ''}

                <!-- Caption Body for Current Platform -->
                <div class="p-5 space-y-3">
                    
                    <div class="relative bg-slate-950/70 rounded-xl p-3.5 border border-slate-800/80 group">
                        <div class="flex items-center justify-between text-[11px] font-medium text-slate-500 mb-1.5">
                            <span class="uppercase font-mono text-brand-400">${capitalize(state.currentChannel)} Copy</span>
                            <span class="${isOverLimit ? 'text-rose-400 font-bold' : ''}">
                                ${charCount} / ${charLimit} chars
                            </span>
                        </div>
                        <p class="text-xs text-slate-300 whitespace-pre-line leading-relaxed line-clamp-4 select-all">${escapeHtml(currentCap)}</p>
                    </div>

                    <!-- Hashtag Pill Cloud -->
                    ${tags.length > 0 ? `
                        <div class="flex flex-wrap gap-1.5 pt-1">
                            ${tags.map(tag => `
                                <span class="px-2 py-0.5 rounded-md bg-slate-800/80 text-brand-300 text-[11px] font-medium hover:bg-slate-700 transition-colors cursor-pointer" onclick="copyToClipboard('${tag}', 'Copied ${tag}!')">
                                    ${escapeHtml(tag)}
                                </span>
                            `).join('')}
                        </div>
                    ` : ''}

                </div>
            </div>

            <!-- Card Bottom One-Click Quick Actions Toolbar -->
            <div class="p-4 pt-2 border-t border-slate-800/60 bg-slate-950/40 grid grid-cols-3 gap-2">
                <button onclick="copyCaptionOnly(${post.id})" class="flex items-center justify-center gap-1.5 py-2 px-2 rounded-xl bg-slate-800/80 hover:bg-slate-700 text-slate-200 hover:text-white text-xs font-medium transition-all" title="Copy only the caption text">
                    <i data-lucide="file-text" class="w-3.5 h-3.5 text-brand-400"></i> Caption
                </button>
                <button onclick="copyHashtagsOnly(${post.id})" class="flex items-center justify-center gap-1.5 py-2 px-2 rounded-xl bg-slate-800/80 hover:bg-slate-700 text-slate-200 hover:text-white text-xs font-medium transition-all" title="Copy only the hashtags">
                    <i data-lucide="hash" class="w-3.5 h-3.5 text-teal-400"></i> Hashtags
                </button>
                <button onclick="copyFullPost(${post.id})" class="flex items-center justify-center gap-1.5 py-2 px-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold shadow-md shadow-brand-600/20 transition-all" title="Copy formatted post + hashtags">
                    <i data-lucide="copy" class="w-3.5 h-3.5"></i> Full Post
                </button>
            </div>

        </div>
        `;
    }).join('');

    lucide.createIcons();
}

// Lightbox modal operations
function openLightbox(url, filename, dims, size, assetId) {
    const modal = document.getElementById('lightbox-modal');
    const img = document.getElementById('lightbox-img');
    const nameElem = document.getElementById('lightbox-filename');
    const dimsElem = document.getElementById('lightbox-dims');
    const sizeElem = document.getElementById('lightbox-filesize');
    const dlBtn = document.getElementById('lightbox-download-btn');

    if (!modal) return;

    img.src = url;
    img.classList.remove('hidden');
    nameElem.textContent = filename;
    dimsElem.textContent = dims;
    sizeElem.textContent = size;
    dlBtn.href = `download?id=${assetId}`;

    modal.classList.remove('hidden');
}

function closeLightbox() {
    const modal = document.getElementById('lightbox-modal');
    if (modal) modal.classList.add('hidden');
}

// Create Post Modal operations
function openCreateModal() {
    const modal = document.getElementById('create-post-modal');
    if (modal) {
        document.getElementById('post-form').reset();
        document.getElementById('selected-files-list').innerHTML = '';
        modal.classList.remove('hidden');
    }
}

function closePostModal() {
    const modal = document.getElementById('create-post-modal');
    if (modal) modal.classList.add('hidden');
}

function switchModalChannel(channel) {
    state.modalChannel = channel;
    document.querySelectorAll('.modal-tab').forEach(tab => {
        tab.classList.remove('active-modal-tab', 'text-white', 'bg-slate-800');
        tab.classList.add('text-slate-400');
    });
    event.currentTarget.classList.add('active-modal-tab', 'text-white', 'bg-slate-800');
    event.currentTarget.classList.remove('text-slate-400');

    document.querySelectorAll('.modal-channel-pane').forEach(pane => pane.classList.add('hidden'));
    const activePane = document.getElementById(`channel-input-${channel}`);
    if (activePane) activePane.classList.remove('hidden');
}

function handleFileSelect(input) {
    const list = document.getElementById('selected-files-list');
    list.innerHTML = '';
    if (input.files) {
        Array.from(input.files).forEach(file => {
            const item = document.createElement('div');
            item.className = 'flex items-center justify-between px-3 py-1.5 bg-slate-950 rounded-lg text-xs text-slate-300 border border-slate-800';
            item.innerHTML = `
                <span class="truncate max-w-xs">${file.name}</span>
                <span class="text-slate-500 font-mono">${formatBytes(file.size)}</span>
            `;
            list.appendChild(item);
        });
    }
}

// Post Submission Handler (Supports File Uploads)
async function handlePostSubmit(e) {
    e.preventDefault();
    const btn = document.getElementById('save-post-btn');
    btn.disabled = true;
    btn.textContent = 'Saving...';

    const form = document.getElementById('post-form');
    const formData = new FormData(form);

    // Collect channel captions
    const channelCaps = {
        instagram: document.getElementById('channel-cap-instagram').value,
        tiktok: document.getElementById('channel-cap-tiktok').value,
        linkedin: document.getElementById('channel-cap-linkedin').value,
        twitter: document.getElementById('channel-cap-twitter').value
    };
    formData.append('channel_captions', JSON.stringify(channelCaps));

    // Hashtags
    const tagsVal = document.getElementById('post-hashtags').value;
    formData.append('hashtags', tagsVal);

    try {
        const res = await fetch('api/posts', {
            method: 'POST',
            body: formData
        });
        const json = await res.json();
        if (json.status === 'success') {
            showToast('Post created successfully!');
            state.posts.unshift(json.data);
            renderPosts();
            closePostModal();
        } else {
            showToast(json.message || 'Error saving post', 'error');
        }
    } catch (err) {
        showToast('Network error while saving post', 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Save & Publish Post';
    }
}

// Delete Post
async function deletePost(postId) {
    if (!confirm('Are you sure you want to delete this marketing post and its assets?')) {
        return;
    }

    try {
        const res = await fetch(`api/posts/${postId}`, { method: 'DELETE' });
        const json = await res.json();
        if (json.status === 'success') {
            showToast('Post deleted');
            state.posts = state.posts.filter(p => p.id != postId);
            renderPosts();
        } else {
            showToast('Failed to delete post', 'error');
        }
    } catch (e) {
        showToast('Error deleting post', 'error');
    }
}

// Batch Asset Selection
function toggleAssetSelection(assetId, checkbox) {
    if (checkbox.checked) {
        state.selectedAssets.add(assetId);
    } else {
        state.selectedAssets.delete(assetId);
    }
    updateBatchBtn();
}

function updateBatchBtn() {
    const count = document.getElementById('selected-count');
    const btn = document.getElementById('batch-zip-btn');
    if (count) count.textContent = state.selectedAssets.size;
    if (btn) btn.disabled = state.selectedAssets.size === 0;
}

// Download Batch ZIP
function downloadSelectedBatchZip() {
    if (state.selectedAssets.size === 0) return;
    const ids = Array.from(state.selectedAssets);

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'batch-download';

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'asset_ids';
    input.value = ids.join(',');
    form.appendChild(input);

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);

    showToast(`Downloading ZIP with ${ids.length} assets...`, 'success');
}

// Utilities
function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function formatBytes(bytes, decimals = 1) {
    if (!bytes || bytes === 0) return '0 B';
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}
