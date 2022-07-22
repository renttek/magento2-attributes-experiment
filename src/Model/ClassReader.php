<?php

declare(strict_types=1);

namespace Renttek\Attributes\Model;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\NodeTraverser;
use PhpParser\NodeTraverserInterface;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitor\FirstFindingVisitor;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use SplFileInfo;

class ClassReader
{
    private FirstFindingVisitor $classFinder;

    /**
     * @return class-string|null
     */
    public function getClassNameByFile(SplFileInfo $path): ?string
    {
        $fileContent = $this->readFile($path);
        $nodes       = $this->getParser()->parse($fileContent);
        $this->getNodeTraverser()->traverse($nodes);

        /** @var Class_|Interface_|null $class */
        $class = $this->getClassFinder()->getFoundNode();

        return $class?->namespacedName->toString();
    }

    private function readFile(SplFileInfo $path): string
    {
        return (string)file_get_contents($path->getRealPath());
    }

    private function getParser(): Parser
    {
        return $this->parser ??= (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
    }

    private function getClassFinder(): NodeVisitor
    {
        return $this->classFinder ??= new FirstFindingVisitor(static function (Node $node) {
            return $node instanceof Interface_
                || $node instanceof Class_;
        });
    }

    private function getNodeTraverser(): NodeTraverserInterface
    {
        if (!isset($this->nodeTraverser)) {
            $this->nodeTraverser = new NodeTraverser;
            $this->nodeTraverser->addVisitor(new NameResolver);
            $this->nodeTraverser->addVisitor($this->getClassFinder());
        }

        return $this->nodeTraverser;
    }
}
