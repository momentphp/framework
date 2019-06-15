<?php

namespace momentphp\traits;

/**
 * CollectionTrait
 */
trait CollectionTrait
{
    /**
     * Collection
     *
     * @var \Illuminate\Support\Collection
     */
    protected $collection;

    /**
     * Collection getter/setter
     *
     * @param  \Illuminate\Support\Collection|null $collection
     * @return \Illuminate\Support\Collection|object
     */
    public function collection(Collection $collection = null)
    {
        if ($collection !== null) {
            $this->collection = $collection;
            return $this;
        }
        if ($this->collection === null) {
            $this->collection = new \Illuminate\Support\Collection;
        }
        return $this->collection;
    }
}
