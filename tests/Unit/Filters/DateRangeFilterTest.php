<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Yuisa\VoltiaDatatable\Filters\DateRangeFilter;

beforeEach(function () {
    Schema::create('test_models', function (Blueprint $table) {
        $table->id();
        $table->date('created_date');
        $table->timestamps();
    });

    config()->set('voltia-datatable.date_format', 'Y-m-d');
});

afterEach(function () {
    Schema::dropIfExists('test_models');
});

it('can apply date range filter with both start and end dates', function () {
    $model = new class extends Model {
        protected $table = 'test_models';
        protected $dates = ['created_date'];
    };

    $filter = new DateRangeFilter('created_date');

    $query = $model->query();
    $filter->apply($query, ['2024-01-01', '2024-12-31']);

    $sql = $query->toSql();
    expect($sql)->toMatch('/created_date.*>=/');
    expect($sql)->toMatch('/created_date.*<=/');
    
    $bindings = $query->getBindings();
    expect($bindings)->toContain('2024-01-01');
    expect($bindings)->toContain('2024-12-31');
});

it('can apply date range filter with only start date', function () {
    $model = new class extends Model {
        protected $table = 'test_models';
        protected $dates = ['created_date'];
    };

    $filter = new DateRangeFilter('created_date');

    $query = $model->query();
    $filter->apply($query, ['2024-01-01', null]);

    $sql = $query->toSql();
    expect($sql)->toMatch('/created_date.*>=/');
    expect($sql)->not->toMatch('/created_date.*<=/');
});

it('can apply date range filter with only end date', function () {
    $model = new class extends Model {
        protected $table = 'test_models';
        protected $dates = ['created_date'];
    };

    $filter = new DateRangeFilter('created_date');

    $query = $model->query();
    $filter->apply($query, [null, '2024-12-31']);

    $sql = $query->toSql();
    expect($sql)->not->toMatch('/created_date.*>=/');
    expect($sql)->toMatch('/created_date.*<=/');
});

it('does not apply filter when value is not an array', function () {
    $model = new class extends Model {
        protected $table = 'test_models';
    };

    $filter = new DateRangeFilter('created_date');

    $query = $model->query();
    $originalSql = $query->toSql();
    
    $filter->apply($query, 'invalid');

    expect($query->toSql())->toBe($originalSql);
});

it('does not apply filter when array does not have exactly 2 elements', function () {
    $model = new class extends Model {
        protected $table = 'test_models';
    };

    $filter = new DateRangeFilter('created_date');

    $query = $model->query();
    $originalSql = $query->toSql();
    
    $filter->apply($query, ['2024-01-01']);

    expect($query->toSql())->toBe($originalSql);
});

it('formats dates according to config', function () {
    config()->set('voltia-datatable.date_format', 'Y-m-d');
    
    $model = new class extends Model {
        protected $table = 'test_models';
        protected $dates = ['created_date'];
    };

    $filter = new DateRangeFilter('created_date');

    $query = $model->query();
    $filter->apply($query, ['2024-01-01', '2024-12-31']);

    $bindings = $query->getBindings();
    expect($bindings[0])->toBe('2024-01-01');
});

it('returns correct metadata', function () {
    $filter = new DateRangeFilter('created_date');

    $meta = $filter->meta();

    expect($meta)
        ->toHaveKey('type', 'daterange')
        ->toHaveKey('column', 'created_date');
});
