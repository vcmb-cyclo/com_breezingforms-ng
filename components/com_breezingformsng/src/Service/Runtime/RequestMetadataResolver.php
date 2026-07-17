<?php
/**
 * @package BreezingFormsNG
 * @copyright Copyright (C) 2024-2026 by XDA+GIL
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Runtime;

\defined('_JEXEC') or die;

use Joomla\CMS\Environment\Browser;

final class RequestMetadataResolver
{
    public function __construct(private readonly Browser $browser)
    {
    }

    public function resolve(
        string $remoteAddress,
        bool $hideIp,
        bool $resolveProvider,
        string $unknownProvider
    ): RequestMetadata {
        $ip = $hideIp ? '0' : $remoteAddress;
        $agent = $this->browser->getAgentString();

        return new RequestMetadata(
            $ip,
            $agent,
            $agent,
            $this->browser->getPlatform(),
            $resolveProvider ? $this->resolveProvider($ip, $unknownProvider) : $unknownProvider
        );
    }

    private function resolveProvider(string $ip, string $unknownProvider): string
    {
        if ($ip === '' || $ip === '0' || filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return $unknownProvider;
        }

        $host = gethostbyaddr($ip);

        if ($host === false || $host === $ip) {
            return $unknownProvider;
        }

        $dot = strpos($host, '.');

        return $dot === false ? $host : substr($host, $dot + 1);
    }
}
