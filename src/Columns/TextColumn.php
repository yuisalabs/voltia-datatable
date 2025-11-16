<?php

namespace Yuisalabs\VoltiaDatatable\Columns;

use Illuminate\Support\Str;
use Yuisalabs\VoltiaDatatable\Column;

class TextColumn extends Column
{
    protected string $type = 'text';

    protected int $truncate = 0;

    protected string $ellipsis = '...';

    protected ?string $emptyAs = null;

    public static function make(string $key, ?string $label = null): static
    {
        $self = parent::make($key, $label);

        return $self;
    }

    public function truncate(int $chars, string $ellipsis = '...'): static
    {
        $this->truncate = max(0, $chars);
        $this->ellipsis = $ellipsis;
        $this->textConfig();

        return $this;
    }

    public function emptyAs(?string $placeholder): static
    {
        $this->emptyAs = $placeholder;
        $this->textConfig();

        return $this;
    }

    public function value(mixed $row, mixed $raw): mixed
    {
        if (is_callable($this->format)) {
            return ($this->format)($row, $raw);
        }

        if ($raw === null || $raw === '') {
            return $this->emptyAs ?? $raw;
        }

        $text = (string) $raw;

        if ($this->truncate > 0) {
            $text = Str::limit($text, $this->truncate, $this->ellipsis);
        }

        return $text;
    }

    protected function textConfig(): static
    {
        $this->config([
            'limit' => $this->truncate,
            'ellipsis' => $this->ellipsis,
            'emptyAs' => $this->emptyAs
        ]);

        return $this;
    }
}