const API_BASE_URL = 'http://microserivces_book:8000'; // API 网关的基础地址
const user_id = sessionStorage.getItem('user_id'); // 从 sessionStorage 中获取当前登录的用户 ID

// 加载可借阅的图书列表
function loadAvailableBooks() {
    fetch(`${API_BASE_URL}/books/available`) // 假设此接口返回所有可借阅的图书
    .then(response => response.json())
    .then(books => {
        const bookList = document.getElementById('available-books');
        bookList.innerHTML = '';
        books.forEach(book => {
            const li = document.createElement('li');
            li.classList.add('list-group-item');
            li.textContent = `书名: ${book.title} --- 作者: ${book.author} (ID: ${book.id})`;
            bookList.appendChild(li);
        });
    })
    .catch(error => {
        console.error(error);
        alert('无法加载可借阅图书列表，请稍后再试。');
    });
}

// 借阅图书表单提交处理
document.getElementById('borrow-form')?.addEventListener('submit', function (e) {
    e.preventDefault();

    const book_id = document.getElementById('borrow-book-id').value;

    if (!user_id) {
        alert('请先登录。');
        window.location.href = 'index.html';
        return;
    }

    fetch(`${API_BASE_URL}/borrow/borrow`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ user_id, book_id })
    })
    .then(response => response.json())
    .then(data => {
        alert(data.status);
        loadAvailableBooks(); // 重新加载可借阅图书列表
        loadBorrowStatus(); // 重新加载借阅情况
    })
    .catch(error => {
        console.error(error);
        alert('借阅接口问题，请检查服务。');
    });
});

// 加载当前用户的借阅情况
function loadBorrowStatus() {
    fetch(`${API_BASE_URL}/borrow/isstatus/${user_id}`)
    .then(response => response.json())
    .then(borrows => {
        const borrowList = document.getElementById('borrow-status-list');
        borrowList.innerHTML = '';
        borrows.forEach(borrow => {
            const li = document.createElement('li');
            li.classList.add('list-group-item');
            li.textContent = `ID: ${borrow.id} 书名: ${borrow.title} 作者: ${borrow.author} 借阅时间: ${borrow.borrow_date}`;
            borrowList.appendChild(li);
        });
    })
    .catch(error => {
        console.error(error);
        alert('无法加载借阅情况，请稍后再试。');
    });
}

// 页面加载时调用
document.addEventListener('DOMContentLoaded', function () {
    loadAvailableBooks();
    loadBorrowStatus();
});

