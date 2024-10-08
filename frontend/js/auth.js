// 检查用户是否已登录
function checkLoginStatus() {
    const isLoggedIn = sessionStorage.getItem('isLoggedIn');
    if (!isLoggedIn || isLoggedIn !== 'true') {
        // 如果未登录，重定向到登录页面
        window.location.href = 'index.html';
    }
}

// 检查用户是否为管理员
function checkAdminRole() {
    const role = sessionStorage.getItem('role');
    if (role !== 'admin') {
        // 如果不是管理员，重定向到普通用户的dashboard页面
        alert('您没有权限访问此页面');
        window.location.href = 'dashboard.html';
    }
}

// 页面加载时检查登录状态并在需要时检查用户角色
document.addEventListener('DOMContentLoaded', function() {
    checkLoginStatus();
    
    // 只有 admin_dashboard.html、books.html 、borrow_status.html和users.html 需要检查管理员权限
    const restrictedPages = ['admin_dashboard.html', 'books.html', 'users.html', 'borrow_status.html'];
    const currentPage = window.location.pathname.split('/').pop();

    if (restrictedPages.includes(currentPage)) {
        checkAdminRole();
    }
});
// 退出登录的函数
function logout() {
    // 清除 sessionStorage 中保存的登录状态和用户信息
    sessionStorage.removeItem('isLoggedIn');
    sessionStorage.removeItem('role');
    sessionStorage.removeItem('user_id');

    // 重定向到登录页面
    window.location.href = 'index.html'; 
}

// 在退出登录链接上绑定点击事件
document.getElementById('logout-link')?.addEventListener('click', function () {
    logout();
});
