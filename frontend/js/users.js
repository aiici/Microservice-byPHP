const API_BASE_URL = 'http://microserivces_book:8000';
let editingUserId = null;  // 用于标识当前是否处于编辑状态

// 加载用户列表
function loadUsers() {
    fetch(`${API_BASE_URL}/users/users`) // 获取所有用户
    .then(response => response.json())
    .then(users => {
        const userList = document.getElementById('user-list');
        userList.innerHTML = '';
        users.forEach(user => {
            const li = document.createElement('li');
            li.classList.add('list-group-item');
            li.innerHTML = `用户名: ${user.username} --- 角色: ${user.role} 
                <button class="btn btn-sm btn-secondary float-right" onclick="editUser(${user.id})">编辑</button>`;
            userList.appendChild(li);
        });
    })
    .catch(error => {
        console.error(error);
        alert('无法加载用户列表，请稍后再试。');
    });
}

// 编辑用户
function editUser(userId) {
    fetch(`${API_BASE_URL}/users/users/${userId}`) // 获取单个用户
    .then(response => response.json())
    .then(user => {
        document.getElementById('user-id').value = user.id;
        document.getElementById('user-username').value = user.username;
        document.getElementById('user-role').value = user.role;
        document.getElementById('delete-user').classList.remove('d-none');
        editingUserId = userId;
    });
}

// 保存用户（新增或编辑）
document.getElementById('add-edit-user-form')?.addEventListener('submit', function (e) {
    e.preventDefault();
    const username = document.getElementById('user-username').value;
    const password = document.getElementById('user-password').value;
    const role = document.getElementById('user-role').value;
    const userId = document.getElementById('user-id').value;

    const method = userId ? 'PUT' : 'POST'; // 如果有 ID 表示是编辑，否则是新增
    const url = userId ? `${API_BASE_URL}/users/users/${userId}` : `${API_BASE_URL}/users/register`; // 使用 /register 注册新用户

    const userData = { username, role };
    if (password) {
        userData.password = password; // 如果输入了密码，则传递
    }

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(userData)
    })
    .then(response => response.json())
    .then(data => {
        alert(data.status);
        loadUsers(); // 重新加载用户列表
        resetForm(); // 重置表单
    })
    .catch(error => {
        console.error(error);
        alert('保存用户时发生错误，请检查服务。');
    });
});

// 删除用户
document.getElementById('delete-user')?.addEventListener('click', function () {
    const userId = document.getElementById('user-id').value;
    if (confirm('确定要删除该用户吗？')) {
        fetch(`${API_BASE_URL}/users/users/${userId}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            alert(data.status);
            loadUsers(); // 重新加载用户列表
            resetForm(); // 重置表单
        })
        .catch(error => {
            console.error(error);
            alert('删除用户时发生错误，请检查服务。');
        });
    }
});

// 重置表单
function resetForm() {
    document.getElementById('user-id').value = '';
    document.getElementById('user-username').value = '';
    document.getElementById('user-password').value = '';
    document.getElementById('user-role').value = 'user';
    document.getElementById('delete-user').classList.add('d-none');
}
// 页面加载时调用加载用户列表
document.addEventListener('DOMContentLoaded', loadUsers);

