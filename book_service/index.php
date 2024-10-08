<?php
class BookService {
    private $db;

    public function __construct() {
        // 初始化数据库连接
        $this->db = new mysqli(
            getenv('MYSQL_HOST'), 
            getenv('MYSQL_USER'), 
            getenv('MYSQL_PASSWORD'), 
            getenv('MYSQL_DATABASE')
        );

        // 检查数据库连接是否成功
        if ($this->db->connect_error) {
            http_response_code(500);
            echo json_encode(['status' => 'Database connection failed', 'error' => $this->db->connect_error]);
            exit();
        }
    }

    // 添加书籍方法
    public function addBook($data) {
        $stmt = $this->db->prepare("INSERT INTO books (title, author) VALUES (?, ?)");
        $stmt->bind_param('ss', $data['title'], $data['author']);

        if ($stmt->execute()) {
            http_response_code(201); // 201 Created
            $stmt->close();
            return json_encode(['status' => 'Book added']);
        } else {
            http_response_code(500);
            return json_encode(['status' => 'Error adding book', 'error' => $this->db->error]);
        }
    }

    // 获取所有书籍方法
    public function getBooks() {
        $result = $this->db->query("SELECT id, title, author FROM books");

        if (!$result) {
            http_response_code(500);
            return json_encode(['status' => 'Error retrieving books', 'error' => $this->db->error]);
        }

        $books = [];
        while ($book = $result->fetch_assoc()) {
            $books[] = $book;
        }
        return json_encode($books);
    }

    // 获取单本书籍方法
    public function getBook($id) {
        $stmt = $this->db->prepare("SELECT id, title, author FROM books WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $book = $result->fetch_assoc();

        if ($book) {
            return json_encode($book);
        } else {
            http_response_code(404); // 404 Not Found
            return json_encode(['status' => 'Book not found']);
        }
    }

    // 更新书籍方法
    public function updateBook($id, $data) {
        $stmt = $this->db->prepare("UPDATE books SET title = ?, author = ? WHERE id = ?");
        $stmt->bind_param('ssi', $data['title'], $data['author'], $id);

        if ($stmt->execute()) {
            http_response_code(200);
            $stmt->close();
            return json_encode(['status' => 'Book updated']);
        } else {
            http_response_code(500);
            return json_encode(['status' => 'Error updating book', 'error' => $this->db->error]);
        }
    }

    // 删除书籍方法
    public function deleteBook($id) {
        $stmt = $this->db->prepare("DELETE FROM books WHERE id = ?");
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            http_response_code(200);
            $stmt->close();
            return json_encode(['status' => 'Book deleted']);
        } else {
            http_response_code(500);
            return json_encode(['status' => 'Error deleting book', 'error' => $this->db->error]);
        }
    }
    public function getAvailableBooks() {
        $query = "
            SELECT books.id, books.title, books.author, 
                   COALESCE(MAX(borrows.status), 'available') AS status
            FROM books
            LEFT JOIN borrows ON books.id = borrows.book_id
            GROUP BY books.id
        ";
        $result = $this->db->query($query);
    
        if (!$result) {
            http_response_code(500);
            return json_encode(['status' => 'Error retrieving available books', 'error' => $this->db->error]);
        }
    
        $books = [];
        while ($book = $result->fetch_assoc()) {
            $books[] = $book;
        }
    
        return json_encode($books);
    }

}

// API 处理逻辑
header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

// 使用 REQUEST_URI 解析路径
$path = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));

// 创建 BookService 实例
$bookService = new BookService();

// 获取输入数据
$input = json_decode(file_get_contents('php://input'), true);

// 处理不同的 API 请求
if ($method === 'POST' && $path[0] === 'books') {
    echo $bookService->addBook($input); // 添加书籍
} elseif ($method === 'GET' && $path[0] === 'books') {
    if (isset($path[1])) {
        echo $bookService->getBook($path[1]); // 获取单本书籍
    } else {
        echo $bookService->getBooks(); // 获取所有书籍
    }
} elseif ($method === 'PUT' && $path[0] === 'books' && isset($path[1])) {
    echo $bookService->updateBook($path[1], $input); // 更新书籍
} elseif ($method === 'DELETE' && $path[0] === 'books' && isset($path[1])) {
    echo $bookService->deleteBook($path[1]); // 删除书籍
} elseif ($method === 'GET' && $path[0] === 'available') {
    echo $bookService->getAvailableBooks(); // 获取所有可借阅图书
}else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid API request']);
}

