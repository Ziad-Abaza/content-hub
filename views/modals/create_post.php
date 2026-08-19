<!-- Create / Edit Post Modal -->
<div id="create-post-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/80 backdrop-blur-sm p-4 overflow-y-auto">
    <div class="relative w-full max-w-3xl bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl overflow-hidden my-8">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between bg-slate-900/80">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg bg-brand-500/10 text-brand-400">
                    <i data-lucide="plus-circle" class="w-5 h-5"></i>
                </div>
                <h3 id="modal-form-title" class="text-lg font-bold text-white">Create Marketing Post</h3>
            </div>
            <button onclick="closePostModal()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Form Content -->
        <form id="post-form" onsubmit="handlePostSubmit(event)" class="p-6 space-y-6">
            <input type="hidden" id="post-id" name="post_id" value="">

            <!-- Title & Campaign -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Post Title *</label>
                    <input type="text" id="post-title" name="title" required placeholder="e.g. ☀️ Summer Drop Announcement" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-sm text-white focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Campaign Bucket</label>
                    <select id="post-campaign" name="campaign_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-sm text-white focus:outline-none focus:border-brand-500">
                        <option value="">-- No Campaign (Standalone) --</option>
                        <?php foreach ($campaigns as $camp): ?>
                            <option value="<?= $camp['id'] ?>"><?= htmlspecialchars($camp['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Primary Master Caption -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Master / Default Caption *</label>
                <textarea id="post-primary-caption" name="primary_caption" rows="3" required placeholder="Primary campaign copy that serves as the baseline..." class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-sm text-white focus:outline-none focus:border-brand-500"></textarea>
            </div>

            <!-- Multi-Channel Specific Overrides -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-semibold uppercase tracking-wider text-slate-400">Channel-Specific Captions (Optional Overrides)</label>
                    <span class="text-xs text-brand-400">Tailor length & tone per network</span>
                </div>
                
                <!-- Channel Sub-Tabs -->
                <div class="flex gap-2 p-1 bg-slate-950 rounded-xl border border-slate-800 mb-3" id="modal-channel-tabs">
                    <button type="button" onclick="switchModalChannel('instagram')" class="modal-tab active-modal-tab flex-1 py-1.5 px-3 rounded-lg text-xs font-medium transition-all text-white bg-slate-800">Instagram</button>
                    <button type="button" onclick="switchModalChannel('tiktok')" class="modal-tab flex-1 py-1.5 px-3 rounded-lg text-xs font-medium text-slate-400 hover:text-white transition-all">TikTok</button>
                    <button type="button" onclick="switchModalChannel('linkedin')" class="modal-tab flex-1 py-1.5 px-3 rounded-lg text-xs font-medium text-slate-400 hover:text-white transition-all">LinkedIn</button>
                    <button type="button" onclick="switchModalChannel('twitter')" class="modal-tab flex-1 py-1.5 px-3 rounded-lg text-xs font-medium text-slate-400 hover:text-white transition-all">X (Twitter)</button>
                </div>

                <div id="modal-channel-inputs">
                    <div id="channel-input-instagram" class="modal-channel-pane">
                        <textarea id="channel-cap-instagram" placeholder="Custom Instagram caption (supports linebreaks, emojis)..." rows="3" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-sm text-white focus:outline-none focus:border-brand-500"></textarea>
                    </div>
                    <div id="channel-input-tiktok" class="modal-channel-pane hidden">
                        <textarea id="channel-cap-tiktok" placeholder="Punchy TikTok caption with call-to-action..." rows="3" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-sm text-white focus:outline-none focus:border-brand-500"></textarea>
                    </div>
                    <div id="channel-input-linkedin" class="modal-channel-pane hidden">
                        <textarea id="channel-cap-linkedin" placeholder="Professional B2B narrative and key takeaways for LinkedIn..." rows="3" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-sm text-white focus:outline-none focus:border-brand-500"></textarea>
                    </div>
                    <div id="channel-input-twitter" class="modal-channel-pane hidden">
                        <textarea id="channel-cap-twitter" placeholder="Short-form copy (under 280 chars) for X/Twitter..." rows="3" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-sm text-white focus:outline-none focus:border-brand-500"></textarea>
                    </div>
                </div>
            </div>

            <!-- Hashtags & Status -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Hashtags (Space or comma separated)</label>
                    <input type="text" id="post-hashtags" placeholder="#Summer2026 #BrandRefresh #Marketing" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-sm text-white focus:outline-none focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Status</label>
                    <select id="post-status" name="status" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-sm text-white focus:outline-none focus:border-brand-500">
                        <option value="ready">Ready to Post</option>
                        <option value="review">In Review</option>
                        <option value="draft">Draft</option>
                    </select>
                </div>
            </div>

            <!-- Media Upload Area -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Upload Media Assets (Images / Videos)</label>
                <div class="border-2 border-dashed border-slate-800 hover:border-brand-500/50 rounded-2xl p-6 text-center transition-colors bg-slate-950/50 cursor-pointer" onclick="document.getElementById('file-upload-input').click()">
                    <input type="file" id="file-upload-input" name="media_files[]" multiple accept="image/*,video/*" class="hidden" onchange="handleFileSelect(this)">
                    <div class="flex flex-col items-center justify-center gap-2">
                        <div class="p-3 rounded-full bg-slate-800 text-brand-400">
                            <i data-lucide="cloud-upload" class="w-6 h-6"></i>
                        </div>
                        <p class="text-sm font-medium text-slate-300">Click to upload media files</p>
                        <p class="text-xs text-slate-500">PNG, JPG, MP4, WebP supported</p>
                    </div>
                </div>
                <!-- Selected Files Preview list -->
                <div id="selected-files-list" class="mt-3 space-y-2"></div>
            </div>

            <!-- Form Actions -->
            <div class="pt-4 border-t border-slate-800 flex items-center justify-end gap-3">
                <button type="button" onclick="closePostModal()" class="px-5 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800 transition-colors">Cancel</button>
                <button type="submit" id="save-post-btn" class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white bg-brand-600 hover:bg-brand-500 shadow-lg shadow-brand-500/25 transition-all">Save & Publish Post</button>
            </div>
        </form>
    </div>
</div>
