const API_BASE_URL = 'http://microserivces_book:8000'; // API 网关的基础地址
// 获取当前登录用户的 user_id
const user_id = sessionStorage.getItem('user_id');
// 加载已借阅的图书列表
function loadBorrowedBooks() {
    fetch(`${API_BASE_URL}/borrow/isstatus/${user_id}`)
    .then(response => response.json())
    .then(borrows => {
        const bookList = document.getElementById('borrowed-books');
        bookList.innerHTML = '';
        borrows.forEach(borrow => {
          const li = document.createElement('li');
          li.classList.add('list-group-item');
          li.textContent = `书名: ${borrow.title} (ID: ${borrow.id}) --- 作者: ${borrow.author} --- 借阅日期: ${borrow.borrow_date}`;
          li.dataset.bookId = borrow.id; // 存储图书 ID
          bookList.appendChild(li);
        });
    })
    .catch(error => {
        console.error(error);
        alert('无法加载已借阅图书列表，请稍后再试。');
    });
}

// 归还书籍表单提交处理
document.getElementById('return-form')?.addEventListener('submit', function (e) {
    e.preventDefault();

    const book_id = document.getElementById('return-book-id').value;

    if (!user_id) {
        alert('请先登录。');
        window.location.href = 'index.html';
        return;
    }

    fetch(`${API_BASE_URL}/borrow/return`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ user_id, book_id })
    })
    .then(response => response.json())
    .then(data => {
        alert(data.status);
        loadBorrowedBooks(); // 重新加载已借阅图书列表
    })
    .catch(error => {
        console.error(error);
        alert('归还接口问题，请检查服务。');
    });
});
// 页面加载时调用加载已借阅图书列表
document.addEventListener('DOMContentLoaded', loadBorrowedBooks);

