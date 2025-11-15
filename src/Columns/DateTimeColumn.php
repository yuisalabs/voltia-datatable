<?php

namespace Yuisalabs\VoltiaDatatable\Columns;

class DateTimeColumn extends DateColumn
{
    protected string $type = 'datetime';

    public function __construct(
        public string $key,
        public ?string $label = null,
        public bool $sortable = false,
        public bool $searchable = false,
        public mixed $format = null,
        public ?string $align = 'left',
        public ?int $minWidth = null,
        public ?bool $hidden = false
    ) {
        parent::__construct($key, $label, $sortable, $searchable, $format, $align, $minWidth, $hidden);
        $this->dateFormat('Y-m-d H:i');
    }
}