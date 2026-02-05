<?php

declare(strict_types=1);

namespace RectorFilament\Rector\Concerns;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Namespace_;
use PHPStan\Type\ObjectType;

/**
 * Shared logic for detecting whether a MethodCall node exists
 * in a Filament context (either via PHPStan type resolution,
 * static call chain analysis, or namespace heuristics).
 */
trait DetectsFilamentContext
{
    /**
     * @var array<string>
     */
    private const FILAMENT_BASE_TYPES = [
        'Filament\\Schemas\\Components\\Component',
        'Filament\\Actions\\Action',
        'Filament\\Actions\\BulkAction',
        'Filament\\Tables\\Table',
        'Filament\\Tables\\Columns\\Column',
        'Filament\\Tables\\Filters\\BaseFilter',
        'Filament\\Tables\\Filters\\Filter',
        'Filament\\Tables\\Filters\\TernaryFilter',
    ];

    /**
     * @var array<string>
     */
    private const FILAMENT_NAMESPACE_PREFIXES = [
        'App\\Filament\\',
        'App\\Livewire\\',
        'App\\Workflows\\',
    ];

    private function isFilamentContext(MethodCall $node): bool
    {
        // 1. PHPStan type resolution
        foreach (self::FILAMENT_BASE_TYPES as $type) {
            if ($this->isObjectType($node->var, new ObjectType($type))) {
                return true;
            }
        }

        // 2. Walk chained method calls to find originating static call
        $className = $this->resolveChainedStaticCallClassName($node->var);
        if ($className !== null) {
            foreach (self::FILAMENT_BASE_TYPES as $type) {
                $shortName = substr($type, strrpos($type, '\\') + 1);
                if ($className === $type || str_ends_with($className, $shortName)) {
                    return true;
                }
            }
        }

        // 3. Fallback: check file namespace
        return $this->isInFilamentNamespace($node);
    }

    private function resolveChainedStaticCallClassName(Node $node): ?string
    {
        if ($node instanceof StaticCall && $node->class instanceof Name) {
            return $node->class->toString();
        }

        if ($node instanceof MethodCall) {
            return $this->resolveChainedStaticCallClassName($node->var);
        }

        return null;
    }

    private function isInFilamentNamespace(Node $node): bool
    {
        $parent = $node->getAttribute('parent');
        while ($parent !== null) {
            if ($parent instanceof Namespace_ && $parent->name !== null) {
                $namespace = $parent->name->toString();
                foreach (self::FILAMENT_NAMESPACE_PREFIXES as $prefix) {
                    if (str_starts_with($namespace, rtrim($prefix, '\\'))) {
                        return true;
                    }
                }

                return false;
            }
            $parent = $parent->getAttribute('parent');
        }

        return false;
    }
}
