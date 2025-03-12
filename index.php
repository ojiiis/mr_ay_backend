<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: token');
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
function getUserInfo($uid){
    global $connect;
       $query = "SELECT * FROM `users` WHERE uid='".mysqli_real_escape_string($connect, $uid)."' ";
   $run = mysqli_query($connect,$query);
   if($run->num_rows > 0){
       foreach($run as $row){
           return $row;
       }
   }
   return [];
}
function getUserFinancials($uid) {
    global $connect;
    $financials = [
        "total_balance" => 0,
        "total_withdrawals" => 0,
        "pending_withdrawals" => 0,
        "total_earning" => 0,
        "active_deposit" => 0,
        "last_access"=>getUserInfo($uid)["last_access"]
    ];
   
    
    // Get User Total Balance (Deposit + Earnings - Withdrawals)
    $query = "SELECT SUM(amount) AS total FROM transactions WHERE uid = ? AND type IN ('deposit', 'earning') AND status = 'completed'";
    $stmt = $connect->prepare($query);
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $totalCredit = $result['total'] ?? 0;

    $query = "SELECT SUM(amount) AS total FROM transactions WHERE uid = ? AND type = 'withdrawal' AND status = 'completed'";
    $stmt = $connect->prepare($query);
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $totalDebit = $result['total'] ?? 0;

    $financials["total_balance"] = $totalCredit - $totalDebit;

    // Get User Total Withdrawals (Completed)
    $query = "SELECT SUM(amount) AS total FROM transactions WHERE uid = ? AND type = 'withdrawal' AND status = 'completed'";
    $stmt = $connect->prepare($query);
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $financials["total_withdrawals"] = $result['total'] ?? 0;

    // Get User Pending Withdrawals
    $query = "SELECT SUM(amount) AS total FROM transactions WHERE uid = ? AND type = 'withdrawal' AND status = 'pending'";
    $stmt = $connect->prepare($query);
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $financials["pending_withdrawals"] = $result['total'] ?? 0;

    // Get Total Earnings (Completed earnings)
    $query = "SELECT SUM(amount) AS total FROM transactions WHERE uid = ? AND type = 'earning' AND status = 'completed'";
    $stmt = $connect->prepare($query);
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $financials["total_earning"] = $result['total'] ?? 0;

    // Get Active Deposit (Deposits that are still running)
    $query = "SELECT SUM(amount) AS total FROM transactions WHERE uid = ? AND type = 'deposit' AND status = 'active'";
    $stmt = $connect->prepare($query);
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $financials["active_deposit"] = $result['total'] ?? 0;

    return $financials;
}

// Decode JSON Input
if($raw_data){
    $data = json_decode($raw_data, true);
if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
    die(json_encode(["status" => 0, "error" => "Invalid JSON input"]));
}
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
    $last_access = date("Y-m-d h:i:s a",strtotime('now'));
    $query = "INSERT INTO users (uid, full_name, username, password, usdt_trc20, eth_erc20, bitcoin, email, recovery_question, recovery_answer,last_access)
              VALUES ('$uid','$full_name', '$username', '$password_hash', '$usdt_trc20', '$eth_erc20', '$bitcoin', '$email', '$recovery_question', '$recovery_answer','$last_access')";

    if (mysqli_query($connect, $query)) {
        echo json_encode(["status" => 1, "message" => "Signup successful","token"=>$uid,"balance"=>getUserFinancials($uid)]);
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
    $query = "SELECT uid, full_name, password FROM users WHERE email = '$email'";
    $result = mysqli_query($connect, $query);

    if (mysqli_num_rows($result) === 0) {
        echo json_encode(["status" => 0, "message" => "Invalid email or password"]);
        exit;
    }

    // Fetch user data
    $user = mysqli_fetch_assoc($result);

    // Verify password
    if (!password_verify($password, $user["password"])) {
        echo json_encode(["status" => 0, "message" => "Invalid email or password"]);
        exit;
    }
    $last_access = date("Y-m-d h:i:s a",strtotime('now'));
    mysqli_query($connect,"
    update users set last_access='$last_access' where uid='{$user["uid"]}'
    ");
    // Login successful
    echo json_encode([
        "status" => 1,
        "message" => "Login successful",
        "token" => $user["uid"],
        "balance"=>getUserFinancials($uid),
        "user" => [
            "full_name" => $user["full_name"],
            "email" => $email,
        ]
    ]);
}


if ($app["path"] == "/profile") {
    header("Content-Type: application/json");
    $data = [];
   $query = "SELECT `full_name`, `username`, `usdt_trc20`, `eth_erc20`, `bitcoin`, `email`, `recovery_question`, `recovery_answer` FROM `users` WHERE uid='".$_SERVER["HTTP_TOKEN"]."' ";
   $run = mysqli_query($connect,$query);
   if($run->num_rows > 0){
       foreach($run as $row){
           $data = $row;
       }
       echo json_encode([
        "status" => 1,
        "message" => "User details for ".$_SERVER["HTTP_TOKEN"],
        "data" => $data
    ]);
    exit;
   }
   
   echo json_encode([
        "status" => 0,
        "message" => "Invalid request",
        "data" => $data
    ]);
}
if ($app["path"] == "/update-profile") {
    header("Content-Type: application/json");
    $data = [];
   $query = "UPDATE `users` SET 
    `full_name` = '".$app["data"]["full_name"]."', 
    `username` = '".$app["data"]["username"]."', 
    `usdt_trc20` = '".$app["data"]["usdt_trc20"]."', 
    `eth_erc20` = '".$app["data"]["eth_erc20"]."', 
    `bitcoin` = '".$app["data"]["bitcoin"]."', 
    `email` = '".$app["data"]["email"]."', 
    `recovery_question` = '".$app["data"]["recovery_question"]."', 
    `recovery_answer` = '".$app["data"]["recovery_answer"]."' 
WHERE `uid` = '".$_SERVER["HTTP_TOKEN"]."'  ";
mysqli_query($connect,$query);
if(!empty($app["data"]["password"]) && strlen($app["data"]["password"]) > 0){
        $password_hash = password_hash($app["data"]["password"], PASSWORD_BCRYPT);
   $query = "UPDATE `users` SET password='$password_hash' WHERE `uid` = '".$_SERVER["HTTP_TOKEN"]."'  ";  
   mysqli_query($connect,$query);
}
  
   echo json_encode([
        "status" => 1,
        "message" => "Profile info updated"
    ]);
}
//  

?>
