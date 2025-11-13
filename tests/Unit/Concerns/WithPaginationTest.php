<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Yuisa\VoltiaDatatable\Concerns\WithPagination;

beforeEach(function () {
    Schema::create('test_items', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });

    $item = new class extends Model {
        protected $table = 'test_items';
        protected $guarded = [];
    };

    for ($i = 1; $i <= 50; $i++) {
        $item::create(['name' => "Item $i"]);
    }

    config()->set('voltia-datatable.default_per_page', 15);
});

afterEach(function () {
    Schema::dropIfExists('test_items');
});

it('paginates query results', function () {
    $model = new class extends Model {
        protected $table = 'test_items';
    };

    $trait = new class {
        use WithPagination;

        public function __construct()
        {
            $this->perPage = 10;
        }

        public function testPaginate($query)
        {
            return $this->paginate($query);
        }
    };

    $query = $model->query();
    $paginator = $trait->testPaginate($query);

    expect($paginator->total())->toBe(50)
        ->and($paginator->perPage())->toBe(10)
        ->and($paginator->count())->toBe(10)
        ->and($paginator->currentPage())->toBe(1);
});

it('respects perPage setting', function () {
    $model = new class extends Model {
        protected $table = 'test_items';
    };

    $trait = new class {
        use WithPagination;

        public function __construct()
        {
            $this->perPage = 25;
        }

        public function testPaginate($query)
        {
            return $this->paginate($query);
        }
    };

    $query = $model->query();
    $paginator = $trait->testPaginate($query);

    expect($paginator->perPage())->toBe(25)
        ->and($paginator->count())->toBe(25);
});

it('returns correct page information', function () {
    request()->merge(['page' => 2]);

    $model = new class extends Model {
        protected $table = 'test_items';
    };

    $trait = new class {
        use WithPagination;

        public function __construct()
        {
            $this->perPage = 10;
        }

        public function testPaginate($query)
        {
            return $this->paginate($query);
        }
    };

    $query = $model->query();
    $paginator = $trait->testPaginate($query);

    expect($paginator->currentPage())->toBe(2)
        ->and($paginator->firstItem())->toBe(11)
        ->and($paginator->lastItem())->toBe(20);
});

it('handles last page correctly', function () {
    request()->merge(['page' => 5]);

    $model = new class extends Model {
        protected $table = 'test_items';
    };

    $trait = new class {
        use WithPagination;

        public function __construct()
        {
            $this->perPage = 10;
        }

        public function testPaginate($query)
        {
            return $this->paginate($query);
        }
    };

    $query = $model->query();
    $paginator = $trait->testPaginate($query);

    expect($paginator->currentPage())->toBe(5)
        ->and($paginator->count())->toBe(10)
        ->and($paginator->hasMorePages())->toBeFalse();
});
