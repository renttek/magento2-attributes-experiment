<?php

declare(strict_types=1);

namespace Renttek\Attributes\Model;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Module\ModuleListInterface;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

use function iter\filter;
use function iter\flatMap;
use function iter\isEmpty;
use function iter\keys;
use function iter\map;
use function iter\toArray;

class ClassFinder
{
    public function __construct(
        private readonly ModuleListInterface $moduleList,
        private readonly ComponentRegistrarInterface $componentRegistrar,
    ) {
    }

    /**
     * @return list<SplFileInfo>
     */
    public function findClasses(array $paths = []): array
    {
        $pathsToSearch = $this->getPathsToSearch($paths);

        $files = (new Finder())
            ->files()
            ->name('*.php')
            ->in($pathsToSearch)
            ->sortByName();

        return toArray($files);
    }

    /**
     * @param list<string> $paths
     *
     * @return list<string>
     */
    private function getPathsToSearch(array $paths): array
    {
        $moduleDirectories = map(
            fn($m) => $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $m),
            keys($this->moduleList->getAll())
        );

        $modulePaths = isEmpty($paths)
            ? $moduleDirectories
            : flatMap(
                fn($directory) => map(
                    fn($path) => $directory . DIRECTORY_SEPARATOR . $path,
                    $paths
                ),
                $moduleDirectories
            );

        return toArray(filter('is_dir', $modulePaths));
    }
}
