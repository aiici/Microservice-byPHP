<?php
class BorrowService {
    private $db;

    public function __construct() {
        $this->db = new mysqli(
            getenv('MYSQL_HOST'),
            getenv('MYSQL_USER'),
            getenv('MYSQL_PASSWORD'),
            getenv('MYSQL_DATABASE')
        );

        if ($this->db->connect_error) {
            http_response_code(500);
            echo json_encode(['status' => 'Database connection failed', 'error' => $this->db->connect_error]);
            exit();
        }
    }

    // 借书操作
    public function borrowBook($data) {
        // 检查该书是否已经被借阅
        $checkQuery = $this->db->prepare("SELECT * FROM borrows WHERE book_id = ? AND status = 'borrowed'");
        $checkQuery->bind_param('i', $data['book_id']);
        $checkQuery->execute();
        $result = $checkQuery->get_result();

        if ($result->num_rows > 0) {
            http_response_code(400);
            return json_encode(['status' => 'This book is already borrowed']);
        }

        // 借书逻辑
        $stmt = $this->db->prepare("INSERT INTO borrows (user_id, book_id, borrow_date, status) VALUES (?, ?, NOW(), 'borrowed')");
        $stmt->bind_param('ii', $data['user_id'], $data['book_id']);

        if ($stmt->execute()) {
            http_response_code(200);
            $stmt->close();
            return json_encode(['status' => 'Book borrowed']);
        } else {
            http_response_code(500);
            return json_encode(['status' => 'Error borrowing book', 'error' => $this->db->error]);
        }
    }

    // 还书操作
    public function returnBook($data) {
        $stmt = $this->db->prepare("UPDATE borrows SET return_date = NOW(), status = 'returned' WHERE user_id = ? AND book_id = ? AND status = 'borrowed'");
        $stmt->bind_param('ii', $data['user_id'], $data['book_id']);

        if ($stmt->execute()) {
            http_response_code(200);
            $stmt->close();
            return json_encode(['status' => 'Book returned']);
        } else {
            http_response_code(500);
            return json_encode(['status' => 'Error returning book', 'error' => $this->db->error]);
        }
    }

    // 获取借阅情况
    public function getBorrowStatus() {
        $query = "
            SELECT users.username, books.title, borrows.borrow_date, borrows.return_date, borrows.status
            FROM borrows
            JOIN users ON borrows.user_id = users.id
            JOIN books ON borrows.book_id = books.id
            ORDER BY borrows.borrow_date DESC
        ";
        $result = $this->db->query($query);

        if (!$result) {
            http_response_code(500);
            return json_encode(['status' => 'Error retrieving borrow status', 'error' => $this->db->error]);
        }

        $borrows = [];
        while ($borrow = $result->fetch_assoc()) {
            $borrows[] = $borrow;
        }

        return json_encode($borrows);
    }
    public function getUserBorrowedBooks($user_id) {
        $stmt = $this->db->prepare("
            SELECT books.id, books.title, books.author, borrows.borrow_date
            FROM borrows
            JOIN books ON borrows.book_id = books.id
            WHERE borrows.user_id = ? AND borrows.status = 'borrowed'
        ");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if (!$result) {
            http_response_code(500);
            return json_encode(['status' => 'Error retrieving borrowed books', 'error' => $this->db->error]);
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
$path = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));

// 创建 BorrowService 实例
$borrowService = new BorrowService();

// 获取输入数据
$input = json_decode(file_get_contents('php://input'), true);

// 处理 API 请求
if ($method === 'POST' && $path[0] === 'borrow') {
    echo $borrowService->borrowBook($input); // 借书操作
} elseif ($method === 'PUT' && $path[0] === 'return') {
    echo $borrowService->returnBook($input); // 还书操作
}elseif ($method === 'GET' && $path[0] === 'isstatus' && isset($path[1])) {
    $user_id = $path[1]; 
    echo $borrowService->getUserBorrowedBooks($user_id);
} elseif ($method === 'GET' && $path[0] === 'status') {
    // 获取所有用户的借阅情况
    echo $borrowService->getBorrowStatus();
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid API request']);
}

