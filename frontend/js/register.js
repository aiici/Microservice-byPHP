const API_BASE_URL = 'http://microserivces_book:8000'; // API 网关的基础地址

// 注册表单提交处理
document.getElementById('register-form').addEventListener('submit', function (e) {
    e.preventDefault();
    const username = document.getElementById('register-username').value;
    const password = document.getElementById('register-password').value;
    const confirmPassword = document.getElementById('confirm-password').value;

    if (password !== confirmPassword) {
        document.getElementById('register-message').textContent = '两次输入的密码不一致';
        return;
    }

    fetch(`${API_BASE_URL}/users/register`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ username, password })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'User registered') {
            alert('注册成功，跳转到登录页面');
            window.location.href = 'index.html'; // 注册成功后跳转到登录页面
        } else {
            document.getElementById('register-message').textContent = data.error || '注册失败，请稍后重试';
        }
    })
    .catch(error => {
        console.error(error);
        document.getElementById('register-message').textContent = '注册接口出现问题，请稍后重试';
    });
});

