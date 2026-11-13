<?php

namespace App\Core\Http;

class Response
{
    /**
     * Redirect - redirect to url
     *
     * @param string $url     - destination url
     * @param int    $status  - http status code
     * @param array  $headers - optional headers to send
     *
     * @return never
     */
    public static function redirect($url, $status = 302, $headers = []): never
    {
        http_response_code($status);

        foreach ($headers as $key => $value) {
            header("{$key}: {$value}");
        }

        header("Location: {$url}");
        exit();
    }

    /**
     * To JSON - return JSON response
     *
     * @param array   $data   - array to convert
     * @param integer $status - http status code
     *
     * @return never
     */
    public static function toJson($data = null, $status = 200): never
    {
        header_remove();
        header("Content-Type: application/json");

        $json = json_encode($data);

        if ($json === false) {
            $json = json_encode(["jsonError" => json_last_error_msg()]);

            http_response_code(500);
        }

        http_response_code($status);
        echo $json;

        exit();
    }
}
