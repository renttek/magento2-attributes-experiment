<?php

declare(strict_types=1);

namespace Renttek\Attributes\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use RuntimeException;

use const DIRECTORY_SEPARATOR;

class ConfigurationCache
{
    private const CACHE_DIRECTORY = 'attributes';

    public function __construct(
        private readonly DirectoryList $directoryList,
    ) {
    }


    /**
     * @param array<string, mixed> $config
     */
    public function save(string $key, array $config): void
    {
        $this->initialize();

        $cacheFile = $this->getCacheFilePath($key);

        if (file_exists($cacheFile) && !unlink($cacheFile)) {
            throw new RuntimeException(sprintf('Could not deleted "%s"', $cacheFile));
        }

        $result = $this->saveConfigToFile($cacheFile, $config);

        if (!$result) {
            throw new RuntimeException(sprintf('Could not create "%s"', $cacheFile));
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    public function load(string $key): ?array
    {
        $this->initialize();

        if (!$this->has($key)) {
            return null;
        }

        return require $this->getCacheFilePath($key);
    }

    public function has(string $key): bool
    {
        $this->initialize();
        $cacheFile = $this->getCacheFilePath($key);

        return file_exists($cacheFile)
            && is_file($cacheFile)
            && is_readable($cacheFile);
    }

    private function initialize(): void
    {
        $cachePath = $this->getCacheBasePath();

        if (file_exists($cachePath) && is_dir($cachePath)) {
            return;
        }

        if (!mkdir($cachePath, recursive: true) && !is_dir($cachePath)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $cachePath));
        }
    }

    /**
     * @param array<string, mixed> $config
     */
    private function saveConfigToFile(string $cacheFile, array $config): bool
    {
        $content = '<?php return ' . var_export($config, true) . ';';
        $result  = file_put_contents($cacheFile, $content);

        $this->refreshOpcache($cacheFile);

        return $result !== false;
    }

    private function getCacheFilePath(string $key): string
    {
        return $this->getCacheBasePath() . DIRECTORY_SEPARATOR . $key . '.php';
    }

    private function getCacheBasePath(): string
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->directoryList->getPath(DirectoryList::VAR_DIR) . DIRECTORY_SEPARATOR . self::CACHE_DIRECTORY;
    }

    private function refreshOpcache(string $filename): void
    {
        if (!$this->isOpcacheActive()) {
            return;
        }

        opcache_invalidate($filename);
        opcache_compile_file($filename);
    }

    private function isOpcacheActive(): bool
    {
        if (!extension_loaded('Zend OPcache')) {
            return false;
        }

        if ((PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') && ini_get('opcache.enable_cli') === '1') {
            return true;
        }

        if (PHP_SAPI !== 'cli' && PHP_SAPI !== 'phpdbg' && ini_get('opcache.enable') === '1') {
            return true;
        }

        return false;
    }
}
