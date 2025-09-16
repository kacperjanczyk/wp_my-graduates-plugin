<?php

namespace KJanczyk\MyGraduates\API;

use WP_Error;
use WP_Query;
use WP_REST_Controller;
use WP_REST_Response;
use WP_REST_Server;

use function Env\env;

class RestController extends WP_REST_Controller
{
    protected $namespace = 'my-graduates';
    protected $rest_base = 'graduates';

    public function register_routes(): void
    {
        register_rest_route($this->namespace, '/' . $this->rest_base, [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'getItems'],
                'permission_callback' => [$this, 'checkPermission'],
            ]
        ]);
    }

    public function getItems(): WP_REST_Response
    {
        $args = [
            'post_type' => 'graduate',
            'post_status' => 'publish',
        ];

        $query = new WP_Query($args);
        $graduates = [];

        foreach ($query->posts as $post) {
            $photoUrl = get_post_meta($post->ID, '_graduate_photo', true);

            $graduates[] = [
                'id' => $post->ID,
                'first_name' => get_post_meta($post->ID, '_graduate_first_name', true),
                'last_name' => get_post_meta($post->ID, '_graduate_last_name', true),
                'description' => get_post_meta($post->ID, '_graduate_description', true),
                'photo' => $photoUrl ? esc_url_raw($photoUrl) : null,
            ];
        }

        return new WP_REST_Response($graduates, 200);
    }

    public function checkPermission($request): bool|WP_Error
    {
        $allowedIpsEnv = env('MY_GRADUATES_ALLOWED_IPS');
        if (!empty($allowedIpsEnv)) {
            $clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $allowedIps = array_map('trim', explode(',', $allowedIpsEnv));

            if (!in_array($clientIp, $allowedIps, true)) {
                return new WP_Error(
                    'forbidden_ip',
                    'Access from your IP address is not allowed',
                    ['status' => 403]
                );
            }
        }

        $apiKey = $request->get_header('X-API-KEY');
        $validApiKey = env('MY_GRADUATES_API_KEY');

        if (!$apiKey || !hash_equals($validApiKey, $apiKey)) {
            return new WP_Error(
                'invalid_api_key',
                'Invalid API Key',
                ['status' => 401]
            );
        }

        return true;
    }
}
