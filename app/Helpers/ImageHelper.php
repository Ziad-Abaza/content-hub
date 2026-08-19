<?php
/**
 * Image Processing & Aspect Ratio Helper
 */

class ImageHelper {
    public static function detectAspectRatio(int $width, int $height): string {
        if ($width <= 0 || $height <= 0) return '1:1';
        $ratio = $width / $height;

        if (abs($ratio - 1.0) < 0.05) {
            return '1:1';
        } elseif (abs($ratio - (9 / 16)) < 0.08) {
            return '9:16';
        } elseif (abs($ratio - (16 / 9)) < 0.08) {
            return '16:9';
        } elseif (abs($ratio - (4 / 5)) < 0.08) {
            return '4:5';
        } elseif (abs($ratio - (5 / 4)) < 0.08) {
            return '5:4';
        } elseif (abs($ratio - (4 / 3)) < 0.08) {
            return '4:3';
        }

        return round($ratio, 2) . ':1';
    }

    public static function processUploadedFile(array $file): ?array {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $uploadDir = __DIR__ . '/../../uploads';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $origName = basename($file['name']);
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        $uniqueName = uniqid('asset_', true) . '.' . $ext;
        $destPath = $uploadDir . '/' . $uniqueName;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return null;
        }

        $mimeType = mime_content_type($destPath) ?: $file['type'];
        $fileSize = filesize($destPath);
        $fileType = 'document';
        $width = null;
        $height = null;
        $aspectRatio = '1:1';

        if (str_starts_with($mimeType, 'image/')) {
            $fileType = 'image';
            $imgInfo = @getimagesize($destPath);
            if ($imgInfo) {
                $width = $imgInfo[0];
                $height = $imgInfo[1];
                $aspectRatio = self::detectAspectRatio($width, $height);
            }
        } elseif (str_starts_with($mimeType, 'video/')) {
            $fileType = 'video';
            $aspectRatio = '9:16'; // Default video assumption unless probed
        }

        return [
            'file_name' => $uniqueName,
            'original_name' => $origName,
            'mime_type' => $mimeType,
            'file_size' => $fileSize,
            'file_path' => 'uploads/' . $uniqueName,
            'width' => $width,
            'height' => $height,
            'aspect_ratio' => $aspectRatio,
            'file_type' => $fileType
        ];
    }
}
