<?php

namespace momentphp\traits;

/**
 * OptionsTrait
 */
trait OptionsTrait
{
    /**
     * Options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Options initialization flag
     *
     * @var bool
     */
    protected $optionsInitialized = false;

    /**
     * Options getter/setter
     *
     * @param  null|string|array $key
     * @param  null|mixed $value
     * @return mixed
     */
    public function options($key = null, $value = null)
    {
        if (!$this->optionsInitialized) {
            if (property_exists($this, 'defaults')) {
                $this->options = $this->defaults;
            }
            $this->optionsInitialized = true;
        }

        // options set
        if (is_array($key)) {
            foreach ($key as $innerKey => $innerValue) {
                \Illuminate\Support\Arr::set($this->options, $innerKey, $innerValue);
            }
            return $this;
        }

        // options set
        if (func_num_args() === 2) {
            \Illuminate\Support\Arr::set($this->options, $key, $value);
            return $this;
        }

        // options get
        return \Illuminate\Support\Arr::get($this->options, $key);
    }
}
