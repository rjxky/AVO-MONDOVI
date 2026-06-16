<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: s-maxage=300, stale-while-revalidate=3600');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'ok' => false,
        'message' => 'Metodo non consentito.',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

const INSTAGRAM_USERNAME = 'avo.mondovi';
const INSTAGRAM_POST_LIMIT = 10;

function fetch_instagram_feed(string $username, int $count): array
{
    $url = sprintf(
        'https://www.instagram.com/api/v1/feed/user/%s/username/?count=%d',
        rawurlencode($username),
        $count
    );

    $headers = [
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
        'Accept: application/json',
        'X-IG-App-ID: 936619743392459',
        'Referer: https://www.instagram.com/' . $username . '/',
    ];

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        $body = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($body === false || $httpCode >= 400) {
            throw new RuntimeException(
                $curlError !== '' ? $curlError : 'Instagram ha restituito HTTP ' . $httpCode
            );
        }

        $decoded = json_decode($body, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Risposta Instagram non valida.');
        }

        return $decoded;
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => implode("\r\n", $headers),
            'timeout' => 15,
            'ignore_errors' => true,
        ],
    ]);

    $body = @file_get_contents($url, false, $context);
    if ($body === false) {
        throw new RuntimeException('Impossibile contattare Instagram.');
    }

    $decoded = json_decode($body, true);
    if (!is_array($decoded)) {
        throw new RuntimeException('Risposta Instagram non valida.');
    }

    return $decoded;
}

function first_media_url(array $item): string
{
    if (!empty($item['carousel_media']) && is_array($item['carousel_media'])) {
        foreach ($item['carousel_media'] as $media) {
            $candidate = $media['image_versions2']['candidates'][0]['url'] ?? '';
            if (is_string($candidate) && $candidate !== '') {
                return $candidate;
            }
        }
    }

    $candidates = [
        $item['image_versions2']['candidates'][0]['url'] ?? '',
        $item['thumbnail_url'] ?? '',
        $item['display_url'] ?? '',
    ];

    foreach ($candidates as $candidate) {
        if (is_string($candidate) && $candidate !== '') {
            return $candidate;
        }
    }

    return '';
}

function caption_text(array $item): string
{
    $caption = $item['caption']['text'] ?? '';
    return is_string($caption) ? trim($caption) : '';
}

function media_kind(array $item): string
{
    $mediaType = (int) ($item['media_type'] ?? 0);

    if ($mediaType === 8) {
        return 'album';
    }

    if ($mediaType === 2) {
        return 'video';
    }

    return 'photo';
}

try {
    $payload = fetch_instagram_feed(INSTAGRAM_USERNAME, INSTAGRAM_POST_LIMIT);
    $items = $payload['items'] ?? [];

    if (!is_array($items)) {
        throw new RuntimeException('Feed Instagram non disponibile.');
    }

    $posts = [];

    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }

        $code = $item['code'] ?? '';
        $imageUrl = first_media_url($item);

        if (!is_string($code) || $code === '' || $imageUrl === '') {
            continue;
        }

        $timestamp = (int) ($item['taken_at'] ?? 0);
        $posts[] = [
            'id' => (string) ($item['id'] ?? $code),
            'shortcode' => $code,
            'url' => 'https://www.instagram.com/p/' . rawurlencode($code) . '/',
            'image' => $imageUrl,
            'caption' => caption_text($item),
            'timestamp' => $timestamp,
            'date_iso' => $timestamp > 0 ? gmdate('c', $timestamp) : null,
            'type' => media_kind($item),
        ];

        if (count($posts) >= INSTAGRAM_POST_LIMIT) {
            break;
        }
    }

    echo json_encode([
        'ok' => true,
        'username' => INSTAGRAM_USERNAME,
        'profile_url' => 'https://www.instagram.com/' . INSTAGRAM_USERNAME . '/',
        'count' => count($posts),
        'posts' => $posts,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $throwable) {
    http_response_code(502);
    echo json_encode([
        'ok' => false,
        'message' => 'Impossibile caricare i post Instagram in questo momento.',
        'detail' => $throwable->getMessage(),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
