<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Yuisalabs\VoltiaDatatable\Filters\SelectFilter;

beforeEach(function () {
    Schema::create('test_models', function (Blueprint $table) {
        $table->id();
        $table->string('status');
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('test_models');
});

it('can apply select filter with valid value', function () {
    $model = new class extends Model {
        protected $table = 'test_models';
    };

    $filter = new SelectFilter('status', [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ]);

    $query = $model->query();
    $filter->apply($query, 'active');

    expect($query->toSql())->toContain('where');
    expect($query->getBindings())->toContain('active');
});

it('does not apply filter when value is null', function () {
    $model = new class extends Model {
        protected $table = 'test_models';
    };

    $filter = new SelectFilter('status', [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ]);

    $query = $model->query();
    $originalSql = $query->toSql();
    
    $filter->apply($query, null);

    expect($query->toSql())->toBe($originalSql);
});

it('does not apply filter when value is empty string', function () {
    $model = new class extends Model {
        protected $table = 'test_models';
    };

    $filter = new SelectFilter('status', [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ]);

    $query = $model->query();
    $originalSql = $query->toSql();
    
    $filter->apply($query, '');

    expect($query->toSql())->toBe($originalSql);
});

it('returns correct metadata', function () {
    $filter = new SelectFilter('status', [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ]);

    $meta = $filter->meta();

    expect($meta)
        ->toHaveKey('type', 'select')
        ->toHaveKey('column', 'status')
        ->toHaveKey('options')
        ->toHaveKey('placeholder')
        ->and($meta['options'])->toBe([
            'active' => 'Active',
            'inactive' => 'Inactive',
        ]);
});
