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

    public function createSalesforceRecord(
        string $username,
        string $passwordAndToken,
        string $objectType,
        array $fields
    ): string {
        $loginEnvelope = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<env:Envelope xmlns:env="http://schemas.xmlsoap.org/soap/envelope/"'
            . ' xmlns:urn="urn:partner.soap.sforce.com"><env:Body><urn:login>'
            . '<urn:username>' . htmlspecialchars($username, ENT_XML1) . '</urn:username>'
            . '<urn:password>' . htmlspecialchars($passwordAndToken, ENT_XML1) . '</urn:password>'
            . '</urn:login></env:Body></env:Envelope>';
        $login = HttpFactory::getHttp()->post(
            'https://login.salesforce.com/services/Soap/u/66.0',
            $loginEnvelope,
            [
                'Content-Type' => 'text/xml; charset=UTF-8',
                'SOAPAction' => 'login',
            ]
        );

        if ($login->code !== 200) {
            throw new RuntimeException('Salesforce login failed: ' . (string) $login->body);
        }

        $xml = simplexml_load_string((string) $login->body, \SimpleXMLElement::class, LIBXML_NONET);
        if ($xml === false) {
            throw new RuntimeException('Invalid Salesforce login response.');
        }

        $sessionNodes = $xml->xpath('//*[local-name()="sessionId"]');
        $serverNodes = $xml->xpath('//*[local-name()="serverUrl"]');
        $sessionId = (string) ($sessionNodes[0] ?? '');
        $serverUrl = (string) ($serverNodes[0] ?? '');
        $server = parse_url($serverUrl);

        if ($sessionId === '' || !is_array($server) || empty($server['scheme']) || empty($server['host'])) {
            throw new RuntimeException('Incomplete Salesforce login response.');
        }

        $baseUrl = $server['scheme'] . '://' . $server['host'] . '/services/data/v66.0';
        $headers = [
            'Authorization' => 'Bearer ' . $sessionId,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
        $describe = HttpFactory::getHttp()->get(
            $baseUrl . '/sobjects/' . rawurlencode($objectType) . '/describe',
            $headers
        );

        if ($describe->code !== 200) {
            throw new RuntimeException('Salesforce object description failed: ' . (string) $describe->body);
        }

        $description = json_decode((string) $describe->body, true, 512, JSON_THROW_ON_ERROR);
        $types = [];
        foreach ($description['fields'] ?? [] as $field) {
            $types[(string) ($field['name'] ?? '')] = (string) ($field['type'] ?? '');
        }

        foreach ($fields as $name => &$value) {
            $value = match ($types[$name] ?? '') {
                'boolean' => (bool) $value,
                'int' => (int) $value,
                'double', 'currency', 'percent' => (float) $value,
                default => $value,
            };
        }
        unset($value);

        $created = HttpFactory::getHttp()->post(
            $baseUrl . '/sobjects/' . rawurlencode($objectType),
            json_encode($fields, JSON_THROW_ON_ERROR),
            $headers
        );
        $result = json_decode((string) $created->body, true, 512, JSON_THROW_ON_ERROR);

        if ($created->code !== 201 || ($result['success'] ?? false) !== true) {
            throw new RuntimeException('Salesforce record creation failed: ' . (string) $created->body);
        }

        return (string) ($result['id'] ?? '');
    }
}
