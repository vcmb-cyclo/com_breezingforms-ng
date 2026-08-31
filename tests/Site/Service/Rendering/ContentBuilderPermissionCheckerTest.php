<?php

declare(strict_types=1);

namespace Vcmb\Component\BreezingformsNG\Tests\Site\Service\Rendering;

use PHPUnit\Framework\TestCase;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\ContentBuilderPermissionChecker;
use Vcmb\Component\BreezingformsNG\Site\Service\Rendering\ContentBuilderPermissionGateway;

final class ContentBuilderPermissionCheckerTest extends TestCase
{
    public function testAuthorizesEveryFrontendFormForNewRecords(): void
    {
        $permission = $this->createMock(ContentBuilderPermissionGateway::class);
        $calls = [];
        $permission->expects(self::exactly(2))->method('setPermissions')->willReturnCallback(
            static function (int $formId, int $recordId, string $suffix) use (&$calls): void {
                $calls[] = [$formId, $recordId, $suffix];
            }
        );
        $permission->expects(self::exactly(2))->method('authorizeFrontend')->with('new')->willReturn(true);

        (new ContentBuilderPermissionChecker(static fn(): ContentBuilderPermissionGateway => $permission))
            ->assertCanCreate([12, 13], true, 'frontend', 'administrator');

        self::assertSame([[12, 0, '_fe'], [13, 0, '_fe']], $calls);
    }

    public function testStopsAtTheFirstUnauthorizedAdministratorForm(): void
    {
        $permission = $this->createMock(ContentBuilderPermissionGateway::class);
        $permission->expects(self::once())->method('setPermissions')->with(12, 0, '');
        $permission->expects(self::once())->method('authorize')->with('new')->willReturn(false);

        $this->expectExceptionCode(403);
        $this->expectExceptionMessage('administrator');

        (new ContentBuilderPermissionChecker(static fn(): ContentBuilderPermissionGateway => $permission))
            ->assertCanCreate([12, 13], false, 'frontend', 'administrator');
    }

    public function testChecksAnExistingFrontendRecord(): void
    {
        $permission = $this->createMock(ContentBuilderPermissionGateway::class);
        $permission->expects(self::once())->method('setPermissions')->with(12, 44, '_fe');
        $permission->expects(self::once())->method('checkPermissions')->with('edit', 'edit denied', '_fe');

        $returned = (new ContentBuilderPermissionChecker(static fn(): ContentBuilderPermissionGateway => $permission))
            ->assertCanEditOrCreate(12, 44, true, 'edit denied', 'create denied');

        self::assertSame($permission, $returned);
    }

    public function testChecksANewAdministratorRecordAndFullArticleAccess(): void
    {
        $permission = $this->createMock(ContentBuilderPermissionGateway::class);
        $permission->expects(self::once())->method('setPermissions')->with(12, 0, '');
        $permission->expects(self::once())->method('checkPermissions')->with('new', 'create denied', '');
        $permission->expects(self::once())->method('authorize')->with('fullarticle')->willReturn(true);

        $checker = new ContentBuilderPermissionChecker(static fn(): ContentBuilderPermissionGateway => $permission);

        self::assertTrue($checker->canViewFullArticle(
            $checker->assertCanEditOrCreate(12, 0, false, 'edit denied', 'create denied'),
            false
        ));
    }
}
