<?php

namespace Yuisalabs\VoltiaDatatable\Columns;

use Yuisalabs\VoltiaDatatable\Column;

class TextColumn extends Column
{
    protected string $type = 'text';

    protected int $truncate = 0;

    public static function make(string $key, ?string $label = null): static
    {
        $self = parent::make($key, $label);

        return $self;
    }

    public function truncate(int $chars): static
    {
        $this->truncate = max(0, $chars);
        $this->config(['limit' => $this->truncate]);

        return $this;
    }

    public function toArray(): array
    {
        return parent::toArray();
    }
}