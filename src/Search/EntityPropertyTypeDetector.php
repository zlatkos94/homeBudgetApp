<?php

namespace App\Search;

use ReflectionClass;
use ReflectionException;

class EntityPropertyTypeDetector
{
    /**
     * @throws ReflectionException
     */
    public static function detect(string $classFqcn, ?array $filters = null): array
    {
        $properties = (new ReflectionClass($classFqcn))->getProperties();

        $result = [];

        foreach ($properties as $property) {
            $result[$property->getName()] = $property->getType()?->getName();
        }

        if ($filters !== null) {
            $result = array_filter(
                $result,
                static fn ($type, $name) => in_array($name, $filters, true),
                ARRAY_FILTER_USE_BOTH
            );
        }

        return $result;
    }
}
