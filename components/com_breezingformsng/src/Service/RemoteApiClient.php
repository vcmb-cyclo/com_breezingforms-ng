<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Vcmb\Component\BreezingformsNG\Site\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Http\HttpFactory;
use RuntimeException;

final class RemoteApiClient
{
    public function verifyRecaptcha(string $secret, string $response, string $remoteIp): bool
    {
        if ($secret === '' || $response === '') {
            return false;
        }

        $result = HttpFactory::getHttp()->post(
            'https://www.google.com/recaptcha/api/siteverify',
            [
                'secret' => $secret,
                'response' => $response,
                'remoteip' => $remoteIp,
            ]
        );
        $payload = json_decode((string) $result->body, true, 512, JSON_THROW_ON_ERROR);

        return $result->code === 200 && ($payload['success'] ?? false) === true;
    }

    public function uploadToDropbox(string $accessToken, string $remotePath, string $localFile): void
    {
        $contents = file_get_contents($localFile);
        if ($contents === false) {
            throw new RuntimeException('Unable to read the Dropbox upload source.');
        }

        $result = HttpFactory::getHttp()->post(
            'https://content.dropboxapi.com/2/files/upload',
            $contents,
            [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/octet-stream',
                'Dropbox-API-Arg' => json_encode([
                    'path' => $remotePath,
                    'mode' => 'add',
                    'autorename' => true,
                    'mute' => false,
                ], JSON_THROW_ON_ERROR),
            ]
        );

        if ($result->code < 200 || $result->code >= 300) {
            throw new RuntimeException('Dropbox API error: ' . (string) $result->body);
        }
    }

    public function mailchimp(
        string $apiKey,
        string $method,
        string $resource,
        ?array $payload = null
    ): array {
        $separator = strrpos($apiKey, '-');
        if ($separator === false || $separator === strlen($apiKey) - 1) {
            throw new RuntimeException('Invalid Mailchimp API key.');
        }

        $dataCenter = substr($apiKey, $separator + 1);
        $headers = [
            'Authorization' => 'Basic ' . base64_encode('breezingformsng:' . $apiKey),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
        $body = $payload === null ? '' : json_encode($payload, JSON_THROW_ON_ERROR);
        $http = HttpFactory::getHttp();
        $url = 'https://' . $dataCenter . '.api.mailchimp.com/3.0/' . ltrim($resource, '/');
        $result = match (strtoupper($method)) {
            'DELETE' => $http->delete($url, $headers),
            'PUT' => $http->put($url, $body, $headers),
            default => throw new RuntimeException('Unsupported Mailchimp HTTP method.'),
        };

        $decoded = (string) $result->body === ''
            ? []
            : json_decode((string) $result->body, true, 512, JSON_THROW_ON_ERROR);

        if ($result->code < 200 || $result->code >= 300) {
            throw new RuntimeException((string) ($decoded['detail'] ?? $result->body));
        }

        return $decoded;
    }
}
