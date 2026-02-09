<?php

declare(strict_types=1);

namespace RectorFilament\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use RectorFilament\AbstractRector;
use RectorFilament\Rector\Concerns\DetectsFilamentContext;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * In Filament v4, ->size() on ImageColumn was renamed to ->imageSize().
 */
final class ImageColumnSizeToImageSizeRector extends AbstractRector
{
    use DetectsFilamentContext;

    private const IMAGE_COLUMN_FQCN = 'Filament\\Tables\\Columns\\ImageColumn';

    private const IMAGE_COLUMN_SHORT = 'ImageColumn';

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Rename ->size() to ->imageSize() on Filament ImageColumn.', [
            new CodeSample(
                <<<'CODE_SAMPLE'
ImageColumn::make('avatar')->size(50);
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
ImageColumn::make('avatar')->imageSize(50);
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
        if ($node->isFirstClassCallable()) {
            return null;
        }

        if (! $node->name instanceof Identifier) {
            return null;
        }

        if ($node->name->name !== 'size') {
            return null;
        }

        if (! $this->isOnImageColumnChain($node)) {
            return null;
        }

        $node->name = new Identifier('imageSize');

        return $node;
    }

    private function isOnImageColumnChain(MethodCall $node): bool
    {
        $current = $node->var;

        while ($current instanceof MethodCall) {
            $current = $current->var;
        }

        if (! $current instanceof StaticCall) {
            return false;
        }

        return $this->isImageColumnClass($current->class);
    }

    private function isImageColumnClass(Node $class): bool
    {
        if ($class instanceof FullyQualified) {
            return $class->toString() === self::IMAGE_COLUMN_FQCN;
        }

        if ($class instanceof Name) {
            return $class->toString() === self::IMAGE_COLUMN_SHORT;
        }

        return false;
    }
}
