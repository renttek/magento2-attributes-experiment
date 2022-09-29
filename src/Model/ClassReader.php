<?php

declare(strict_types=1);

namespace Renttek\Attributes\Model;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\NodeTraverser;
use PhpParser\NodeTraverserInterface;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use SplFileInfo;

class ClassReader
{
    private FindingVisitor $classFinder;
    private Parser $parser;
    private NodeTraverser $nodeTraverser;

    /**
     * @param SplFileInfo $path
     *
     * @return array|null
     * @psalm-return list<Class_|Interface_>|null
     */
    public function getClassNodes(SplFileInfo $path): ?array
    {
        $fileContent = $this->readFile($path);

        /** @var list<Node> $nodes */
        $nodes = $this->getParser()->parse($fileContent);

        $this->getNodeTraverser()->traverse($nodes);

        /** @var list<Class_|Interface_>|null $classNodes */
        $classNodes = $this->getClassFinder()->getFoundNodes();

        return $classNodes;
    }

    private function readFile(SplFileInfo $path): string
    {
        return (string)file_get_contents($path->getRealPath());
    }

    private function getParser(): Parser
    {
        return $this->parser ??= (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
    }

    private function getClassFinder(): FindingVisitor
    {
        return $this->classFinder ??= new FindingVisitor(static function (Node $node) {
            return $node instanceof Interface_
                || $node instanceof Class_;
        });
    }

    private function getNodeTraverser(): NodeTraverserInterface
    {
        if (!isset($this->nodeTraverser)) {
            $this->nodeTraverser = new NodeTraverser();
            $this->nodeTraverser->addVisitor(new NameResolver());
            $this->nodeTraverser->addVisitor($this->getClassFinder());
        }

        return $this->nodeTraverser;
    }
}
