<?php

declare(strict_types=1);

namespace Develate\CodexCli\Transport;

use Develate\CodexCli\Value\PermissionGrantScope;

/** The granted subset returned for an additional-permissions request. */
final readonly class PermissionResponse
{
    /**
     * @param  array<string, mixed>  $permissions
     */
    public function __construct(
        public array $permissions,
        public PermissionGrantScope $scope = PermissionGrantScope::Turn,
        public ?bool $strictAutoReview = null,
    ) {}

    /**
     * Grants the requested subset for this turn by default.
     *
     * Callers should pass only the permissions the user reviewed.
     *
     * @param  array<string, mixed>  $permissions
     */
    public static function grant(
        array $permissions,
        PermissionGrantScope $scope = PermissionGrantScope::Turn,
        ?bool $strictAutoReview = null,
    ): self {
        return new self($permissions, $scope, $strictAutoReview);
    }

    public static function deny(): self
    {
        return new self([]);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $result = ['permissions' => (object) $this->permissions];

        if ($this->scope !== PermissionGrantScope::Turn) {
            $result['scope'] = $this->scope->value;
        }

        if ($this->strictAutoReview !== null) {
            $result['strictAutoReview'] = $this->strictAutoReview;
        }

        return $result;
    }
}
