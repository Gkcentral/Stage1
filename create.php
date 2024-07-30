<?php
header("Content-Type: application/json");

// Replace these with your Freshdesk domain and API key
$freshdesk_domain = "gkinternal.freshdesk.com";
$freshdesk_api_key = "6GC4LafS6xUpUzmPq9Fl";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!empty($input)) {
        $response = createFreshdeskTicket($input, $freshdesk_domain, $freshdesk_api_key);
        echo json_encode($response);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}

function createFreshdeskTicket($data, $domain, $api_key) {
    $url = "https://{$domain}.freshdesk.com/api/v2/tickets";

    $payload = json_encode($data);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_USERPWD, "$api_key:X");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json"
    ]);

    $response = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    curl_close($ch);

    if ($http_status === 201) {
        $response_data = json_decode($response, true);
        return ['ticket_id' => $response_data['id']];
    } else {
        return ['error' => 'Failed to create ticket', 'status' => $http_status, 'response' => $response];
    }
}
?>
