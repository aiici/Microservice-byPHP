const API_BASE_URL = 'http://microserivces_book:8000';

// 加载借阅情况
function loadBorrowStatus() {
    fetch(`${API_BASE_URL}/borrow/status`)
    .then(response => response.json())
    .then(borrows => {
        const borrowList = document.getElementById('borrow-status-list');
        borrowList.innerHTML = '';
        borrows.forEach(borrow => {
            const li = document.createElement('li');
            li.classList.add('list-group-item');
            li.textContent = `用户: ${borrow.username} 借阅了图书: ${borrow.title} 于 ${borrow.borrow_date} 归还日期: ${borrow.return_date || '未归还'} (状态: ${borrow.status})`;
            borrowList.appendChild(li);
        });
    })
    .catch(error => {
        console.error(error);
        alert('无法加载借阅情况，请稍后再试。');
    });
}
// 页面加载时调用加载借阅情况
document.addEventListener('DOMContentLoaded', loadBorrowStatus);

