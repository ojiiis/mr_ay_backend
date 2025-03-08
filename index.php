<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Content-Type: application/json');

// Read raw JSON input
$raw_data = file_get_contents("php://input");

//live  Database Connection
$connect = mysqli_connect("localhost", "doksummz_iv", "2vg7oNH[;)uX", "doksummz_iv");

//local Database Connection
//$connect = mysqli_connect("localhost", "doksummz_filmhouse", "z!FO@R)PHH[T", "doksummz_filmhouse");

if (!$connect) {
    die(json_encode(["status" => 0, "error" => "Database connection failed: " . mysqli_connect_error()]));
}

// Decode JSON Input
$data = json_decode($raw_data, true);
if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
    die(json_encode(["status" => 0, "error" => "Invalid JSON input"]));
}

// Initialize API Handling
$app = [];
$app["data"] = $data;

// Extract path from URL
$spliter = "/iv";
$app["full-link"] = isset(explode($spliter, $_SERVER["REQUEST_URI"])[1]) ? explode($spliter, $_SERVER["REQUEST_URI"])[1] : "";
$app["path"] = explode("?", $app["full-link"])[0] ?? "";


// Extract query parameters
$params = [];
parse_str(parse_url($_SERVER["REQUEST_URI"], PHP_URL_QUERY) ?? "", $params);
$app["params"] = $params;

// API Routes Placeholder
if ($app["path"] == "/signup") {
    header("Content-Type: application/json");

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        echo json_encode(["status" => 0, "message" => "Invalid request method"]);
        exit;
    }

    // Check if all required fields are present
    $required_fields = [
        "full_name", "username", "password", "usdt_trc20", "eth_erc20",
        "bitcoin", "email", "recovery_question", "recovery_answer"
    ];
    
    foreach ($required_fields as $field) {
        if (!isset($app["data"][$field]) || empty($app["data"][$field])) {
            echo json_encode(["status" => 0, "message" => "$field is required"]);
            exit;
        }
    }

    // Sanitize inputs
    $full_name = mysqli_real_escape_string($connect, $app["data"]["full_name"]);
    $username = mysqli_real_escape_string($connect, $app["data"]["username"]);
    $email = mysqli_real_escape_string($connect, $app["data"]["email"]);
    $usdt_trc20 = mysqli_real_escape_string($connect, $app["data"]["usdt_trc20"]);
    $eth_erc20 = mysqli_real_escape_string($connect, $app["data"]["eth_erc20"]);
    $bitcoin = mysqli_real_escape_string($connect, $app["data"]["bitcoin"]);
    $recovery_question = mysqli_real_escape_string($connect, $app["data"]["recovery_question"]);
    $recovery_answer = mysqli_real_escape_string($connect, $app["data"]["recovery_answer"]);

    // Hash password
    $password_hash = password_hash($app["data"]["password"], PASSWORD_BCRYPT);

    // Check if email or username already exists
    $check_user = mysqli_query($connect, "SELECT id FROM users WHERE email='$email' OR username='$username'");
    if (mysqli_num_rows($check_user) > 0) {
        echo json_encode(["status" => 0, "message" => "Email or username already exists"]);
        exit;
    }
    $uid = rand(0,9).rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9) .  rand(0,9) . rand(0,9) .rand(0,9).rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9) .  rand(0,9) . rand(0,9);
    // Insert user into database
    $query = "INSERT INTO users (uid, full_name, username, password_hash, usdt_trc20, eth_erc20, bitcoin, email, recovery_question, recovery_answer)
              VALUES ('$uid','$full_name', '$username', '$password_hash', '$usdt_trc20', '$eth_erc20', '$bitcoin', '$email', '$recovery_question', '$recovery_answer')";

    if (mysqli_query($connect, $query)) {
        echo json_encode(["status" => 1, "message" => "Signup successful","token"=>$uid]);
    } else {
        echo json_encode(["status" => 0, "message" => "Signup failed, try again"]);
    }
}



if ($app["path"] == "/signin") {
    header("Content-Type: application/json");

    // Ensure request method is POST
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        echo json_encode(["status" => 0, "message" => "Invalid request method"]);
        exit;
    }

    // Check if email and password are provided
    if (!isset($app["data"]["email"]) || !isset($app["data"]["password"])) {
        echo json_encode(["status" => 0, "message" => "Email and password are required"]);
        exit;
    }

    // Sanitize inputs
    $email = mysqli_real_escape_string($connect, $app["data"]["email"]);
    $password = $app["data"]["password"];

    // Check if user exists
    $query = "SELECT id, full_name, password_hash FROM users WHERE email = '$email'";
    $result = mysqli_query($connect, $query);

    if (mysqli_num_rows($result) === 0) {
        echo json_encode(["status" => 0, "message" => "Invalid email or password"]);
        exit;
    }

    // Fetch user data
    $user = mysqli_fetch_assoc($result);

    // Verify password
    if (!password_verify($password, $user["password_hash"])) {
        echo json_encode(["status" => 0, "message" => "Invalid email or password"]);
        exit;
    }

    // Login successful
    echo json_encode([
        "status" => 1,
        "message" => "Login successful",
        "user" => [
            "id" => $user["id"],
            "full_name" => $user["full_name"],
            "email" => $email
        ]
    ]);
}



?>
