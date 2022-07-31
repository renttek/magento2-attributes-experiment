<?php

declare(strict_types=1);

namespace Renttek\Attributes\Model\Webapi;

use Magento\Webapi\Model\Config\Converter;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use Renttek\Attributes\Attributes\Webapi;
use Renttek\Attributes\Model\ClassProcessorInterface;

use function array_unique as unique;
use function iter\count;
use function iter\filter;
use function iter\flip;
use function iter\map;
use function iter\reindex;
use function iter\toArray;
use function iter\toArrayWithKeys;
use function Renttek\Attributes\Functions\hasAttribute;

/**
 * @psalm-type RouteConfigs = array<string, array<string, RouteConfig>>
 * @psalm-type RouteConfig = array{secure: bool, service: RestService, resources: RestResourceList, parameters: Parameters, input-array-size-limit: int|null}
 * @psalm-type RestService = array{class: class-string, method: string}
 * @psalm-type RestResourceList = array<string, true>
 *
 * @psalm-type ServiceConfigs = array<class-string, array<string, array<string, array<string>>>>
 * @psalm-type SoapMethod = array{resources: SoapResourceList, secure: bool, realMethod: string, parameters: Parameters, input-array-size-limit: int|null}
 * @psalm-type SoapResourceList = list<string>
 *
 * @psalm-type Parameters = array<string, array{force: bool, value: string}>
 */
class WebapiProcessor implements ClassProcessorInterface
{
    public function process(ReflectionClass $reflection): iterable
    {
        $classAttributes = $reflection->getAttributes(Webapi::class);

        /** @var Webapi $classConfig */
        $classConfig = count($classAttributes) === 1
            ? $classAttributes[array_key_first($classAttributes)]->newInstance()
            : null;

        $classMethods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        $classMethods = filter(fn(ReflectionMethod $method) => $method->isPublic(), $classMethods);
        if (!$reflection->isInterface()) {
            $classMethods = filter(fn(ReflectionMethod $method) => !$method->isStatic(), $classMethods);
            $classMethods = filter(fn(ReflectionMethod $method) => !$method->isAbstract(), $classMethods);
        }

        $routesConfig   = [];
        $servicesConfig = [];
        foreach ($classMethods as $classMethod) {
            /** @var ReflectionMethod $classMethod */
            $routeAttributes = $classMethod->getAttributes(Webapi\Route::class);

            $className = $reflection->getName();
            $method    = $classMethod->getName();

            // Merge array, but ignore duplicate keys
            $routesConfig   += $this->getRouteConfigs($className, $method, $routeAttributes, $classConfig);
            $servicesConfig += $this->getServiceConfigs($className, $method, $routeAttributes, $classConfig);
        }

        yield [
            'routes' => $routesConfig,
            'services' => $servicesConfig
        ];
    }

    /**
     * @param list<ReflectionAttribute> $methodConfigs
     *
     * @return RouteConfigs
     */
    private function getRouteConfigs(string $class, string $method, array $methodConfigs, ?Webapi $classConfig): array
    {
        $config = [];

        foreach ($methodConfigs as $methodConfig) {
            /** @var Webapi\Route $route */
            $route = $methodConfig->newInstance();

            $url                        = $classConfig?->path . $route->path;
            $route->resources           ??= $classConfig?->resources;
            $route->parameters          ??= $classConfig?->parameters;
            $route->inputArraySizeLimit ??= $classConfig?->inputArraySizeLimit;

            unique(toArray(map(fn(Webapi\Resource $r) => $r->ref, $route->resources)));

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
     * @param list<ReflectionAttribute> $methodConfigs
     *
     * @return ServiceConfigs
     */
    private function getServiceConfigs(string $class, string $method, array $methodConfigs, ?Webapi $classConfig): array
    {
        $config = [];
        foreach ($methodConfigs as $methodConfig) {
            /** @var Webapi\Route $route */
            $route = $methodConfig->newInstance();

            $url                        = $classConfig?->path . $route->path;
            $version                    = $this->getVersion($url);
            $soapMethod                 = $route->soapOperation ?? $route->method;
            $route->resources           ??= $classConfig?->resources;
            $route->parameters          ??= $classConfig?->parameters;
            $route->inputArraySizeLimit ??= $classConfig?->inputArraySizeLimit;

            $config[$class]                                                ??= [];
            $config[$class][$version]                                      ??= [];
            $config[$class][$version][Converter::KEY_METHODS]              ??= [];
            $config[$class][$version][Converter::KEY_METHODS][$soapMethod] = [
                Converter::KEY_ACL_RESOURCES          => $this->getResourceList($route),
                Converter::KEY_SECURE                 => $route->secure,
                Converter::KEY_REAL_SERVICE_METHOD    => $method,
                Converter::KEY_INPUT_ARRAY_SIZE_LIMIT => $route->inputArraySizeLimit,
                Converter::KEY_DATA_PARAMETERS        => $this->getParameters($route),
            ];
        }

        return $config;
    }

    /**
     * The rest API needs to have the resources in different format than soap. Because magento.
     *
     * @return RestResourceList
     *
     * @see \Magento\Webapi\Model\Config\Converter::convert
     */
    private function getResourceRestList(Webapi\Route $route): array
    {
        $resources = $this->getResourceList($route);
        $resources = flip($resources);
        $resources = map(fn() => true, $resources);

        return toArrayWithKeys($resources);
    }

    /**
     * @return SoapResourceList
     */
    private function getResourceList(Webapi\Route $route): array
    {
        $resources = $route->resources ?? [];
        $resources = map(fn(Webapi\Resource $r) => $r->ref, $resources);

        return toArray($resources);
    }

    /**
     * @return Parameters
     */
    private function getParameters(Webapi\Route $route): array
    {
        $parameters = $route->parameters ?? [];
        $parameters = reindex(fn(Webapi\Parameter $p) => $p->name, $parameters);
        $parameters = map(fn(Webapi\Parameter $p) => ['force' => $p->force, 'value' => $p->value], $parameters);

        return toArrayWithKeys($parameters);
    }

    private function getVersion(string $url): string
    {
        return substr($url, 1, strpos($url, '/', 1) - 1);
    }

    public function supports(Interface_|Class_ $class): bool
    {
        return hasAttribute($class, Webapi::class);
    }
}
