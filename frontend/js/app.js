const API_BASE_URL = 'http://microserivces_book:8000'; // API 网关的基础地址

// 通用错误处理函数
function handleError(response) {
    if (!response.ok) {
        throw Error(`Error: ${response.statusText}`);
    }
    return response.json();
}

// 通用 fetch API 请求函数
function fetchAPI(endpoint, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json' // 使用 JSON 数据传输
        }
    };

    if (data) {
        options.body = JSON.stringify(data);
    }

    return fetch(`${API_BASE_URL}${endpoint}`, options)
        .then(handleError);
}

// 用户登录表单提交处理
document.getElementById('login-form')?.addEventListener('submit', function (e) {
    e.preventDefault();
    const username = document.getElementById('login-username').value;
    const password = document.getElementById('login-password').value;

    const loginButton = document.querySelector('button[type="submit"]');
    loginButton.disabled = true; // 禁用按钮，防止重复提交

    fetchAPI('/users/login', 'POST', { username, password })
    .then(data => {
        loginButton.disabled = false;
        if (data.status === 'Login successful') {
            // 存储用户的登录状态、角色和 user_id
            sessionStorage.setItem('isLoggedIn', 'true');
            sessionStorage.setItem('role', data.role);
            sessionStorage.setItem('user_id', data.user_id); // 存储 user_id

            // 根据角色跳转到相应的页面
            window.location.href = data.role === 'admin' ? 'admin_dashboard.html' : 'dashboard.html';
        } else {
            document.getElementById('login-message').textContent = '登录失败，请检查用户名和密码。';
        }
    })
    .catch(error => {
        loginButton.disabled = false;
        console.error(error);
        document.getElementById('login-message').textContent = '接口问题，请检查服务。';
    });
});

// 检查用户是否已登录
function checkLoginStatus() {
    const isLoggedIn = sessionStorage.getItem('isLoggedIn');
    const role = sessionStorage.getItem('role');
    
    // 如果当前页面不是登录页面，并且未登录，才进行跳转
    if (window.location.pathname !== '/index.html' && (!isLoggedIn || isLoggedIn !== 'true')) {
        window.location.href = 'index.html';
    }
}
// 页面加载时检查登录状态
document.addEventListener('DOMContentLoaded', checkLoginStatus);

