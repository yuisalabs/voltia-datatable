<?php

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Yuisalabs\VoltiaDatatable\Column;
use Yuisalabs\VoltiaDatatable\Table;
use Yuisalabs\VoltiaDatatable\Filters\SelectFilter;
use Yuisalabs\VoltiaDatatable\Filters\TextFilter;

beforeEach(function () {
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email');
        $table->string('status')->default('active');
        $table->timestamps();
    });

    // Create test data
    $user = new class extends Model {
        protected $table = 'users';
        protected $guarded = [];
    };

    $user::create(['name' => 'John Doe', 'email' => 'john@example.com', 'status' => 'active']);
    $user::create(['name' => 'Jane Smith', 'email' => 'jane@example.com', 'status' => 'inactive']);
    $user::create(['name' => 'Bob Johnson', 'email' => 'bob@example.com', 'status' => 'active']);
    $user::create(['name' => 'Alice Brown', 'email' => 'alice@example.com', 'status' => 'pending']);
    $user::create(['name' => 'Charlie Wilson', 'email' => 'charlie@example.com', 'status' => 'active']);

    config()->set('voltia-datatable.default_per_page', 15);
    config()->set('voltia-datatable.max_per_page', 100);
});

afterEach(function () {
    Schema::dropIfExists('users');
});

it('can make datatable with basic data', function () {
    $user = new class extends Model {
        protected $table = 'users';
    };

    $datatable = new class extends Table {
        public function query(): Builder
        {
            return (new class extends Model {
                protected $table = 'users';
            })->query();
        }

        public function columns(): array
        {
            return [
                Column::make('id')->sortable(),
                Column::make('name')->sortable()->searchable(),
                Column::make('email')->searchable(),
            ];
        }
    };

    $result = $datatable->make();

    expect($result)
        ->toHaveKeys(['rows', 'columns', 'meta', 'sort', 'search', 'filters'])
        ->and($result['rows'])->toHaveCount(5)
        ->and($result['columns'])->toHaveCount(3)
        ->and($result['meta']['total'])->toBe(5)
        ->and($result['meta']['perPage'])->toBe(15);
});

it('applies search correctly', function () {
    request()->merge(['search' => 'John']);

    $datatable = new class extends Table {
        public function query(): Builder
        {
            return (new class extends Model {
                protected $table = 'users';
            })->query();
        }

        public function columns(): array
        {
            return [
                Column::make('id'),
                Column::make('name')->searchable(),
                Column::make('email')->searchable(),
            ];
        }
    };

    $result = $datatable->make();

    expect($result['rows'])->toHaveCount(2) // John Doe and Bob Johnson
        ->and($result['search'])->toBe('John');
});

it('applies sorting correctly', function () {
    request()->merge(['sortBy' => 'name', 'sortDirection' => 'asc']);

    $datatable = new class extends Table {
        public function query(): Builder
        {
            return (new class extends Model {
                protected $table = 'users';
            })->query();
        }

        public function columns(): array
        {
            return [
                Column::make('id'),
                Column::make('name')->sortable(),
            ];
        }
    };

    $result = $datatable->make();

    expect($result['rows'][0]['name'])->toBe('Alice Brown')
        ->and($result['sort']['sortBy'])->toBe('name')
        ->and($result['sort']['sortDirection'])->toBe('asc');
});

it('applies filters correctly', function () {
    request()->merge(['filters' => ['status' => 'active']]);

    $datatable = new class extends Table {
        public function query(): Builder
        {
            return (new class extends Model {
                protected $table = 'users';
            })->query();
        }

        public function columns(): array
        {
            return [
                Column::make('id'),
                Column::make('name'),
                Column::make('status'),
            ];
        }

        protected function filters(): array
        {
            return [
                'status' => new SelectFilter('status', [
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                    'pending' => 'Pending',
                ]),
            ];
        }
    };

    $result = $datatable->make();

    expect($result['rows'])->toHaveCount(3)
        ->and($result['filters']['status']['value'])->toBe('active');
});

it('respects perPage parameter', function () {
    request()->merge(['perPage' => 2]);

    $datatable = new class extends Table {
        public function query(): Builder
        {
            return (new class extends Model {
                protected $table = 'users';
            })->query();
        }

        public function columns(): array
        {
            return [
                Column::make('id'),
                Column::make('name'),
            ];
        }
    };

    $result = $datatable->make();

    expect($result['rows'])->toHaveCount(2)
        ->and($result['meta']['perPage'])->toBe(2)
        ->and($result['meta']['total'])->toBe(5);
});

it('excludes hidden columns from output', function () {
    $datatable = new class extends Table {
        public function query(): Builder
        {
            return (new class extends Model {
                protected $table = 'users';
            })->query();
        }

        public function columns(): array
        {
            return [
                Column::make('id')->hidden(),
                Column::make('name'),
                Column::make('email'),
            ];
        }
    };

    $result = $datatable->make();

    expect($result['columns'])->toHaveCount(2)
        ->and(collect($result['columns'])->pluck('key')->toArray())->not->toContain('id')
        ->and($result['rows'][0])->toHaveKey('id'); // Data still included
});

it('prevents perPage exceeding max limit', function () {
    request()->merge(['perPage' => 999]);

    $datatable = new class extends Table {
        public function query(): Builder
        {
            return (new class extends Model {
                protected $table = 'users';
            })->query();
        }

        public function columns(): array
        {
            return [Column::make('id')];
        }
    };

    $result = $datatable->make();

    expect($result['meta']['perPage'])->toBe(100); // Limited by max_per_page
});

it('only allows sorting on sortable columns', function () {
    request()->merge(['sortBy' => 'email', 'sortDirection' => 'asc']);

    $datatable = new class extends Table {
        public function query(): Builder
        {
            return (new class extends Model {
                protected $table = 'users';
            })->query();
        }

        public function columns(): array
        {
            return [
                Column::make('id'),
                Column::make('name')->sortable(),
                Column::make('email'), // Not sortable
            ];
        }
    };

    $result = $datatable->make();

    // Should not apply sorting since email is not sortable
    expect($result['sort']['sortBy'])->toBe('email')
        ->and($result['rows'])->toHaveCount(5);
});

it('handles pagination correctly', function () {
    request()->merge(['page' => 2, 'perPage' => 2]);

    $datatable = new class extends Table {
        public function query(): Builder
        {
            return (new class extends Model {
                protected $table = 'users';
            })->query();
        }

        public function columns(): array
        {
            return [Column::make('id'), Column::make('name')];
        }
    };

    $result = $datatable->make();

    expect($result['meta']['page'])->toBe(2)
        ->and($result['meta']['from'])->toBe(3)
        ->and($result['meta']['to'])->toBe(4)
        ->and($result['rows'])->toHaveCount(2);
});

it('returns empty filters when no filters defined', function () {
    $datatable = new class extends Table {
        public function query(): Builder
        {
            return (new class extends Model {
                protected $table = 'users';
            })->query();
        }

        public function columns(): array
        {
            return [Column::make('id')];
        }
    };

    $result = $datatable->make();

    expect($result['filters'])->toBeEmpty();
});
