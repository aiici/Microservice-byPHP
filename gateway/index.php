<?php
// API 网关，将前端请求分发到不同的微服务

// 允许跨域请求的 CORS 头部设置
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// 处理 OPTIONS 预检请求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 获取请求方法和路径
$method = $_SERVER['REQUEST_METHOD'];
$path = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));

// 微服务的 URL 映射
$services = [
    'users'  => 'http://user_service',  // 用户服务
    'books'  => 'http://book_service',  // 图书服务
    'borrow' => 'http://borrow_service' // 借阅服务
];

// 检查请求路径并路由到对应的微服务
if (isset($services[$path[0]])) {
    // 将剩下的路径部分拼接到服务 URL 中
    $url = $services[$path[0]] . '/' . implode('/', array_slice($path, 1));
    forwardRequest($method, $url);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Invalid API endpoint']);
}

// 函数：处理请求并转发到微服务
function forwardRequest($method, $url) {
    // 初始化 cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    // 根据请求方法设置不同的 cURL 选项
    switch ($method) {
        case 'POST':
        case 'PUT':
            $inputData = file_get_contents('php://input');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method); // POST 或 PUT
            curl_setopt($ch, CURLOPT_POSTFIELDS, $inputData);
            break;
        case 'DELETE':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            break;
        case 'GET':
        default:
            // GET 请求不需要额外设置
            break;
    }

    // 执行 cURL 请求
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        echo json_encode(['error' => 'Error communicating with microservice: ' . curl_error($ch)]);
    } else {
        // 检查响应是否为有效的 JSON
        $jsonDecoded = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            http_response_code($httpCode);
            echo json_encode($jsonDecoded);
        } else {
            http_response_code(500); // 服务器错误
            echo json_encode(['error' => 'Invalid JSON response from microservice']);
        }
    }

    curl_close($ch);
}

