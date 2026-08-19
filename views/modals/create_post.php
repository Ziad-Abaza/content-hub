<!-- Add / Edit Post Modal (Viewport Budgeted 2-Column Responsive Form with Comprehensive Formatter Deck) -->
<div id="create-post-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/85 backdrop-blur-md p-4 overflow-hidden">
    <div class="relative w-full max-w-6xl h-[95vh] bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        
        <!-- Pinned Header -->
        <div class="px-6 py-3 border-b border-slate-800 flex items-center justify-between bg-slate-900/95 shrink-0">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-brand-500/10 text-brand-400">
                    <i data-lucide="edit-3" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 id="modal-form-title" class="text-base font-bold text-white leading-tight">Create Marketing Post</h3>
                    <p class="text-xs text-slate-400">Studio Formatter, Multi-Platform Copywriting & Staging</p>
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
                        <label class="text-xs font-semibold uppercase tracking-wider text-slate-400">Master / Baseline Caption *</label>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="formatHeadlineHighlight('post-primary-caption')" class="text-xs text-amber-400 hover:text-amber-300 font-medium">⭐ Add Hook</button>
                            <button type="button" onclick="syncMasterToChannels()" class="text-xs text-brand-400 hover:text-brand-300 font-medium flex items-center gap-1">
                                <i data-lucide="refresh-cw" class="w-3 h-3"></i> Sync to All Channels
                            </button>
                        </div>
                    </div>
                    <textarea id="post-primary-caption" name="primary_caption" rows="3" required placeholder="Write the core marketing message..." class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-sm text-white focus:outline-none focus:border-brand-500"></textarea>
                </div>

                <!-- Channel Specific Captions & Formatter Deck -->
                <div class="flex-1 flex flex-col bg-slate-950/70 p-4 rounded-xl border border-slate-800 space-y-3">
                    
                    <!-- Advanced Marketing Formatter Toolbar (Tier 1: Typography & Stylers) -->
                    <div class="flex flex-wrap items-center justify-between gap-2 pb-2.5 border-b border-slate-800/80">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                            <i data-lucide="sparkles" class="w-3.5 h-3.5 text-brand-400"></i> Typography & Styles
                        </span>
                        
                        <div class="flex flex-wrap items-center gap-1.5">
                            <!-- Unicode Social Typography -->
                            <div class="flex items-center bg-slate-900 border border-slate-800 rounded-lg p-0.5">
                                <button type="button" onclick="formatTransformSelection('bold')" class="px-2 py-0.5 hover:bg-slate-800 rounded text-[11px] font-bold text-slate-200" title="Convert to Unicode Bold (𝗕𝗼𝗹𝗱)">𝗕</button>
                                <button type="button" onclick="formatTransformSelection('italic')" class="px-2 py-0.5 hover:bg-slate-800 rounded text-[11px] italic text-slate-200" title="Convert to Unicode Italic (𝘐𝘵𝘢𝘭𝘪𝘤)">𝘐</button>
                                <button type="button" onclick="formatTransformSelection('monospace')" class="px-2 py-0.5 hover:bg-slate-800 rounded text-[11px] font-mono text-slate-200" title="Convert to Monospace (𝚖𝚘𝚗𝚘)">𝚖</button>
                                <button type="button" onclick="formatTransformSelection('titlecase')" class="px-2 py-0.5 hover:bg-slate-800 rounded text-[11px] text-slate-200 font-mono" title="Headline Title Case">Aa</button>
                                <button type="button" onclick="formatTransformSelection('uppercase')" class="px-2 py-0.5 hover:bg-slate-800 rounded text-[11px] font-semibold text-slate-200" title="Convert to UPPERCASE">AA</button>
                            </div>

                            <!-- List & Bullets -->
                            <div class="flex items-center bg-slate-900 border border-slate-800 rounded-lg p-0.5">
                                <button type="button" onclick="formatTransformSelection('bullet')" class="px-2 py-0.5 hover:bg-slate-800 rounded text-[11px] text-slate-300" title="Bullet List Format">• Bullet</button>
                                <button type="button" onclick="formatTransformSelection('numbered')" class="px-2 py-0.5 hover:bg-slate-800 rounded text-[11px] text-slate-300" title="Numbered List 1. 2. 3.">1. Numbered</button>
                                <button type="button" onclick="formatTransformSelection('check')" class="px-2 py-0.5 hover:bg-slate-800 rounded text-[11px] text-emerald-400" title="Checklist Format">✓ Check</button>
                            </div>

                            <!-- Quick Emoji Palettes -->
                            <div class="flex items-center bg-slate-900 border border-slate-800 rounded-lg p-0.5">
                                <button type="button" onclick="insertEmoji('🔥')" class="px-1.5 py-0.5 hover:bg-slate-800 rounded text-xs">🔥</button>
                                <button type="button" onclick="insertEmoji('✨')" class="px-1.5 py-0.5 hover:bg-slate-800 rounded text-xs">✨</button>
                                <button type="button" onclick="insertEmoji('🚀')" class="px-1.5 py-0.5 hover:bg-slate-800 rounded text-xs">🚀</button>
                                <button type="button" onclick="insertEmoji('💡')" class="px-1.5 py-0.5 hover:bg-slate-800 rounded text-xs">💡</button>
                                <button type="button" onclick="insertEmoji('👇')" class="px-1.5 py-0.5 hover:bg-slate-800 rounded text-xs">👇</button>
                            </div>
                        </div>
                    </div>

                    <!-- Marketing Copy Booster Bar (Tier 2: Structure & Cleaners) -->
                    <div class="flex flex-wrap items-center justify-between gap-1.5 pb-2 border-b border-slate-800/60">
                        <span class="text-[11px] text-slate-400 flex items-center gap-1">
                            <i data-lucide="wand-2" class="w-3 h-3 text-indigo-400"></i> Action Modules
                        </span>
                        <div class="flex flex-wrap items-center gap-1">
                            <button type="button" onclick="insertHeadlineHook()" class="px-2 py-1 rounded-lg bg-slate-900 hover:bg-slate-800 text-[11px] text-amber-400 border border-slate-800 transition-colors" title="Insert catchy hook headline">+ Viral Hook</button>
                            <button type="button" onclick="insertKeyTakeaways()" class="px-2 py-1 rounded-lg bg-slate-900 hover:bg-slate-800 text-[11px] text-blue-400 border border-slate-800 transition-colors" title="Insert Structured Takeaways for LinkedIn">+ 3 Key Takeaways</button>
                            <button type="button" onclick="insertCallToAction()" class="px-2 py-1 rounded-lg bg-slate-900 hover:bg-slate-800 text-[11px] text-teal-400 border border-slate-800 transition-colors" title="Insert platform-tailored CTA">+ Smart CTA</button>
                            <button type="button" onclick="insertUtmTemplate()" class="px-2 py-1 rounded-lg bg-slate-900 hover:bg-slate-800 text-[11px] text-brand-400 border border-slate-800 transition-colors" title="Insert UTM campaign link">+ UTM URL</button>
                            <button type="button" onclick="insertLineBreakSpacers()" class="px-2 py-1 rounded-lg bg-slate-900 hover:bg-slate-800 text-[11px] text-slate-300 border border-slate-800 transition-colors" title="Add clean linebreaks for IG/FB">Clean Spacers</button>
                            <button type="button" onclick="stripExtraWhitespace()" class="px-2 py-1 rounded-lg bg-slate-900 hover:bg-slate-800 text-[11px] text-slate-400 border border-slate-800 transition-colors" title="Clean extra blank lines & spaces">Tidy Spaces</button>
                        </div>
                    </div>

                    <!-- Channel Sub-Tabs -->
                    <div class="flex gap-1 p-1 bg-slate-900 rounded-xl border border-slate-800 overflow-x-auto" id="modal-channel-tabs">
                        <button type="button" onclick="switchModalChannel('facebook')" class="modal-tab flex-1 py-1.5 px-2 rounded-lg text-xs font-medium text-slate-400 hover:text-white transition-all flex items-center justify-center gap-1" data-channel="facebook">
                            <i data-lucide="facebook" class="w-3.5 h-3.5 text-blue-500"></i> Facebook
                        </button>
                        <button type="button" onclick="switchModalChannel('instagram')" class="modal-tab active-modal-tab flex-1 py-1.5 px-2 rounded-lg text-xs font-medium text-white bg-slate-800 transition-all flex items-center justify-center gap-1" data-channel="instagram">
                            <i data-lucide="instagram" class="w-3.5 h-3.5 text-pink-400"></i> Instagram
                        </button>
                        <button type="button" onclick="switchModalChannel('tiktok')" class="modal-tab flex-1 py-1.5 px-2 rounded-lg text-xs font-medium text-slate-400 hover:text-white transition-all flex items-center justify-center gap-1" data-channel="tiktok">
                            <i data-lucide="video" class="w-3.5 h-3.5 text-teal-400"></i> TikTok
                        </button>
                        <button type="button" onclick="switchModalChannel('linkedin')" class="modal-tab flex-1 py-1.5 px-2 rounded-lg text-xs font-medium text-slate-400 hover:text-white transition-all flex items-center justify-center gap-1" data-channel="linkedin">
                            <i data-lucide="linkedin" class="w-3.5 h-3.5 text-blue-400"></i> LinkedIn
                        </button>
                        <button type="button" onclick="switchModalChannel('twitter')" class="modal-tab flex-1 py-1.5 px-2 rounded-lg text-xs font-medium text-slate-400 hover:text-white transition-all flex items-center justify-center gap-1" data-channel="twitter">
                            <i data-lucide="twitter" class="w-3.5 h-3.5 text-sky-400"></i> X
                        </button>
                        <button type="button" onclick="switchModalChannel('threads')" class="modal-tab flex-1 py-1.5 px-2 rounded-lg text-xs font-medium text-slate-400 hover:text-white transition-all flex items-center justify-center gap-1" data-channel="threads">
                            <i data-lucide="message-circle" class="w-3.5 h-3.5 text-slate-300"></i> Threads
                        </button>
                    </div>

                    <!-- Platform-Tailored Inputs with Smart Live Word/Character Counting -->
                    <div id="modal-channel-inputs" class="flex-1">
                        <div id="channel-input-facebook" class="modal-channel-pane hidden">
                            <textarea id="channel-cap-facebook" oninput="updateCharCounter('facebook')" placeholder="Facebook long-form copy, call to action, and link previews..." rows="4" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-sm text-white focus:outline-none focus:border-brand-500 font-sans"></textarea>
                            <div class="flex justify-between items-center text-[11px] text-slate-500 mt-1">
                                <span class="text-slate-400">Facebook Story / Feed Ready</span>
                                <span id="counter-facebook" class="font-mono">0 / 63,206 chars</span>
                            </div>
                        </div>
                        <div id="channel-input-instagram" class="modal-channel-pane">
                            <textarea id="channel-cap-instagram" oninput="updateCharCounter('instagram')" placeholder="Custom Instagram caption (clean spacing, CTA, emojis)..." rows="4" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-sm text-white focus:outline-none focus:border-brand-500 font-sans"></textarea>
                            <div class="flex justify-between items-center text-[11px] text-slate-500 mt-1">
                                <span class="text-slate-400">Ideal for Reels, Carousels & Grid</span>
                                <span id="counter-instagram" class="font-mono">0 / 2,200 chars</span>
                            </div>
                        </div>
                        <div id="channel-input-tiktok" class="modal-channel-pane hidden">
                            <textarea id="channel-cap-tiktok" oninput="updateCharCounter('tiktok')" placeholder="Punchy TikTok caption with trending hook..." rows="4" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-sm text-white focus:outline-none focus:border-brand-500 font-sans"></textarea>
                            <div class="flex justify-between items-center text-[11px] text-slate-500 mt-1">
                                <span class="text-slate-400">Short & viral hooks</span>
                                <span id="counter-tiktok" class="font-mono">0 / 2,200 chars</span>
                            </div>
                        </div>
                        <div id="channel-input-linkedin" class="modal-channel-pane hidden">
                            <textarea id="channel-cap-linkedin" oninput="updateCharCounter('linkedin')" placeholder="Professional B2B narrative, key insights, and takeaways..." rows="4" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-sm text-white focus:outline-none focus:border-brand-500 font-sans"></textarea>
                            <div class="flex justify-between items-center text-[11px] text-slate-500 mt-1">
                                <span class="text-slate-400">B2B leadership tone</span>
                                <span id="counter-linkedin" class="font-mono">0 / 3,000 chars</span>
                            </div>
                        </div>
                        <div id="channel-input-twitter" class="modal-channel-pane hidden">
                            <textarea id="channel-cap-twitter" oninput="updateCharCounter('twitter')" placeholder="Short-form message under 280 characters for X..." rows="4" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-sm text-white focus:outline-none focus:border-brand-500 font-sans"></textarea>
                            <div class="flex justify-between items-center text-[11px] text-slate-500 mt-1">
                                <span class="text-slate-400">Standard 280-char Tweet</span>
                                <span id="counter-twitter" class="font-mono">0 / 280 chars</span>
                            </div>
                        </div>
                        <div id="channel-input-threads" class="modal-channel-pane hidden">
                            <textarea id="channel-cap-threads" oninput="updateCharCounter('threads')" placeholder="Casual conversational prompt for Threads..." rows="4" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-sm text-white focus:outline-none focus:border-brand-500 font-sans"></textarea>
                            <div class="flex justify-between items-center text-[11px] text-slate-500 mt-1">
                                <span class="text-slate-400">Threads feed post</span>
                                <span id="counter-threads" class="font-mono">0 / 500 chars</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hashtags Input & Cleaner -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-xs font-semibold uppercase tracking-wider text-slate-400">Hashtags Bundle</label>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="camelCaseHashtags()" class="text-xs text-brand-400 hover:text-brand-300 font-medium" title="Format #CamelCase">#CamelCase</button>
                            <button type="button" onclick="deduplicateHashtags()" class="text-xs text-indigo-400 hover:text-indigo-300 font-medium" title="Remove duplicate hashtags">Deduplicate</button>
                            <button type="button" onclick="autoFormatHashtags()" class="text-xs text-teal-400 hover:text-teal-300 font-medium">Clean & Prefix #</button>
                        </div>
                    </div>
                    <input type="text" id="post-hashtags" placeholder="#Summer2026 #BrandRefresh #Marketing" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-sm text-white focus:outline-none focus:border-brand-500">
                    <p class="text-[11px] text-slate-500 mt-1" id="hashtag-counter-hint">0 tags detected (Recommended: 3-5 tags for X, 5-10 for Instagram)</p>
                </div>

            </div>

            <!-- Right Column: Media Asset Upload & Staging Zone (5 Cols) -->
            <div class="lg:col-span-5 flex flex-col space-y-4">
                
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Attach Media Assets (Images & Videos)</label>
                    
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
                        <span class="text-[11px] text-slate-500 font-mono">Aspect & size checked</span>
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
