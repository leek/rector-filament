<?php

declare(strict_types=1);

namespace RectorFilament\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Namespace_;
use PHPStan\Type\ObjectType;
use RectorFilament\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Filament components support dependency injection in closure parameters.
 * This rule ensures proper type hints for well-known utility parameters
 * like $get, $set, $record, $operation, $component, and $livewire.
 */
final class FilamentUtilityInjectionTypeRector extends AbstractRector
{
    /**
     * Filament base types whose methods support utility injection.
     *
     * @var array<string>
     */
    private const FILAMENT_BASE_TYPES = [
        'Filament\\Schemas\\Components\\Component',
        'Filament\\Actions\\Action',
        'Filament\\Tables\\Table',
        'Filament\\Tables\\Columns\\Column',
        'Filament\\Tables\\Filters\\BaseFilter',
    ];

    /**
     * Filament namespace prefixes used as a fallback heuristic
     * when PHPStan cannot resolve the chained method call type.
     *
     * @var array<string>
     */
    private const FILAMENT_NAMESPACE_PREFIXES = [
        'App\\Filament\\',
        'App\\Livewire\\',
        'App\\Workflows\\',
    ];

    /**
     * Parameter name → [fqcn, nullable].
     *
     * @var array<string, array{fqcn: string, nullable: bool}>
     */
    private const PARAM_MAP = [
        'get' => ['fqcn' => 'Filament\\Schemas\\Components\\Utilities\\Get', 'nullable' => false],
        'set' => ['fqcn' => 'Filament\\Schemas\\Components\\Utilities\\Set', 'nullable' => false],
        'record' => ['fqcn' => 'Illuminate\\Database\\Eloquent\\Model', 'nullable' => true],
        'component' => ['fqcn' => 'Filament\\Schemas\\Components\\Component', 'nullable' => false],
        'livewire' => ['fqcn' => 'Livewire\\Component', 'nullable' => false],
    ];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add proper type hints for Filament utility injection parameters in closures.', [
            new CodeSample(
                <<<'CODE_SAMPLE'
TextInput::make('name')
    ->visible(function ($get, $record) {
        return $get('type') === 'business';
    });
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
TextInput::make('name')
    ->visible(function (Get $get, ?Model $record) {
        return $get('type') === 'business';
    });
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isFilamentContext($node)) {
            return null;
        }

        $changed = false;

        foreach ($node->getArgs() as $arg) {
            $callable = $arg->value;
            if (! $callable instanceof Closure && ! $callable instanceof ArrowFunction) {
                continue;
            }

            foreach ($callable->params as $param) {
                $paramName = $this->getName($param->var);
                if ($paramName === null) {
                    continue;
                }

                if ($paramName === 'operation') {
                    if ($param->type === null || $this->isCallableType($param->type)) {
                        $param->type = new Identifier('string');
                        $changed = true;
                    }

                    continue;
                }

                $mapping = self::PARAM_MAP[$paramName] ?? null;
                if ($mapping === null) {
                    continue;
                }

                if ($param->type !== null && ! $this->isCallableType($param->type)) {
                    continue;
                }

                $typeNode = new FullyQualified($mapping['fqcn']);

                if ($mapping['nullable']) {
                    $param->type = new NullableType($typeNode);
                } else {
                    $param->type = $typeNode;
                }

                $changed = true;
            }
        }

        if (! $changed) {
            return null;
        }

        return $node;
    }

    private function isFilamentContext(MethodCall $node): bool
    {
        // Try PHPStan type resolution first (works for simple cases)
        foreach (self::FILAMENT_BASE_TYPES as $type) {
            if ($this->isObjectType($node->var, new ObjectType($type))) {
                return true;
            }
        }

        // Fallback: check if this file lives in a Filament namespace
        return $this->isInFilamentNamespace($node);
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

    private function isCallableType(Node $type): bool
    {
        return $type instanceof Identifier && $type->name === 'callable';
    }
}
