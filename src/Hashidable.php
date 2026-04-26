<?php

namespace Mcris112\LaravelHashidable;

/**
 * @property-read string $hashid
 */
trait Hashidable
{
    /**
     * Decode a hash or array of hashes
     *
     * @param string|array $hash
     * @return int|array
     */
    public static function hashIdDecode(string|array $hash): int|array
    {
        if (is_array($hash)) {
            return array_map(fn($h) => static::hashIdDecode($h), $hash);
        }

        if (config('hashidable.cache.enabled', false)) {
            $key = "hashidable.decode." . static::class . "." . $hash;
            return cache()->remember($key, config('hashidable.cache.ttl', 86400), function () use ($hash) {
                return (new static())->hashidableEncoder()->decode($hash);
            });
        }

        return (new static())->hashidableEncoder()->decode($hash);
    }

    /**
     * Finds a model by the hashid
     *
     * @param string|array $hash
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|static[]|static|null
     */
    public static function findByHashid(string|array $hash, array $columns = ['*']): mixed
    {
        return static::query()->findByHashid($hash, $columns);
    }

    /**
     * Finds a model by the hashid or fails
     *
     * @param string $hash
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|static[]|static|null 
     */
    public static function findByHashidOrFail(string $hash, array $columns = ['*']): mixed
    {
        return static::query()->findByHashidOrFail($hash, $columns);
    }

    /**
     * Scope to find a model by its hashid
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|array $hash
     * @param array $columns
     * @return mixed
     */
    public function scopeFindByHashid($query, $hash, array $columns = ['*']): mixed
    {
        return $query->whereHashid($hash)->first($columns);
    }

    /**
     * Scope to find a model by its hashid or fail
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $hash
     * @param array $columns
     * @return mixed
     */
    public function scopeFindByHashidOrFail($query, $hash, array $columns = ['*']): mixed
    {
        return $query->whereHashid($hash)->firstOrFail($columns);
    }

    /**
     * Scope to filter by hashid
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|array $hash
     * @param string|null $column
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereHashid($query, $hash, string $column = null): \Illuminate\Database\Eloquent\Builder
    {
        $column = $column ?? $this->getKeyName();

        if (is_array($hash)) {
            return $query->whereIn($column, static::hashIdDecode($hash));
        }

        return $query->where($column, static::hashIdDecode($hash));
    }

    /**
     * Finds a model by the hashid
     * 
     * @deprecated Use whereHashid scope instead
     * @param string $hash
     * @param string $column
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function whereHashid(string $hash, string $column = 'id'): \Illuminate\Database\Eloquent\Builder
    {
        return static::query()->whereHashid($hash, $column);
    }

    /**
     * Getter for the calling model to return the generated hashid
     *
     * @param mixed $value
     * @return string
     */
    public function getHashidAttribute($value): string
    {
        return $this->hashidableEncoder()->encode($this->getKey());
    }

    /** @inheritDoc */
    public function getRouteKey(): mixed
    {
        return $this->hashid;
    }

    /** @inheritDoc */
    public function resolveRouteBinding($value, $field = null): mixed
    {
        if ($field && $field !== 'hashid') {
            return $this->where($field, $value)->first();
        }

        return $this->where(
            $this->getKeyName(),
            $this->hashidableEncoder()->decode($value)
        )->firstOrFail();
    }

    /** @inheritDoc */
    public function resolveChildRouteBinding($childType, $value, $field): mixed
    {
        if ($field && $field !== 'hashid') {
            return parent::resolveChildRouteBinding($childType, $value, $field);
        }

        return $this->where(
            $this->getKeyName(),
            $this->hashidableEncoder()->decode($value)
        )->firstOrFail();
    }

    /**
     * Hashid Encoder-decoder
     *
     * @return \Mcris112\LaravelHashidable\Encoder
     */
    public final function hashidableEncoder(): Encoder
    {
        $interfaces = class_implements(get_called_class());
        $exists = array_key_exists(HashidableConfigInterface::class, $interfaces);
        $custom = $exists ? $this->hashidableConfig() : [];
        $config = array_merge(config('hashidable'), $custom);

        return new Encoder(get_called_class(), $config);
    }
}
