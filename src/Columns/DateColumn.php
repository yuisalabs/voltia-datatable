<?php

namespace Yuisalabs\VoltiaDatatable\Columns;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Yuisalabs\VoltiaDatatable\Column;

class DateColumn extends Column
{
    protected string $type = 'date';

    protected string $dateFormat = 'Y-m-d';

    protected ?string $timezone = null;

    public function dateFormat(string $format): static
    {
        $this->dateFormat = $format;
        $this->config(['format' => $this->dateFormat]);

        return $this;
    }

    public function timezone(?string $tz): static
    {
        $this->timezone = $tz;
        $this->config(['timezone' => $this->timezone]);

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

        $carbon = $raw instanceof CarbonInterface ? $raw : Carbon::parse($raw);
        if ($this->timezone) {
            $carbon = $carbon->setTimezone($this->timezone);
        }

        return $carbon->format($this->dateFormat);
    }
}