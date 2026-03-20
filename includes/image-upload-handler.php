<?php
/**
 * Image Upload Handler
 * Handles secure file uploads for hostel images
 */

class ImageUploadHandler {
    
    private $upload_dir = 'uploads/hostels/';
    private $base_path;
    private $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    private $max_file_size = 5242880; // 5MB
    
    public function __construct() {
        $this->base_path = dirname(__DIR__) . '/';
    }
    
    // ...
    
    public function uploadHostelImages($files, $hostel_id) {
        $results = [];
        
        // Create hostel-specific directory
        $relative_dir = $this->upload_dir . $hostel_id . '/';
        $full_dir = $this->base_path . $relative_dir;
        
        if (!is_dir($full_dir)) {
            if (!mkdir($full_dir, 0755, true)) {
                return ['error' => 'Failed to create upload directory'];
            }
        }
        
        // Handle multiple file uploads
        if (isset($files['name']) && is_array($files['name'])) {
            $file_count = count($files['name']);
            
            for ($i = 0; $i < $file_count; $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $upload_result = $this->processSingleFile(
                        $files['tmp_name'][$i],
                        $files['name'][$i],
                        $files['type'][$i],
                        $files['size'][$i],
                        $full_dir,
                        $relative_dir
                    );
                    
                    $results[] = $upload_result;
                } else {
                    $results[] = ['error' => $this->getUploadErrorMessage($files['error'][$i])];
                }
            }
        } else {
            // Single file upload
            if ($files['error'] === UPLOAD_ERR_OK) {
                $upload_result = $this->processSingleFile(
                    $files['tmp_name'],
                    $files['name'],
                    $files['type'],
                    $files['size'],
                    $full_dir,
                    $relative_dir
                );
                
                $results[] = $upload_result;
            } else {
                $results[] = ['error' => $this->getUploadErrorMessage($files['error'])];
            }
        }
        
        return $results;
    }
    
    /**
     * Process a single file upload
     */
    /**
     * Process a single file upload
     */
    private function processSingleFile($tmp_name, $original_name, $file_type, $file_size, $destination_dir, $web_dir) {
        // Validate file type
        if (!in_array($file_type, $this->allowed_types)) {
            return ['error' => 'Invalid file type. Only JPG, PNG, and GIF are allowed.'];
        }
        
        // Validate file size
        if ($file_size > $this->max_file_size) {
            return ['error' => 'File size exceeds 5MB limit.'];
        }
        
        // Generate unique filename
        $file_extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $unique_filename = uniqid('hostel_img_', true) . '.' . $file_extension;
        $destination_path = $destination_dir . $unique_filename;
        $web_path = $web_dir . $unique_filename;
        
        // Move uploaded file
        if (move_uploaded_file($tmp_name, $destination_path)) {
            // Optionally resize/compress image
            $this->optimizeImage($destination_path, $file_type);
            
            return [
                'success' => true,
                'path' => $web_path,
                'filename' => $unique_filename
            ];
        } else {
            return ['error' => 'Failed to move uploaded file.'];
        }
    }
    
    /**
     * Optimize uploaded image (resize if too large, compress)
     */
    private function optimizeImage($file_path, $mime_type) {
        // Check if GD library is available
        if (!extension_loaded('gd') || !function_exists('imagecreatefromjpeg')) {
            return; // Skip optimization if GD is not available
        }


        // Get image dimensions
        list($width, $height) = getimagesize($file_path);
        
        // Only resize if width or height exceeds 1920px
        $max_dimension = 1920;
        if ($width > $max_dimension || $height > $max_dimension) {
            $ratio = $width / $height;
            
            if ($width > $height) {
                $new_width = $max_dimension;
                $new_height = $max_dimension / $ratio;
            } else {
                $new_height = $max_dimension;
                $new_width = $max_dimension * $ratio;
            }
            
            // Create new image resource
            switch ($mime_type) {
                case 'image/jpeg':
                case 'image/jpg':
                    $source = imagecreatefromjpeg($file_path);
                    break;
                case 'image/png':
                    $source = imagecreatefrompng($file_path);
                    break;
                case 'image/gif':
                    $source = imagecreatefromgif($file_path);
                    break;
                default:
                    return; // Unsupported type
            }
            
            // Create resized image
            $destination = imagecreatetruecolor($new_width, $new_height);
            
            // Preserve transparency for PNG and GIF
            if ($mime_type == 'image/png' || $mime_type == 'image/gif') {
                imagealphablending($destination, false);
                imagesavealpha($destination, true);
            }
            
            imagecopyresampled($destination, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            
            // Save resized image
            switch ($mime_type) {
                case 'image/jpeg':
                case 'image/jpg':
                    imagejpeg($destination, $file_path, 85); // 85% quality
                    break;
                case 'image/png':
                    imagepng($destination, $file_path, 8); // Compression level 8
                    break;
                case 'image/gif':
                    imagegif($destination, $file_path);
                    break;
            }
            
            // Free memory
            imagedestroy($source);
            imagedestroy($destination);
        }
    }
    
    /**
     * Delete an image file and its database record
     */
    public function deleteImage($image_path) {
        if (file_exists($image_path)) {
            return unlink($image_path);
        }
        return false;
    }
    
    /**
     * Get human-readable upload error message
     */
    private function getUploadErrorMessage($error_code) {
        $upload_errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive in HTML form',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];
        
        return isset($upload_errors[$error_code]) ? $upload_errors[$error_code] : 'Unknown upload error';
    }
}
?>
