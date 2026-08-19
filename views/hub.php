<?php
require_once __DIR__ . '/layout/header.php';
?>

<!-- App Layout -->
<div class="flex h-screen overflow-hidden">

    <!-- Sidebar: Campaigns & Publishing Schedule Views -->
    <aside class="w-72 bg-slate-900/90 border-r border-slate-800 flex flex-col shrink-0">
        <!-- Brand Header -->
        <div class="p-5 border-b border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-400 flex items-center justify-center text-white shadow-md shadow-brand-500/20">
                    <i data-lucide="layers" class="w-5 h-5"></i>
                </div>
                <div>
                    <h1 class="font-bold text-white text-base leading-tight">Content Hub</h1>
                    <span class="text-[10px] uppercase font-mono tracking-wider text-brand-400 font-semibold">Marketing Engine</span>
                </div>
            </div>
        </div>

        <!-- Sidebar Navigation & Filters -->
        <div class="flex-1 overflow-y-auto p-4 space-y-6">
            
            <!-- Quick Actions -->
            <div class="space-y-2">
                <button onclick="openCreateModal()" class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-sm font-semibold shadow-lg shadow-brand-600/30 transition-all hover:scale-[1.02] active:scale-[0.98]">
                    <i data-lucide="plus" class="w-4 h-4"></i> New Marketing Post
                </button>
                <button onclick="openCampaignModal()" class="w-full flex items-center justify-center gap-2 py-2 px-3 rounded-xl bg-slate-800/80 hover:bg-slate-700 text-xs font-medium text-slate-300 hover:text-white border border-slate-750 transition-all">
                    <i data-lucide="folder-plus" class="w-3.5 h-3.5 text-indigo-400"></i> + New Campaign Bucket
                </button>
            </div>

            <!-- Publishing Schedule Timeline Views -->
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 block mb-2 px-1">Publishing Schedule</span>
                <div class="space-y-1" id="schedule-views-list">
                    <button onclick="filterBySchedule('')" class="sched-filter-btn active-filter w-full flex items-center justify-between px-3 py-1.5 rounded-xl text-xs font-medium text-white bg-slate-800/80" data-sched="">
                        <span class="flex items-center gap-2">
                            <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400"></i> All Posts
                        </span>
                    </button>
                    <button onclick="filterBySchedule('latest')" class="sched-filter-btn w-full flex items-center justify-between px-3 py-1.5 rounded-xl text-xs font-medium text-slate-400 hover:text-white hover:bg-slate-800/50" data-sched="latest">
                        <span class="flex items-center gap-2">
                            <i data-lucide="sparkles" class="w-3.5 h-3.5 text-brand-400"></i> Latest Releases
                        </span>
                    </button>
                    <button onclick="filterBySchedule('today')" class="sched-filter-btn w-full flex items-center justify-between px-3 py-1.5 rounded-xl text-xs font-medium text-slate-400 hover:text-white hover:bg-slate-800/50" data-sched="today">
                        <span class="flex items-center gap-2">
                            <i data-lucide="clock" class="w-3.5 h-3.5 text-emerald-400"></i> Scheduled Today
                        </span>
                    </button>
                    <button onclick="filterBySchedule('this_week')" class="sched-filter-btn w-full flex items-center justify-between px-3 py-1.5 rounded-xl text-xs font-medium text-slate-400 hover:text-white hover:bg-slate-800/50" data-sched="this_week">
                        <span class="flex items-center gap-2">
                            <i data-lucide="calendar-days" class="w-3.5 h-3.5 text-amber-400"></i> This Week
                        </span>
                    </button>
                </div>
            </div>

            <!-- Campaign Buckets -->
            <div>
                <div class="flex items-center justify-between mb-2 px-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Campaign Buckets</span>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-slate-800 text-slate-400 font-mono" id="campaign-count"><?= count($campaigns) ?></span>
                </div>
                <div class="space-y-1" id="campaign-filter-list">
                    <button onclick="filterByCampaign(null)" class="camp-filter-btn active-filter w-full flex items-center justify-between px-3 py-1.5 rounded-xl text-xs font-medium transition-all text-white bg-slate-800/80">
                        <span class="flex items-center gap-2">
                            <i data-lucide="folder" class="w-3.5 h-3.5 text-brand-400"></i> All Campaigns
                        </span>
                    </button>
                    <?php foreach ($campaigns as $c): ?>
                        <button onclick="filterByCampaign(<?= $c['id'] ?>)" class="camp-filter-btn w-full flex items-center justify-between px-3 py-1.5 rounded-xl text-xs font-medium text-slate-400 hover:text-white hover:bg-slate-800/50 transition-all" data-camp-id="<?= $c['id'] ?>">
                            <span class="truncate text-left flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full shrink-0" style="background-color: <?= htmlspecialchars($c['color'] ?? '#6366f1') ?>;"></span>
                                <span class="truncate"><?= htmlspecialchars($c['title']) ?></span>
                            </span>
                            <span class="text-xs text-slate-500 font-mono"><?= $c['post_count'] ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Status Filters -->
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 block mb-2 px-1">Status</span>
                <div class="space-y-1">
                    <button onclick="filterByStatus('')" class="status-filter-btn active-filter w-full flex items-center justify-between px-3 py-1.5 rounded-xl text-xs font-medium text-white bg-slate-800/80">
                        <span>All Statuses</span>
                    </button>
                    <button onclick="filterByStatus('ready')" class="status-filter-btn w-full flex items-center justify-between px-3 py-1.5 rounded-xl text-xs font-medium text-slate-400 hover:text-white hover:bg-slate-800/50">
                        <span class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span> Ready to Post
                        </span>
                    </button>
                    <button onclick="filterByStatus('review')" class="status-filter-btn w-full flex items-center justify-between px-3 py-1.5 rounded-xl text-xs font-medium text-slate-400 hover:text-white hover:bg-slate-800/50">
                        <span class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-400"></span> In Review
                        </span>
                    </button>
                    <button onclick="filterByStatus('draft')" class="status-filter-btn w-full flex items-center justify-between px-3 py-1.5 rounded-xl text-xs font-medium text-slate-400 hover:text-white hover:bg-slate-800/50">
                        <span class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-slate-500"></span> Drafts
                        </span>
                    </button>
                </div>
            </div>

            <!-- Batch Download Tool Box -->
            <div class="p-3.5 rounded-xl bg-slate-950/60 border border-slate-800">
                <span class="text-xs font-semibold text-slate-300 block mb-1">Batch Asset Downloader</span>
                <p class="text-[11px] text-slate-500 mb-3">Select asset checkboxes on post cards to export a single ZIP bundle.</p>
                <button onclick="downloadSelectedBatchZip()" id="batch-zip-btn" class="w-full flex items-center justify-center gap-2 py-2 px-3 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs font-medium text-slate-300 hover:text-white transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                    <i data-lucide="archive" class="w-3.5 h-3.5"></i> Download Selected (<span id="selected-count">0</span>)
                </button>
            </div>

        </div>

        <!-- Footer / Environment Info -->
        <div class="p-4 border-t border-slate-800 text-[11px] text-slate-500 flex items-center justify-between">
            <span class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span> Apache Native
            </span>
            <span>PHP <?= phpversion() ?></span>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col min-w-0 bg-[#080C14] overflow-y-auto">

        <!-- Top Navigation Bar with Search & Multi-Platform Switcher -->
        <header class="sticky top-0 z-30 bg-slate-900/85 backdrop-blur-md border-b border-slate-800 px-8 py-3.5 flex items-center justify-between gap-4">
            
            <!-- Global Search Bar -->
            <div class="relative flex-1 max-w-md">
                <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500"></i>
                <input type="text" id="search-input" oninput="handleSearch(this.value)" placeholder="Search campaigns, copy, hashtags..." class="w-full bg-slate-950/80 border border-slate-800 rounded-xl pl-10 pr-4 py-2 text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
            </div>

            <!-- Platform Quick Tabs Switcher -->
            <div class="flex items-center gap-1 bg-slate-950 p-1 rounded-xl border border-slate-800 overflow-x-auto" id="global-channel-tabs">
                <button onclick="setGlobalChannel('facebook')" class="global-tab px-3 py-1.5 rounded-lg text-xs font-medium text-slate-400 hover:text-white transition-all flex items-center gap-1.5" data-channel="facebook">
                    <i data-lucide="facebook" class="w-3.5 h-3.5 text-blue-500"></i> Facebook
                </button>
                <button onclick="setGlobalChannel('instagram')" class="global-tab active-global-tab px-3 py-1.5 rounded-lg text-xs font-medium text-white bg-slate-800 transition-all flex items-center gap-1.5" data-channel="instagram">
                    <i data-lucide="instagram" class="w-3.5 h-3.5 text-pink-400"></i> Instagram
                </button>
                <button onclick="setGlobalChannel('tiktok')" class="global-tab px-3 py-1.5 rounded-lg text-xs font-medium text-slate-400 hover:text-white transition-all flex items-center gap-1.5" data-channel="tiktok">
                    <i data-lucide="video" class="w-3.5 h-3.5 text-teal-400"></i> TikTok
                </button>
                <button onclick="setGlobalChannel('linkedin')" class="global-tab px-3 py-1.5 rounded-lg text-xs font-medium text-slate-400 hover:text-white transition-all flex items-center gap-1.5" data-channel="linkedin">
                    <i data-lucide="linkedin" class="w-3.5 h-3.5 text-blue-400"></i> LinkedIn
                </button>
                <button onclick="setGlobalChannel('twitter')" class="global-tab px-3 py-1.5 rounded-lg text-xs font-medium text-slate-400 hover:text-white transition-all flex items-center gap-1.5" data-channel="twitter">
                    <i data-lucide="twitter" class="w-3.5 h-3.5 text-sky-400"></i> X
                </button>
                <button onclick="setGlobalChannel('threads')" class="global-tab px-3 py-1.5 rounded-lg text-xs font-medium text-slate-400 hover:text-white transition-all flex items-center gap-1.5" data-channel="threads">
                    <i data-lucide="message-circle" class="w-3.5 h-3.5 text-slate-300"></i> Threads
                </button>
            </div>

        </header>

        <!-- Feed / Cards Container -->
        <div class="p-8 max-w-7xl mx-auto w-full">

            <!-- Subheader / Status Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl font-bold text-white tracking-tight" id="view-title">Marketing Content Feed</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Click any card to inspect full post copy & media, or use 1-click quick copy.</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-slate-400 font-medium">Showing <strong id="post-count" class="text-white"><?= count($posts) ?></strong> posts</span>
                </div>
            </div>

            <!-- Posts Grid -->
            <div id="posts-grid" class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6"></div>

            <!-- Empty State -->
            <div id="empty-state" class="hidden text-center py-20 bg-slate-900/40 border border-slate-800/80 rounded-2xl p-8">
                <div class="w-12 h-12 rounded-2xl bg-slate-800 flex items-center justify-center mx-auto text-slate-400 mb-3">
                    <i data-lucide="inbox" class="w-6 h-6"></i>
                </div>
                <h3 class="text-base font-semibold text-white">No content items found</h3>
                <p class="text-xs text-slate-400 max-w-sm mx-auto mt-1 mb-4">Try adjusting your filters or search keywords, or create a brand new post.</p>
                <button onclick="openCreateModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold transition-all">
                    <i data-lucide="plus" class="w-4 h-4"></i> Create First Post
                </button>
            </div>

        </div>

    </main>

</div>

<!-- Modals -->
<?php require_once __DIR__ . '/modals/create_post.php'; ?>
<?php require_once __DIR__ . '/modals/create_campaign.php'; ?>
<?php require_once __DIR__ . '/modals/post_detail.php'; ?>
<?php require_once __DIR__ . '/modals/lightbox.php'; ?>

<!-- Initial Data -->
<script>
    window.INITIAL_POSTS = <?= json_encode($posts, JSON_UNESCAPED_UNICODE) ?>;
    window.INITIAL_CAMPAIGNS = <?= json_encode($campaigns, JSON_UNESCAPED_UNICODE) ?>;
</script>

<?php
require_once __DIR__ . '/layout/footer.php';
?>
