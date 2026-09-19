<?php

declare(strict_types=1);

namespace Develate\CodexCli\Tests\Transport;

use Develate\CodexCli\Transport\AppServerTransport;
use Develate\CodexCli\Transport\ExecTransport;
use Develate\CodexCli\Transport\RunMode;
use Develate\CodexCli\Transport\RunRequest;
use Develate\CodexCli\Value\Sandbox;
use PHPUnit\Framework\TestCase;

/**
 * Both transports spawn the Codex binary themselves, so the only way a caller
 * can keep its own environment out of a Codex process is to hand one in.
 */
final class TransportEnvironmentTest extends TestCase
{
    private const PASSED = 'DEVELATE_CODEX_TEST_PASSED';

    private const INHERITED = 'DEVELATE_CODEX_TEST_INHERITED';

    /** @var list<string> */
    private array $directories = [];

    protected function setUp(): void
    {
        putenv(self::INHERITED.'=from-the-parent');
        $_ENV[self::INHERITED] = 'from-the-parent';
    }

    protected function tearDown(): void
    {
        putenv(self::INHERITED);
        unset($_ENV[self::INHERITED]);

        foreach ($this->directories as $directory) {
            foreach ((array) glob($directory.'/*') as $file) {
                @unlink((string) $file);
            }
            @rmdir($directory);
        }
    }

    public function testTheExecTransportPassesVariablesAndRemovesInheritedOnes(): void
    {
        $directory = $this->directory();
        $binary = $this->environmentReporter($directory, "exit 0\n");

        $transport = new ExecTransport($binary, env: [
            self::PASSED => 'from-the-caller',
            self::INHERITED => false,
        ]);

        iterator_to_array($transport->stream($this->request()), false);

        self::assertSame(
            ['passed' => 'from-the-caller', 'inherited' => 'unset'],
            $this->reported($directory),
        );
    }

    public function testTheExecTransportInheritsEverythingWhenGivenNoEnvironment(): void
    {
        $directory = $this->directory();
        $binary = $this->environmentReporter($directory, "exit 0\n");

        iterator_to_array((new ExecTransport($binary))->stream($this->request()), false);

        self::assertSame(
            ['passed' => 'unset', 'inherited' => 'from-the-parent'],
            $this->reported($directory),
        );
    }

    public function testTheAppServerTransportPassesVariablesAndRemovesInheritedOnes(): void
    {
        $directory = $this->directory();
        $binary = $this->environmentReporter($directory, sprintf(
            "exec %s %s\n",
            escapeshellarg(PHP_BINARY),
            escapeshellarg($this->appServer($directory)),
        ));

        $transport = new AppServerTransport($binary, 10.0, env: [
            self::PASSED => 'from-the-caller',
            self::INHERITED => false,
        ]);

        $transport->quota();

        self::assertSame(
            ['passed' => 'from-the-caller', 'inherited' => 'unset'],
            $this->reported($directory),
        );
    }

    public function testTheAppServerTransportInheritsEverythingWhenGivenNoEnvironment(): void
    {
        $directory = $this->directory();
        $binary = $this->environmentReporter($directory, sprintf(
            "exec %s %s\n",
            escapeshellarg(PHP_BINARY),
            escapeshellarg($this->appServer($directory)),
        ));

        (new AppServerTransport($binary, 10.0))->quota();

        self::assertSame(
            ['passed' => 'unset', 'inherited' => 'from-the-parent'],
            $this->reported($directory),
        );
    }

    /**
     * A fake `codex` that records the two variables under test before handing
     * over to whatever the caller wants the process to go on doing.
     */
    private function environmentReporter(string $directory, string $then): string
    {
        $binary = $directory.'/codex';
        $script = sprintf(
            "#!/bin/sh\nprintf '%%s\\n%%s\\n' \"\${%s-unset}\" \"\${%s-unset}\" > %s\n%s",
            self::PASSED,
            self::INHERITED,
            escapeshellarg($directory.'/environment.txt'),
            $then,
        );

        file_put_contents($binary, $script);
        chmod($binary, 0700);

        return $binary;
    }

    /** @return array{passed: string, inherited: string} */
    private function reported(string $directory): array
    {
        $lines = file($directory.'/environment.txt', FILE_IGNORE_NEW_LINES);

        self::assertIsArray($lines, 'The fake Codex binary reported no environment.');

        return ['passed' => $lines[0], 'inherited' => $lines[1]];
    }

    private function appServer(string $directory): string
    {
        $path = $directory.'/app-server.php';

        file_put_contents($path, <<<'PHP'
        <?php
        while (($line = fgets(STDIN)) !== false) {
            $message = json_decode(trim($line), true);
            if (! is_array($message) || ! isset($message['id'])) {
                continue;
            }
            fwrite(STDOUT, json_encode(['id' => $message['id'], 'result' => []])."\n");
            fflush(STDOUT);
        }
        PHP);

        return $path;
    }

    private function request(): RunRequest
    {
        return new RunRequest(
            mode: RunMode::Start,
            threadId: null,
            prompt: 'Do the thing',
            cwd: sys_get_temp_dir(),
            model: null,
            sandbox: Sandbox::ReadOnly,
            timeout: 10.0,
        );
    }

    private function directory(): string
    {
        $directory = sys_get_temp_dir().'/codex-env-'.bin2hex(random_bytes(8));
        mkdir($directory);
        $this->directories[] = $directory;

        return $directory;
    }
}
