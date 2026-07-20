<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Vcmb\Component\BreezingformsNG\Site\Service\Integration;

\defined('_JEXEC') or die;

use Joomla\Http\Http;
use Joomla\Http\HttpFactory;
use RuntimeException;

final class SalesforceClient
{
    private const API_VERSION = '66.0';

    private Http $http;

    public function __construct(?Http $http = null)
    {
        $this->http = $http ?? HttpFactory::getHttp();
    }

    public function createRecord(string $username, string $passwordAndToken, string $objectType, array $fields): string
    {
        [$baseUrl, $headers] = $this->login($username, $passwordAndToken);
        $describe = $this->http->get(
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

        $created = $this->http->post(
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

    private function login(string $username, string $passwordAndToken): array
    {
        $envelope = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<env:Envelope xmlns:env="http://schemas.xmlsoap.org/soap/envelope/"'
            . ' xmlns:urn="urn:partner.soap.sforce.com"><env:Body><urn:login>'
            . '<urn:username>' . htmlspecialchars($username, ENT_XML1) . '</urn:username>'
            . '<urn:password>' . htmlspecialchars($passwordAndToken, ENT_XML1) . '</urn:password>'
            . '</urn:login></env:Body></env:Envelope>';
        $response = $this->http->post(
            'https://login.salesforce.com/services/Soap/u/' . self::API_VERSION,
            $envelope,
            ['Content-Type' => 'text/xml; charset=UTF-8', 'SOAPAction' => 'login']
        );

        if ($response->code !== 200) {
            throw new RuntimeException('Salesforce login failed: ' . (string) $response->body);
        }

        $xml = simplexml_load_string((string) $response->body, \SimpleXMLElement::class, LIBXML_NONET);
        if ($xml === false) {
            throw new RuntimeException('Invalid Salesforce login response.');
        }

        $sessionNodes = $xml->xpath('//*[local-name()="sessionId"]');
        $serverNodes = $xml->xpath('//*[local-name()="serverUrl"]');
        $sessionId = (string) ($sessionNodes[0] ?? '');
        $server = parse_url((string) ($serverNodes[0] ?? ''));

        if ($sessionId === '' || !is_array($server) || empty($server['scheme']) || empty($server['host'])) {
            throw new RuntimeException('Incomplete Salesforce login response.');
        }

        return [
            $server['scheme'] . '://' . $server['host'] . '/services/data/v' . self::API_VERSION,
            [
                'Authorization' => 'Bearer ' . $sessionId,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ];
    }
}
