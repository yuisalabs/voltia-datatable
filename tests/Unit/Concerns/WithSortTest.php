<?php

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Yuisa\VoltiaDatatable\Concerns\WithSort;
use Yuisa\VoltiaDatatable\Column;

beforeEach(function () {
    Schema::create('test_products', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->decimal('price', 10, 2);
        $table->integer('stock');
        $table->timestamps();
    });

    $product = new class extends Model {
        protected $table = 'test_products';
        protected $guarded = [];
    };

    $product::create(['name' => 'Product A', 'price' => 100, 'stock' => 10]);
    $product::create(['name' => 'Product C', 'price' => 50, 'stock' => 5]);
    $product::create(['name' => 'Product B', 'price' => 150, 'stock' => 20]);
});

afterEach(function () {
    Schema::dropIfExists('test_products');
});

it('applies ascending sort', function () {
    $model = new class extends Model {
        protected $table = 'test_products';
    };

    $trait = new class {
        use WithSort;

        protected array $columns = [];

        public function __construct()
        {
            $this->sortKey = 'name';
            $this->sortDirection = 'asc';
            $this->columns = [
                Column::make('name')->sortable(),
                Column::make('price')->sortable(),
            ];
        }

        public function testApplySort($query)
        {
            $this->applySort($query);
        }
    };

    $query = $model->query();
    $trait->testApplySort($query);

    $results = $query->get();
    expect($results[0]->name)->toBe('Product A')
        ->and($results[1]->name)->toBe('Product B')
        ->and($results[2]->name)->toBe('Product C');
});

it('applies descending sort', function () {
    $model = new class extends Model {
        protected $table = 'test_products';
    };

    $trait = new class {
        use WithSort;

        protected array $columns = [];

        public function __construct()
        {
            $this->sortKey = 'price';
            $this->sortDirection = 'desc';
            $this->columns = [
                Column::make('name')->sortable(),
                Column::make('price')->sortable(),
            ];
        }

        public function testApplySort($query)
        {
            $this->applySort($query);
        }
    };

    $query = $model->query();
    $trait->testApplySort($query);

    $results = $query->get();
    expect((float)$results[0]->price)->toBe(150.0)
        ->and((float)$results[2]->price)->toBe(50.0);
});

it('does not apply sort when sortKey is null', function () {
    $model = new class extends Model {
        protected $table = 'test_products';
    };

    $trait = new class {
        use WithSort;

        protected array $columns = [];

        public function __construct()
        {
            $this->sortKey = null;
            $this->sortDirection = 'asc';
            $this->columns = [
                Column::make('name')->sortable(),
            ];
        }

        public function testApplySort($query)
        {
            $this->applySort($query);
        }
    };

    $query = $model->query();
    $originalSql = $query->toSql();
    $trait->testApplySort($query);

    expect($query->toSql())->toBe($originalSql);
});

it('does not apply sort when column is not sortable', function () {
    $model = new class extends Model {
        protected $table = 'test_products';
    };

    $trait = new class {
        use WithSort;

        protected array $columns = [];

        public function __construct()
        {
            $this->sortKey = 'stock';
            $this->sortDirection = 'asc';
            $this->columns = [
                Column::make('name')->sortable(),
                Column::make('price')->sortable(),
                Column::make('stock'), // Not sortable
            ];
        }

        public function testApplySort($query)
        {
            $this->applySort($query);
        }
    };

    $query = $model->query();
    $originalSql = $query->toSql();
    $trait->testApplySort($query);

    expect($query->toSql())->toBe($originalSql);
});

it('defaults to asc when sortDirection is invalid', function () {
    $model = new class extends Model {
        protected $table = 'test_products';
    };

    $trait = new class {
        use WithSort;

        protected array $columns = [];

        public function __construct()
        {
            $this->sortKey = 'name';
            $this->sortDirection = 'invalid';
            $this->columns = [
                Column::make('name')->sortable(),
            ];
        }

        public function testApplySort($query)
        {
            $this->applySort($query);
        }
    };

    $query = $model->query();
    $trait->testApplySort($query);

    expect($query->toSql())->toMatch('/order by.*name.*asc/');
});
