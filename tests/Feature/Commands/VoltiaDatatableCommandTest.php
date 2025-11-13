<?php

use Illuminate\Support\Facades\File;
use Yuisa\VoltiaDatatable\Commands\VoltiaDatatableCommand;

beforeEach(function () {
    // Clean up any existing DataTable files
    if (File::isDirectory(app_path('Tables'))) {
        File::deleteDirectory(app_path('Tables'));
    }
});

afterEach(function () {
    // Clean up after tests
    if (File::isDirectory(app_path('Tables'))) {
        File::deleteDirectory(app_path('Tables'));
    }
});

it('can generate a datatable class', function () {
    $this->artisan(VoltiaDatatableCommand::class, ['name' => 'User'])
        ->assertSuccessful();

    expect(File::exists(app_path('Tables/UserDataTable.php')))->toBeTrue();
});

it('appends DataTable to class name if not provided', function () {
    $this->artisan(VoltiaDatatableCommand::class, ['name' => 'Product'])
        ->assertSuccessful();

    expect(File::exists(app_path('Tables/ProductDataTable.php')))->toBeTrue();
});

it('does not duplicate DataTable suffix', function () {
    $this->artisan(VoltiaDatatableCommand::class, ['name' => 'OrderDataTable'])
        ->assertSuccessful();

    $content = File::get(app_path('Tables/OrderDataTable.php'));
    expect($content)->toContain('class OrderDataTable extends Table')
        ->and($content)->not->toContain('OrderDataTableDataTable');
});

it('uses custom model name when provided', function () {
    $this->artisan(VoltiaDatatableCommand::class, [
        'name' => 'User',
        '--model' => 'Customer',
    ])->assertSuccessful();

    $content = File::get(app_path('Tables/UserDataTable.php'));
    expect($content)->toContain('use App\Models\Customer;')
        ->and($content)->toContain('Customer::query()');
});

it('infers model name from datatable name', function () {
    $this->artisan(VoltiaDatatableCommand::class, ['name' => 'Users'])
        ->assertSuccessful();

    $content = File::get(app_path('Tables/UsersDataTable.php'));
    expect($content)->toContain('use App\Models\User;');
});

it('creates Tables directory if it does not exist', function () {
    expect(File::isDirectory(app_path('Tables')))->toBeFalse();

    $this->artisan(VoltiaDatatableCommand::class, ['name' => 'User'])
        ->assertSuccessful();

    expect(File::isDirectory(app_path('Tables')))->toBeTrue();
});

it('fails when datatable already exists', function () {
    // Create first time
    $this->artisan(VoltiaDatatableCommand::class, ['name' => 'User'])
        ->assertSuccessful();

    // Try to create again
    $this->artisan(VoltiaDatatableCommand::class, ['name' => 'User'])
        ->assertFailed();
});

it('generates valid PHP syntax', function () {
    $this->artisan(VoltiaDatatableCommand::class, ['name' => 'Product'])
        ->assertSuccessful();

    $file = app_path('Tables/ProductDataTable.php');
    expect(File::exists($file))->toBeTrue();

    // Check if file has valid PHP syntax
    $content = File::get($file);
    expect($content)->toContain('<?php')
        ->and($content)->toContain('namespace App\Tables;')
        ->and($content)->toContain('class ProductDataTable extends Table')
        ->and($content)->toContain('public function query(): Builder')
        ->and($content)->toContain('public function columns(): array')
        ->and($content)->toContain('protected function filters(): array');
});

it('includes example columns in generated file', function () {
    $this->artisan(VoltiaDatatableCommand::class, ['name' => 'User'])
        ->assertSuccessful();

    $content = File::get(app_path('Tables/UserDataTable.php'));
    expect($content)->toContain('Column::make')
        ->and($content)->toContain('->sortable()')
        ->and($content)->toContain('->searchable()');
});

it('includes commented filter examples', function () {
    $this->artisan(VoltiaDatatableCommand::class, ['name' => 'User'])
        ->assertSuccessful();

    $content = File::get(app_path('Tables/UserDataTable.php'));
    expect($content)->toContain('// \'status\' => new SelectFilter')
        ->and($content)->toContain('// \'search\' => new TextFilter')
        ->and($content)->toContain('// \'is_verified\' => new BooleanFilter')
        ->and($content)->toContain('// \'created_at\' => new DateRangeFilter');
});

it('displays helpful next steps after generation', function () {
    $this->artisan(VoltiaDatatableCommand::class, ['name' => 'User'])
        ->expectsOutput('Next steps:')
        ->assertSuccessful();
});
