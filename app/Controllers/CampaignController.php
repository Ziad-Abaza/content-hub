<?php
/**
 * Campaign Controller
 */

require_once __DIR__ . '/../Models/Campaign.php';

class CampaignController {
    private Campaign $campaignModel;

    public function __construct() {
        $this->campaignModel = new Campaign();
    }

    public function index(): void {
        header('Content-Type: application/json');
        $campaigns = $this->campaignModel->getAll();
        echo json_encode(['status' => 'success', 'data' => $campaigns]);
        exit;
    }

    public function store(): void {
        header('Content-Type: application/json');
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?? $_POST;

        if (empty($data['title'])) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Campaign title is required']);
            exit;
        }

        $id = $this->campaignModel->create($data);
        $campaign = $this->campaignModel->getById($id);

        http_response_code(201);
        echo json_encode(['status' => 'success', 'message' => 'Campaign created', 'data' => $campaign]);
        exit;
    }

    public function update(int $id): void {
        header('Content-Type: application/json');
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?? $_POST;

        if (empty($data['title'])) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Campaign title is required']);
            exit;
        }

        $this->campaignModel->update($id, $data);
        $campaign = $this->campaignModel->getById($id);

        echo json_encode(['status' => 'success', 'message' => 'Campaign updated', 'data' => $campaign]);
        exit;
    }

    public function delete(int $id): void {
        header('Content-Type: application/json');
        $this->campaignModel->delete($id);
        echo json_encode(['status' => 'success', 'message' => 'Campaign deleted']);
        exit;
    }
}
