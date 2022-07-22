<?php

declare(strict_types=1);

namespace Renttek\Attributes\Model;

use Magento\Webapi\Model\Config\Converter;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Renttek\Attributes\Attributes\WebApi;

use function array_merge as merge;
use function array_unique as unique;
use function iter\apply;
use function iter\count;
use function iter\filter;
use function iter\flip;
use function iter\map;
use function iter\reindex;
use function iter\toArray;
use function iter\toArrayWithKeys;

class ConfigGenerator
{
    private array $config = [];

    public function __construct(
        private readonly WebApiRegistrar $webApiRegistrar,
    ) {
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function getConfig(): array
    {
        apply(
            fn(string $class) => $this->generateConfigForClass($class),
            $this->webApiRegistrar->getRegisteredClasses()
        );

        return $this->config;
    }

    /**
     * @param class-string $class
     *
     * @throws ReflectionException
     */
    private function generateConfigForClass(string $class): void
    {
        $reflectionClass = new ReflectionClass($class);

        $classAttributes = $reflectionClass->getAttributes(WebApi::class);

        /** @var WebApi $classConfig */
        $classConfig = count($classAttributes) === 1
            ? $classAttributes[array_key_first($classAttributes)]->newInstance()
            : null;

        $classMethods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);
        $classMethods = filter(fn(ReflectionMethod $method) => $method->isPublic(), $classMethods);
        if (!$reflectionClass->isInterface()) {
            $classMethods = filter(fn(ReflectionMethod $method) => !$method->isStatic(), $classMethods);
            $classMethods = filter(fn(ReflectionMethod $method) => !$method->isAbstract(), $classMethods);
        }

        $routesConfig   = [];
        $servicesConfig = [];
        foreach ($classMethods as $classMethod) {
            /** @var ReflectionMethod $classMethod */
            $routeAttributes = $classMethod->getAttributes(WebApi\Route::class);

            $className = $reflectionClass->getName();
            $method    = $classMethod->getName();

            // Merge array, but ignore duplicate keys
            $routesConfig   += $this->getRouteConfigs($className, $method, $routeAttributes, $classConfig);
            $servicesConfig += $this->getServiceConfigs($className, $method, $routeAttributes, $classConfig);
        }

        $this->config = [
            Converter::KEY_SERVICES => merge(
                $this->config[Converter::KEY_SERVICES] ?? [],
                $servicesConfig
            ),
            Converter::KEY_ROUTES   => merge(
                $this->config[Converter::KEY_ROUTES] ?? [],
                $routesConfig
            ),
        ];
    }

    /**
     * @param list<WebApi\Route> $methodConfigs
     */
    private function getRouteConfigs(string $class, string $method, array $methodConfigs, ?WebApi $classConfig): array
    {
        $config = [];

        foreach ($methodConfigs as $methodConfig) {
            /** @var WebApi\Route $route */
            $route = $methodConfig->newInstance();

            $url                        = $classConfig?->path . $route->path;
            $route->resources           ??= $classConfig?->resources;
            $route->parameters          ??= $classConfig?->parameters;
            $route->inputArraySizeLimit ??= $classConfig?->inputArraySizeLimit;

            unique(toArray(map(fn(WebApi\Resource $r) => $r->ref, $route->resources)));

            $config[$url]                 ??= [];
            $config[$url][$route->method] = [
                Converter::KEY_SECURE                 => $route->secure,
                Converter::KEY_SERVICE                => [
                    Converter::KEY_SERVICE_CLASS  => $class,
                    Converter::KEY_SERVICE_METHOD => $method,
                ],
                Converter::KEY_ACL_RESOURCES          => $this->getResourceRestList($route),
                Converter::KEY_DATA_PARAMETERS        => $this->getParameters($route),
                Converter::KEY_INPUT_ARRAY_SIZE_LIMIT => $route->inputArraySizeLimit,
            ];
        }

        return $config;
    }

    /**
     * @param list<WebApi\Route> $methodConfigs
     */
    private function getServiceConfigs(string $class, string $method, array $methodConfigs, ?WebApi $classConfig): array
    {
        $config = [];
        foreach ($methodConfigs as $methodConfig) {
            /** @var WebApi\Route $route */
            $route = $methodConfig->newInstance();

            $url                        = $classConfig?->path . $route->path;
            $version                    = $this->getVersion($url);
            $soapMethod                 = $route->soapOperation ?? $route->method;
            $route->resources           ??= $classConfig?->resources;
            $route->parameters          ??= $classConfig?->parameters;
            $route->inputArraySizeLimit ??= $classConfig?->inputArraySizeLimit;

            $config[$class]                                   ??= [];
            $config[$class][$version]                         ??= [];
            $config[$class][$version][Converter::KEY_METHODS] ??= [];
            $config[$class][$version]                         = [
                Converter::KEY_METHODS => [
                    $soapMethod => [
                        Converter::KEY_ACL_RESOURCES          => $this->getResourceList($route),
                        Converter::KEY_SECURE                 => $route->secure,
                        Converter::KEY_REAL_SERVICE_METHOD    => $method,
                        Converter::KEY_INPUT_ARRAY_SIZE_LIMIT => $route->inputArraySizeLimit,
                        Converter::KEY_DATA_PARAMETERS        => $this->getParameters($route),
                    ],
                ],
            ];
        }

        return $config;
    }

    /**
     * The rest API needs to have the resources in different format than soap. Because magento.
     *
     * @return list<string>
     *
     * @see \Magento\Webapi\Model\Config\Converter::convert
     */
    private function getResourceRestList(WebApi\Route $route): array
    {
        $resources = $this->getResourceList($route);
        $resources = flip($resources);
        $resources = map(fn() => true, $resources);

        return toArrayWithKeys($resources);
    }

    /**
     * @return list<string>
     */
    private function getResourceList(WebApi\Route $route): array
    {
        $resources = $route->resources ?? [];
        $resources = map(fn(WebApi\Resource $r) => $r->ref, $resources);

        return toArray($resources);
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function getParameters(WebApi\Route $route): array
    {
        $parameters = $route->parameters ?? [];
        $parameters = reindex(fn(WebApi\Parameter $p) => $p->name, $parameters);
        $parameters = map(fn(WebApi\Parameter $p) => ['force' => $p->force, 'value' => $p->value], $parameters);

        return toArrayWithKeys($parameters);
    }

    private function getVersion(string $url): string
    {
        return substr($url, 1, strpos($url, '/', 1) - 1);
    }
}
