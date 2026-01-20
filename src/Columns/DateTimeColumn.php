<?php

namespace Yuisalabs\VoltiaDatatable\Columns;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Yuisalabs\VoltiaDatatable\Column;

class DateTimeColumn extends Column
{
    protected string $type = 'datetime';

    protected string $dateTimeFormat = 'Y-m-d H:i:s';

    protected ?string $timezone = null;

    public function dateTimeFormat(string $format): static
    {
        $this->dateTimeFormat = $format;
        $this->config(['format' => $this->dateTimeFormat]);

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

        return $carbon->format($this->dateTimeFormat);
    }
}