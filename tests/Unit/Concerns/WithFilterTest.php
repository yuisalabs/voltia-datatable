<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Yuisalabs\VoltiaDatatable\Concerns\WithFilter;
use Yuisalabs\VoltiaDatatable\Filters\SelectFilter;
use Yuisalabs\VoltiaDatatable\Filters\TextFilter;

beforeEach(function () {
    Schema::create('test_posts', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->string('status');
        $table->timestamps();
    });

    $post = new class extends Model {
        protected $table = 'test_posts';
        protected $guarded = [];
    };

    $post::create(['title' => 'Post 1', 'status' => 'published']);
    $post::create(['title' => 'Post 2', 'status' => 'draft']);
    $post::create(['title' => 'Post 3', 'status' => 'published']);
});

afterEach(function () {
    Schema::dropIfExists('test_posts');
});

it('applies filters to query', function () {
    request()->merge(['filters' => ['status' => 'published']]);

    $model = new class extends Model {
        protected $table = 'test_posts';
    };

    $trait = new class {
        use WithFilter;

        protected array $filters = [];

        public function __construct()
        {
            $this->filters = [
                'status' => new SelectFilter('status', [
                    'published' => 'Published',
                    'draft' => 'Draft',
                ]),
            ];
        }

        public function testApplyFilters($query)
        {
            $this->applyFilters($query);
        }
    };

    $query = $model->query();
    $trait->testApplyFilters($query);

    $results = $query->get();
    expect($results)->toHaveCount(2)
        ->and($results->every(fn ($post) => $post->status === 'published'))->toBeTrue();
});

it('applies multiple filters', function () {
    request()->merge(['filters' => [
        'status' => 'published',
        'title' => 'Post 1',
    ]]);

    $model = new class extends Model {
        protected $table = 'test_posts';
    };

    $trait = new class {
        use WithFilter;

        protected array $filters = [];

        public function __construct()
        {
            $this->filters = [
                'status' => new SelectFilter('status', [
                    'published' => 'Published',
                    'draft' => 'Draft',
                ]),
                'title' => new TextFilter('title'),
            ];
        }

        public function testApplyFilters($query)
        {
            $this->applyFilters($query);
        }
    };

    $query = $model->query();
    $trait->testApplyFilters($query);

    $results = $query->get();
    expect($results)->toHaveCount(1)
        ->and($results->first()->title)->toBe('Post 1');
});

it('returns correct filters metadata', function () {
    request()->merge(['filters' => ['status' => 'published']]);

    $trait = new class {
        use WithFilter;

        protected array $filters = [];

        public function __construct()
        {
            $this->filters = [
                'status' => new SelectFilter('status', [
                    'published' => 'Published',
                    'draft' => 'Draft',
                ]),
            ];
        }

        public function testFiltersMeta()
        {
            return $this->filtersMeta();
        }
    };

    $meta = $trait->testFiltersMeta();

    expect($meta)->toHaveKey('status')
        ->and($meta['status']['value'])->toBe('published')
        ->and($meta['status']['type'])->toBe('select')
        ->and($meta['status']['options'])->toHaveKeys(['published', 'draft']);
});

it('handles empty filters', function () {
    $model = new class extends Model {
        protected $table = 'test_posts';
    };

    $trait = new class {
        use WithFilter;

        protected array $filters = [];

        public function testApplyFilters($query)
        {
            $this->applyFilters($query);
        }
    };

    $query = $model->query();
    $originalSql = $query->toSql();
    $trait->testApplyFilters($query);

    expect($query->toSql())->toBe($originalSql);
});
