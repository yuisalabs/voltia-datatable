<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Yuisalabs\VoltiaDatatable\Filters\BooleanFilter;

beforeEach(function () {
    Schema::create('test_models', function (Blueprint $table) {
        $table->id();
        $table->boolean('is_active');
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('test_models');
});

it('can apply boolean filter with true value', function () {
    $model = new class extends Model {
        protected $table = 'test_models';
    };

    $filter = new BooleanFilter('is_active');

    $query = $model->query();
    $filter->apply($query, true);

    expect($query->toSql())->toContain('where');
    expect($query->getBindings())->toContain(true);
});

it('can apply boolean filter with false value', function () {
    $model = new class extends Model {
        protected $table = 'test_models';
    };

    $filter = new BooleanFilter('is_active');

    $query = $model->query();
    $filter->apply($query, false);

    expect($query->toSql())->toContain('where');
    expect($query->getBindings())->toContain(false);
});

it('converts string "1" to boolean true', function () {
    $model = new class extends Model {
        protected $table = 'test_models';
    };

    $filter = new BooleanFilter('is_active');

    $query = $model->query();
    $filter->apply($query, '1');

    expect($query->getBindings())->toContain(true);
});

it('converts string "0" to boolean false', function () {
    $model = new class extends Model {
        protected $table = 'test_models';
    };

    $filter = new BooleanFilter('is_active');

    $query = $model->query();
    $filter->apply($query, '0');

    expect($query->getBindings())->toContain(false);
});

it('does not apply filter when value is null', function () {
    $model = new class extends Model {
        protected $table = 'test_models';
    };

    $filter = new BooleanFilter('is_active');

    $query = $model->query();
    $originalSql = $query->toSql();
    
    $filter->apply($query, null);

    expect($query->toSql())->toBe($originalSql);
});

it('does not apply filter when value is empty string', function () {
    $model = new class extends Model {
        protected $table = 'test_models';
    };

    $filter = new BooleanFilter('is_active');

    $query = $model->query();
    $originalSql = $query->toSql();
    
    $filter->apply($query, '');

    expect($query->toSql())->toBe($originalSql);
});

it('returns correct metadata', function () {
    $filter = new BooleanFilter('is_active');

    $meta = $filter->meta();

    expect($meta)
        ->toHaveKey('type', 'boolean')
        ->toHaveKey('column', 'is_active');
});
