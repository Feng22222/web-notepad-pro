<?php
$target_dir = "_file/";

// 创建文件夹请求处理
if (isset($_POST['createFolder'])) {
    $folderName = $_POST['folderName'];
    $fullPath = $target_dir . $folderName;
    
    // 安全检查：防止目录遍历
    if (strpos($fullPath, '../') !== false || strpos($fullPath, './') === 0) {
        http_response_code(400);
        echo "无效的文件夹路径。";
        exit;
    }
    
    if (!file_exists($fullPath)) {
        if (mkdir($fullPath, 0755, true)) {
            echo "文件夹创建成功。";
        } else {
            http_response_code(500);
            echo "文件夹创建失败。";
        }
    } else {
        http_response_code(400);
        echo "文件夹已存在。";
    }
    exit;
}

// 文件上传处理
if (isset($_FILES['image'])) {
    $file = $_FILES['image'];
    $relativePath = isset($_POST['relativePath']) ? $_POST['relativePath'] : '';
    
    // 安全检查：防止目录遍历
    if (strpos($relativePath, '../') !== false || strpos($relativePath, './') === 0) {
        http_response_code(400);
        echo "无效的文件路径。";
        exit;
    }
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $original_name = $file['name'];
        $file_ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        
        $dirPath = $target_dir;
        $fileName = '';
        
        // 处理相对路径
        if (!empty($relativePath)) {
            // 检查是否包含文件扩展名（判断是否为文件路径）
            if (pathinfo($relativePath, PATHINFO_EXTENSION) !== '') {
                // 文件夹上传，使用原始相对路径
                $pathInfo = pathinfo($relativePath);
                $dirPath = $target_dir . $pathInfo['dirname'];
                $fileName = basename($relativePath);
            } else {
                // 单个文件上传到指定目录，或者relativePath是目录路径
                $dirPath = $target_dir . rtrim($relativePath, '/');
                $fileName = '';
            }
        } else {
            // 单个文件上传到根目录
            $fileName = '';
        }
        
        // 创建目录结构
        if (!file_exists($dirPath)) {
            mkdir($dirPath, 0755, true);
        }
        
        // 生成文件名
        if (empty($fileName)) {
            // 单个文件上传，使用日期+随机数命名
            $date = date('Ymd');
            $random = sprintf('%04d', rand(0, 9999));
            $fileName = $date . '-' . $random . '.' . $file_ext;
        }
        
        // 完整的目标文件路径
        $target_file = $dirPath . '/' . $fileName;
        
        // 检查文件是否已经存在
        if (file_exists($target_file)) {
            // 如果文件已存在，添加随机数后缀
            $date = date('Ymd');
            $random = sprintf('%04d', rand(0, 9999));
            $fileName = $date . '-' . $random . '.' . $file_ext;
            $target_file = $dirPath . '/' . $fileName;
        }
        
        // 调试信息
        error_log("Upload attempt: ".print_r($file, true));
        error_log("Target file: $target_file");
        error_log("Dir path exists: " . (file_exists($dirPath) ? "yes" : "no"));
        error_log("Tmp file exists: " . (file_exists($file['tmp_name']) ? "yes" : "no"));
        error_log("Tmp file size: " . filesize($file['tmp_name']));
        
        // 尝试将文件移动到目标目录
        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            error_log("File moved successfully to $target_file");
            
            // 对于图片文件，创建缩略图
            $image_types = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tiff'];
            if (in_array($file_ext, $image_types)) {
                // 只有顶层目录的图片才创建缩略图
                if (empty($relativePath)) {
                    $thumbWidth = 200; // 定义缩略图的宽度
                    $thumbnail_dir = $target_dir . 'thumbnails/';
                    
                    // 确保缩略图目录存在
                    if (!file_exists($thumbnail_dir)) {
                        mkdir($thumbnail_dir, 0755, true);
                    }
                    
                    $thumbnail_file = $thumbnail_dir . basename($target_file);
                    
                    // 检查文件是否是有效的图片
                    $image_info = getimagesize($target_file);
                    if ($image_info !== false) {
                        list($width, $height) = $image_info;
                        
                        // 计算缩略图的高度
                        $thumbHeight = $height * ($thumbWidth / $width);
                        
                        $newImg = imagecreatetruecolor($thumbWidth, $thumbHeight);
                        
                        // 根据图片类型创建源图像
                        switch ($file_ext) {
                            case 'jpg':
                            case 'jpeg':
                                $sourceImg = imagecreatefromjpeg($target_file);
                                break;
                            case 'png':
                                $sourceImg = imagecreatefrompng($target_file);
                                break;
                            case 'gif':
                                $sourceImg = imagecreatefromgif($target_file);
                                break;
                            case 'webp':
                                $sourceImg = imagecreatefromwebp($target_file);
                                break;
                            case 'bmp':
                                $sourceImg = imagecreatefrombmp($target_file);
                                break;
                            default:
                                $sourceImg = false;
                        }
                        
                        if ($sourceImg !== false) {
                            imagecopyresized($newImg, $sourceImg, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);
                            
                            // 根据图片类型保存缩略图
                            switch ($file_ext) {
                                case 'jpg':
                                case 'jpeg':
                                    imagejpeg($newImg, $thumbnail_file);
                                    break;
                                case 'png':
                                    imagepng($newImg, $thumbnail_file);
                                    break;
                                case 'gif':
                                    imagegif($newImg, $thumbnail_file);
                                    break;
                                case 'webp':
                                    imagewebp($newImg, $thumbnail_file);
                                    break;
                            }
                            
                            imagedestroy($newImg);
                            imagedestroy($sourceImg);
                        }
                    }
                }
            }
            
            echo "文件成功上传。";
        } else {
            $error_message = "文件上传失败。";
            switch($file['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    $error_message .= " 超出php.ini中upload_max_filesize限制。";
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $error_message .= " 超出表单中MAX_FILE_SIZE限制。";
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $error_message .= " 文件只有部分被上传。";
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $error_message .= " 没有文件被上传。";
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $error_message .= " 缺少临时文件夹。";
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $error_message .= " 文件写入失败。";
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $error_message .= " PHP扩展阻止了文件上传。";
                    break;
            }
            error_log("Upload failed: $error_message");
            http_response_code(500);
            echo $error_message;
        }
    } else {
        http_response_code(400);
        echo "文件上传错误。";
    }
}
?>
