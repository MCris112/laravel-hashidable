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
     * @return string|array
     */
    public static function hashIdDecode(string|array $hash):string|array
    {
        $static = new static();
        if(is_string($hash)) return $static->hashidableEncoder()->decode($hash);

        $decodedIds = [];
        foreach ($hash as $id) {
            $decodedIds[] = $static->hashidableEncoder()->decode($id);
        }

        return $decodedIds;
    }

    /**
     * Finds a model by the hashid
     *
     * @param string $hash
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|static[]|static|null
     */
    public static function findByHashid(string|array $hash, array $columns = ['*'])
    {
        $static = new static();
        return $static->find($static->hashIdDecode($hash), $columns);
    }

    /**
     * Finds a model by the hashid or fails
     *
     * @param string $hash
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|static[]|static|null 
     */
     public static function findByHashidOrFail(string $hash, array $columns = ['*'])
    {
        $static = new static();
        return $static->findOrFail( $static->hashIdDecode($hash), $columns);
    }

    /**
     * Finds a model by the hashid or fails
     *
     * @param string $hash
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function whereHashid(string $hash, string $column = 'id')
    {
        $static = new static();

        return $static->where($column, $static->hashidableEncoder()->decode($hash));
    }

    /**
     * Getter for the calling model to return the generated hashid
     *
     * @return string
     */
    public function getHashidAttribute($value)
    {
        return $this->hashidableEncoder()->encode($this->getKey());
    }

    /** @inheritDoc */
    public function getRouteKey()
    {
        return $this->hashid;
    }

    /** @inheritDoc */
    public function resolveRouteBinding($value, $field = null)
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
    public function resolveChildRouteBinding($childType, $value, $field)
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
    public final function hashidableEncoder()
    {
        $interfaces = class_implements(get_called_class());
        $exists = array_key_exists(HashidableConfigInterface::class, $interfaces);
        $custom = $exists ? $this->hashidableConfig() : [];
        $config = array_merge(config('hashidable'), $custom);

        return new Encoder(get_called_class(), $config);
    }
}
