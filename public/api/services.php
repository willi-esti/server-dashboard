
<?php

// env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
$dotenv->load();

if ($requestMethod === 'GET') {
    $services = explode(',', $_ENV['SERVICES']);
    foreach ($services as $service) {
        $data[] = ['name' => $service];
        $result = exec("systemctl is-active --quiet " . $service . " && echo 1 || echo 0", $output, $status);
        $data[count($data) - 1]['status'] = $result;
    }

    jsonResponse($data);
} else if ($requestMethod === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $service = $data['service'];
    $action = $data['action'];

    if (!in_array($service, explode(',', $_ENV['SERVICES']))) {
        jsonResponse(['error' => 'Service not found'], 404);
    }

    if ($action === 'restart') {
        exec("sudo systemctl restart " . $service, $output, $status);
    } else if ($action === 'stop') {
        exec("sudo systemctl stop " . $service, $output, $status);
    } else if ($action === 'status') {
        exec("systemctl status " . $service, $output, $status);
        jsonResponse(['content' => $output]);
    } else {
        jsonResponse(['error' => 'Invalid action'], 400);
    }
    //jsonResponse(['error' => 'Method not allowed'], 405);
}
?>
