<?php
session_start();
header('Content-Type: application/json');

// Midtrans configuration
$server_key = 'Mid-server-DXD4mYC-7COHSlgYJH5cGfqa';
$is_production = true; // Set to true for production
$api_url = $is_production ? 'https://app.midtrans.com/snap/v1/transactions' : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

try {
    // Validate POST data
    if (!isset($_POST['email']) || !isset($_POST['phone']) || !isset($_POST['name']) || !isset($_POST['membership']) || !isset($_POST['price'])) {
        throw new Exception('Missing required fields');
    }

    // Sanitize input data
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $membership = filter_var($_POST['membership'], FILTER_SANITIZE_STRING);
    $price = intval($_POST['price']);

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    // Validate price
    if ($price <= 0) {
        throw new Exception('Invalid membership price');
    }

    // Generate unique order ID
    $order_id = 'DP-' . date('YmdHis') . '-' . rand(1000, 9999);

    // Membership plan names
    $membership_names = [
        'basic' => 'Basic Membership',
        'premium' => 'Premium Membership', 
        'vip' => 'VIP Membership'
    ];

    $membership_name = isset($membership_names[$membership]) ? $membership_names[$membership] : 'Unknown Membership';

    // Prepare transaction data for Midtrans
    $transaction_data = array(
        'transaction_details' => array(
            'order_id' => $order_id,
            'gross_amount' => $price,
        ),
        'customer_details' => array(
            'first_name' => $name,
            'email' => $email,
            'phone' => $phone,
        ),
        'item_details' => array(
            array(
                'id' => $membership,
                'price' => $price,
                'quantity' => 1,
                'name' => $membership_name
            )
        ),
        'callbacks' => array(
            'finish' => '../success.php'
        )
    );

    // Convert to JSON
    $json_data = json_encode($transaction_data);

    // Create cURL request to Midtrans
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Accept: application/json',
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode($server_key . ':')
    ));

    // Execute request
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        throw new Exception('cURL Error: ' . curl_error($ch));
    }
    
    curl_close($ch);

    // Parse response
    $response_data = json_decode($response, true);

    if ($http_code == 201 && isset($response_data['token'])) {
        // Store order details in session
        $_SESSION['order_details'] = array(
            'order_id' => $order_id,
            'email' => $email,
            'phone' => $phone,
            'name' => $name,
            'membership' => $membership,
            'price' => $price,
            'snap_token' => $response_data['token'],
            'created_at' => date('Y-m-d H:i:s')
        );

        // Return success response
        echo json_encode(array(
            'status' => 'success',
            'snap_token' => $response_data['token'],
            'order_id' => $order_id
        ));

    } else {
        // Handle Midtrans error
        $error_message = 'Payment gateway error';
        if (isset($response_data['error_messages'])) {
            $error_message = implode(', ', $response_data['error_messages']);
        }
        throw new Exception($error_message);
    }

} catch (Exception $e) {
    // Return error response
    echo json_encode(array(
        'status' => 'error',
        'message' => $e->getMessage()
    ));
}
?>