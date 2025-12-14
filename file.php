<?php session_start(); // 启动会话
// #$correct_password = '123'; // 在这里设置你的密码

// if (isset($_POST['password'])) {
//     if ($_POST['password'] === $correct_password) {
//         $_SESSION['authenticated'] = true;
//         header('Location: ' . $_SERVER['REQUEST_URI']); // 添加这一行来进行重定向
//         exit; // 结束脚本执行
//     }
// }
// ?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>网盘管理</title>
    <link rel="stylesheet" href="/styles.css">
    <style>

        
        /* 文件夹项样式 */
        .folder-item {
            cursor: pointer;
            display: flex;
            align-items: center;
            color: var(--text-primary);
            padding: 10px 14px;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
            user-select: none;
            background-color: var(--background-color);
            border: 1px solid var(--border-color);
            margin-bottom: 6px;
            box-shadow: var(--shadow-sm);
        }
        
        /* 文件夹悬停效果 */
        .folder-item:hover {
            background-color: var(--primary-color);
            color: white;
            transform: translateX(4px);
            box-shadow: var(--shadow-md);
            border-color: var(--primary-color);
        }
        
        /* 文件夹激活状态 */
        .folder-item.active {
            background-color: var(--primary-color);
            color: white;
            box-shadow: var(--shadow-md);
            border-color: var(--primary-color);
            transform: translateX(4px);
        }
        
        /* 文件夹点击效果 */
        .folder-item:active {
            transform: translateX(2px) scale(0.99);
        }
        
        /* 文件夹图标 */
        .folder-icon {
            margin-right: 12px;
            font-size: 1.3rem;
            font-weight: bold;
            transition: transform 0.2s;
        }
        
        .folder-item:hover .folder-icon {
            transform: scale(1.1);
        }
        
        /* 切换图标 */
        .toggle-icon {
            margin-left: auto;
            font-size: 1rem;
            font-weight: bold;
            transition: transform 0.2s ease;
            color: var(--secondary-color);
        }
        
        .folder-item:hover .toggle-icon {
            color: white;
        }
        
        /* 子文件夹样式 */
        .subfolder {
            list-style: none;
            padding-left: 30px;
            margin: 6px 0 0 0;
            background-color: var(--surface-color);
            border-left: 2px solid var(--border-color);
            border-radius: 0 0 0 0.5rem;
            display: none; /* 默认折叠子文件夹 */
        }
        
        /* 子文件夹项 */
        .subfolder .folder-item {
            padding-left: 12px;
            font-size: 0.95rem;
            margin-bottom: 4px;
        }
        
        /* 深层子文件夹 */
        .subfolder .subfolder {
            padding-left: 25px;
        }
        
        /* 深层子文件夹项 */
        .subfolder .subfolder .folder-item {
            padding-left: 10px;
            font-size: 0.9rem;
        }
        
        /* 上传区域样式 */
        .upload-area {
            margin-bottom: 25px;
            padding: 20px;
            background-color: var(--background-color);
            border-radius: 0.75rem;
            box-shadow: var(--shadow-sm);
        }
        
        /* 按钮容器样式 */
        .button-container {
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        /* 进度条样式 */
        .progress {
            margin: 20px 0;
            padding: 15px;
            background-color: var(--background-color);
            border-radius: 0.75rem;
            box-shadow: var(--shadow-sm);
        }
        
        .progress-title {
            margin-bottom: 10px;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        
        .progress-bar {
            width: 0%;
            height: 24px;
            background-color: var(--primary-color);
            border-radius: 12px;
            transition: width 0.3s ease;
            color: white;
            text-align: center;
            line-height: 24px;
            font-weight: 600;
            box-shadow: var(--shadow-sm);
        }
        
        /* 消息样式 */
        #message {
            margin: 20px 0;
            padding: 15px;
            border-radius: 0.75rem;
            text-align: center;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-sm);
        }
        
        #message.success {
            background-color: rgba(16, 185, 129, 0.1);
            color: #10b981;
            border: 1px solid #10b981;
        }
        
        #message.error {
            background-color: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border: 1px solid #ef4444;
        }
        
        #message.info {
            background-color: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
            border: 1px solid #3b82f6;
        }
        
        /* 当前路径样式 */
        #currentPath {
            color: var(--primary-color);
            font-weight: 600;
        }
        
        /* 文件容器样式 - 网盘风格 */
        .file-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            padding: 15px;
        }
        
        /* 文件项样式 */
        .file-item {
            background-color: var(--background-color);
            border-radius: 0.75rem;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            transition: all 0.3s ease;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            border: 2px solid transparent;
        }
        
        .file-item:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-color);
        }
        
        .file-item:active {
            transform: translateY(-2px);
        }
        
        /* 文件缩略图 */
        .file-thumbnail {
            width: 100%;
            height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--surface-color);
            overflow: hidden;
            position: relative;
        }
        
        .file-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .file-item:hover .file-thumbnail img {
            transform: scale(1.05);
        }
        
        /* 文件图标 */
        .file-icon {
            font-size: 3.5rem;
            color: var(--primary-color);
            font-weight: bold;
            background-color: var(--surface-color);
        }
        
        /* 文件信息 */
        .file-info {
            padding: 16px;
            flex-grow: 1;
            background-color: var(--background-color);
        }
        
        .file-name {
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--text-primary);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 1rem;
        }
        
        .file-meta {
            font-size: 0.85rem;
            color: var(--text-secondary);
            display: flex;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        /* 文件操作按钮 */
        .file-actions {
            display: flex;
            gap: 8px;
            padding: 12px 16px;
            border-top: 1px solid var(--border-color);
            background-color: var(--surface-color);
        }
        
        .file-action-btn {
            /* 移除flex: 1，让按钮宽度根据内容自适应 */
            padding: 8px 12px;
            border: none;
            border-radius: 0.5rem;
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.85rem;
            box-shadow: var(--shadow-sm);
            /* 添加最大宽度限制，确保按钮不会太宽 */
            max-width: 200px;
            /* 让按钮宽度根据内容自适应 */
            width: auto;
            /* 保持按钮内文字居中 */
            text-align: center;
        }
        
        .file-action-btn:hover {
            background-color: #4338ca;
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }
        
        .file-action-btn:active {
            transform: translateY(0);
            box-shadow: var(--shadow-sm);
        }
        
        /* 顶部上传按钮样式 */
        .upload-btn {
            /* 让按钮宽度根据内容自适应 */
            width: auto;
            /* 取消flex: 1效果 */
            flex: none;
            /* 添加适当的内边距 */
            padding: 8px 16px;
            /* 设置最大宽度 */
            max-width: 180px;
            /* 保持文字居中 */
            text-align: center;
        }
        
        .file-actions a {
            flex: 1;
            padding: 8px 12px;
            border: none;
            border-radius: 0.5rem;
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.85rem;
            text-decoration: none;
            text-align: center;
            box-shadow: var(--shadow-sm);
        }
        
        .file-actions a:hover {
            background-color: #4338ca;
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }
        
        /* 灯箱样式 */
        .lightbox {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.95);
            text-align: center;
            backdrop-filter: blur(5px);
        }
        
        .lightbox-image {
            position: absolute;
            top: 50%;
            left: 50%;
            max-width: 95%;
            max-height: 90%;
            transform: translate(-50%, -50%);
            border-radius: 0.75rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            transition: all 0.3s ease;
        }
        
        #close {
            color: #f1f1f1;
            position: absolute;
            top: 30px;
            right: 50px;
            font-size: 45px;
            font-weight: bold;
            transition: all 0.3s;
            cursor: pointer;
            opacity: 0.8;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
        }
        
        #close:hover, #close:focus {
            color: var(--primary-color);
            opacity: 1;
            transform: scale(1.1);
        }
        
        /* 灯箱控制按钮 */
        #lightbox-controls {
            position: absolute;
            bottom: 40px;
            left: 50%;
            transform: translateX(-50%);
            background-color: rgba(0, 0, 0, 0.7);
            padding: 15px 25px;
            border-radius: 30px;
            backdrop-filter: blur(10px);
        }
        
        /* 灯箱控制按钮容器 */
        #lightbox-controls .button-container {
            gap: 15px;
        }
        
        /* 灯箱控制按钮样式 */
        #lightbox-controls button {
            padding: 12px 24px;
            font-size: 1rem;
        }
        
        /* 创建文件夹对话框 */
        #createFolderDialog {
            backdrop-filter: blur(5px);
        }
        

    </style>
</head>
<body>
    <div class="container" id="app">
        <div class="sidebar" id="my-menu">
            <nav class="breadcrumb">
                <a href="/" class="breadcrumb-item">笔记</a>
                <a href="file.php" class="breadcrumb-item">网盘</a>
            </nav>
            
            <!-- 左侧文件夹导航 -->
            <ul id="folderTree" class="note-list">
                    <?php
                    // 递归函数生成文件夹树
                    function generateFolderTree($dir, $prefix = '') {
                        foreach (glob($dir . '/*', GLOB_ONLYDIR) as $folder) {
                            $folderName = basename($folder);
                            $relativePath = substr($folder, 5); // 移除 '_file' 前缀
                            
                            // 获取当前文件夹的子目录
                            $subfolders = glob($folder . '/*', GLOB_ONLYDIR);
                            $hasSubfolders = !empty($subfolders);
                            
                            echo '<li>';
                            echo '<span class="folder-item" onclick="selectFolder(this)' . ($hasSubfolders ? '; toggleFolder(this)' : '') . '" data-path="' . $relativePath . '">';
                            echo '<span class="folder-icon">📁</span>';
                            echo $folderName;
                            
                            // 只有当有子目录时才显示展开/折叠图标
                            if ($hasSubfolders) {
                                echo '<span class="toggle-icon">▶</span>';
                            }
                            
                            echo '</span>';
                            
                            // 只有当有子目录时才生成子目录列表
                            if ($hasSubfolders) {
                                echo '<ul class="subfolder">';
                                generateFolderTree($folder, $prefix . $folderName . '/');
                                echo '</ul>';
                            }
                            
                            echo '</li>';
                        }
                    }
                    
                    // 生成文件夹树 - 根目录
                    echo '<li>';
                    // 检查根目录是否有子文件夹
                    $rootSubfolders = glob('_file/*', GLOB_ONLYDIR);
                    $rootHasSubfolders = !empty($rootSubfolders);
                    
                    echo '<span class="folder-item" onclick="selectFolder(this)' . ($rootHasSubfolders ? '; toggleFolder(this)' : '') . '" data-path="">';
                    echo '<span class="folder-icon">📁</span>';
                    echo '根目录';
                    
                    // 只有当根目录有子目录时才显示展开/折叠图标
                    if ($rootHasSubfolders) {
                        echo '<span class="toggle-icon">▶</span>';
                    }
                    
                    echo '</span>';
                    
                    // 只有当根目录有子目录时才生成子目录列表
                    if ($rootHasSubfolders) {
                        echo '<ul class="subfolder">';
                    }
                    generateFolderTree('_file');
                    if ($rootHasSubfolders) {
                        echo '</ul>';
                    }
                    echo '</li>';
                    ?>
                </ul>
            </div>
        <main class="content">

            <div style="margin: 10px 0; padding: 10px; background-color: var(--surface-color); border-radius: 0.75rem;">
                <p style="margin: 0; color: var(--text-primary);">
                    <strong>当前路径：</strong><span id="currentPath">根目录</span>
                </p>
            </div>
            <div class="upload-area">
                <input type="file" accept="*/*" id="fileInput" style="display: none;">
                <input type="file" webkitdirectory directory multiple id="folderInput" style="display: none;">
                <div class="button-container upload-buttons" style="flex-direction: row; gap: 10px; justify-content: flex-start;">
                    <button class="file-action-btn upload-btn" onclick="openFilePicker()">选择文件上传</button>
                    <button class="file-action-btn upload-btn" onclick="openFolderPicker()">选择文件夹上传</button>
                    <button class="file-action-btn upload-btn" onclick="showCreateFolderDialog()">创建文件夹</button>
                </div>
            </div>
            
            <!-- 创建文件夹对话框 -->
            <div id="createFolderDialog" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 2000;">
                <div style="background-color: var(--surface-color); margin: 15% auto; padding: 20px; border-radius: 0.75rem; width: 90%; max-width: 400px; box-shadow: var(--shadow-lg);">
                    <h3>创建文件夹</h3>
                    <input type="text" id="folderName" placeholder="请输入文件夹名称" style="width: 100%; padding: 10px; margin: 10px 0; border: 1px solid var(--border-color); border-radius: 0.5rem; background-color: var(--background-color); color: var(--text-primary);">
                    <div style="display: flex; justify-content: flex-end; gap: 10px;">
                        <button class="file-action-btn" onclick="closeCreateFolderDialog()" style="background-color: var(--secondary-color);">取消</button>
                        <button class="file-action-btn" onclick="createFolder()">创建</button>
                    </div>
                </div>
            </div>
            
            <div class="progress" style="display: none;">
                <div class="progress-title">上传进度：</div>
                <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">0%</div>
            </div>
            

            

            <div id="lightbox" class="lightbox" onclick="closeLightbox()">
              <span id="close">&times;</span>
              <img id="lightbox-img" class="lightbox-image">
              <video id="lightbox-video" class="lightbox-image" controls></video>
              <div id="lightbox-controls"> <!-- 控制按钮容器 -->
                <div class="button-container" style="flex-direction: row; gap: 0.5rem;">
                    <button class="file-action-btn" onclick="copyImageLink(event)">复制链接</button>
                    <button class="file-action-btn" onclick="downloadFile(event)">下载文件</button>
                </div>
              </div>
            </div>

    <script>
        // 简化上传功能，选择文件后直接上传
        // 添加调试信息
        console.log('Upload script loaded');
        
        var fileInput = document.getElementById('fileInput');
        var folderInput = document.getElementById('folderInput');
        
        // 检查DOM元素是否正确获取
        console.log('fileInput:', fileInput);
        console.log('folderInput:', folderInput);
        
        // 从URL中获取当前路径，避免刷新后回到根目录
        var currentPath = '';
        var urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('path')) {
            currentPath = urlParams.get('path') + '/';
        }
        
        // 页面加载完成后，初始化当前路径显示和文件列表
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing upload functionality');
            // 更新当前路径显示
            document.getElementById('currentPath').textContent = currentPath ? currentPath : '根目录';
            // 加载当前路径的文件列表
            loadFiles(currentPath);
        });
        
        // 保存当前展开的文件夹路径
        function saveExpandedFolders() {
            var expandedFolders = [];
            // 查找所有展开的子文件夹
            var expandedSubfolders = document.querySelectorAll('.subfolder[style*="display: block"]');
            expandedSubfolders.forEach(function(subfolder) {
                // 获取父文件夹项
                var folderItem = subfolder.previousElementSibling;
                if (folderItem && folderItem.classList.contains('folder-item')) {
                    // 获取文件夹路径
                    var path = folderItem.dataset.path;
                    expandedFolders.push(path);
                }
            });
            return expandedFolders;
        }
        
        // 恢复文件夹展开状态
        function restoreExpandedFolders(expandedFolders) {
            expandedFolders.forEach(function(path) {
                // 查找对应的文件夹项
                var folderItems = document.querySelectorAll('.folder-item');
                folderItems.forEach(function(item) {
                    if (item.dataset.path === path) {
                        // 展开文件夹
                        toggleFolder(item);
                    }
                });
            });
        }
        
        // 刷新左侧文件夹树
        function refreshFolderTree() {
            // 保存当前展开的文件夹
            var expandedFolders = saveExpandedFolders();
            
            // 使用Ajax获取新的文件夹树
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'file.php?refresh_tree=1', true);
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    var response = xhr.responseText;
                    var parser = new DOMParser();
                    var doc = parser.parseFromString(response, 'text/html');
                    var newFolderTree = doc.getElementById('folderTree');
                    if (newFolderTree) {
                        // 替换旧的文件夹树
                        var oldFolderTree = document.getElementById('folderTree');
                        if (oldFolderTree) {
                            oldFolderTree.innerHTML = newFolderTree.innerHTML;
                            // 恢复展开状态
                            restoreExpandedFolders(expandedFolders);
                            // 重新选择当前文件夹
                            var currentFolderItem = document.querySelector('.folder-item.active');
                            if (currentFolderItem) {
                                selectFolder(currentFolderItem);
                            }
                        }
                    }
                }
            };
            
            xhr.send();
        }
        
        // 监听文件选择
        fileInput.addEventListener('change', function () {
            console.log('File selected, files:', this.files);
            for (var i = 0; i < this.files.length; i++) {
                console.log('Uploading file:', this.files[i].name, 'size:', this.files[i].size);
                // 传递currentPath作为relativePath，确保文件上传到当前选择的目录
                uploadFile(this.files[i], '');
            }
        });
        
        // 监听文件夹选择
        folderInput.addEventListener('change', function () {
            console.log('Folder selected, files:', this.files);
            uploadFolder(this.files);
        });
        
        // 打开文件选择器
        function openFilePicker() {
            console.log('Opening file picker');
            fileInput.click();
        }
        
        // 打开文件夹选择器
        function openFolderPicker() {
            console.log('Opening folder picker');
            folderInput.click();
        }
        

        
        // 上传单个文件
        function uploadFile(file, relativePath = '', callback = null) {
            console.log('uploadFile function called with file:', file.name, 'size:', file.size);
            
            var message = document.getElementById('message');
            var progressDiv = document.querySelector('.progress');
            var progressBar = document.querySelector('.progress-bar');
            
            console.log('Progress elements:', progressDiv, progressBar);
            
            var fileName = relativePath || file.name;
            // 检查是否为文件夹上传模式（有回调函数即为文件夹上传）
            var isFolderUpload = !!callback;
            
            // 检查文件大小
            console.log('Checking file size, limit:', 500*1024*1024, 'actual:', file.size);
            if (file.size > 500*1024*1024) {
                var errorMsg = fileName + ': 文件过大，必须小于500MB';
                console.log('File too large:', errorMsg);
                if (message) {
                    showMessage(errorMsg, 'error');
                }
                if (callback) callback(false, errorMsg);
                return;
            }
            
            // 只有单个文件上传时才显示文件名称，文件夹上传时显示文件夹上传信息
            if (message && !isFolderUpload) {
                message.innerHTML = '正在上传: ' + fileName;
                message.className = 'message info';
                message.style.display = 'block';
            }
            
            // 显示进度条
            if (progressDiv) {
                progressDiv.style.display = 'block';
            } else {
                console.error('Progress div not found!');
            }
            
            // 单个文件上传时重置进度，文件夹上传时保留整体进度
            if (!isFolderUpload && progressBar) {
                progressBar.style.width = '0%';
                progressBar.textContent = '0%';
            }
            
            var formData = new FormData();
            formData.append('image', file);
            formData.append('relativePath', currentPath + relativePath);
            
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'upload.php', true);
            
            // 上传进度事件
            xhr.upload.onprogress = function(e) {
                if (e.lengthComputable) {
                    // 单个文件上传时显示当前文件的上传进度
                    // 文件夹上传时显示的是整体进度，由uploadFolder函数控制
                    if (!isFolderUpload) {
                        var percentComplete = (e.loaded / e.total) * 100;
                        progressBar.style.width = percentComplete + '%';
                        progressBar.textContent = Math.round(percentComplete) + '%';
                    }
                }
            };
            
            xhr.onload = function () {
                // 只有单个文件上传时才处理进度条和消息，文件夹上传时由uploadFolder函数控制
                if (!isFolderUpload) {
                    if (xhr.status === 200) {
                        // 单个文件上传，显示成功信息并刷新文件列表
                        if (message) {
                            showMessage('文件上传成功', 'success');
                        }
                        setTimeout(function() {
                            // 使用Ajax刷新文件列表，而不是整页刷新
                            loadFiles(currentPath);
                            // 刷新左侧文件夹树
                            refreshFolderTree();
                            // 隐藏进度条
                            progressDiv.style.display = 'none';
                        }, 1000);
                    } else {
                        var errorMsg = fileName + ': 文件上传失败：' + xhr.responseText;
                        if (message) {
                            showMessage(errorMsg, 'error');
                        }
                        // 隐藏进度条
                        progressDiv.style.display = 'none';
                    }
                } else {
                    // 文件夹上传，调用回调函数
                    if (xhr.status === 200) {
                        callback(true);
                    } else {
                        var errorMsg = fileName + ': 文件上传失败：' + xhr.responseText;
                        callback(false, errorMsg);
                    }
                }
            };
            
            xhr.onerror = function() {
                // 只有单个文件上传时才隐藏进度条，文件夹上传时由uploadFolder函数控制进度条的显示/隐藏
                if (!isFolderUpload) {
                    progressDiv.style.display = 'none';
                    var errorMsg = fileName + ': 上传失败，请重试';
                    if (message) {
                        showMessage(errorMsg, 'error');
                    }
                }
                if (callback) callback(false, fileName + ': 上传失败，请重试');
            };
            
            xhr.send(formData);
        }
        
        // 上传文件夹
        function uploadFolder(files) {
            var totalFiles = files.length;
            var uploadedFiles = 0;
            var failedFiles = 0;
            var failedFilesList = [];
            var message = document.getElementById('message');
            var progressDiv = document.querySelector('.progress');
            var progressBar = document.querySelector('.progress-bar');
            
            // 显示文件夹上传信息
            if (message) {
                message.innerHTML = '正在上传文件夹，共 ' + totalFiles + ' 个文件...';
                message.className = 'message info';
                message.style.display = 'block';
            }
            
            // 显示进度条，用于文件夹上传的整体进度
            progressDiv.style.display = 'block';
            progressBar.style.width = '0%';
            progressBar.textContent = '0%';
            
            for (var i = 0; i < files.length; i++) {
                var currentFile = files[i];
                var currentRelativePath = files[i].webkitRelativePath;
                
                uploadFile(currentFile, currentRelativePath, function(success, errorMessage) {
                    if (success) {
                        uploadedFiles++;
                    } else {
                        failedFiles++;
                        failedFilesList.push(errorMessage || '未知错误');
                    }
                    
                    // 更新整体进度
                    var overallProgress = (uploadedFiles / totalFiles) * 100;
                    progressBar.style.width = overallProgress + '%';
                    progressBar.textContent = Math.round(overallProgress) + '%';
                    
                    if (uploadedFiles + failedFiles === totalFiles) {
                        // 所有文件上传完成
                        if (message) {
                            if (failedFiles === 0) {
                                // 全部成功
                                message.innerHTML = '文件夹上传成功，共 ' + totalFiles + ' 个文件';
                                message.className = 'message success';
                            } else {
                                // 部分失败
                                message.innerHTML = '文件夹上传完成，成功 ' + uploadedFiles + ' 个文件，失败 ' + failedFiles + ' 个文件';
                                message.className = 'message error';
                                
                                // 如果有失败文件，显示具体错误信息
                                if (failedFilesList.length > 0) {
                                    setTimeout(function() {
                                        var errorDetails = '失败详情：<br>' + failedFilesList.join('<br>');
                                        message.innerHTML = message.innerHTML + '<br>' + errorDetails;
                                    }, 500);
                                }
                            }
                        }
                        
                        // 延迟隐藏进度条和刷新文件列表
                        setTimeout(function() {
                            progressDiv.style.display = 'none';
                            // 使用Ajax刷新文件列表，而不是整页刷新
                            loadFiles(currentPath);
                            // 刷新左侧文件夹树
                            refreshFolderTree();
                        }, 1000);
                    }
                });
            }
        }
        
        // 显示创建文件夹对话框
        function showCreateFolderDialog() {
            document.getElementById('createFolderDialog').style.display = 'block';
            document.getElementById('folderName').focus();
        }
        
        // 关闭创建文件夹对话框
        function closeCreateFolderDialog() {
            document.getElementById('createFolderDialog').style.display = 'none';
            document.getElementById('folderName').value = '';
        }
        
        // 文件夹导航功能
        function toggleFolder(element) {
            var subfolder = element.nextElementSibling;
            var toggleIcon = element.querySelector('.toggle-icon');
            
            if (subfolder && subfolder.classList.contains('subfolder')) {
                if (subfolder.style.display === 'block') {
                    subfolder.style.display = 'none';
                    toggleIcon.textContent = '▶';
                } else {
                    subfolder.style.display = 'block';
                    toggleIcon.textContent = '▼';
                }
            }
        }
        
        // 选择文件夹
        function selectFolder(element) {
            // 移除所有选中状态
            var allFolders = document.querySelectorAll('.folder-item');
            allFolders.forEach(function(folder) {
                folder.classList.remove('active');
            });
            
            // 添加当前选中状态
            element.classList.add('active');
            
            // 更新当前路径
            currentPath = element.dataset.path + '/';
            
            // 更新当前路径显示
            document.getElementById('currentPath').textContent = currentPath ? currentPath : '根目录';
            
            // 更新URL，保持当前路径状态
            var newUrl = window.location.origin + window.location.pathname + '?path=' + encodeURIComponent(element.dataset.path);
            window.history.pushState({}, '', newUrl);
            
            // 刷新文件列表
            loadFiles(currentPath);
        }
        
        // 加载文件列表
        function loadFiles(path) {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'file.php?path=' + encodeURIComponent(path), true);
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    var response = xhr.responseText;
                    var parser = new DOMParser();
                    var doc = parser.parseFromString(response, 'text/html');
                    var newFileContainer = doc.querySelector('.file-container');
                    if (newFileContainer) {
                        document.querySelector('.file-container').innerHTML = newFileContainer.innerHTML;
                    }
                } else {
                    showMessage('加载文件失败', 'error');
                }
            };
            
            xhr.send();
        }
        
        // 创建文件夹
        function createFolder() {
            var folderName = document.getElementById('folderName').value.trim();
            if (!folderName) {
                var message = document.getElementById('message');
                if (message) {
                    showMessage('请输入文件夹名称', 'error');
                }
                return;
            }
            
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'upload.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onload = function() {
                var message = document.getElementById('message');
                if (xhr.status === 200) {
                    if (message) {
                        showMessage('文件夹创建成功', 'success');
                    }
                    closeCreateFolderDialog();
                    setTimeout(function() {
                        // 使用Ajax刷新文件列表，而不是整页刷新
                        loadFiles(currentPath);
                        // 刷新左侧文件夹树
                        refreshFolderTree();
                    }, 1000);
                } else {
                    if (message) {
                        showMessage('文件夹创建失败：' + xhr.responseText, 'error');
                    }
                }
            };
            
            xhr.onerror = function() {
                var message = document.getElementById('message');
                if (message) {
                    showMessage('创建文件夹失败，请重试', 'error');
                }
            };
            
            xhr.send('createFolder=1&folderName=' + encodeURIComponent(currentPath + folderName));
        }
        
        // 通过路径选择文件夹
        function selectFolderByPath(path) {
            // 查找对应的文件夹元素
            var folderElements = document.querySelectorAll('.folder-item');
            var targetElement = null;
            
            folderElements.forEach(function(element) {
                if (element.dataset.path === path) {
                    targetElement = element;
                }
            });
            
            if (targetElement) {
                selectFolder(targetElement);
            } else {
                // 如果找不到元素，直接更新路径并加载文件
                currentPath = path + '/';
                document.getElementById('currentPath').textContent = currentPath ? currentPath : '根目录';
                loadFiles(currentPath);
            }
        }
        
        // 删除项目（文件或文件夹）
        function deleteItem(e, path, isFolder) {
            e.stopPropagation();
            
            if (confirm('确定要删除' + (isFolder ? '文件夹' : '文件') + '吗？')) {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', '/delete.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        showMessage((isFolder ? '文件夹' : '文件') + '删除成功', 'success');
                        setTimeout(function() {
                            // 使用Ajax刷新文件列表，而不是整页刷新
                            loadFiles(currentPath);
                            // 刷新左侧文件夹树
                            refreshFolderTree();
                        }, 1000);
                    } else {
                        showMessage((isFolder ? '文件夹' : '文件') + '删除失败', 'error');
                    }
                };
                
                xhr.send('type=file&path=' + encodeURIComponent(path) + '&isFolder=' + (isFolder ? '1' : '0'));
            }
        }
        
        // 灯箱功能
        function openLightbox(filePath, isImage, isVideo) {
            var lightbox = document.getElementById('lightbox');
            var lightboxImg = document.getElementById('lightbox-img');
            var lightboxVideo = document.getElementById('lightbox-video');
            
            // 隐藏所有媒体元素
            lightboxImg.style.display = 'none';
            lightboxVideo.style.display = 'none';
            
            if (isVideo) {
                // 视频预览
                lightboxVideo.src = filePath;
                lightboxVideo.style.display = 'block';
                lightboxVideo.style.maxWidth = '95%';
                lightboxVideo.style.maxHeight = '90%';
                lightboxVideo.style.width = 'auto';
                lightboxVideo.style.height = 'auto';
                lightboxVideo.load();
                lightboxVideo.play();
            } else if (isImage) {
                // 图片预览
                var img = new Image();
                img.src = filePath;
                img.onload = function() {
                    var aspectRatio = this.width / this.height;
                    var boxWidth = window.innerWidth * 0.9;
                    var boxHeight = window.innerHeight * 0.8;
                    var imgWidth, imgHeight;
                    
                    if (boxWidth / boxHeight > aspectRatio) {
                        imgHeight = boxHeight;
                        imgWidth = imgHeight * aspectRatio;
                    } else {
                        imgWidth = boxWidth;
                        imgHeight = imgWidth / aspectRatio;
                    }
                    
                    lightboxImg.style.width = imgWidth + 'px';
                    lightboxImg.style.height = imgHeight + 'px';
                }
                lightboxImg.src = filePath;
                lightboxImg.style.display = 'block';
            }
            
            lightbox.style.display = 'block';
        }
        
        function closeLightbox() {
            var lightbox = document.getElementById('lightbox');
            var lightboxVideo = document.getElementById('lightbox-video');
            
            // 暂停视频
            if (lightboxVideo) {
                lightboxVideo.pause();
                lightboxVideo.currentTime = 0;
            }
            
            lightbox.style.display = 'none';
        }
        
        // 复制文件链接
        function copyImageLink(e, filePath = null) {
            e.stopPropagation(); // 阻止事件冒泡
            
            let linkToCopy;
            if (filePath) {
                // 如果直接传入文件路径，使用该路径
                linkToCopy = window.location.origin + '/' + filePath;
            } else {
                // 否则从灯箱获取
                var lightboxImg = document.getElementById('lightbox-img');
                var lightboxVideo = document.getElementById('lightbox-video');
                
                if (lightboxImg.style.display === 'block') {
                    linkToCopy = lightboxImg.src;
                } else {
                    linkToCopy = lightboxVideo.src;
                }
            }
            
            navigator.clipboard.writeText(linkToCopy).then(function() {
                showMessage("复制成功！", "success");
            }, function() {
                showMessage("复制失败，请手动复制！", "error");
            });
        }
        
        // 显示消息函数
        function showMessage(text, type = "info") {
            const messageDiv = document.getElementById('message');
            if (messageDiv) {
                messageDiv.innerHTML = text;
                messageDiv.className = `message ${type}`;
                messageDiv.style.display = 'block';
                
                setTimeout(function() {
                    messageDiv.style.display = 'none';
                }, 2000);
            }
        }
        
        // 下载文件
        function downloadFile(e) {
            e.stopPropagation(); // 阻止事件冒泡，防止触发关闭灯箱
            var lightboxImg = document.getElementById('lightbox-img');
            var lightboxVideo = document.getElementById('lightbox-video');
            var fileSrc;
            
            // 确定当前显示的是图片还是视频
            if (lightboxImg.style.display === 'block') {
                fileSrc = lightboxImg.src;
            } else {
                fileSrc = lightboxVideo.src;
            }
            
            var link = document.createElement('a');
            link.href = fileSrc;
            link.download = fileSrc.split('/').pop();
            link.click();
        }
        
        // 打开文件预览
        function openFilePreview(filePath, isImage, isVideo) {
            if (isImage === '1' || isImage === true) {
                // 对于图片，使用灯箱预览
                openLightbox(filePath, true, false);
            } else if (isVideo === '1' || isVideo === true) {
                // 对于视频，使用灯箱预览
                openLightbox(filePath, false, true);
            } else {
                // 对于其他文件，直接下载或在新标签页打开
                window.open(filePath, '_blank');
            }
        }
    </script>


<div id="lightbox" class="lightbox" onclick="closeLightbox()">
  <span id="close">&times;</span>
  <img id="lightbox-img" class="lightbox-image">
  <video id="lightbox-video" class="lightbox-image" controls></video>
  <div id="lightbox-controls"> <!-- 控制按钮容器 -->
    <div class="button-container" style="flex-direction: row; gap: 0.5rem;">
        <button class="file-action-btn" onclick="copyImageLink(event)">复制链接</button>
        <button class="file-action-btn" onclick="downloadFile(event)">下载文件</button>
    </div>
  </div>
</div>
    <div class="file-container">
<?php
    // 获取当前路径参数
    $path = isset($_GET['path']) ? $_GET['path'] : '';
    $file_directory = '_file/' . $path;
    
    // 安全检查：防止目录遍历
    if (strpos($file_directory, '../') !== false || strpos($file_directory, './') === 0) {
        $file_directory = '_file/';
    }
    
    // 确保目录存在
    if (!file_exists($file_directory)) {
            $file_directory = '_file/';
        }
    
    // 获取当前目录下的所有文件和文件夹
    $items = glob($file_directory . '*');
    usort($items, function($a, $b) {
        // 先按类型排序（文件夹在前），再按修改时间排序
        $aIsDir = is_dir($a);
        $bIsDir = is_dir($b);
        
        if ($aIsDir && !$bIsDir) return -1;
        if (!$aIsDir && $bIsDir) return 1;
        
        return filemtime($b) - filemtime($a);
    });

    foreach ($items as $index => $item) {
        // 跳过文件夹，只显示文件
        if (is_dir($item)) {
            continue;
        }
        
        $itemName = basename($item);
        $itemPath = $item;
        $relativePath = substr($item, 6); // 移除 '_file/' 前缀
        
        // 文件
        $itemExt = strtolower(pathinfo($item, PATHINFO_EXTENSION));
        $itemSize = filesize($item);
        $isImage = in_array($itemExt, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg']);
        $isVideo = in_array($itemExt, ['mp4', 'webm', 'ogg', 'avi', 'mov', 'mkv']);
        $icon = strtoupper($itemExt);
        
        // 格式化文件大小
        if ($itemSize < 1024) {
            $formattedSize = $itemSize . ' B';
        } elseif ($itemSize < 1048576) {
            $formattedSize = round($itemSize / 1024, 2) . ' KB';
        } else {
            $formattedSize = round($itemSize / 1048576, 2) . ' MB';
        }
        
        $modTime = date("Y-m-d H:i:s", filemtime($item));
        
        echo '<div class="file-item" onclick="';
        echo 'openFilePreview(\'' . $itemPath . '\', \'' . $isImage . '\', \'' . $isVideo . '\')';
        echo '">';
        
        if ($isImage) {
            echo '<div class="file-thumbnail"><img src="' . $itemPath . '" alt="' . $itemName . '"></div>';
        } else if ($isVideo) {
            echo '<div class="file-thumbnail">';
            echo '<video src="' . $itemPath . '" autoplay muted loop playsinline style="width: 100%; height: 100%; object-fit: cover;"></video>';
            echo '</div>';
        } else {
            echo '<div class="file-thumbnail file-icon">' . $icon . '</div>';
        }
        
        echo '<div class="file-info">';
        echo '<div class="file-name">' . $itemName . '</div>';
        echo '<div class="file-meta">' . $formattedSize . ' | ' . $modTime . '</div>';
        echo '</div>';
        
        echo '<div class="file-actions">';
        echo '<button class="file-action-btn" onclick="event.stopPropagation(); copyImageLink(event, \'' . $itemPath . '\')">复制链接</button>';
        echo '<a href="' . $itemPath . '" download class="file-action-btn">下载</a>';
        echo '</div>';
        
        echo '</div>';
    }
    
    // 如果目录为空
    if (empty($items)) {
        echo '<div style="text-align: center; padding: 50px; color: var(--text-secondary);">';
        echo '<p>当前目录为空</p>';
        echo '</div>';
    }
?>
    </div>
</main>
</div>
</body>
</html>
