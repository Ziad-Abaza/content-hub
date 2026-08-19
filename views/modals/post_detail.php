<!-- Dedicated Full Post Detail & Inspection Modal -->
<div id="post-detail-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/85 backdrop-blur-md p-4 overflow-hidden">
    <div class="relative w-full max-w-5xl h-[90vh] bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between bg-slate-900/95 shrink-0">
            <div class="flex items-center gap-3">
                <span id="detail-camp-badge" class="px-2.5 py-1 rounded-lg text-xs font-semibold uppercase tracking-wider bg-brand-500/10 text-brand-400"></span>
                <span id="detail-status-badge" class="px-2.5 py-1 rounded-lg text-xs font-semibold uppercase tracking-wider bg-emerald-500/10 text-emerald-400"></span>
                <span id="detail-scheduled-badge" class="hidden px-2.5 py-1 rounded-lg text-xs font-mono bg-slate-800 text-slate-300 flex items-center gap-1">
                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-brand-400"></i>
                    <span id="detail-scheduled-text"></span>
                </span>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="editCurrentPostFromDetail()" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs font-medium text-white transition-colors">
                    <i data-lucide="edit-3" class="w-3.5 h-3.5 text-brand-400"></i> Edit Post
                </button>
                <button onclick="closePostDetailModal()" class="text-slate-400 hover:text-white p-1.5 rounded-lg hover:bg-slate-800">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
        </div>

        <!-- Body: 2 Columns (Media Gallery & Multi-Platform Copy Inspector) -->
        <div class="flex-1 overflow-y-auto p-6 grid grid-cols-1 lg:grid-cols-12 gap-6 min-h-0">
            
            <!-- Left Side: Media Gallery & File Details (5 cols) -->
            <div class="lg:col-span-5 flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-400">Attached Media Assets (<span id="detail-media-count">0</span>)</h4>
                    <button onclick="downloadAllCurrentPostMedia()" class="text-xs text-brand-400 hover:underline flex items-center gap-1">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i> Download All
                    </button>
                </div>

                <!-- Primary Active Preview -->
                <div id="detail-primary-media" class="w-full aspect-video rounded-xl bg-slate-950 border border-slate-800 overflow-hidden flex items-center justify-center relative group">
                    <img id="detail-primary-img" src="" alt="Post Media" class="w-full h-full object-contain">
                    <video id="detail-primary-video" src="" controls class="w-full h-full object-contain hidden"></video>
                </div>

                <!-- Thumbnail Carousel / Strip -->
                <div id="detail-media-thumbnails" class="grid grid-cols-4 gap-2.5 overflow-x-auto">
                    <!-- Dynamic Thumbnails -->
                </div>

                <!-- Post Stats Box -->
                <div class="mt-auto p-4 rounded-xl bg-slate-950/60 border border-slate-800/80 space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-400">Total Copy Events:</span>
                        <span id="detail-copy-count" class="font-mono font-bold text-white">0</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-400">Created:</span>
                        <span id="detail-created-at" class="text-slate-300 font-mono">-</span>
                    </div>
                </div>
            </div>

            <!-- Right Side: Complete Content & Multi-Channel Copy Deck (7 cols) -->
            <div class="lg:col-span-7 flex flex-col gap-4">
                <div>
                    <h3 id="detail-post-title" class="text-lg font-bold text-white leading-tight"></h3>
                </div>

                <!-- Channel Switcher Tabs -->
                <div class="flex gap-1.5 p-1 bg-slate-950 rounded-xl border border-slate-800 overflow-x-auto" id="detail-channel-tabs">
                    <button onclick="switchDetailChannel('facebook')" class="detail-tab flex-1 py-1.5 px-3 rounded-lg text-xs font-medium text-slate-400 hover:text-white transition-all flex items-center justify-center gap-1.5" data-channel="facebook">
                        <i data-lucide="facebook" class="w-3.5 h-3.5 text-blue-500"></i> Facebook
                    </button>
                    <button onclick="switchDetailChannel('instagram')" class="detail-tab active-detail-tab flex-1 py-1.5 px-3 rounded-lg text-xs font-medium text-white bg-slate-800 transition-all flex items-center justify-center gap-1.5" data-channel="instagram">
                        <i data-lucide="instagram" class="w-3.5 h-3.5 text-pink-400"></i> Instagram
                    </button>
                    <button onclick="switchDetailChannel('tiktok')" class="detail-tab flex-1 py-1.5 px-3 rounded-lg text-xs font-medium text-slate-400 hover:text-white transition-all flex items-center justify-center gap-1.5" data-channel="tiktok">
                        <i data-lucide="video" class="w-3.5 h-3.5 text-teal-400"></i> TikTok
                    </button>
                    <button onclick="switchDetailChannel('linkedin')" class="detail-tab flex-1 py-1.5 px-3 rounded-lg text-xs font-medium text-slate-400 hover:text-white transition-all flex items-center justify-center gap-1.5" data-channel="linkedin">
                        <i data-lucide="linkedin" class="w-3.5 h-3.5 text-blue-400"></i> LinkedIn
                    </button>
                    <button onclick="switchDetailChannel('twitter')" class="detail-tab flex-1 py-1.5 px-3 rounded-lg text-xs font-medium text-slate-400 hover:text-white transition-all flex items-center justify-center gap-1.5" data-channel="twitter">
                        <i data-lucide="twitter" class="w-3.5 h-3.5 text-sky-400"></i> X
                    </button>
                    <button onclick="switchDetailChannel('threads')" class="detail-tab flex-1 py-1.5 px-3 rounded-lg text-xs font-medium text-slate-400 hover:text-white transition-all flex items-center justify-center gap-1.5" data-channel="threads">
                        <i data-lucide="message-circle" class="w-3.5 h-3.5 text-slate-300"></i> Threads
                    </button>
                </div>

                <!-- Channel Copy Preview Box -->
                <div class="flex-1 bg-slate-950 rounded-xl border border-slate-800 p-4 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between text-xs text-slate-400 pb-2 border-b border-slate-800/80 mb-3">
                            <span class="font-semibold uppercase tracking-wider text-brand-400" id="detail-platform-name">Instagram Caption</span>
                            <span id="detail-char-count" class="font-mono">0 chars</span>
                        </div>
                        <div id="detail-caption-text" class="text-sm text-slate-200 whitespace-pre-line leading-relaxed max-h-56 overflow-y-auto pr-2 select-all font-normal"></div>
                    </div>

                    <!-- Quick Copy Actions Inside Detail View -->
                    <div class="pt-4 mt-3 border-t border-slate-800/80 flex items-center justify-between gap-2">
                        <button onclick="copyDetailCaption()" class="flex-1 py-2 px-3 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs font-medium text-slate-200 hover:text-white flex items-center justify-center gap-1.5 transition-colors">
                            <i data-lucide="file-text" class="w-3.5 h-3.5 text-brand-400"></i> Copy Caption
                        </button>
                        <button onclick="copyDetailHashtags()" class="flex-1 py-2 px-3 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs font-medium text-slate-200 hover:text-white flex items-center justify-center gap-1.5 transition-colors">
                            <i data-lucide="hash" class="w-3.5 h-3.5 text-teal-400"></i> Copy Hashtags
                        </button>
                        <button onclick="copyDetailFullPost()" class="flex-1 py-2 px-3 rounded-lg bg-brand-600 hover:bg-brand-500 text-xs font-semibold text-white flex items-center justify-center gap-1.5 shadow-md shadow-brand-600/30 transition-all">
                            <i data-lucide="copy" class="w-3.5 h-3.5"></i> Copy Formatted Post
                        </button>
                    </div>
                </div>

                <!-- Hashtags Section -->
                <div class="bg-slate-950/60 rounded-xl border border-slate-800 p-3.5">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Hashtag Bundle (<span id="detail-tag-count">0</span>)</span>
                    </div>
                    <div id="detail-hashtag-container" class="flex flex-wrap gap-1.5"></div>
                </div>

            </div>

        </div>

    </div>
</div>
