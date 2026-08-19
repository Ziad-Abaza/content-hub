<!-- Create Campaign Modal -->
<div id="create-campaign-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
    <div class="relative w-full max-w-lg bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between bg-slate-900/80">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg bg-indigo-500/10 text-indigo-400">
                    <i data-lucide="folder-plus" class="w-5 h-5"></i>
                </div>
                <h3 class="text-base font-bold text-white">Create Campaign Bucket</h3>
            </div>
            <button onclick="closeCampaignModal()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Form -->
        <form onsubmit="handleCampaignSubmit(event)" class="p-6 space-y-4">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Campaign Name *</label>
                <input type="text" id="camp-title" required placeholder="e.g. Q4 Black Friday Sale 2026" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-sm text-white focus:outline-none focus:border-brand-500">
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Description</label>
                <textarea id="camp-desc" rows="2" placeholder="Campaign goals, target audience, and key messaging pillars..." class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-sm text-white focus:outline-none focus:border-brand-500"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Theme Color</label>
                    <div class="flex items-center gap-2">
                        <input type="color" id="camp-color" value="#6366f1" class="w-10 h-10 rounded-xl bg-transparent cursor-pointer border border-slate-800 p-0.5">
                        <span class="text-xs font-mono text-slate-400" id="camp-color-hex">#6366f1</span>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Status</label>
                    <select id="camp-status" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-sm text-white focus:outline-none focus:border-brand-500">
                        <option value="active">Active</option>
                        <option value="draft">Draft / Planning</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Tags (Comma-separated)</label>
                <input type="text" id="camp-tags" placeholder="Promo, Holiday, Retail, B2B" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-sm text-white focus:outline-none focus:border-brand-500">
            </div>

            <div class="pt-4 border-t border-slate-800 flex items-center justify-end gap-3">
                <button type="button" onclick="closeCampaignModal()" class="px-4 py-2 rounded-xl text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800">Cancel</button>
                <button type="submit" id="save-camp-btn" class="px-5 py-2 rounded-xl text-sm font-semibold text-white bg-brand-600 hover:bg-brand-500 shadow-md">Create Campaign</button>
            </div>
        </form>
    </div>
</div>
