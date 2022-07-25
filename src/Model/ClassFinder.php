<?php

declare(strict_types=1);

namespace Renttek\Attributes\Model;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Module\ModuleListInterface;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

use function array_intersect as intersect;
use function iter\filter;
use function iter\flatMap;
use function iter\func\not;
use function iter\isEmpty;
use function iter\keys;
use function iter\map;
use function iter\toArray;

class ClassFinder
{
    public function __construct(
        private readonly ModuleListInterface $moduleList,
        private readonly ComponentRegistrarInterface $componentRegistrar,
        private readonly array $modules = [],
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
        $pathsToSearch = map(fn ($module) => $this->getModuleDirectory($module), $this->getModulesToSearch());
        $pathsToSearch = filter(not('is_null'), $pathsToSearch);

        if (!isEmpty($paths)) {
            $pathsToSearch = flatMap(
                fn($dir) => map(fn($path) => $dir . DIRECTORY_SEPARATOR . $path, $paths),
                $pathsToSearch
            );
        }

        $pathsToSearch = filter('is_dir', $pathsToSearch);

        return toArray($pathsToSearch);
    }

    private function getModuleDirectory(string $module): ?string
    {
        return $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $module);
    }

    /**
     * @return list<string>
     */
    private function getModulesToSearch(): array
    {
        $enabledModules = toArray(keys($this->moduleList->getAll()));

        return toArray(intersect($enabledModules, $this->modules));
    }
}
