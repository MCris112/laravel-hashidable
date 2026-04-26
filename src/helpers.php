<?php

if (!function_exists('hashid_decode')) {
    /**
     * Decode a hashid for a specific model.
     * 
     * @param string|object $model Model class name or instance
     * @param string|array $hash The hash(es) to decode
     * @return int|array
     */
    function hashid_decode($model, $hash)
    {
        $class = is_object($model) ? get_class($model) : $model;
        
        if (!method_exists($class, 'hashIdDecode')) {
            throw new \InvalidArgumentException("Model {$class} does not use Hashidable trait.");
        }

        return $class::hashIdDecode($hash);
    }
}

if (!function_exists('hashid_encode')) {
    /**
     * Encode an ID for a specific model.
     * 
     * @param string|object $model Model class name or instance
     * @param int $id The ID to encode
     * @return string
     */
    function hashid_encode($model, int $id)
    {
        $class = is_object($model) ? get_class($model) : $model;
        $instance = is_object($model) ? $model : new $class;

        if (!method_exists($instance, 'hashidableEncoder')) {
            throw new \InvalidArgumentException("Model {$class} does not use Hashidable trait.");
        }

        return $instance->hashidableEncoder()->encode($id);
    }
}
