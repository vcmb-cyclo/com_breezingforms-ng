<?php

// PHPStan stub, not a real implementation - see ../README.md.

namespace CB\Component\Contentbuilderng\Administrator\Service;

use Joomla\Database\DatabaseInterface;

class FormSupportService
{
    public function __construct(
        PathService $pathService,
        DatabaseInterface $db,
        TemplateSampleService $templateSampleService
    ) {
    }

    /**
     * @param mixed $formId
     * @param mixed $form
     * @return array<mixed>
     */
    public function synchElements($formId, $form, bool $removeMissing = true): array
    {
        return [];
    }
}
