<?php

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Yuisa\VoltiaDatatable\Concerns\WithSearch;
use Yuisa\VoltiaDatatable\Column;

beforeEach(function () {
    Schema::create('test_users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email');
        $table->text('bio')->nullable();
        $table->timestamps();
    });

    $user = new class extends Model {
        protected $table = 'test_users';
        protected $guarded = [];
    };

    $user::create(['name' => 'John Doe', 'email' => 'john@example.com', 'bio' => 'Developer']);
    $user::create(['name' => 'Jane Smith', 'email' => 'jane@example.com', 'bio' => 'Designer']);
    $user::create(['name' => 'Bob Wilson', 'email' => 'bob@example.com', 'bio' => 'Manager']);
});

afterEach(function () {
    Schema::dropIfExists('test_users');
});

it('applies search to searchable columns', function () {
    $model = new class extends Model {
        protected $table = 'test_users';
    };

    $trait = new class {
        use WithSearch;

        protected array $columns = [];

        public function __construct()
        {
            $this->search = 'John';
            $this->columns = [
                Column::make('name')->searchable(),
                Column::make('email')->searchable(),
                Column::make('bio'),
            ];
        }

        public function testApplySearch($query)
        {
            $this->applySearch($query);
        }
    };

    $query = $model->query();
    $trait->testApplySearch($query);

    $results = $query->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->name)->toBe('John Doe');
});

it('searches across multiple columns', function () {
    $model = new class extends Model {
        protected $table = 'test_users';
    };

    $trait = new class {
        use WithSearch;

        protected array $columns = [];

        public function __construct()
        {
            $this->search = 'example.com';
            $this->columns = [
                Column::make('name')->searchable(),
                Column::make('email')->searchable(),
            ];
        }

        public function testApplySearch($query)
        {
            $this->applySearch($query);
        }
    };

    $query = $model->query();
    $trait->testApplySearch($query);

    $results = $query->get();
    expect($results)->toHaveCount(3);
});

it('does not apply search when search is null', function () {
    $model = new class extends Model {
        protected $table = 'test_users';
    };

    $trait = new class {
        use WithSearch;

        protected array $columns = [];

        public function __construct()
        {
            $this->search = null;
            $this->columns = [
                Column::make('name')->searchable(),
            ];
        }

        public function testApplySearch($query)
        {
            $this->applySearch($query);
        }
    };

    $query = $model->query();
    $originalSql = $query->toSql();
    $trait->testApplySearch($query);

    expect($query->toSql())->toBe($originalSql);
});

it('does not apply search when no searchable columns', function () {
    $model = new class extends Model {
        protected $table = 'test_users';
    };

    $trait = new class {
        use WithSearch;

        protected array $columns = [];

        public function __construct()
        {
            $this->search = 'John';
            $this->columns = [
                Column::make('name'), // Not searchable
                Column::make('email'), // Not searchable
            ];
        }

        public function testApplySearch($query)
        {
            $this->applySearch($query);
        }
    };

    $query = $model->query();
    $originalSql = $query->toSql();
    $trait->testApplySearch($query);

    expect($query->toSql())->toBe($originalSql);
});

it('uses LIKE operator for partial matching', function () {
    $model = new class extends Model {
        protected $table = 'test_users';
    };

    $trait = new class {
        use WithSearch;

        protected array $columns = [];

        public function __construct()
        {
            $this->search = 'Doe';
            $this->columns = [
                Column::make('name')->searchable(),
            ];
        }

        public function testApplySearch($query)
        {
            $this->applySearch($query);
        }
    };

    $query = $model->query();
    $trait->testApplySearch($query);

    expect($query->toSql())->toContain('like');
    expect($query->getBindings()[0])->toContain('%Doe%');
});
