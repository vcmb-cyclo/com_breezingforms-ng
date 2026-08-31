<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Site\Service\Rendering;

use CB\Component\Contentbuilderng\Administrator\Service\PermissionService;

/** Adapts the ContentBuilder permission service to the BFNG rendering contract. */
final class ContentBuilderPermissionServiceAdapter implements ContentBuilderPermissionGateway
{
    public function __construct(private readonly PermissionService $permissionService)
    {
    }

    public function setPermissions(int $formId, int $recordId, string $suffix): void
    {
        $this->permissionService->setPermissions($formId, $recordId, $suffix);
    }

    public function authorize(string $action): bool
    {
        return $this->permissionService->authorize($action);
    }

    public function authorizeFrontend(string $action): bool
    {
        return $this->permissionService->authorizeFe($action);
    }

    public function checkPermissions(string $action, string $errorMessage, string $suffix): void
    {
        $this->permissionService->checkPermissions($action, $errorMessage, $suffix);
    }
}
