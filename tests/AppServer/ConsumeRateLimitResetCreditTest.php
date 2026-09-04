<?php

declare(strict_types=1);

namespace Develate\CodexCli\Tests\AppServer;

use Develate\CodexCli\Transport\AppServerTransport;
use Develate\CodexCli\Value\ConsumeRateLimitResetCreditOutcome;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ConsumeRateLimitResetCreditTest extends TestCase
{
    /** @return array<string, array{string, ConsumeRateLimitResetCreditOutcome}> */
    public static function outcomes(): array
    {
        return [
            'reset' => ['reset', ConsumeRateLimitResetCreditOutcome::Reset],
            'nothing to reset' => ['nothingToReset', ConsumeRateLimitResetCreditOutcome::NothingToReset],
            'no credit' => ['noCredit', ConsumeRateLimitResetCreditOutcome::NoCredit],
            'already redeemed' => ['alreadyRedeemed', ConsumeRateLimitResetCreditOutcome::AlreadyRedeemed],
        ];
    }

    #[DataProvider('outcomes')]
    public function testMapsEveryConsumeOutcome(string $outcome, ConsumeRateLimitResetCreditOutcome $expected): void
    {
        $requests = tempnam(sys_get_temp_dir(), 'codex-requests-');
        self::assertNotFalse($requests);
        $binary = $this->fakeAppServer(['account/rateLimitResetCredit/consume' => ['outcome' => $outcome]], $requests);

        $result = (new AppServerTransport($binary))->consumeRateLimitResetCredit('request-key', 'opaque-credit');

        self::assertSame($expected, $result->outcome);
    }

    public function testSendsTheOfficialConsumeParameters(): void
    {
        $requests = tempnam(sys_get_temp_dir(), 'codex-requests-');
        self::assertNotFalse($requests);
        $binary = $this->fakeAppServer(['account/rateLimitResetCredit/consume' => ['outcome' => 'reset']], $requests);

        (new AppServerTransport($binary))->consumeRateLimitResetCredit('stable-key', 'RateLimitResetCredit_1');

        $messages = array_map('json_decode', array_filter(file($requests, FILE_IGNORE_NEW_LINES)));
        $request = array_values(array_filter($messages, static fn (\stdClass $message): bool => $message->method === 'account/rateLimitResetCredit/consume'))[0];

        self::assertSame('stable-key', $request->params->idempotencyKey);
        self::assertSame('RateLimitResetCredit_1', $request->params->creditId);
    }

    /**
     * @param array<string, array<string, string>> $responses
     */
    private function fakeAppServer(array $responses, string $requests): string
    {
        $directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'codex-app-server-'.bin2hex(random_bytes(8));
        mkdir($directory);
        $server = $directory.DIRECTORY_SEPARATOR.'app-server.php';
        $binary = $directory.DIRECTORY_SEPARATOR.'codex';
        $encodedResponses = var_export(json_encode($responses, JSON_THROW_ON_ERROR), true);
        $encodedRequests = var_export($requests, true);

        $body = strtr(<<<'PHP'
        <?php
        $responses = json_decode(__RESPONSES__, true);
        while (($line = fgets(STDIN)) !== false) {
            $message = json_decode(trim($line), true);
            if (! is_array($message) || ! isset($message['id'])) {
                continue;
            }
            file_put_contents(__REQUESTS__, json_encode($message)."\n", FILE_APPEND);
            fwrite(STDOUT, json_encode(['id' => $message['id'], 'result' => $responses[$message['method'] ?? ''] ?? []])."\n");
            fflush(STDOUT);
        }
        PHP, ['__RESPONSES__' => $encodedResponses, '__REQUESTS__' => $encodedRequests]);
        file_put_contents($server, $body);
        file_put_contents($binary, sprintf("#!/bin/sh\nexec %s %s \"\$@\"\n", escapeshellarg(PHP_BINARY), escapeshellarg($server)));
        chmod($binary, 0700);

        return $binary;
    }
}
