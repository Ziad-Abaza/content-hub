<!-- Add / Edit Post Modal (Viewport Budgeted 2-Column Responsive Form) -->
<div id="create-post-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/85 backdrop-blur-md p-4 overflow-hidden">
    <div class="relative w-full max-w-5xl h-[92vh] bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        
        <!-- Pinned Header -->
        <div class="px-6 py-3.5 border-b border-slate-800 flex items-center justify-between bg-slate-900/95 shrink-0">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-brand-500/10 text-brand-400">
                    <i data-lucide="edit-3" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 id="modal-form-title" class="text-base font-bold text-white leading-tight">Create Marketing Post</h3>
                    <p class="text-xs text-slate-400">Draft, format for multi-channels, and attach media assets</p>
                </div>
            </div>
            <button onclick="closePostModal()" class="text-slate-400 hover:text-white p-2 rounded-xl hover:bg-slate-800 transition-colors" title="Close Modal">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Scrollable Form Container -->
        <form id="post-form" onsubmit="handlePostSubmit(event)" class="flex-1 overflow-y-auto p-6 grid grid-cols-1 lg:grid-cols-12 gap-6 min-h-0">
            <input type="hidden" id="post-id" name="post_id" value="">

            <!-- Left Column: Copywriting & Channel Formatting Deck (7 Cols) -->
            <div class="lg:col-span-7 space-y-4 flex flex-col">
                
                <!-- Post Title & Campaign Row -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Post Title *</label>
                        <input type="text" id="post-title" name="title" required placeholder="e.g. ☀️ Summer Drop Reveal" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-sm text-white focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Campaign Bucket</label>
                        <select id="post-campaign" name="campaign_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-sm text-white focus:outline-none focus:border-brand-500">
                            <option value="">-- No Campaign (Standalone) --</option>
                            <?php foreach ($campaigns as $camp): ?>
                                <option value="<?= $camp['id'] ?>"><?= htmlspecialchars($camp['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Schedule & Status Row -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1 flex items-center justify-between">
                            <span>Publishing Schedule</span>
                            <span class="text-[10px] text-brand-400 lowercase">date & time</span>
                        </label>
                        <input type="datetime-local" id="post-scheduled-for" name="scheduled_for" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Workflow Status</label>
                        <select id="post-status" name="status" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-sm text-white focus:outline-none focus:border-brand-500">
                            <option value="ready">Ready to Post</option>
                            <option value="review">In Review</option>
                            <option value="draft">Draft</option>
                        </select>
                    </div>
                </div>

                <!-- Master Baseline Caption -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-xs font-semibold uppercase tracking-wider text-slate-400">Master / Primary Caption *</label>
                        <button type="button" onclick="syncMasterToChannels()" class="text-xs text-brand-400 hover:text-brand-300 font-medium">Sync to all channels</button>
                    </div>
                    <textarea id="post-primary-caption" name="primary_caption" rows="3" required placeholder="Write the core marketing copy here..." class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-sm text-white focus:outline-none focus:border-brand-500"></textarea>
                </div>

                <!-- Channel Specific Captions & Validators -->
                <div class="flex-1 flex flex-col bg-slate-950/70 p-4 rounded-xl border border-slate-800">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Platform-Specific Copy Overrides</span>
                        <!-- Quick Formatting Toolbar -->
                        <div class="flex items-center gap-1">
                            <button type="button" onclick="insertEmoji('🔥')" class="px-1.5 py-0.5 rounded bg-slate-800 hover:bg-slate-700 text-xs">🔥</button>
                            <button type="button" onclick="insertEmoji('✨')" class="px-1.5 py-0.5 rounded bg-slate-800 hover:bg-slate-700 text-xs">✨</button>
                            <button type="button" onclick="insertEmoji('🚀')" class="px-1.5 py-0.5 rounded bg-slate-800 hover:bg-slate-700 text-xs">🚀</button>
                            <button type="button" onclick="insertLineBreakSpacers()" class="px-2 py-0.5 rounded bg-slate-800 hover:bg-slate-700 text-[11px] text-slate-300" title="Format clean Instagram linebreaks">Add Spacers</button>
                            <button type="button" onclick="insertUtmTemplate()" class="px-2 py-0.5 rounded bg-slate-800 hover:bg-slate-700 text-[11px] text-brand-400" title="Insert UTM Link Template">+ UTM Link</button>
                        </div>
                    </div>

                    <!-- Channel Sub-Tabs (FB, IG, TikTok, LinkedIn, X, Threads) -->
                    <div class="flex gap-1 p-1 bg-slate-900 rounded-xl border border-slate-800 mb-3 overflow-x-auto" id="modal-channel-tabs">
                        <button type="button" onclick="switchModalChannel('facebook')" class="modal-tab flex-1 py-1 px-2 rounded-lg text-xs font-medium text-slate-400 hover:text-white transition-all">Facebook</button>
                        <button type="button" onclick="switchModalChannel('instagram')" class="modal-tab active-modal-tab flex-1 py-1 px-2 rounded-lg text-xs font-medium text-white bg-slate-800 transition-all">Instagram</button>
                        <button type="button" onclick="switchModalChannel('tiktok')" class="modal-tab flex-1 py-1 px-2 rounded-lg text-xs font-medium text-slate-400 hover:text-white transition-all">TikTok</button>
                        <button type="button" onclick="switchModalChannel('linkedin')" class="modal-tab flex-1 py-1 px-2 rounded-lg text-xs font-medium text-slate-400 hover:text-white transition-all">LinkedIn</button>
                        <button type="button" onclick="switchModalChannel('twitter')" class="modal-tab flex-1 py-1 px-2 rounded-lg text-xs font-medium text-slate-400 hover:text-white transition-all">X</button>
                        <button type="button" onclick="switchModalChannel('threads')" class="modal-tab flex-1 py-1 px-2 rounded-lg text-xs font-medium text-slate-400 hover:text-white transition-all">Threads</button>
                    </div>

                    <!-- Platform-Tailored Inputs -->
                    <div id="modal-channel-inputs" class="flex-1">
                        <div id="channel-input-facebook" class="modal-channel-pane hidden">
                            <textarea id="channel-cap-facebook" oninput="updateCharCounter('facebook')" placeholder="Facebook long-form copy, call to action, and link previews..." rows="4" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-sm text-white focus:outline-none focus:border-brand-500"></textarea>
                            <div class="flex justify-between text-[11px] text-slate-500 mt-1">
                                <span>Facebook preview</span>
                                <span id="counter-facebook">0 / 63,206 chars</span>
                            </div>
                        </div>
                        <div id="channel-input-instagram" class="modal-channel-pane">
                            <textarea id="channel-cap-instagram" oninput="updateCharCounter('instagram')" placeholder="Custom Instagram caption (clean spacing, CTA, emojis)..." rows="4" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-sm text-white focus:outline-none focus:border-brand-500"></textarea>
                            <div class="flex justify-between text-[11px] text-slate-500 mt-1">
                                <span>Ideal for captions & stories</span>
                                <span id="counter-instagram">0 / 2,200 chars</span>
                            </div>
                        </div>
                        <div id="channel-input-tiktok" class="modal-channel-pane hidden">
                            <textarea id="channel-cap-tiktok" oninput="updateCharCounter('tiktok')" placeholder="Punchy TikTok caption with trending hook..." rows="4" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-sm text-white focus:outline-none focus:border-brand-500"></textarea>
                            <div class="flex justify-between text-[11px] text-slate-500 mt-1">
                                <span>Short & engaging</span>
                                <span id="counter-tiktok">0 / 2,200 chars</span>
                            </div>
                        </div>
                        <div id="channel-input-linkedin" class="modal-channel-pane hidden">
                            <textarea id="channel-cap-linkedin" oninput="updateCharCounter('linkedin')" placeholder="Professional B2B narrative, key insights, and takeaways..." rows="4" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-sm text-white focus:outline-none focus:border-brand-500"></textarea>
                            <div class="flex justify-between text-[11px] text-slate-500 mt-1">
                                <span>B2B professional tone</span>
                                <span id="counter-linkedin">0 / 3,000 chars</span>
                            </div>
                        </div>
                        <div id="channel-input-twitter" class="modal-channel-pane hidden">
                            <textarea id="channel-cap-twitter" oninput="updateCharCounter('twitter')" placeholder="Short-form message under 280 characters for X..." rows="4" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-sm text-white focus:outline-none focus:border-brand-500"></textarea>
                            <div class="flex justify-between text-[11px] text-slate-500 mt-1">
                                <span>Standard tweet limit</span>
                                <span id="counter-twitter" class="font-mono">0 / 280 chars</span>
                            </div>
                        </div>
                        <div id="channel-input-threads" class="modal-channel-pane hidden">
                            <textarea id="channel-cap-threads" oninput="updateCharCounter('threads')" placeholder="Casual conversational prompt for Threads..." rows="4" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-sm text-white focus:outline-none focus:border-brand-500"></textarea>
                            <div class="flex justify-between text-[11px] text-slate-500 mt-1">
                                <span>Threads post</span>
                                <span id="counter-threads">0 / 500 chars</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hashtags Input & Cleaner -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-xs font-semibold uppercase tracking-wider text-slate-400">Hashtags Bundle</label>
                        <button type="button" onclick="autoFormatHashtags()" class="text-xs text-teal-400 hover:text-teal-300 font-medium">Auto-format # tags</button>
                    </div>
                    <input type="text" id="post-hashtags" placeholder="#Summer2026 #BrandRefresh #Marketing" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-sm text-white focus:outline-none focus:border-brand-500">
                    <p class="text-[11px] text-slate-500 mt-1" id="hashtag-counter-hint">0 tags detected (Recommended: 3-5 tags for X, 5-10 for Instagram)</p>
                </div>

            </div>

            <!-- Right Column: Media Asset Upload & Staging Zone (5 Cols) -->
            <div class="lg:col-span-5 flex flex-col space-y-4">
                
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Attach Media Assets (Multiple Images & Videos)</label>
                    
                    <!-- Drag & Drop Zone -->
                    <div class="border-2 border-dashed border-slate-800 hover:border-brand-500/50 rounded-2xl p-6 text-center transition-colors bg-slate-950/60 cursor-pointer" onclick="document.getElementById('file-upload-input').click()">
                        <input type="file" id="file-upload-input" name="media_files[]" multiple accept="image/*,video/*" class="hidden" onchange="handleFileSelect(this)">
                        <div class="flex flex-col items-center justify-center gap-2">
                            <div class="p-3 rounded-full bg-slate-800 text-brand-400">
                                <i data-lucide="cloud-upload" class="w-6 h-6"></i>
                            </div>
                            <p class="text-sm font-semibold text-slate-200">Click or drag media files here</p>
                            <p class="text-xs text-slate-400">Attach multiple JPG, PNG, MP4, WebP</p>
                        </div>
                    </div>
                </div>

                <!-- Staged Media Previews Grid -->
                <div class="flex-1 bg-slate-950/60 rounded-xl border border-slate-800 p-3.5 flex flex-col">
                    <div class="flex items-center justify-between mb-2 pb-2 border-b border-slate-800/80">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Staged Media (<span id="staged-media-count">0</span>)</span>
                    </div>

                    <!-- Grid of Thumbnails -->
                    <div id="staged-media-grid" class="flex-1 grid grid-cols-2 gap-2.5 overflow-y-auto max-h-72 p-1">
                        <div id="no-staged-media" class="col-span-2 text-center py-8 text-xs text-slate-500">
                            No files attached yet.
                        </div>
                    </div>
                </div>

            </div>

        </form>

        <!-- Pinned Modal Footer Actions -->
        <div class="px-6 py-3.5 border-t border-slate-800 bg-slate-900/95 flex items-center justify-between shrink-0">
            <button type="button" onclick="closePostModal()" class="px-5 py-2 rounded-xl text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800 transition-colors">Cancel</button>
            <div class="flex items-center gap-3">
                <button type="submit" form="post-form" id="save-post-btn" class="px-6 py-2 rounded-xl text-sm font-semibold text-white bg-brand-600 hover:bg-brand-500 shadow-lg shadow-brand-500/25 transition-all">Save & Publish Post</button>
            </div>
        </div>

    </div>
</div>
