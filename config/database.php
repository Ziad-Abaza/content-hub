<?php
/**
 * Database Configuration & Schema Initializer
 * Uses SQLite with PDO for zero-config portable deployment
 */

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $dbDir = __DIR__ . '/../data';
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0777, true);
            }
            $dbPath = $dbDir . '/content_hub.sqlite';
            $isNew = !file_exists($dbPath);

            try {
                self::$instance = new PDO('sqlite:' . $dbPath);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                self::$instance->exec("PRAGMA foreign_keys = ON;");
                self::$instance->exec("PRAGMA journal_mode = WAL;");

                if ($isNew || filesize($dbPath) === 0) {
                    self::initSchema(self::$instance);
                    self::seedInitialData(self::$instance);
                } else {
                    self::initSchema(self::$instance);
                }
            } catch (PDOException $e) {
                die("Database Connection Error: " . $e->getMessage());
            }
        }
        return self::$instance;
    }

    private static function initSchema(PDO $db): void {
        $db->exec("
            CREATE TABLE IF NOT EXISTS campaigns (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                description TEXT,
                status TEXT DEFAULT 'active',
                tags TEXT DEFAULT '[]',
                color TEXT DEFAULT '#6366f1',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS content_posts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                campaign_id INTEGER NULL,
                title TEXT NOT NULL,
                primary_caption TEXT NOT NULL,
                channel_captions TEXT DEFAULT '{}',
                hashtags TEXT DEFAULT '[]',
                status TEXT DEFAULT 'ready',
                copy_count INTEGER DEFAULT 0,
                scheduled_for DATETIME NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE SET NULL
            );

            CREATE TABLE IF NOT EXISTS media_assets (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                post_id INTEGER NOT NULL,
                file_name TEXT NOT NULL,
                original_name TEXT NOT NULL,
                mime_type TEXT NOT NULL,
                file_size INTEGER NOT NULL,
                file_path TEXT NOT NULL,
                thumbnail_path TEXT NULL,
                width INTEGER NULL,
                height INTEGER NULL,
                aspect_ratio TEXT DEFAULT '1:1',
                file_type TEXT DEFAULT 'image',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (post_id) REFERENCES content_posts(id) ON DELETE CASCADE
            );

            CREATE INDEX IF NOT EXISTS idx_posts_campaign ON content_posts(campaign_id);
            CREATE INDEX IF NOT EXISTS idx_posts_status ON content_posts(status);
            CREATE INDEX IF NOT EXISTS idx_posts_scheduled ON content_posts(scheduled_for);
            CREATE INDEX IF NOT EXISTS idx_media_post ON media_assets(post_id);
        ");
    }

    public static function seedInitialData(PDO $db): void {
        $uploadDir = __DIR__ . '/../uploads';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Insert Campaigns
        $campaigns = [
            [
                'title' => 'Summer Brand Refresh 2026',
                'description' => 'Global summer campaign featuring new aesthetic product drops, lifestyle visuals, and influencer spotlights.',
                'status' => 'active',
                'tags' => json_encode(['Summer2026', 'Lifestyle', 'BrandRefresh']),
                'color' => '#ec4899'
            ],
            [
                'title' => 'Product V2 Feature Launch',
                'description' => 'Technical and marketing rollouts for the major 2.0 software update and AI capabilities.',
                'status' => 'active',
                'tags' => json_encode(['ProductLaunch', 'Tech', 'SaaS', 'AI']),
                'color' => '#6366f1'
            ],
            [
                'title' => 'Customer Success Stories',
                'description' => 'Case studies, user testimonials, and enterprise partner highlights for LinkedIn, Facebook and social proof.',
                'status' => 'active',
                'tags' => json_encode(['B2B', 'Testimonials', 'CaseStudy']),
                'color' => '#10b981'
            ]
        ];

        $stmtCamp = $db->prepare("INSERT INTO campaigns (title, description, status, tags, color) VALUES (?, ?, ?, ?, ?)");
        $campIds = [];
        foreach ($campaigns as $camp) {
            $stmtCamp->execute([$camp['title'], $camp['description'], $camp['status'], $camp['tags'], $camp['color']]);
            $campIds[] = (int)$db->lastInsertId();
        }

        // Generate synthetic demo media images using GD
        $sampleImages = [
            [
                'name' => 'summer-hero-square.png',
                'orig' => 'Summer_Hero_Banner_1080x1080.png',
                'width' => 1080,
                'height' => 1080,
                'aspect' => '1:1',
                'title' => 'Summer Collection 2026',
                'bg' => [255, 107, 107],
                'text_color' => [255, 255, 255]
            ],
            [
                'name' => 'summer-story-reel.png',
                'orig' => 'Summer_Story_Reel_1080x1920.png',
                'width' => 1080,
                'height' => 1920,
                'aspect' => '9:16',
                'title' => '9:16 Story - Summer Vibes',
                'bg' => [78, 205, 196],
                'text_color' => [255, 255, 255]
            ],
            [
                'name' => 'product-v2-landscape.png',
                'orig' => 'Product_Launch_Banner_1920x1080.png',
                'width' => 1920,
                'height' => 1080,
                'aspect' => '16:9',
                'title' => 'Next-Gen Content Engine 2.0',
                'bg' => [44, 62, 80],
                'text_color' => [241, 196, 15]
            ],
            [
                'name' => 'case-study-portrait.png',
                'orig' => 'Partner_Highlight_1080x1350.png',
                'width' => 1080,
                'height' => 1350,
                'aspect' => '4:5',
                'title' => 'Enterprise Customer Spotlight',
                'bg' => [99, 110, 114],
                'text_color' => [255, 255, 255]
            ]
        ];

        foreach ($sampleImages as $imgData) {
            $destPath = $uploadDir . '/' . $imgData['name'];
            if (!file_exists($destPath) && function_exists('imagecreatetruecolor')) {
                $img = imagecreatetruecolor($imgData['width'], $imgData['height']);
                $bg = imagecolorallocate($img, $imgData['bg'][0], $imgData['bg'][1], $imgData['bg'][2]);
                $fg = imagecolorallocate($img, $imgData['text_color'][0], $imgData['text_color'][1], $imgData['text_color'][2]);
                imagefill($img, 0, 0, $bg);
                
                $white = imagecolorallocate($img, 255, 255, 255);
                imagerectangle($img, 40, 40, $imgData['width'] - 40, $imgData['height'] - 40, $white);
                imagestring($img, 5, 80, 80, $imgData['title'], $fg);
                imagestring($img, 4, 80, 120, "Resolution: " . $imgData['width'] . "x" . $imgData['height'] . " (" . $imgData['aspect'] . ")", $white);
                imagestring($img, 4, 80, 150, "Marketing Content Hub - High-Res Asset", $white);
                
                imagepng($img, $destPath);
                imagedestroy($img);
            }
        }

        $today = date('Y-m-d H:i:s');
        $tomorrow = date('Y-m-d H:i:s', strtotime('+1 day'));
        $nextWeek = date('Y-m-d H:i:s', strtotime('+4 days'));

        // Insert Content Posts with Facebook, Instagram, TikTok, LinkedIn, Twitter, Threads
        $posts = [
            [
                'campaign_id' => $campIds[0] ?? 1,
                'title' => '☀️ Summer Glow Collection Reveal',
                'primary_caption' => "Summer has officially arrived! ☀️ Dive into our all-new vibrant collection crafted for sunshine days, effortless elegance, and modern creators. Available worldwide starting today.",
                'channel_captions' => json_encode([
                    'facebook' => "Summer is here! ☀️ Explore our brand-new summer drop featuring sustainable fabrics and breathable designs for all your warm-weather adventures.\n\nShop the collection: https://brand.com/summer-2026",
                    'instagram' => "Summer has arrived! ☀️ Dive into our new vibrant collection crafted for sunshine days and effortless elegance. Tap the link in bio to shop the drop before it sells out! ✨\n\nDrop your favorite piece in the comments 👇",
                    'tiktok' => "Wait till you see the new summer drop 🔥 Pack your bags and get ready for sunshine season! Which one are you wearing first? #linkinbio to cop now!",
                    'linkedin' => "We are excited to announce the launch of our Summer 2026 Campaign. This release represents months of dedicated design, customer feedback integration, and sustainable sourcing. Discover how our brand continues to innovate in retail and lifestyle.",
                    'twitter' => "It's finally here ☀️ Our Summer 2026 Collection is live now! Fresh styles, sustainable fabrics, and vibrant colors. Grab yours before they're gone: http://hub.brand.com/summer",
                    'threads' => "Our Summer 2026 Collection is live! ☀️ Sustainable, breathable, and designed for every day. What colorway are you picking up?"
                ]),
                'hashtags' => json_encode(['#Summer2026', '#StyleDrop', '#SummerVibes', '#NewArrivals', '#SustainableFashion', '#TrendingNow']),
                'status' => 'ready',
                'scheduled_for' => $today,
                'media' => [
                    [
                        'file_name' => 'summer-hero-square.png',
                        'original_name' => 'Summer_Hero_Banner_1080x1080.png',
                        'mime_type' => 'image/png',
                        'file_size' => file_exists($uploadDir . '/summer-hero-square.png') ? filesize($uploadDir . '/summer-hero-square.png') : 150000,
                        'file_path' => 'uploads/summer-hero-square.png',
                        'width' => 1080,
                        'height' => 1080,
                        'aspect_ratio' => '1:1',
                        'file_type' => 'image'
                    ],
                    [
                        'file_name' => 'summer-story-reel.png',
                        'original_name' => 'Summer_Story_Reel_1080x1920.png',
                        'mime_type' => 'image/png',
                        'file_size' => file_exists($uploadDir . '/summer-story-reel.png') ? filesize($uploadDir . '/summer-story-reel.png') : 220000,
                        'file_path' => 'uploads/summer-story-reel.png',
                        'width' => 1080,
                        'height' => 1920,
                        'aspect_ratio' => '9:16',
                        'file_type' => 'image'
                    ]
                ]
            ],
            [
                'campaign_id' => $campIds[1] ?? 2,
                'title' => '🚀 Introducing Content Engine 2.0: AI-Powered Workflows',
                'primary_caption' => "Supercharge your marketing velocity. We just rolled out Content Engine 2.0 featuring real-time collaborative editing, instant asset resizing, and one-click channel copy distribution.",
                'channel_captions' => json_encode([
                    'facebook' => "Content Engine 2.0 is live! 🚀 Accelerate your marketing production with automated multi-channel formatting, asset resizing, and team-wide campaign buckets.\n\nRead our launch announcement: https://brand.com/v2-announcement",
                    'instagram' => "Creating content just got 10x faster ⚡ Meet Content Engine 2.0! Instant resizing, smart caption formatting, and one-click asset downloads. Check out the walkthrough in our bio! 🚀",
                    'tiktok' => "If you work in marketing or content creation, this tool is going to save you 5 hours a week 🤯 Here is how Content Engine 2.0 works!",
                    'linkedin' => "Marketing velocity is no longer optional—it's a competitive advantage. Today we are launching Content Engine 2.0 to empower distributed teams with centralized asset control, compliant branding, and lightning-fast social distribution.",
                    'twitter' => "Big launch day! 🚀 Content Engine 2.0 is now live for all teams. Say goodbye to manual resizing and copy-pasting hashtags across 5 apps. Try it today: http://hub.brand.com/v2",
                    'threads' => "Content Engine 2.0 is here! 🚀 Multi-channel copy distribution, instant batch asset zip packaging, and real-time character limit enforcement."
                ]),
                'hashtags' => json_encode(['#ProductLaunch', '#MarketingTech', '#MarTech', '#Productivity', '#ContentStrategy', '#B2B']),
                'status' => 'ready',
                'scheduled_for' => $tomorrow,
                'media' => [
                    [
                        'file_name' => 'product-v2-landscape.png',
                        'original_name' => 'Product_Launch_Banner_1920x1080.png',
                        'mime_type' => 'image/png',
                        'file_size' => file_exists($uploadDir . '/product-v2-landscape.png') ? filesize($uploadDir . '/product-v2-landscape.png') : 310000,
                        'file_path' => 'uploads/product-v2-landscape.png',
                        'width' => 1920,
                        'height' => 1080,
                        'aspect_ratio' => '16:9',
                        'file_type' => 'image'
                    ]
                ]
            ],
            [
                'campaign_id' => $campIds[2] ?? 3,
                'title' => '💼 Enterprise Case Study: Scaling to 10M Impressions',
                'primary_caption' => "Discover how GlobalFin scaled their social presence by 400% in 90 days using structured asset hubs and multi-platform workflows.",
                'channel_captions' => json_encode([
                    'facebook' => "How did GlobalFin reach 10 Million organic impressions in just 3 months? We dive deep into their team workflow, asset repository structure, and publishing cadences in our new case study.\n\nDownload the case study: https://brand.com/globalfin-case-study",
                    'instagram' => "From 0 to 10M impressions in 90 days 📈 Swipe through to see the exact 3-step strategy GlobalFin used to dominate social media this quarter. Link in bio for the full breakdown! 💡",
                    'tiktok' => "How this company got 10M impressions without spending extra on ads 📈 Steal their 3-step strategy now!",
                    'linkedin' => "Consistency and asset governance are the secret engines of enterprise social growth. In our latest case study, we break down how GlobalFin restructured their content distribution to achieve a 400% YoY increase in engagement. Read the full report.",
                    'twitter' => "How did @GlobalFin scale to 10M impressions in 90 days? We broke down their complete playbook in our new case study 📊 Read here: http://hub.brand.com/case-study",
                    'threads' => "10M impressions in 90 days with 0 ad spend increase. The GlobalFin case study is now available for marketing teams."
                ]),
                'hashtags' => json_encode(['#CaseStudy', '#GrowthMarketing', '#EnterpriseStrategy', '#SocialMediaROI', '#Leadership']),
                'status' => 'ready',
                'scheduled_for' => $nextWeek,
                'media' => [
                    [
                        'file_name' => 'case-study-portrait.png',
                        'original_name' => 'Partner_Highlight_1080x1350.png',
                        'mime_type' => 'image/png',
                        'file_size' => file_exists($uploadDir . '/case-study-portrait.png') ? filesize($uploadDir . '/case-study-portrait.png') : 280000,
                        'file_path' => 'uploads/case-study-portrait.png',
                        'width' => 1080,
                        'height' => 1350,
                        'aspect_ratio' => '4:5',
                        'file_type' => 'image'
                    ]
                ]
            ]
        ];

        $stmtPost = $db->prepare("INSERT INTO content_posts (campaign_id, title, primary_caption, channel_captions, hashtags, status, scheduled_for) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmtMedia = $db->prepare("INSERT INTO media_assets (post_id, file_name, original_name, mime_type, file_size, file_path, width, height, aspect_ratio, file_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        foreach ($posts as $post) {
            $stmtPost->execute([
                $post['campaign_id'],
                $post['title'],
                $post['primary_caption'],
                $post['channel_captions'],
                $post['hashtags'],
                $post['status'],
                $post['scheduled_for']
            ]);
            $postId = (int)$db->lastInsertId();

            if (!empty($post['media'])) {
                foreach ($post['media'] as $media) {
                    $stmtMedia->execute([
                        $postId,
                        $media['file_name'],
                        $media['original_name'],
                        $media['mime_type'],
                        $media['file_size'],
                        $media['file_path'],
                        $media['width'],
                        $media['height'],
                        $media['aspect_ratio'],
                        $media['file_type']
                    ]);
                }
            }
        }
    }
}
