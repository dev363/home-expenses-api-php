<?php
include('config.php');

class Auth
{

    function generateJWT($payload)
    {
        $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode($payload));
        $signature = hash_hmac('sha256', "$header.$payload", JWT_SECRET_KEY, true);
        $signature = base64_encode($signature);

        return "$header.$payload.$signature";
    }

    function verifyJWT()
    {
        $headers = getallheaders();
        if (isset($headers['token'])) {
            list($header, $payload, $signature) = explode('.', $headers['token']);

            $decodedHeader = json_decode(base64_decode($header), true);
            $decodedPayload = json_decode(base64_decode($payload), true);

            if ($decodedHeader['alg'] !== 'HS256') {
                return false; // Unsupported algorithm
            }

            $expectedSignature = hash_hmac('sha256', "$header.$payload", JWT_SECRET_KEY, true);
            $expectedSignature = base64_encode($expectedSignature);

            return hash_equals($signature, $expectedSignature) ? $decodedPayload : false;
        } else {
            header("HTTP/1.1 401 Unauthorized");
            header('Content-Type: application/json');
            $response['status'] = 0;
            $response['status_message'] = "Not Varify user";
            $response['error'] = "token missing in Api headers";
            echo json_encode($response);
            die();
        }
    }
}
