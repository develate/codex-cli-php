<?php

declare(strict_types=1);

namespace Develate\CodexCli\Tests;

use Develate\CodexCli\Codex;
use PHPUnit\Framework\TestCase;

final class CodexTest extends TestCase
{
    public function testVersionSurvivesADeletedWorkingDirectoryWithoutChangingIt(): void
    {
        $originalDirectory = getcwd();
        $directory = sys_get_temp_dir().'/codex-version-'.bin2hex(random_bytes(8));
        mkdir($directory);
        $codex = new Codex(binary: PHP_BINARY);

        try {
            chdir($directory);
            rmdir($directory);

            self::assertSame(PHP_VERSION, $codex->version());
            self::assertFalse(getcwd());
        } finally {
            chdir($originalDirectory);
            if (is_dir($directory)) {
                rmdir($directory);
            }
        }
    }

    public function testVersionPreservesAnExistingWorkingDirectory(): void
    {
        $directory = getcwd();

        self::assertSame(PHP_VERSION, (new Codex(binary: PHP_BINARY))->version());
        self::assertSame($directory, getcwd());
    }
}
