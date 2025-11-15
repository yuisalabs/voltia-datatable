<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Yuisalabs\VoltiaDatatable\Filters\TextFilter;

beforeEach(function () {
    Schema::create('test_models', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('test_models');
});

it('can apply text filter with valid value', function () {
    $model = new class extends Model {
        protected $table = 'test_models';
    };

    $filter = new TextFilter('name');

    $query = $model->query();
    $filter->apply($query, 'john');

    expect($query->toSql())->toContain('like');
    expect($query->getBindings())->toContain('%john%');
});

it('does not apply filter when value is null', function () {
    $model = new class extends Model {
        protected $table = 'test_models';
    };

    $filter = new TextFilter('name');

    $query = $model->query();
    $originalSql = $query->toSql();
    
    $filter->apply($query, null);

    expect($query->toSql())->toBe($originalSql);
});

it('does not apply filter when value is empty string', function () {
    $model = new class extends Model {
        protected $table = 'test_models';
    };

    $filter = new TextFilter('name');

    $query = $model->query();
    $originalSql = $query->toSql();
    
    $filter->apply($query, '');

    expect($query->toSql())->toBe($originalSql);
});

it('applies wildcard search correctly', function () {
    $model = new class extends Model {
        protected $table = 'test_models';
    };

    $filter = new TextFilter('name');

    $query = $model->query();
    $filter->apply($query, 'test');

    $bindings = $query->getBindings();
    expect($bindings[0])->toBe('%test%');
});

it('returns correct metadata', function () {
    $filter = new TextFilter('name');

    $meta = $filter->meta();

    expect($meta)
        ->toHaveKey('type', 'text')
        ->toHaveKey('column', 'name')
        ->toHaveKey('placeholder');
});
