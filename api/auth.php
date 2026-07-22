<?php
/**
 * ContractPeer - API: Auth (Register, Login, Logout)
 */
require_once __DIR__ . '/../includes/config.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = get_input();
$action = $input['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'register':
        $result = register_user($input['email'] ?? '', $input['password'] ?? '', $input['name'] ?? '');
        if (isset($result['error'])) json_response($result, 400);
        json_response($result);
        break;
        
    case 'login':
        $result = login_user($input['email'] ?? '', $input['password'] ?? '');
        if (isset($result['error'])) json_response($result, 401);
        json_response($result);
        break;
        
    case 'logout':
        logout_user();
        json_response(['success' => true]);
        break;
        
    case 'me':
        $user = current_user();
        if (!$user) json_response(['user' => null]);
        json_response(['user' => $user]);
        break;
        
    default:
        json_response(['error' => 'Invalid action'], 400);
}
