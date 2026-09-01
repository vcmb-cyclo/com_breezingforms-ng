<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

/** Minimal permission contract required by BreezingForms ContentBuilder flow. */
interface ContentBuilderPermissionGateway
{
    public function setPermissions(int $formId, int $recordId, string $suffix): void;

    public function authorize(string $action): bool;

    public function authorizeFrontend(string $action): bool;

    public function checkPermissions(string $action, string $errorMessage, string $suffix): void;
}
