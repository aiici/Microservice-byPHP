<?php
class UserService {
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

    // 用户注册方法
    public function register($data) {
        // 准备和绑定参数以防止 SQL 注入
        $stmt = $this->db->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $password = password_hash($data['password'], PASSWORD_DEFAULT);
        $role = isset($data['role']) ? $data['role'] : 'user'; // 默认用户角色为 'user'
        $stmt->bind_param('sss', $data['username'], $password, $role);

        // 执行查询并检查是否成功
        if ($stmt->execute()) {
            http_response_code(201); // 201 Created
            return json_encode(['status' => 'User registered']);
        } else {
            http_response_code(500);
            return json_encode(['status' => 'Registration failed', 'error' => $this->db->error]);
        }
    }

    // 用户登录方法
    public function login($data) {
        // 使用准备语句防止 SQL 注入
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param('s', $data['username']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        // 检查用户是否存在
        if (!$user) {
            http_response_code(401);
            return json_encode(['status' => 'Login failed', 'message' => 'User not found']);
        }

        // 验证密码
        if (password_verify($data['password'], $user['password'])) {
            http_response_code(200);
            return json_encode(['status' => 'Login successful', 'user_id' => $user['id'], 'role' => $user['role']]);
        } else {
            http_response_code(401);
            return json_encode(['status' => 'Login failed', 'message' => 'Incorrect password']);
        }
    }

    // 获取所有用户
    public function getUsers() {
        $result = $this->db->query("SELECT id, username, role FROM users");
        $users = [];

        while ($user = $result->fetch_assoc()) {
            $users[] = $user;
        }

        return json_encode($users);
    }

    // 获取指定用户
    public function getUser($id) {
        $stmt = $this->db->prepare("SELECT id, username, role FROM users WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            return json_encode($user);
        } else {
            http_response_code(404); // 404 Not Found
            return json_encode(['status' => 'User not found']);
        }
    }

    // 更新用户信息
    public function updateUser($id, $data) {
        // 如果传入密码，进行密码更新
        if (isset($data['password']) && !empty($data['password'])) {
            $stmt = $this->db->prepare("UPDATE users SET username = ?, password = ?, role = ? WHERE id = ?");
            $password = password_hash($data['password'], PASSWORD_DEFAULT);
            $stmt->bind_param('sssi', $data['username'], $password, $data['role'], $id);
        } else {
            $stmt = $this->db->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
            $stmt->bind_param('ssi', $data['username'], $data['role'], $id);
        }

        if ($stmt->execute()) {
            http_response_code(200);
            return json_encode(['status' => 'User updated']);
        } else {
            http_response_code(500);
            return json_encode(['status' => 'Error updating user', 'error' => $this->db->error]);
        }
    }

    // 删除用户
    public function deleteUser($id) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            http_response_code(200);
            return json_encode(['status' => 'User deleted']);
        } else {
            http_response_code(500);
            return json_encode(['status' => 'Error deleting user', 'error' => $this->db->error]);
        }
    }
}

// API 处理逻辑
header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];
$path = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));

// 创建 UserService 实例
$userService = new UserService();

// 处理不同的 API 请求
if ($method === 'POST' && $path[0] === 'register') {
    // 获取并解析输入数据
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON input']);
        exit();
    }
    echo $userService->register($input); // 用户注册
} elseif ($method === 'POST' && $path[0] === 'login') {
    // 获取并解析输入数据
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON input']);
        exit();
    }
    echo $userService->login($input); // 用户登录
} elseif ($method === 'GET' && $path[0] === 'users') {
    if (isset($path[1])) {
        echo $userService->getUser($path[1]); // 获取单个用户
    } else {
        echo $userService->getUsers(); // 获取所有用户
    }
} elseif ($method === 'PUT' && $path[0] === 'users' && isset($path[1])) {
    // 获取并解析输入数据
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON input']);
        exit();
    }
    echo $userService->updateUser($path[1], $input); // 更新用户
} elseif ($method === 'DELETE' && $path[0] === 'users' && isset($path[1])) {
    echo $userService->deleteUser($path[1]); // 删除用户
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid API request']);
}

