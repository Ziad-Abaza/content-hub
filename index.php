<?php
/**
 * Marketing Content Hub - Entry Point & Front Controller
 * Pure PHP 8.4+ / Apache mod_rewrite Compatible
 */

// Error handling in development
error_reporting(E_ALL);
ini_set('display_errors', '0');

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/Controllers/PostController.php';
require_once __DIR__ . '/app/Controllers/CampaignController.php';
require_once __DIR__ . '/app/Controllers/MediaController.php';

// Initialize Database & Run Auto-Migration/Seeder if first run
Database::getConnection();

// Parse requested route
$route = $_GET['route'] ?? '';
$route = trim($route, '/');
$method = $_SERVER['REQUEST_METHOD'];

// Route dispatching
if ($route === '' || $route === 'index.php') {
    // Render the Main Content Hub Single Page Interface
    $postModel = new Post();
    $campaignModel = new Campaign();
    
    $campaigns = $campaignModel->getAll();
    $posts = $postModel->getAll();
    
    require __DIR__ . '/views/hub.php';
    exit;
}

// API Routes
$postController = new PostController();
$campaignController = new CampaignController();
$mediaController = new MediaController();

// Match API patterns
if (preg_match('#^api/posts/(\d+)/track-copy$#', $route, $matches)) {
    $postController->trackCopy((int)$matches[1]);
} elseif (preg_match('#^api/posts/(\d+)$#', $route, $matches)) {
    $id = (int)$matches[1];
    if ($method === 'GET') {
        $postController->show($id);
    } elseif ($method === 'POST' || $method === 'PUT') {
        $postController->update($id);
    } elseif ($method === 'DELETE') {
        $postController->delete($id);
    }
} elseif ($route === 'api/posts') {
    if ($method === 'GET') {
        $postController->index();
    } elseif ($method === 'POST') {
        $postController->store();
    }
} elseif (preg_match('#^api/campaigns/(\d+)$#', $route, $matches)) {
    $id = (int)$matches[1];
    if ($method === 'POST' || $method === 'PUT') {
        $campaignController->update($id);
    } elseif ($method === 'DELETE') {
        $campaignController->delete($id);
    }
} elseif ($route === 'api/campaigns') {
    if ($method === 'GET') {
        $campaignController->index();
    } elseif ($method === 'POST') {
        $campaignController->store();
    }
} elseif ($route === 'api/media/upload') {
    if ($method === 'POST') {
        $mediaController->upload();
    }
} elseif (preg_match('#^api/media/(\d+)$#', $route, $matches)) {
    $id = (int)$matches[1];
    if ($method === 'DELETE') {
        $mediaController->delete($id);
    }
} elseif ($route === 'download') {
    $id = !empty($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id > 0) {
        $mediaController->download($id);
    } else {
        http_response_code(400);
        echo "Invalid media ID";
    }
} elseif ($route === 'batch-download' || $route === 'api/media/batch-download') {
    $mediaController->batchDownload();
} else {
    // 404 handler
    if (str_starts_with($route, 'api/')) {
        header('Content-Type: application/json');
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'API endpoint not found']);
    } else {
        // Fallback to hub
        header("Location: index.php");
    }
    exit;
}
