const API_BASE_URL = 'http://microserivces_book:8000'; // API 网关的基础地址

// 加载图书列表
function loadBooks() {
    fetch(`${API_BASE_URL}/books/books`)
    .then(response => response.json())
    .then(books => {
        const bookList = document.getElementById('book-list');
        bookList.innerHTML = '';
        books.forEach(book => {
            const li = document.createElement('li');
            li.classList.add('list-group-item');
            li.innerHTML = `ID: ${book.id}  书名：${book.title} --- 作者: ${book.author} 
                <button class="btn btn-sm btn-secondary float-right" onclick="editBook(${book.id})">编辑</button>`;
            bookList.appendChild(li);
        });
    })
    .catch(error => {
        console.error(error);
        alert('无法加载图书列表，请稍后再试。');
    });
}

// 编辑图书
function editBook(bookId) {
    fetch(`${API_BASE_URL}/books/books/${bookId}`)
    .then(response => response.json())
    .then(book => {
        document.getElementById('book-id').value = book.id;
        document.getElementById('book-title').value = book.title;
        document.getElementById('book-author').value = book.author;
        document.getElementById('delete-book').classList.remove('d-none');
    })
    .catch(error => {
        console.error(error);
        alert('无法加载图书信息，请稍后再试。');
    });
}

// 保存图书（新增或编辑）
document.getElementById('add-edit-book-form')?.addEventListener('submit', function (e) {
    e.preventDefault();
    const title = document.getElementById('book-title').value;
    const author = document.getElementById('book-author').value;
    const bookId = document.getElementById('book-id').value;

    const method = bookId ? 'PUT' : 'POST'; // 如果有 ID 表示是编辑，否则是新增
    const url = bookId ? `${API_BASE_URL}/books/books/${bookId}` : `${API_BASE_URL}/books/books`;

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ title, author })
    })
    .then(response => response.json())
    .then(data => {
        alert(data.status);
        loadBooks(); // 重新加载图书列表
        resetForm(); // 重置表单
    })
    .catch(error => {
        console.error(error);
        alert('保存图书时发生错误，请检查服务。');
    });
});

// 删除图书
document.getElementById('delete-book')?.addEventListener('click', function () {
    const bookId = document.getElementById('book-id').value;
    if (confirm('确定要删除该图书吗？')) {
        fetch(`${API_BASE_URL}/books/books/${bookId}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            alert(data.status);
            loadBooks(); // 重新加载图书列表
            resetForm(); // 重置表单
        })
        .catch(error => {
            console.error(error);
            alert('删除图书时发生错误，请检查服务。');
        });
    }
});

// 重置表单
function resetForm() {
    document.getElementById('book-id').value = '';
    document.getElementById('book-title').value = '';
    document.getElementById('book-author').value = '';
    document.getElementById('delete-book').classList.add('d-none');
}

// 页面加载时调用加载图书列表
document.addEventListener('DOMContentLoaded', loadBooks);

