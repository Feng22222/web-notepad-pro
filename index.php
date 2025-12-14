<?php
/* 网络笔记本增强版! Web Notepad web-notepad-enhanced */
/* https://github.com/jocksliu/web-notepad-enhanced  */
/* 本项目源于原作者pereorga 的项目Minimalist Web Notepad上二次开发而来  本项目作者：jocksliu */
/* 原仓库地址 https://github.com/pereorga/minimalist-web-notepad */

/* 在这个版本中，密码使用了哈希值，增加了安全性，建议搜索在线php哈希生成工具直接生成密码的哈希值，然后填入哈希内容 */
/* 推荐的在线生成哈希值网站：https://uutool.cn/php-password/  或者 https://toolkk.com/tools/php-password-hash 或者其他自行百度谷歌 */
/* 将这个密码改成自己的登录密码的哈希值，当前哈希值对应的密码是123456


-----------------------------------------------------------------------------------*/
// 需要将域名改成自己的域名
$base_url = 'https://pad.wld.ink';

// 以下被注释的代码是密码功能，如果需要网站密码，把注释删掉，然后改掉哈希值即可
// $hashed_password = '$2y$10$XKj.XSU08WALtpqrBwOUouiVv/hJsDAT8uWOhn4KalJ1HfW579JqO';
// 
// $session_time = 604800; // 3600秒
// 
// if (isset($_POST['remember_me'])) {
//     $session_time = 604800; // 604800秒
// }
// 
// ini_set('session.gc_maxlifetime', $session_time);
// session_set_cookie_params($session_time);
// 
// session_start();
// 
// if (isset($_POST['password'])) {
//     if (password_verify($_POST['password'], $hashed_password)) {
//         $_SESSION['authenticated'] = true;
//         header('Location: ' . $_SERVER['PHP_SELF']);
//         exit;
//     } else {
//         $error = '密码错误!';
//     }
// }
// 
// if (!isset($_SESSION['authenticated'])) {
//     include 'login.php'; 
//     exit; 
// }
// 
// if (isset($_GET['logout'])) {
//     session_unset();
//     session_destroy();
//     if (isset($_COOKIE[session_name()])) {
//         setcookie(session_name(), '', time() - 3600, '/');
//     }
//     header('Location: $base_url/');
//     exit;
// }
//-----------------------------------------------------------------------------------------

$save_path = '_tmp';
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

function getNoteList($dir) {
    $files = array_diff(scandir($dir), array('.', '..'));
    $filteredFiles = [];

    foreach ($files as $file) {
        if (!in_array($file, array('.htaccess'))) {
            $filteredFiles[] = $file;
        }
    }

    usort($filteredFiles, function($a, $b) use ($dir) {
        return filemtime($dir . '/' . $b) - filemtime($dir . '/' . $a);
    });

    return $filteredFiles;
}

// 优先处理笔记列表请求，直接返回HTML片段，不进行重定向
if (isset($_GET['list'])) {
    $noteList = getNoteList($save_path);
    $currentNote = isset($_GET['note']) ? $_GET['note'] : '';
    
    foreach ($noteList as $noteId) {
            $activeClass = $noteId == $currentNote ? ' active' : '';
            $isReadOnly = !is_writable($save_path . '/' . $noteId);
            $readOnlyClass = $isReadOnly ? ' readonly' : '';
            $readOnlyTitle = $isReadOnly ? 'title="这是一个只读文件"' : '';
            $noteContent = file_get_contents($save_path . '/' . $noteId);
            // 安全地获取笔记标题，处理特殊字符
            $firstLine = strtok($noteContent, PHP_EOL);
            $noteTitle = substr($firstLine !== false ? $firstLine : '', 0, 12) ?: $noteId;
            echo "<li class='note-item" . $activeClass . $readOnlyClass . "' " . $readOnlyTitle . " onclick='window.location.href=window.location.origin+\"/\"+\"" . htmlspecialchars($noteId, ENT_QUOTES, "UTF-8") . "\";'><div class='note-title-container'>" . htmlspecialchars($noteTitle, ENT_QUOTES, "UTF-8") . "</div><span class='note-id' onclick='event.stopPropagation(); startEditNoteId(this);'>" . htmlspecialchars($noteId, ENT_QUOTES, "UTF-8") . "</span><button class='delete-note-btn' onclick='event.stopPropagation(); deleteNote(\"" . htmlspecialchars($noteId, ENT_QUOTES, "UTF-8") . "\");'>删除</button></li>";
        }
    // 随机新建笔记按钮 - 使用3位随机数
    echo '<li class="note-item new-note"><div class="note-title-container"><a href="' . $base_url . '/' . substr(str_shuffle('123457890'), -3) . '">随机建一个</a></div></li>';
    die;
}

// 非列表请求，继续正常处理
if (!isset($_GET['note']) || !preg_match('/^[a-zA-Z0-9_-]+$/', $_GET['note']) || strlen($_GET['note']) > 64) {
    // 使用3位随机数
    header("Location: $base_url/" . substr(str_shuffle('123457890'), -3));
    die;
}

$path = $save_path . '/' . $_GET['note'];

if (isset($_POST['text'])) {
    // 检查是否是重命名请求
    if (isset($_POST['rename_from']) && isset($_POST['rename_to'])) {
        $oldNoteId = $_POST['rename_from'];
        $newNoteId = $_POST['rename_to'];
        $oldPath = $save_path . '/' . $oldNoteId;
        $newPath = $save_path . '/' . $newNoteId;
        
        // 验证ID格式
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $newNoteId) || strlen($newNoteId) > 64) {
            http_response_code(400);
            echo "无效的ID格式";
            die;
        }
        
        // 检查新ID是否已存在
        if (file_exists($newPath)) {
            http_response_code(400);
            echo "ID已存在";
            die;
        }
        
        // 检查旧文件是否存在
        if (!file_exists($oldPath)) {
            http_response_code(404);
            echo "文件不存在";
            die;
        }
        
        // 执行重命名
        if (rename($oldPath, $newPath)) {
            // 如果当前正在编辑的笔记被重命名，更新内容
            if (isset($_POST['text'])) {
                file_put_contents($newPath, $_POST['text']);
            }
            echo "重命名成功";
        } else {
            http_response_code(500);
            echo "重命名失败";
        }
        die;
    }
    
    // 常规保存请求
    file_put_contents($path, $_POST['text']);
    if (!strlen($_POST['text'])) {
        unlink($path);
    }
    die;
}

if (isset($_GET['raw']) || strpos($_SERVER['HTTP_USER_AGENT'], 'curl') === 0 || strpos($_SERVER['HTTP_USER_AGENT'], 'Wget') === 0) {
    if (is_file($path)) {
        header('Content-type: text/plain');
        print file_get_contents($path);
    } else {
        header('HTTP/1.0 404 Not Found');
    }
    die;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>记事本-<?php print $_GET['note']; ?></title>
    <link rel="icon" href="<?php print $base_url; ?>/favicon.ico" sizes="any">
    <link rel="icon" href="<?php print $base_url; ?>/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="<?php print $base_url; ?>/styles.css">
    <script>
        function openNote(id) {
            console.log('openNote called with id:', id);
            window.location.href = window.location.origin + '/' + id;
        }
        
        // 删除笔记函数
        function deleteNote(noteId) {
            if (confirm('确定要删除这条笔记吗？')) {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'delete.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        // 删除成功，刷新笔记列表
                        location.reload();
                    } else {
                        alert('删除失败：' + xhr.responseText);
                    }
                };
                
                xhr.onerror = function() {
                    alert('删除失败，请重试。');
                };
                
                xhr.send('type=note&path=' + encodeURIComponent(noteId));
            }
        }
        
        // 笔记ID编辑功能
        let editTimeout = null;
        let currentEditingNote = null;
        let originalNoteId = null;
        
        // 点击笔记ID开始编辑
        function startEditNoteId(element) {
            // 暂停自动刷新
            if (typeof NotepadApp !== 'undefined' && NotepadApp.pauseRefresh) {
                NotepadApp.pauseRefresh();
            }
            
            // 如果已经有正在编辑的笔记，先保存
            if (currentEditingNote && currentEditingNote !== element) {
                saveNoteId(currentEditingNote, originalNoteId);
            }
            
            currentEditingNote = element;
            originalNoteId = element.textContent;
            
            const input = document.createElement('input');
            input.type = 'text';
            input.value = originalNoteId;
            input.className = 'note-id-edit';
            input.setAttribute('data-original-id', originalNoteId);
            
            // 替换文本为输入框
            element.parentNode.replaceChild(input, element);
            
            // 聚焦并全选文本
            input.focus();
            input.select();
            
            // 添加事件监听
            input.addEventListener('blur', function() {
                saveNoteId(this, originalNoteId);
            });
            
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    saveNoteId(this, originalNoteId);
                } else if (e.key === 'Escape') {
                    cancelEditNoteId(this);
                }
            });
            
            // 阻止点击事件冒泡，防止触发父元素的onclick事件
            input.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
        
        // 取消编辑，恢复原始ID
        function cancelEditNoteId(inputElement) {
            const noteIdElement = document.createElement('span');
            noteIdElement.className = 'note-id';
            noteIdElement.textContent = originalNoteId;
            noteIdElement.onclick = function() {
                startEditNoteId(this);
            };
            
            inputElement.parentNode.replaceChild(noteIdElement, inputElement);
            currentEditingNote = null;
            originalNoteId = null;
            
            // 恢复自动刷新
            if (typeof NotepadApp !== 'undefined' && NotepadApp.resumeRefresh) {
                NotepadApp.resumeRefresh();
            }
        }
        
        // 保存修改后的笔记ID（直接生效）
        function saveNoteId(inputElement, oldNoteId) {
            const newNoteId = inputElement.value.trim();
            
            // 验证ID格式
            if (!newNoteId || !/^[a-zA-Z0-9_-]+$/.test(newNoteId) || newNoteId.length > 64) {
                alert('无效的ID格式，只能包含字母、数字、下划线和连字符，长度不超过64个字符');
                cancelEditNoteId(inputElement);
                return;
            }
            
            // 如果ID没有变化，直接恢复
            if (newNoteId === oldNoteId) {
                cancelEditNoteId(inputElement);
                return;
            }
            
            // 创建新的显示元素，显示正在保存
            const savingElement = document.createElement('span');
            savingElement.className = 'note-id note-id-saving';
            savingElement.textContent = newNoteId;
            
            // 替换输入框
            inputElement.parentNode.replaceChild(savingElement, inputElement);
            
            // 直接发送请求修改ID（无延迟）
            const xhr = new XMLHttpRequest();
            xhr.open('POST', window.location.href, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onload = function() {
                // 恢复自动刷新
                if (typeof NotepadApp !== 'undefined' && NotepadApp.resumeRefresh) {
                    NotepadApp.resumeRefresh();
                }
                
                if (xhr.status === 200) {
                    // 修改成功，更新显示
                    const savedElement = document.createElement('span');
                    savedElement.className = 'note-id note-id-saved';
                    savedElement.textContent = newNoteId;
                    savedElement.onclick = function() {
                        startEditNoteId(this);
                    };
                    
                    savingElement.parentNode.replaceChild(savedElement, savingElement);
                    
                    // 1秒后恢复正常样式
                    setTimeout(function() {
                        savedElement.className = 'note-id';
                    }, 1000);
                    
                    // 如果是当前正在查看的笔记，重定向到新地址
                    const currentUrlNoteId = window.location.pathname.split('/').pop();
                    if (currentUrlNoteId === oldNoteId) {
                        setTimeout(function() {
                            window.location.href = window.location.origin + '/' + newNoteId;
                        }, 500);
                    }
                } else {
                    // 修改失败，恢复原始ID
                    alert('修改失败：' + xhr.responseText);
                    const errorElement = document.createElement('span');
                    errorElement.className = 'note-id';
                    errorElement.textContent = oldNoteId;
                    errorElement.onclick = function() {
                        startEditNoteId(this);
                    };
                    
                    savingElement.parentNode.replaceChild(errorElement, savingElement);
                }
                
                currentEditingNote = null;
                originalNoteId = null;
            };
            
            xhr.onerror = function() {
                // 恢复自动刷新
                if (typeof NotepadApp !== 'undefined' && NotepadApp.resumeRefresh) {
                    NotepadApp.resumeRefresh();
                }
                
                alert('网络错误，请重试。');
                cancelEditNoteId(inputElement);
            };
            
            xhr.send('text=' + encodeURIComponent(document.getElementById('content').value) + '&rename_from=' + encodeURIComponent(oldNoteId) + '&rename_to=' + encodeURIComponent(newNoteId));
        }
    </script>
</head>
<body>
    <div class="container" id="app">
        <div class="sidebar" id="my-menu">
            <nav class="breadcrumb">
                <a href="/" class="breadcrumb-item">笔记</a>
                <a href="file.php" class="breadcrumb-item">网盘</a>
            </nav>

            <ul id="noteList" class="note-list">
                <?php
                $noteList = getNoteList($save_path);
                foreach ($noteList as $noteId) {
                    $activeClass = $noteId == $_GET['note'] ? ' active' : '';
                    $isReadOnly = !is_writable($save_path . '/' . $noteId);
                    $readOnlyClass = $isReadOnly ? ' readonly' : '';
                    $readOnlyTitle = $isReadOnly ? 'title="这是一个只读文件"' : '';
                    $noteContent = file_get_contents($save_path . '/' . $noteId);
                    // 安全地获取笔记标题，处理特殊字符
                    $noteTitle = $noteId;
                    if (!empty($noteContent)) {
                        // 分割内容为行
                        $lines = explode(PHP_EOL, $noteContent);
                        // 查找第一个非空行
                        foreach ($lines as $line) {
                            $trimmedLine = trim($line);
                            if (!empty($trimmedLine)) {
                                // 使用第一个非空行的前12个字符作为标题
                                $noteTitle = substr($trimmedLine, 0, 12);
                                break;
                            }
                        }
                    }
                    echo "<li class='note-item" . $activeClass . $readOnlyClass . "' " . $readOnlyTitle . " onclick='window.location.href=window.location.origin+\"/\"+\"" . htmlspecialchars($noteId, ENT_QUOTES, "UTF-8") . "\";'><div class='note-title-container'>" . htmlspecialchars($noteTitle, ENT_QUOTES, "UTF-8") . "</div><span class='note-id' onclick='event.stopPropagation(); startEditNoteId(this);'>" . htmlspecialchars($noteId, ENT_QUOTES, "UTF-8") . "</span><button class='delete-note-btn' onclick='event.stopPropagation(); deleteNote(\"" . htmlspecialchars($noteId, ENT_QUOTES, "UTF-8") . "\");'>删除</button></li>";
                }
                
                // 随机新建笔记按钮 - 使用3位随机数
                echo '<li class="note-item new-note"><div class="note-title-container"><a href="' . $base_url . '/' . substr(str_shuffle('123457890'), -3) . '">随机建一个</a></div></li>';
                ?>
            </ul>
        </div>
        
        <div id="message-box" class="message-box"></div>
        
        <main class="content">
            <textarea id="content" class="note-textarea"><?php
                if (is_file($path)) {
                    print htmlspecialchars(file_get_contents($path), ENT_QUOTES, 'UTF-8');
                }
            ?></textarea>
        </main>
    </div>
    <pre id="printable"></pre>
    <script src="<?php print $base_url; ?>/script.js"></script>
</body>
</html>