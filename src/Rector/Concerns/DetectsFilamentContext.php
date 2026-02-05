<?php

declare(strict_types=1);

namespace RectorFilament\Rector\Concerns;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Function_;
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

    /**
     * Rename a closure/arrow-function parameter and all its usages in the body,
     * skipping variables shadowed by nested scopes.
     */
    private function renameClosureParam(Closure|ArrowFunction $callable, Param $param, string $oldName, string $newName): void
    {
        $param->var = new Variable($newName);

        if ($callable instanceof Closure) {
            $this->renameVariableInNodes($callable->stmts, $oldName, $newName);
        } else {
            $result = $this->renameVariableInNode($callable->expr, $oldName, $newName);
            if ($result !== null) {
                $callable->expr = $result;
            }
        }
    }

    /**
     * Recursively rename Variable nodes in an array of statements,
     * skipping nested scopes that shadow the variable name.
     *
     * @param array<Node> $nodes
     */
    private function renameVariableInNodes(array &$nodes, string $oldName, string $newName): void
    {
        foreach ($nodes as &$node) {
            $result = $this->renameVariableInNode($node, $oldName, $newName);
            if ($result !== null) {
                $node = $result;
            }
        }
    }

    private function renameVariableInNode(Node $node, string $oldName, string $newName): ?Node
    {
        // Rename matching variables
        if ($node instanceof Variable && $node->name === $oldName) {
            return new Variable($newName);
        }

        // Skip nested scopes that redeclare the variable as a param
        if (($node instanceof Closure || $node instanceof ArrowFunction || $node instanceof Function_)
            && $this->scopeDeclaresParam($node, $oldName)) {
            return null;
        }

        // Recurse into sub-nodes
        foreach ($node->getSubNodeNames() as $name) {
            $subNode = $node->{$name};

            if ($subNode instanceof Node) {
                $result = $this->renameVariableInNode($subNode, $oldName, $newName);
                if ($result !== null) {
                    $node->{$name} = $result;
                }
            } elseif (is_array($subNode)) {
                foreach ($subNode as $i => $item) {
                    if ($item instanceof Node) {
                        $result = $this->renameVariableInNode($item, $oldName, $newName);
                        if ($result !== null) {
                            $node->{$name}[$i] = $result;
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * @param Closure|ArrowFunction|Function_ $scope
     */
    private function scopeDeclaresParam(Closure|ArrowFunction|Function_ $scope, string $name): bool
    {
        foreach ($scope->params as $p) {
            if ($p->var instanceof Variable && $p->var->name === $name) {
                return true;
            }
        }

        return false;
    }

    private function isInFilamentNamespace(Node $node): bool
    {
        $parent = $node->getAttribute('parent');
        while ($parent !== null) {
            if ($parent instanceof Namespace_ && $parent->name !== null) {
                $namespace = $parent->name->toString();
                foreach (self::FILAMENT_NAMESPACE_PREFIXES as $prefix) {
                    if (str_starts_with($namespace, $prefix)) {
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
