<?php

namespace Yuisalabs\VoltiaDatatable\Columns;

use Yuisalabs\VoltiaDatatable\Column;

class NumericColumn extends Column
{
    protected string $type = 'numeric';

    protected int $decimals = 0;

    protected string $decimalSeparator = '.';

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

    public function decimalSeparator(string $separator): static
    {
        $this->decimalSeparator = $separator;
        $this->numericConfig();

        return $this;
    }

    public function thousandSeparator(string $separator): static
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

    public function useDefaultFormatter(): static
    {
        $this->format(function ($row, $value) {
            if ($value === null || $value === '') return $value;

            $num = (float) $value;
            $formatted = number_format($num, $this->decimalSeparator, $this->thousandsSeparator);
            
            return ($this->prefix . $formatted . $this->suffix);
        });

        return $this;
    }

    protected function numericConfig(): void
    {
        $this->config([
            'decimals' => $this->decimals,
            'decimalSeparator' => $this->decimalSeparator,
            'thousandsSeparator' => $this->thousandsSeparator,
            'prefix' => $this->prefix,
            'suffix' => $this->suffix,
        ]);
    }
}