<?php

namespace Mcris112\LaravelHashidable;

use Hashids\Hashids;

class Encoder
{
    private Hashids $encoder;

    private array $config = [];

    public function __construct($salt, $config = [])
    {
        $this->config = $config;
        $this->encoder = new Hashids(
            $this->hashSaltFromString($salt),
            $this->config['length'],
            $this->config['charset']
        );
    }

    /**
     * Generates a unique hashid based on a provided integer
     *
     * @param int $id
     * @return string
     */
    public function encode(int $id): string
    {
        return $this->wrap($this->encoder->encode($id));
    }

    /**
     * Decode a model hashid to the original id.
     *
     * @param string $hash
     * @return int|false
     */
    public function decode(string $hash): int|false
    {
        $hashArray = $this->encoder->decode($this->unwrap($hash));

        return reset($hashArray);
    }

    /**
     * Generate a salt from a string
     *
     * @param string $salt
     * @return string
     */
    public function hashSaltFromString(string $salt): string
    {
        $moreSalt = $salt . '\\' . ($this->config['salt'] ?? '');
        $input = array_fill(0, $this->config['length'], $moreSalt);

        return hash('sha512', serialize($input));
    }

    /**
     * Wrap the hash with prefix/suffix
     *
     * @param string $hash
     * @return string
     */
    private function wrap(string $hash): string
    {
        $array = [$hash];
        $separator = $this->config['separator'] ?? '';

        if ($prefix = $this->config['prefix'] ?? null) {
            array_unshift($array, $prefix, $separator);
        }

        if ($suffix = $this->config['suffix'] ?? null) {
            array_push($array, $separator, $suffix);
        }

        return implode('', $array);
    }

    /**
     * Unwrap the hash from prefix/suffix
     *
     * @param string $hash
     * @return string
     */
    private function unwrap(string $hash): string
    {
        $separator = $this->config['separator'] ?? '';

        if ($prefix = $this->config['prefix'] ?? null) {
            $fullPrefix = $prefix . $separator;
            if (str_starts_with($hash, $fullPrefix)) {
                $hash = substr($hash, strlen($fullPrefix));
            }
        }

        if ($suffix = $this->config['suffix'] ?? null) {
            $fullSuffix = $separator . $suffix;
            if (str_ends_with($hash, $fullSuffix)) {
                $hash = substr($hash, 0, -strlen($fullSuffix));
            }
        }

        return $hash;
    }
}
