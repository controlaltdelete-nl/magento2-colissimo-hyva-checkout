<?php

declare(strict_types=1);

namespace ControlAltDelete\ColissimoHyva\Test\Unit\Fake;

use Magento\Framework\App\Config\ScopeConfigInterface;

class FakeScopeConfig implements ScopeConfigInterface
{
    public function __construct(
        private readonly array $values = [],
    ) {}

    public function getValue($path, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null): mixed
    {
        return $this->values[$path] ?? null;
    }

    public function isSetFlag($path, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null): bool
    {
        return (bool)($this->values[$path] ?? false);
    }
}
