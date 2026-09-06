<?php

declare(strict_types=1);

namespace Develate\CodexCli\Tests\Transport;

use Develate\CodexCli\Transport\ElicitationRequest;
use Develate\CodexCli\Transport\ElicitationResponse;
use Develate\CodexCli\Transport\ElicitationSchema;
use Develate\CodexCli\Value\ElicitationAction;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ElicitationSchemaTest extends TestCase
{
    public function testLegacyFormsAndEveryPrimitiveShapePreserveTypesAndTitles(): void
    {
        $schema = ['type' => 'object', 'properties' => [
            'text' => ['type' => 'string', 'minLength' => 2, 'maxLength' => 2],
            'number' => ['type' => 'number', 'minimum' => 0, 'maximum' => 1],
            'integer' => ['type' => 'integer'],
            'boolean' => ['type' => 'boolean', 'default' => false],
            'legacy' => ['type' => 'string', 'enum' => ['x'], 'enumNames' => ['First']],
            'titled' => ['type' => 'string', 'oneOf' => [['const' => 'x', 'title' => 'First']]],
            'multi' => ['type' => 'array', 'items' => ['anyOf' => [['const' => 'x', 'title' => 'First']]], 'minItems' => 1, 'maxItems' => 1],
        ], 'required' => ['boolean']];
        $request = ElicitationRequest::fromParams(['requestedSchema' => $schema]);
        self::assertSame('form', $request->mode);
        ElicitationSchema::validate($schema, ['text' => 'éü', 'number' => 0.5, 'integer' => 0, 'boolean' => false, 'legacy' => 'x', 'titled' => 'x', 'multi' => ['x']]);
        self::assertSame([['value' => 'x', 'label' => 'First']], ElicitationSchema::options($schema['properties']['legacy']));
        self::assertSame([['value' => 'x', 'label' => 'First']], ElicitationSchema::options($schema['properties']['multi']));
    }

    #[DataProvider('invalidAnswers')]
    public function testInvalidAnswersAreRejected(array $field, mixed $value): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ElicitationSchema::validate(['type' => 'object', 'properties' => ['field' => $field]], ['field' => $value]);
    }

    public static function invalidAnswers(): array
    {
        return [
            'string type' => [['type' => 'string'], false],
            'boolean type' => [['type' => 'boolean'], 'false'],
            'integer type' => [['type' => 'integer'], 1.5],
            'number type' => [['type' => 'number'], '1'],
            'infinite' => [['type' => 'number'], INF],
            'array type' => [['type' => 'array', 'items' => ['type' => 'string', 'enum' => ['x']]], 'x'],
            'enum' => [['type' => 'string', 'enum' => ['x']], 'y'],
            'multi enum' => [['type' => 'array', 'items' => ['type' => 'string', 'enum' => ['x']]], ['y']],
            'minimum' => [['type' => 'number', 'minimum' => 2], 1],
            'maximum' => [['type' => 'integer', 'maximum' => 2], 3],
            'short' => [['type' => 'string', 'minLength' => 2], 'a'],
            'long' => [['type' => 'string', 'maxLength' => 2], 'abc'],
            'min items' => [['type' => 'array', 'items' => ['enum' => ['x']], 'minItems' => 1], []],
            'max items' => [['type' => 'array', 'items' => ['enum' => ['x']], 'maxItems' => 0], ['x']],
            'email' => [['type' => 'string', 'format' => 'email'], 'not an email'],
            'uri' => [['type' => 'string', 'format' => 'uri'], 'relative/path'],
            'date' => [['type' => 'string', 'format' => 'date'], '2026-02-30'],
            'datetime' => [['type' => 'string', 'format' => 'date-time'], '2026-09-06T25:00:00Z'],
        ];
    }

    public function testMissingRequiredFieldsAndExtraFieldsAreRejected(): void
    {
        $schema = ['type' => 'object', 'properties' => ['flag' => ['type' => 'boolean']], 'required' => ['flag']];
        foreach ([[], ['flag' => false, 'extra' => true]] as $content) {
            try {
                ElicitationSchema::validate($schema, $content);
                self::fail('Invalid answers must be rejected.');
            } catch (\InvalidArgumentException $exception) {
                self::assertNotSame('', $exception->getMessage());
            }
        }
    }

    public function testAcceptEncodesAnEmptyObjectAndOtherActionsCannotLeakContent(): void
    {
        self::assertSame('{"action":"accept","content":{}}', json_encode((new ElicitationResponse(ElicitationAction::Accept, []))->toArray()));
        foreach ([ElicitationAction::Decline, ElicitationAction::Cancel] as $action) {
            self::assertSame(['action' => $action->value], (new ElicitationResponse($action, ['private' => 'value']))->toArray());
        }
    }

    public function testUnsupportedSchemaConstraintsAreNotSilentlyIgnored(): void
    {
        foreach ([['type' => 'object'], ['type' => 'string', 'pattern' => '^secret$'], ['type' => 'array', 'items' => ['type' => 'number']]] as $field) {
            self::assertNull(ElicitationRequest::fromParams(['mode' => 'form', 'requestedSchema' => ['type' => 'object', 'properties' => ['field' => $field]]]));
        }
    }
}
