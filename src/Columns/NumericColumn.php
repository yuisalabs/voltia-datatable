<?php

namespace Yuisalabs\VoltiaDatatable\Columns;

use Yuisalabs\VoltiaDatatable\Column;

class NumericColumn extends Column
{
    protected string $type = 'numeric';

    protected int $decimals = 0;

    protected string $decimalsSeparator = '.';

    protected string $thousandsSeparator = ',';

    protected string $prefix = '';

    protected string $suffix = '';

    public static function make(string $key, ?string $label = null): static
    {
        $self = parent::make($key, $label);

        return $self;
    }

    public function decimals(int $decimals): static
    {
        $this->decimals = max(0, $decimals);
        $this->numericConfig();
        
        return $this;
    }

    public function decimalsSeparator(string $separator): static
    {
        $this->decimalsSeparator = $separator;
        $this->numericConfig();

        return $this;
    }

    public function thousandsSeparator(string $separator): static
    {
        $this->thousandsSeparator = $separator;
        $this->numericConfig();

        return $this;
    }

    public function prefix(string $prefix): static
    {
        $this->prefix = $prefix;
        $this->numericConfig();

        return $this;
    }

    public function suffix(string $suffix): static
    {
        $this->suffix = $suffix;
        $this->numericConfig();

        return $this;
    }

    public function value(mixed $row, mixed $raw): mixed
    {
        if (is_callable($this->format)) {
            return ($this->format)($row, $raw);
        }

        if ($raw === null || $raw === '') {
            return $raw;
        }

        if (!is_numeric($raw)) {
            return $raw;
        }

        $num = (float) $raw;

        return $num;
    }

    protected function numericConfig(): void
    {
        $this->config([
            'decimals' => $this->decimals,
            'decimalsSeparator' => $this->decimalsSeparator,
            'thousandsSeparator' => $this->thousandsSeparator,
            'prefix' => $this->prefix,
            'suffix' => $this->suffix,
        ]);
    }
}