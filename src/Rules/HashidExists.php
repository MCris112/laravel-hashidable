<?php

namespace Mcris112\LaravelHashidable\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Exception;

class HashidExists implements Rule
{
    /**
     * @var string
     */
    protected $table;

    /**
     * @var string
     */
    protected $column;

    /**
     * @var string|null
     */
    protected $model;

    /**
     * Create a new rule instance.
     *
     * @param  string  $model
     * @param  string|null  $column
     * @return void
     */
    public function __construct(string $model, string $column = null)
    {
        if (class_exists($model)) {
            $instance = new $model;
            $this->model = $model;
            $this->table = $instance->getTable();
            $this->column = $column ?: $instance->getKeyName();
        } else {
            $this->table = $model;
            $this->column = $column ?: 'id';
        }
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (empty($value)) {
            return false;
        }

        try {
            $decodedId = null;

            if ($this->model && method_exists($this->model, 'hashIdDecode')) {
                $decodedId = ($this->model)::hashIdDecode($value);
            } elseif (function_exists('hashid_decode') && $this->model) {
                $decodedId = hashid_decode($this->model, $value);
            }

            if (is_array($decodedId)) {
                return DB::table($this->table)->whereIn($this->column, $decodedId)->count() === count($decodedId);
            }

            return DB::table($this->table)->where($this->column, $decodedId)->exists();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.exists');
    }
}
