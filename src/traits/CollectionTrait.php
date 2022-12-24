<?php

namespace momentphp\traits;

use Illuminate\Support\Collection;

/**
 * CollectionTrait
 */
trait CollectionTrait
{
    /**
     * Collection
     *
     * @var Collection
     */
    protected $collection;

    /**
     * Collection getter/setter
     *
     * @param Collection|null $collection
     * @return Collection|object
     */
    public function collection(Collection $collection = null)
    {
        if ($collection !== null) {
            $this->collection = $collection;
            return $this;
        }
        if ($this->collection === null) {
            $this->collection = new Collection;
        }
        return $this->collection;
    }
}
