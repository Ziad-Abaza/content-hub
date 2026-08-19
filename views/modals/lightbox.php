<!-- Lightbox Modal -->
<div id="lightbox-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/90 backdrop-blur-md p-4">
    <div class="relative max-w-5xl w-full max-h-[90vh] flex flex-col items-center justify-center">
        <!-- Close Button -->
        <button onclick="closeLightbox()" class="absolute -top-12 right-0 p-2 text-slate-400 hover:text-white rounded-full bg-slate-800/60 transition-colors">
            <i data-lucide="x" class="w-6 h-6"></i>
        </button>

        <!-- Media Display -->
        <div class="w-full flex items-center justify-center overflow-hidden rounded-xl bg-slate-950 border border-slate-800">
            <img id="lightbox-img" src="" alt="Media Preview" class="max-h-[75vh] w-auto object-contain hidden" />
            <video id="lightbox-video" src="" controls class="max-h-[75vh] w-auto hidden"></video>
        </div>

        <!-- Lightbox Footer Meta & Action -->
        <div class="w-full mt-4 flex items-center justify-between px-2 text-sm text-slate-300">
            <div class="flex items-center gap-4">
                <span id="lightbox-filename" class="font-medium text-white"></span>
                <span id="lightbox-dims" class="px-2.5 py-0.5 rounded-full bg-slate-800 text-xs text-brand-400 font-mono"></span>
                <span id="lightbox-filesize" class="text-xs text-slate-400"></span>
            </div>
            <a id="lightbox-download-btn" href="#" class="inline-flex items-center gap-2 px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-lg text-xs font-semibold shadow-lg transition-all">
                <i data-lucide="download" class="w-4 h-4"></i> Download Full Asset
            </a>
        </div>
    </div>
</div>
