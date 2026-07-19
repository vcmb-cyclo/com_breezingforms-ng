<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Runtime;

defined('_JEXEC') or die;

final class RequestParameterParser
{
    public function parse(string $parameters): array
    {
        $result = [];
        $parts = str_contains($parameters, '&amp;')
            ? explode('&amp;', $parameters)
            : explode('&', $parameters);

        foreach ($parts as $part) {
            [$name, $value] = array_pad(explode('=', $part, 2), 2, '');
            $name = trim($name);

            if ($name !== '') {
                $result[$name] = trim($value);
            }
        }

        return $result;
    }
}
