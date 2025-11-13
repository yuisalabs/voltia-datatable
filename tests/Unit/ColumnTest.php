<?php

use Yuisa\VoltiaDatatable\Column;

describe('Column', function () {
    it('can create a column with make method', function () {
        $column = Column::make('name', 'Name');

        expect($column)
            ->toBeInstanceOf(Column::class)
            ->and($column->key)->toBe('name')
            ->and($column->label)->toBe('Name')
            ->and($column->sortable)->toBeFalse()
            ->and($column->searchable)->toBeFalse()
            ->and($column->hidden)->toBeFalse()
            ->and($column->align)->toBe('left');
    });

    it('generates label from key if not provided', function () {
        $column = Column::make('first_name');

        expect($column->label)->toBe('First Name');
    });

    it('can set sortable', function () {
        $column = Column::make('name')->sortable();

        expect($column->sortable)->toBeTrue();
    });

    it('can set searchable', function () {
        $column = Column::make('name')->searchable();

        expect($column->searchable)->toBeTrue();
    });

    it('can set hidden', function () {
        $column = Column::make('id')->hidden();

        expect($column->hidden)->toBeTrue();
    });

    it('can set alignment', function () {
        $column = Column::make('price')->align('right');

        expect($column->align)->toBe('right');
    });

    it('can set minimum width', function () {
        $column = Column::make('description')->minWidth(200);

        expect($column->minWidth)->toBe(200);
    });

    it('can set format callback', function () {
        $formatter = fn ($row, $value) => strtoupper($value);
        $column = Column::make('name')->format($formatter);

        expect($column->format)->toBe($formatter);
    });

    it('can chain multiple methods', function () {
        $column = Column::make('email', 'Email Address')
            ->sortable()
            ->searchable()
            ->align('center')
            ->minWidth(150);

        expect($column)
            ->sortable->toBeTrue()
            ->and($column->searchable)->toBeTrue()
            ->and($column->align)->toBe('center')
            ->and($column->minWidth)->toBe(150);
    });

    it('converts to array correctly', function () {
        $column = Column::make('name', 'Full Name')
            ->sortable()
            ->searchable()
            ->align('left')
            ->minWidth(100);

        $array = $column->toArray();

        expect($array)->toHaveKeys(['key', 'label', 'sortable', 'searchable', 'format', 'align', 'minWidth', 'hidden'])
            ->and($array['key'])->toBe('name')
            ->and($array['label'])->toBe('Full Name')
            ->and($array['sortable'])->toBeTrue()
            ->and($array['searchable'])->toBeTrue()
            ->and($array['align'])->toBe('left')
            ->and($array['minWidth'])->toBe(100)
            ->and($array['hidden'])->toBeFalse();
    });

    it('handles format callback in array', function () {
        $formatter = fn ($row, $value) => $value;
        $column = Column::make('name')->format($formatter);

        $array = $column->toArray();

        expect($array['format'])->toBe($formatter);
    });

    it('can create hidden column', function () {
        $column = Column::make('password')->hidden();

        expect($column->hidden)->toBeTrue();
    });

    it('has default alignment as left', function () {
        $column = Column::make('name');

        expect($column->align)->toBe('left');
    });
});
