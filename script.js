/* 网络笔记本增强版! Web Notepad web-notepad-enhanced */
/* https://github.com/jocksliu/web-notepad-enhanced  */
/* 本项目源于原作者pereorga 的项目Minimalist Web Notepad上二次开发而来  本项目作者：jocksliu */
/* 原仓库地址 https://github.com/pereorga/minimalist-web-notepad */

// 使用ES6模块语法的结构
const NotepadApp = {
    // 全局变量
    textarea: null,
    printable: null,
    content: '',
    saveTimer: null,
    noteList: null,
    messageBox: null,
    
    // 初始化应用
    init() {
        // 初始化DOM元素
        this.textarea = document.getElementById('content');
        this.printable = document.getElementById('printable');
        this.noteList = document.getElementById('noteList');
        this.messageBox = document.getElementById('message-box');
        
        // 如果没有找到文本区域，退出初始化（可能在其他页面）
        if (!this.textarea) return;
        
        // 初始化内容
        this.content = this.textarea.value;
        
        // 初始化事件监听
        this.setupEventListeners();
        
        // 设置定时器
        this.setupTimers();
        
        // 初始化打印区域
        if (this.printable) {
            this.printable.textContent = this.content;
        }
        
        // 设置自动聚焦
        this.textarea.focus();
        
        // 启动自动加载功能
        this.loadContent();
    },
    
    // 设置事件监听
    setupEventListeners() {
        // 添加输入事件监听
        this.textarea.addEventListener('input', () => this.debouncedSave());
    },
    
    // 设置定时器
    setupTimers() {
        // 设置定时器，每5秒自动刷新笔记列表
        this.refreshTimer = setInterval(() => this.refreshNoteList(), 5000);
    },
    
    // 暂停自动刷新
    pauseRefresh() {
        if (this.refreshTimer) {
            clearInterval(this.refreshTimer);
            this.refreshTimer = null;
        }
    },
    
    // 恢复自动刷新
    resumeRefresh() {
        if (!this.refreshTimer) {
            this.refreshTimer = setInterval(() => this.refreshNoteList(), 5000);
        }
    },
    
    // 显示保存提示
    showSaveMessage() {
        if (this.messageBox) {
            this.messageBox.textContent = '已保存';
            this.messageBox.style.display = 'block';
            this.messageBox.style.opacity = '1';
            
            // 使用requestAnimationFrame优化动画性能
            requestAnimationFrame(() => {
                setTimeout(() => {
                    this.messageBox.style.opacity = '0';
                    setTimeout(() => {
                        this.messageBox.style.display = 'none';
                    }, 300);
                }, 500); // 0.5秒显示时间
            });
        }
    },
    
    // 保存内容到服务器
    saveContent() {
        if (!this.textarea || this.content === this.textarea.value) return;
        
        const temp = this.textarea.value;
        const request = new XMLHttpRequest();
        
        request.open('POST', window.location.href, true);
        request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
        
        request.onload = () => {
            if (request.readyState === 4) {
                this.content = temp;
                this.showSaveMessage();
                this.refreshNoteList();
            }
        };
        
        request.onerror = () => {
            // 忽略错误，保持静默失败
        };
        
        request.send('text=' + encodeURIComponent(temp));
        
        // 更新打印区域
        if (this.printable) {
            this.printable.textContent = temp;
        }
    },
    
    // 防抖保存功能
    debouncedSave() {
        clearTimeout(this.saveTimer);
        this.saveTimer = setTimeout(() => this.saveContent(), 1000); // 1秒防抖延迟
    },
    
    // 自动加载功能 - 从服务器获取最新内容
    loadContent() {
        // 只有在本地内容与当前显示内容一致时才加载，避免覆盖用户正在输入的内容
        if (this.content === this.textarea.value) {
            const request = new XMLHttpRequest();
            request.open('GET', window.location.href + '?raw', true);
            
            request.onload = () => {
                if (request.readyState === 4 && request.status === 200) {
                    const serverContent = request.responseText;
                    if (serverContent !== this.content) {
                        this.content = serverContent;
                        this.textarea.value = serverContent;
                        if (this.printable) {
                            this.printable.textContent = serverContent;
                        }
                    }
                    setTimeout(() => this.loadContent(), 5000); // 5秒后再次加载
                } else {
                    setTimeout(() => this.loadContent(), 5000); // 加载失败，5秒后重试
                }
            };
            
            request.onerror = () => {
                setTimeout(() => this.loadContent(), 5000); // 网络错误，5秒后重试
            };
            
            request.send();
        } else {
            setTimeout(() => this.loadContent(), 1000); // 本地内容有修改，1秒后再次检查
        }
    },
    
    // 刷新笔记列表
    refreshNoteList() {
        const noteId = window.location.pathname.split('/')[2] || '';
        
        // 构建正确的URL，将note参数作为查询参数传递
        const request = new XMLHttpRequest();
        request.open('GET', window.location.origin + '/?note=' + encodeURIComponent(noteId) + '&list=1', true);
        
        request.onload = () => {
            if (request.readyState === 4 && request.status === 200 && this.noteList) {
                this.noteList.innerHTML = request.responseText;
            }
        };
        
        request.onerror = () => {
            // 忽略错误，保持静默失败
        };
        
        request.send();
    }
};

// DOM加载完成后初始化应用
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => NotepadApp.init());
} else {
    NotepadApp.init();
}
