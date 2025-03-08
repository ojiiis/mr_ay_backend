<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Content-Type: application/json');

// Read raw JSON input
$raw_data = file_get_contents("php://input");

// Database Connection
$connect = mysqli_connect("localhost", "doksummz_filmhouse", "z!FO@R)PHH[T", "doksummz_filmhouse");
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
$spliter = "/filmhouse";
$app["full-link"] = isset(explode($spliter, $_SERVER["REQUEST_URI"])[1]) ? explode($spliter, $_SERVER["REQUEST_URI"])[1] : "";
$app["path"] = explode("?", $app["full-link"])[0] ?? "";

// Extract query parameters
$params = [];
parse_str(parse_url($_SERVER["REQUEST_URI"], PHP_URL_QUERY) ?? "", $params);
$app["params"] = $params;

// API Routes Placeholder
if ($app["path"] == "/signup") {
    header("Content-Type: application/json");

    // Ensure request method is POST
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        echo json_encode(["status" => 0, "message" => "Invalid request method"]);
        exit;
    }

    // Extract user data
    $required_fields = ["account_name", "full_name", "password", "confirm_password", "email"];
    foreach ($required_fields as $field) {
        if (!isset($app["data"][$field]) || empty(trim($app["data"][$field]))) {
            echo json_encode(["status" => 0, "message" => "$field is required"]);
            exit;
        }
    }

    // Validate password confirmation
    if ($app["data"]["password"] !== $app["data"]["confirm_password"]) {
        echo json_encode(["status" => 0, "message" => "Passwords do not match"]);
        exit;
    }

    // Hash the password
    $hashed_password = password_hash($app["data"]["password"], PASSWORD_DEFAULT);

    // Prepare user data
    $account_name = mysqli_real_escape_string($connect, $app["data"]["account_name"]);
    $full_name = mysqli_real_escape_string($connect, $app["data"]["full_name"]);
    $email = mysqli_real_escape_string($connect, $app["data"]["email"]);
    $usdt_id = isset($app["data"]["usdt_id"]) ? mysqli_real_escape_string($connect, $app["data"]["usdt_id"]) : null;
    $ethereum_id = isset($app["data"]["ethereum_id"]) ? mysqli_real_escape_string($connect, $app["data"]["ethereum_id"]) : null;
    $bitcoin_id = isset($app["data"]["bitcoin_id"]) ? mysqli_real_escape_string($connect, $app["data"]["bitcoin_id"]) : null;

    // Check if email already exists
    $check_email = mysqli_query($connect, "SELECT id FROM users WHERE email = '$email'");
    if (mysqli_num_rows($check_email) > 0) {
        echo json_encode(["status" => 0, "message" => "Email already registered"]);
        exit;
    }

    // Insert into database
    $query = "INSERT INTO users (account_name, full_name, password_hash, usdt_id, ethereum_id, bitcoin_id, email) 
              VALUES ('$account_name', '$full_name', '$hashed_password', '$usdt_id', '$ethereum_id', '$bitcoin_id', '$email')";

    if (mysqli_query($connect, $query)) {
        echo json_encode(["status" => 1, "message" => "Signup successful"]);
    } else {
        echo json_encode(["status" => 0, "message" => "Database error: " . mysqli_error($connect)]);
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
