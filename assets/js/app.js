/**
 * Marketing Content Hub - Client-side Application Logic
 * Supports Multi-Platform, Scheduling Views, Campaign Buckets, Full Post View/Edit, and Enhanced Authoring
 */

let state = {
    posts: window.INITIAL_POSTS || [],
    campaigns: window.INITIAL_CAMPAIGNS || [],
    currentChannel: 'instagram', // facebook, instagram, tiktok, linkedin, twitter, threads
    filterCampaignId: null,
    filterStatus: '',
    filterSchedule: '', // '', 'latest', 'today', 'this_week'
    searchQuery: '',
    selectedAssets: new Set(),
    modalChannel: 'instagram',
    detailChannel: 'instagram',
    currentDetailPost: null,
    stagedFiles: []
};

// Platform specific metadata & limits
const PLATFORMS = {
    facebook: { name: 'Facebook', limit: 63206, icon: 'facebook', color: 'text-blue-500', maxTags: 10 },
    instagram: { name: 'Instagram', limit: 2200, icon: 'instagram', color: 'text-pink-400', maxTags: 30 },
    tiktok: { name: 'TikTok', limit: 2200, icon: 'video', color: 'text-teal-400', maxTags: 10 },
    linkedin: { name: 'LinkedIn', limit: 3000, icon: 'linkedin', color: 'text-blue-400', maxTags: 10 },
    twitter: { name: 'X (Twitter)', limit: 280, icon: 'twitter', color: 'text-sky-400', maxTags: 5 },
    threads: { name: 'Threads', limit: 500, icon: 'message-circle', color: 'text-slate-300', maxTags: 5 }
};

document.addEventListener('DOMContentLoaded', () => {
    renderPosts();
    updateBatchBtn();
    
    // Live update hashtag count hint on post form
    const hashtagInput = document.getElementById('post-hashtags');
    if (hashtagInput) {
        hashtagInput.addEventListener('input', updateHashtagHint);
    }
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

    requestAnimationFrame(() => {
        toast.classList.remove('translate-y-2', 'opacity-0');
    });

    setTimeout(() => {
        toast.classList.add('translate-y-2', 'opacity-0');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Copy to Clipboard
async function copyToClipboard(text, successMessage = 'Copied to clipboard!', postId = null) {
    if (!text) {
        showToast('Nothing to copy', 'error');
        return;
    }

    try {
        await navigator.clipboard.writeText(text);
        showToast(successMessage, 'success');
        if (postId) trackCopyEvent(postId);
    } catch (err) {
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

async function trackCopyEvent(postId) {
    try {
        const res = await fetch(`api/posts/${postId}/track-copy`, { method: 'POST' });
        const json = await res.json();
        if (json.status === 'success') {
            const countElem = document.getElementById(`copy-count-${postId}`);
            if (countElem) countElem.textContent = json.copy_count;
            const p = state.posts.find(item => item.id == postId);
            if (p) p.copy_count = json.copy_count;
            if (state.currentDetailPost && state.currentDetailPost.id == postId) {
                state.currentDetailPost.copy_count = json.copy_count;
                const detailCount = document.getElementById('detail-copy-count');
                if (detailCount) detailCount.textContent = json.copy_count;
            }
        }
    } catch (e) {
        console.error('Analytics track error:', e);
    }
}

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

// Global Filter Actions
function setGlobalChannel(channel) {
    state.currentChannel = channel;
    document.querySelectorAll('.global-tab').forEach(btn => {
        btn.classList.remove('active-global-tab', 'text-white', 'bg-slate-800');
        btn.classList.add('text-slate-400');
        if (btn.getAttribute('data-channel') === channel) {
            btn.classList.add('active-global-tab', 'text-white', 'bg-slate-800');
            btn.classList.remove('text-slate-400');
        }
    });
    renderPosts();
}

function filterByCampaign(campaignId) {
    state.filterCampaignId = campaignId;
    document.querySelectorAll('.camp-filter-btn').forEach(btn => {
        btn.classList.remove('active-filter', 'text-white', 'bg-slate-800/80');
        btn.classList.add('text-slate-400');
    });

    const activeBtn = event ? event.currentTarget : null;
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

    const activeBtn = event ? event.currentTarget : null;
    if (activeBtn) {
        activeBtn.classList.add('active-filter', 'text-white', 'bg-slate-800/80');
        activeBtn.classList.remove('text-slate-400');
    }
    renderPosts();
}

function filterBySchedule(view) {
    state.filterSchedule = view;
    document.querySelectorAll('.sched-filter-btn').forEach(btn => {
        btn.classList.remove('active-filter', 'text-white', 'bg-slate-800/80');
        btn.classList.add('text-slate-400');
        if (btn.getAttribute('data-sched') === view) {
            btn.classList.add('active-filter', 'text-white', 'bg-slate-800/80');
            btn.classList.remove('text-slate-400');
        }
    });
    renderPosts();
}

function handleSearch(query) {
    state.searchQuery = query.toLowerCase().trim();
    renderPosts();
}

// Render Feed Cards
function renderPosts() {
    const grid = document.getElementById('posts-grid');
    const emptyState = document.getElementById('empty-state');
    const countDisplay = document.getElementById('post-count');
    if (!grid) return;

    const now = new Date();
    const todayStr = now.toISOString().split('T')[0];

    const filtered = state.posts.filter(post => {
        if (state.filterCampaignId && post.campaign_id != state.filterCampaignId) return false;
        if (state.filterStatus && post.status !== state.filterStatus) return false;

        // Schedule filter
        if (state.filterSchedule) {
            if (!post.scheduled_for) {
                if (state.filterSchedule !== 'latest') return false;
            } else {
                const schedDate = new Date(post.scheduled_for);
                const postDateStr = post.scheduled_for.split(' ')[0].split('T')[0];

                if (state.filterSchedule === 'today') {
                    if (postDateStr !== todayStr) return false;
                } else if (state.filterSchedule === 'this_week') {
                    const diffDays = (schedDate - now) / (1000 * 3600 * 24);
                    if (diffDays < -1 || diffDays > 7) return false;
                }
            }
        }

        if (state.searchQuery) {
            const matchTitle = post.title && post.title.toLowerCase().includes(state.searchQuery);
            const matchCap = post.primary_caption && post.primary_caption.toLowerCase().includes(state.searchQuery);
            const matchCampaign = post.campaign_title && post.campaign_title.toLowerCase().includes(state.searchQuery);
            const matchTags = Array.isArray(post.hashtags) && post.hashtags.some(t => t.toLowerCase().includes(state.searchQuery));
            if (!matchTitle && !matchCap && !matchCampaign && !matchTags) return false;
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
        const charLimit = (PLATFORMS[state.currentChannel] && PLATFORMS[state.currentChannel].limit) || 2200;
        const charCount = currentCap.length;
        const isOverLimit = charCount > charLimit;
        const tags = Array.isArray(post.hashtags) ? post.hashtags : [];

        const firstMedia = post.media && post.media.length > 0 ? post.media[0] : null;
        const hasMultipleMedia = post.media && post.media.length > 1;

        return `
        <div class="glass-card rounded-2xl overflow-hidden flex flex-col justify-between border border-slate-800 hover:border-slate-700 bg-slate-900/60 transition-all cursor-pointer" id="post-card-${post.id}">
            
            <div onclick="openPostDetailModal(${post.id})">
                <!-- Card Header -->
                <div class="p-5 pb-3 border-b border-slate-800/60 flex items-start justify-between gap-3">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            ${post.campaign_title ? `
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold uppercase tracking-wider bg-brand-500/10 text-brand-400 flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full" style="background-color: ${post.campaign_color || '#6366f1'};"></span>
                                    ${escapeHtml(post.campaign_title)}
                                </span>
                            ` : ''}
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold uppercase tracking-wider ${
                                post.status === 'ready' ? 'bg-emerald-500/10 text-emerald-400' :
                                post.status === 'review' ? 'bg-amber-500/10 text-amber-400' : 'bg-slate-800 text-slate-400'
                            }">
                                ${post.status}
                            </span>
                            ${post.scheduled_for ? `
                                <span class="px-2 py-0.5 rounded-md bg-slate-800 text-slate-300 text-[10px] font-mono flex items-center gap-1">
                                    <i data-lucide="clock" class="w-2.5 h-2.5 text-brand-400"></i> ${post.scheduled_for.replace('T', ' ').slice(0, 16)}
                                </span>
                            ` : ''}
                        </div>
                        <h3 class="font-bold text-white text-base leading-snug mt-1">${escapeHtml(post.title)}</h3>
                    </div>

                    <!-- Post Analytics & Actions -->
                    <div class="flex items-center gap-1.5 text-xs text-slate-500" onclick="event.stopPropagation()">
                        <span title="Times copied to clipboard" class="flex items-center gap-1 px-2 py-0.5 rounded-full bg-slate-950/80 border border-slate-800 text-[11px] font-mono text-slate-400">
                            <i data-lucide="copy" class="w-3 h-3 text-brand-400"></i>
                            <span id="copy-count-${post.id}">${post.copy_count || 0}</span>
                        </span>
                        <button onclick="openEditPostModal(${post.id})" class="p-1 rounded-lg text-slate-500 hover:text-brand-400 hover:bg-slate-800/60 transition-colors" title="Edit Post">
                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                        </button>
                        <button onclick="deletePost(${post.id})" class="p-1 rounded-lg text-slate-500 hover:text-rose-400 hover:bg-slate-800/60 transition-colors" title="Delete Post">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>

                <!-- Media Preview Area -->
                ${firstMedia ? `
                    <div class="relative bg-slate-950 aspect-video overflow-hidden group border-b border-slate-800">
                        <img src="${firstMedia.file_path}" alt="${escapeHtml(firstMedia.original_name)}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        
                        <!-- Badges -->
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

                        <!-- Checkbox -->
                        <label class="absolute top-3 right-3 p-1.5 rounded-lg bg-black/70 backdrop-blur-md border border-white/10 cursor-pointer flex items-center gap-1 hover:bg-black/90" onclick="event.stopPropagation()">
                            <input type="checkbox" onchange="toggleAssetSelection(${firstMedia.id}, this)" class="w-3.5 h-3.5 rounded text-brand-600 focus:ring-0 cursor-pointer" ${state.selectedAssets.has(firstMedia.id) ? 'checked' : ''}>
                        </label>
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

                    <!-- Hashtag Cloud -->
                    ${tags.length > 0 ? `
                        <div class="flex flex-wrap gap-1.5 pt-1" onclick="event.stopPropagation()">
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
            <div class="p-4 pt-2 border-t border-slate-800/60 bg-slate-950/40 grid grid-cols-3 gap-2" onclick="event.stopPropagation()">
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

// Dedicated Full Post Detail Modal
function openPostDetailModal(postId) {
    const post = state.posts.find(p => p.id == postId);
    if (!post) return;
    state.currentDetailPost = post;

    const modal = document.getElementById('post-detail-modal');
    document.getElementById('detail-post-title').textContent = post.title;

    const campBadge = document.getElementById('detail-camp-badge');
    if (post.campaign_title) {
        campBadge.textContent = post.campaign_title;
        campBadge.style.color = post.campaign_color || '#6366f1';
        campBadge.classList.remove('hidden');
    } else {
        campBadge.classList.add('hidden');
    }

    const statusBadge = document.getElementById('detail-status-badge');
    statusBadge.textContent = post.status;

    const schedBadge = document.getElementById('detail-scheduled-badge');
    if (post.scheduled_for) {
        document.getElementById('detail-scheduled-text').textContent = post.scheduled_for.replace('T', ' ').slice(0, 16);
        schedBadge.classList.remove('hidden');
    } else {
        schedBadge.classList.add('hidden');
    }

    document.getElementById('detail-copy-count').textContent = post.copy_count || 0;
    document.getElementById('detail-created-at').textContent = post.created_at || '-';

    // Render Media Thumbnails & Active Preview
    const mediaCount = post.media ? post.media.length : 0;
    document.getElementById('detail-media-count').textContent = mediaCount;
    const thumbsContainer = document.getElementById('detail-media-thumbnails');
    thumbsContainer.innerHTML = '';

    if (mediaCount > 0) {
        setDetailActiveMedia(post.media[0]);
        post.media.forEach((m, idx) => {
            const thumb = document.createElement('div');
            thumb.className = `cursor-pointer rounded-lg overflow-hidden border-2 aspect-video bg-slate-950 ${idx === 0 ? 'border-brand-500' : 'border-slate-800'}`;
            thumb.innerHTML = `<img src="${m.file_path}" class="w-full h-full object-cover">`;
            thumb.onclick = () => {
                thumbsContainer.querySelectorAll('div').forEach(d => d.classList.remove('border-brand-500'));
                thumb.classList.add('border-brand-500');
                setDetailActiveMedia(m);
            };
            thumbsContainer.appendChild(thumb);
        });
    } else {
        document.getElementById('detail-primary-img').src = '';
        document.getElementById('detail-primary-img').classList.add('hidden');
    }

    // Hashtags
    const tagCount = Array.isArray(post.hashtags) ? post.hashtags.length : 0;
    document.getElementById('detail-tag-count').textContent = tagCount;
    const tagContainer = document.getElementById('detail-hashtag-container');
    tagContainer.innerHTML = (post.hashtags || []).map(t => `
        <span class="px-2 py-0.5 rounded-md bg-slate-800 text-brand-300 text-xs font-medium cursor-pointer hover:bg-slate-700" onclick="copyToClipboard('${t}', 'Copied ${t}!')">
            ${escapeHtml(t)}
        </span>
    `).join('');

    switchDetailChannel(state.currentChannel);
    modal.classList.remove('hidden');
    lucide.createIcons();
}

function setDetailActiveMedia(media) {
    const img = document.getElementById('detail-primary-img');
    const video = document.getElementById('detail-primary-video');

    if (media.file_type === 'video') {
        img.classList.add('hidden');
        video.src = media.file_path;
        video.classList.remove('hidden');
    } else {
        video.classList.add('hidden');
        img.src = media.file_path;
        img.classList.remove('hidden');
    }
}

function switchDetailChannel(channel) {
    state.detailChannel = channel;
    document.querySelectorAll('.detail-tab').forEach(tab => {
        tab.classList.remove('active-detail-tab', 'text-white', 'bg-slate-800');
        tab.classList.add('text-slate-400');
        if (tab.getAttribute('data-channel') === channel) {
            tab.classList.add('active-detail-tab', 'text-white', 'bg-slate-800');
            tab.classList.remove('text-slate-400');
        }
    });

    if (!state.currentDetailPost) return;
    const post = state.currentDetailPost;
    const channelCaps = post.channel_captions || {};
    const text = channelCaps[channel] || post.primary_caption || '';
    
    document.getElementById('detail-platform-name').textContent = `${capitalize(channel)} Caption`;
    document.getElementById('detail-char-count').textContent = `${text.length} chars`;
    document.getElementById('detail-caption-text').textContent = text;
}

function copyDetailCaption() {
    if (!state.currentDetailPost) return;
    const post = state.currentDetailPost;
    const channelCaps = post.channel_captions || {};
    const text = channelCaps[state.detailChannel] || post.primary_caption;
    copyToClipboard(text, `Copied ${capitalize(state.detailChannel)} caption!`, post.id);
}

function copyDetailHashtags() {
    if (!state.currentDetailPost) return;
    const tags = Array.isArray(state.currentDetailPost.hashtags) ? state.currentDetailPost.hashtags : [];
    if (tags.length === 0) return showToast('No hashtags on this post', 'error');
    copyToClipboard(tags.join(' '), 'Copied hashtag bundle!', state.currentDetailPost.id);
}

function copyDetailFullPost() {
    if (!state.currentDetailPost) return;
    const text = getFormattedPost(state.currentDetailPost, state.detailChannel);
    copyToClipboard(text, `Copied formatted ${capitalize(state.detailChannel)} post!`, state.currentDetailPost.id);
}

function downloadAllCurrentPostMedia() {
    if (!state.currentDetailPost || !state.currentDetailPost.media || state.currentDetailPost.media.length === 0) {
        showToast('No media assets attached to this post', 'error');
        return;
    }
    const ids = state.currentDetailPost.media.map(m => m.id);
    submitBatchZipDownload(ids);
}

function closePostDetailModal() {
    const modal = document.getElementById('post-detail-modal');
    if (modal) modal.classList.add('hidden');
}

function editCurrentPostFromDetail() {
    if (!state.currentDetailPost) return;
    const p = state.currentDetailPost;
    closePostDetailModal();
    openEditPostModal(p.id);
}

// Create / Edit Post Modal
function openCreateModal() {
    const modal = document.getElementById('create-post-modal');
    document.getElementById('modal-form-title').textContent = 'Create Marketing Post';
    document.getElementById('post-form').reset();
    document.getElementById('post-id').value = '';
    document.getElementById('staged-media-grid').innerHTML = '<div id="no-staged-media" class="col-span-2 text-center py-8 text-xs text-slate-500">No files attached yet.</div>';
    document.getElementById('staged-media-count').textContent = '0';
    state.stagedFiles = [];

    // Reset platform char counters
    Object.keys(PLATFORMS).forEach(p => updateCharCounter(p));
    updateHashtagHint();

    modal.classList.remove('hidden');
    lucide.createIcons();
}

function openEditPostModal(postId) {
    const post = state.posts.find(p => p.id == postId);
    if (!post) return;

    openCreateModal();
    document.getElementById('modal-form-title').textContent = 'Edit Marketing Post';
    document.getElementById('post-id').value = post.id;
    document.getElementById('post-title').value = post.title;
    document.getElementById('post-campaign').value = post.campaign_id || '';
    document.getElementById('post-scheduled-for').value = post.scheduled_for ? post.scheduled_for.replace(' ', 'T').slice(0, 16) : '';
    document.getElementById('post-status').value = post.status || 'ready';
    document.getElementById('post-primary-caption').value = post.primary_caption;

    const channelCaps = post.channel_captions || {};
    document.getElementById('channel-cap-facebook').value = channelCaps['facebook'] || '';
    document.getElementById('channel-cap-instagram').value = channelCaps['instagram'] || '';
    document.getElementById('channel-cap-tiktok').value = channelCaps['tiktok'] || '';
    document.getElementById('channel-cap-linkedin').value = channelCaps['linkedin'] || '';
    document.getElementById('channel-cap-twitter').value = channelCaps['twitter'] || '';
    document.getElementById('channel-cap-threads').value = channelCaps['threads'] || '';

    document.getElementById('post-hashtags').value = (post.hashtags || []).join(' ');

    // Show existing media assets in staged grid
    if (post.media && post.media.length > 0) {
        const grid = document.getElementById('staged-media-grid');
        grid.innerHTML = '';
        document.getElementById('staged-media-count').textContent = post.media.length;
        post.media.forEach(m => {
            const card = document.createElement('div');
            card.className = 'relative rounded-lg overflow-hidden border border-slate-800 bg-slate-900 group aspect-video';
            card.innerHTML = `
                <img src="${m.file_path}" class="w-full h-full object-cover">
                <span class="absolute bottom-1 left-1 px-1.5 py-0.5 rounded bg-black/70 text-[9px] font-mono text-white">${m.aspect_ratio || '1:1'}</span>
                <span class="absolute top-1 right-1 px-1.5 py-0.5 rounded bg-slate-900/80 text-[9px] text-slate-300">${formatBytes(m.file_size)}</span>
            `;
            grid.appendChild(card);
        });
    }

    Object.keys(PLATFORMS).forEach(p => updateCharCounter(p));
    updateHashtagHint();
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

function updateCharCounter(platform) {
    const textarea = document.getElementById(`channel-cap-${platform}`);
    const counter = document.getElementById(`counter-${platform}`);
    if (!textarea || !counter) return;

    const count = textarea.value.length;
    const limit = (PLATFORMS[platform] && PLATFORMS[platform].limit) || 2200;
    counter.textContent = `${count} / ${limit.toLocaleString()} chars`;
    if (count > limit) {
        counter.classList.add('text-rose-400', 'font-bold');
    } else {
        counter.classList.remove('text-rose-400', 'font-bold');
    }
}

function updateHashtagHint() {
    const val = document.getElementById('post-hashtags').value;
    const matches = val.match(/#?([\p{L}\p{N}_]+)/gu) || [];
    const hint = document.getElementById('hashtag-counter-hint');
    if (hint) {
        hint.textContent = `${matches.length} tags detected (Recommended: 3-5 tags for X, 5-10 for Instagram)`;
    }
}

// Formatting Tool Helpers
function syncMasterToChannels() {
    const master = document.getElementById('post-primary-caption').value;
    if (!master) return showToast('Please write a master caption first', 'error');

    ['facebook', 'instagram', 'tiktok', 'linkedin', 'twitter', 'threads'].forEach(p => {
        const input = document.getElementById(`channel-cap-${p}`);
        if (input && !input.value) {
            input.value = master;
            updateCharCounter(p);
        }
    });
    showToast('Master copy synced to channels!');
}

function insertEmoji(emoji) {
    const activeTextarea = document.getElementById(`channel-cap-${state.modalChannel}`);
    if (activeTextarea) {
        activeTextarea.value += emoji;
        updateCharCounter(state.modalChannel);
    }
}

function insertLineBreakSpacers() {
    const activeTextarea = document.getElementById(`channel-cap-${state.modalChannel}`);
    if (activeTextarea) {
        activeTextarea.value += '\n.\n';
        updateCharCounter(state.modalChannel);
    }
}

function insertUtmTemplate() {
    const activeTextarea = document.getElementById(`channel-cap-${state.modalChannel}`);
    if (activeTextarea) {
        activeTextarea.value += `\nhttps://brand.com/link?utm_source=${state.modalChannel}&utm_medium=social&utm_campaign=content_hub`;
        updateCharCounter(state.modalChannel);
    }
}

function autoFormatHashtags() {
    const input = document.getElementById('post-hashtags');
    if (!input) return;
    const raw = input.value;
    const matches = raw.match(/#?([\p{L}\p{N}_]+)/gu) || [];
    const formatted = matches.map(m => m.startsWith('#') ? m : '#' + m);
    input.value = formatted.join(' ');
    updateHashtagHint();
    showToast('Hashtags auto-formatted!');
}

function handleFileSelect(input) {
    const grid = document.getElementById('staged-media-grid');
    if (!input.files || input.files.length === 0) return;

    grid.innerHTML = '';
    state.stagedFiles = Array.from(input.files);
    document.getElementById('staged-media-count').textContent = state.stagedFiles.length;

    state.stagedFiles.forEach((file, idx) => {
        const item = document.createElement('div');
        item.className = 'relative rounded-lg overflow-hidden border border-slate-800 bg-slate-900 group aspect-video flex items-center justify-center';
        
        const isVideo = file.type.startsWith('video/');
        const url = URL.createObjectURL(file);

        if (isVideo) {
            item.innerHTML = `
                <video src="${url}" class="w-full h-full object-cover"></video>
                <span class="absolute top-1 left-1 px-1.5 py-0.5 rounded bg-teal-500/80 text-[9px] font-bold text-white">VIDEO</span>
                <span class="absolute bottom-1 right-1 px-1.5 py-0.5 rounded bg-black/70 text-[9px] text-slate-300 font-mono">${formatBytes(file.size)}</span>
            `;
        } else {
            item.innerHTML = `
                <img src="${url}" class="w-full h-full object-cover">
                <span class="absolute bottom-1 right-1 px-1.5 py-0.5 rounded bg-black/70 text-[9px] text-slate-300 font-mono">${formatBytes(file.size)}</span>
            `;
        }
        grid.appendChild(item);
    });
}

// Post Submission
async function handlePostSubmit(e) {
    e.preventDefault();
    const btn = document.getElementById('save-post-btn');
    btn.disabled = true;
    btn.textContent = 'Saving...';

    const form = document.getElementById('post-form');
    const formData = new FormData(form);
    const postId = document.getElementById('post-id').value;

    const channelCaps = {
        facebook: document.getElementById('channel-cap-facebook').value,
        instagram: document.getElementById('channel-cap-instagram').value,
        tiktok: document.getElementById('channel-cap-tiktok').value,
        linkedin: document.getElementById('channel-cap-linkedin').value,
        twitter: document.getElementById('channel-cap-twitter').value,
        threads: document.getElementById('channel-cap-threads').value
    };
    formData.append('channel_captions', JSON.stringify(channelCaps));

    const tagsVal = document.getElementById('post-hashtags').value;
    formData.append('hashtags', tagsVal);

    try {
        const url = postId ? `api/posts/${postId}` : 'api/posts';
        const res = await fetch(url, { method: 'POST', body: formData });
        const json = await res.json();
        
        if (json.status === 'success') {
            showToast(postId ? 'Post updated successfully!' : 'Post created successfully!');
            if (postId) {
                const idx = state.posts.findIndex(p => p.id == postId);
                if (idx !== -1) state.posts[idx] = json.data;
            } else {
                state.posts.unshift(json.data);
            }
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

// Campaign Bucket Creation Modal
function openCampaignModal() {
    const modal = document.getElementById('create-campaign-modal');
    if (modal) modal.classList.remove('hidden');
}

function closeCampaignModal() {
    const modal = document.getElementById('create-campaign-modal');
    if (modal) modal.classList.add('hidden');
}

async function handleCampaignSubmit(e) {
    e.preventDefault();
    const btn = document.getElementById('save-camp-btn');
    btn.disabled = true;
    btn.textContent = 'Creating...';

    const payload = {
        title: document.getElementById('camp-title').value,
        description: document.getElementById('camp-desc').value,
        color: document.getElementById('camp-color').value,
        status: document.getElementById('camp-status').value,
        tags: document.getElementById('camp-tags').value
    };

    try {
        const res = await fetch('api/campaigns', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const json = await res.json();
        if (json.status === 'success') {
            showToast('Campaign created successfully!');
            state.campaigns.unshift(json.data);
            
            // Append to sidebar & create post dropdown
            const filterList = document.getElementById('campaign-filter-list');
            const newBtn = document.createElement('button');
            newBtn.className = 'camp-filter-btn w-full flex items-center justify-between px-3 py-1.5 rounded-xl text-xs font-medium text-slate-400 hover:text-white hover:bg-slate-800/50 transition-all';
            newBtn.setAttribute('data-camp-id', json.data.id);
            newBtn.onclick = () => filterByCampaign(json.data.id);
            newBtn.innerHTML = `
                <span class="truncate text-left flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full shrink-0" style="background-color: ${json.data.color};"></span>
                    <span class="truncate">${escapeHtml(json.data.title)}</span>
                </span>
                <span class="text-xs text-slate-500 font-mono">0</span>
            `;
            filterList.appendChild(newBtn);

            const select = document.getElementById('post-campaign');
            const opt = document.createElement('option');
            opt.value = json.data.id;
            opt.textContent = json.data.title;
            select.appendChild(opt);

            document.getElementById('campaign-count').textContent = state.campaigns.length;
            closeCampaignModal();
        } else {
            showToast(json.message || 'Failed to create campaign', 'error');
        }
    } catch (err) {
        showToast('Network error creating campaign', 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Create Campaign';
    }
}

// Delete Post
async function deletePost(postId) {
    if (!confirm('Are you sure you want to delete this marketing post and its assets?')) return;
    try {
        const res = await fetch(`api/posts/${postId}`, { method: 'DELETE' });
        const json = await res.json();
        if (json.status === 'success') {
            showToast('Post deleted');
            state.posts = state.posts.filter(p => p.id != postId);
            renderPosts();
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

function downloadSelectedBatchZip() {
    if (state.selectedAssets.size === 0) return;
    submitBatchZipDownload(Array.from(state.selectedAssets));
}

function submitBatchZipDownload(ids) {
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

    showToast(`Packaging ${ids.length} assets into ZIP...`, 'success');
}

// Utilities
function capitalize(str) {
    if (!str) return '';
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
