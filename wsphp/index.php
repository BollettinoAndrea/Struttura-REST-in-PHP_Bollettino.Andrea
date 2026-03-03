<?php
session_start();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Accept");

$method = $_SERVER['REQUEST_METHOD'];
$contentType = $_SERVER['CONTENT_TYPE'] ?? "application/json";
$accept = $_SERVER['HTTP_ACCEPT'] ?? "application/json";

if (!isset($_SESSION['utenti'])) {
    $_SESSION['utenti'] = [
        ["id" => 1, "nome" => "Mario"],
        ["id" => 2, "nome" => "Luca"]
    ];
}

function sendResponse($data, $statusCode = 200) {
    global $accept;

    http_response_code($statusCode);

    if ($accept === "application/xml") {
        header("Content-Type: application/xml");
        $xml = new SimpleXMLElement("<response/>");

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $item = $xml->addChild("item");
                foreach ($value as $k => $v) {
                    $item->addChild($k, $v);
                }
            } else {
                $xml->addChild($key, $value);
            }
        }

        echo $xml->asXML();
    } else {
        header("Content-Type: application/json");
        echo json_encode($data, JSON_PRETTY_PRINT);
    }
    exit;
}

$input = file_get_contents("php://input");

if ($contentType === "application/xml" && !empty($input)) {
    $inputData = json_decode(json_encode(simplexml_load_string($input)), true);
} else {
    $inputData = json_decode($input, true);
}

switch ($method) {

    case "GET":
        sendResponse([
            "status" => "success",
            "data" => $_SESSION['utenti']
        ]);
        break;

    case "POST":
        if (!$inputData || !isset($inputData['nome'])) {
            sendResponse(["error" => "Dati non validi"], 400);
        }

        $newId = end($_SESSION['utenti'])['id'] + 1;
        $nuovo = [
            "id" => $newId,
            "nome" => $inputData['nome']
        ];

        $_SESSION['utenti'][] = $nuovo;

        sendResponse([
            "status" => "Creato",
            "data" => $nuovo
        ], 201);
        break;

    case "PUT":
        if (!$inputData || !isset($inputData['id'])) {
            sendResponse(["error" => "ID mancante"], 400);
        }

        foreach ($_SESSION['utenti'] as &$utente) {
            if ($utente['id'] == $inputData['id']) {
                $utente['nome'] = $inputData['nome'];
                sendResponse(["status" => "Aggiornato", "data" => $utente]);
            }
        }

        sendResponse(["error" => "Utente non trovato"], 404);
        break;

    case "DELETE":
        if (!$inputData || !isset($inputData['id'])) {
            sendResponse(["error" => "ID mancante"], 400);
        }

        foreach ($_SESSION['utenti'] as $key => $utente) {
            if ($utente['id'] == $inputData['id']) {
                unset($_SESSION['utenti'][$key]);
                sendResponse(["status" => "Eliminato"]);
            }
        }

        sendResponse(["error" => "Utente non trovato"], 404);
        break;

    default:
        sendResponse(["error" => "Metodo non supportato"], 405);
}
