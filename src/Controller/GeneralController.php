<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class GeneralController extends AbstractController
{
    /**
     * @Route("/curl", name="app_curl")
     */
    public function curl(): JsonResponse
    {
        $responses = array();
        for ($i = 1; $i <= 100; $i++) {

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://pokeapi.co/api/v2/pokemon-form/$i");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            } else {
                $responses[] = json_decode($response, true);
            }
            curl_close($ch);
        }

        return new JsonResponse($responses, JsonResponse::HTTP_OK);
    }

    /**
     * @Route("/curl-async", name="app_curl_async")
     */
    public function curlAsync(HttpClientInterface $client): JsonResponse
    {
        $requests = [];

        for ($i = 1; $i <= 100; $i++) {
            $requests[$i] = $client->request('GET', "https://pokeapi.co/api/v2/pokemon-form/$i");
        }

        $responses = [];
        foreach ($client->stream($requests) as $response => $chunk) {
            if ($chunk->isLast()) {
                $responses[] = $response->toArray();
            }
        }

        return new JsonResponse($responses, JsonResponse::HTTP_OK);
    }

    /**
     * @Route("/curl-async-orden", name="app_curl_async_orden")
     */
    public function curlAsyncOrden(HttpClientInterface $client): JsonResponse
    {
        $requests = [];
        $responses = [];

        for ($i = 1; $i <= 100; $i++) {
            $requests[$i] = $client->request('GET', "https://pokeapi.co/api/v2/pokemon-form/$i");
        }

        $tempResponses = [];

        foreach ($client->stream($requests) as $response => $chunk) {
            if ($chunk->isLast()) {
                foreach ($requests as $index => $req) {
                    if ($req === $response) {
                        $tempResponses[$index] = $response->toArray();
                        break;
                    }
                }
            }
        }

        for ($i = 1; $i <= 100; $i++) {
            if (isset($tempResponses[$i])) {
                $responses[] = $tempResponses[$i];
            }
        }

        return new JsonResponse($responses, JsonResponse::HTTP_OK);
    }
}
