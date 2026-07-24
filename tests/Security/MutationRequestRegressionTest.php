<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingFormsNG\Tests\Security;

use PHPUnit\Framework\TestCase;

final class MutationRequestRegressionTest extends TestCase
{
    private const ROOT = __DIR__ . '/../..';

    public function testFrontendSubmissionChecksThePostToken(): void
    {
        $source = $this->read('components/com_breezingformsng/src/Controller/DisplayController.php');

        self::assertStringContainsString("Session::checkToken('post')", $source);
        self::assertStringNotContainsString("Session::checkToken('request')", $source);
    }

    public function testMenuOrderingUsesTheTokenisedPostForm(): void
    {
        $source = $this->read('administrator/components/com_breezingformsng/tmpl/menus/default.php');

        self::assertStringContainsString('method="post"', $source);
        self::assertStringContainsString("HTMLHelper::_('form.token')", $source);
        self::assertStringContainsString("Joomla.listItemTask('cb", $source);
        self::assertStringContainsString("'menus.orderup'", $source);
        self::assertStringContainsString("'menus.orderdown'", $source);
        self::assertStringNotContainsString('task=menus.orderup', $source);
        self::assertStringNotContainsString('task=menus.orderdown', $source);
    }

    private function read(string $path): string
    {
        $source = file_get_contents(self::ROOT . '/' . $path);

        self::assertNotFalse($source, "Unable to read {$path}");

        return $source;
    }
}
