# Migration Core

Common interface for implementing Whirlwind framework database migrations.
## Implementation notes

1. Create implementation of `MigrationTableGatewayInterface`. Method `queryOrCreateCollection`
must create migration collection if it not exists.
```php
class DummyMigrationTableGateway extends MongoTableGateway implements MigrationTableGatewayInterface
{
    protected string $collectionName = 'migrations';
    
    public function queryOrCreateCollection(array $conditions = [], int $limit = 0, array $order = []): array
    {
        $collection = $this->connection->listCollections(['name' => $this->collectionName]);

        if (!$collection) {
            $this->createCollection($this->collectionName);
        }
        
        return $this->queryAll($conditions, $order, $limit)
    }
}
```
2. Create your database implementation for `BlueprintInterface` and `BlueprintFactoryInterface`.
All operation such as creating, modifying, dropping and others will be handled there.
```php
class DummyBlueprint implements \Whirlwind\MigrationCore\BlueprintInterface
{
    protected string $collectionName;
    protected Query $current; // your database query builder implementation
    protected array $queries = [];
    
    public function __construct(string $collectionName) {
        $this->collectionName = $collectionName;
    }
    
    public function build(ConnectionInterface $connection): void
    {
        foreach ($queries as $query) {
            $query->execute($connection);
        }
    }
    
    public function create(callable $callback): void
    {
        $this->current = new Query();
        $callback($this);
        $this->queries[] = $this->current;
    }
    
    public function drop(): void
    {
        $this->queries[] = (new Query())->dropCollection($this->collectionName);
    }
    public function dropIfExists(): void
    {
        $this->queries[] = (new Query())->dropCollectionIfExists($this->collectionName);
    }
    public function createIfNotExists(callable $callback)
    {
        $this->create($callback);
    }
}
```
3. Create ServiceProvider for your migration tool implementation. Bind your implementations with interfaces.
Also, you need to configure your module by adding `Config` dependency. For example
```php

class MyDatabaseServiceProvider extends \League\Container\ServiceProvider\AbstractServiceProvider
{
    public function register(): void
    {
         $container->add(
            \Whirlwind\MigrationCore\Config\MigrationPaths::class,
            fn() => new \Whirlwind\MigrationCore\Config\MigrationPaths([
                new \Whirlwind\MigrationCore\Config\MigrationPath(
                    'path/to/your/migrations',
                    'Your\\Migration\\Namespace'
                ),
                new \Whirlwind\MigrationCore\Config\MigrationPath(
                    'path/to/your/another/migrations',
                    'Your\\Another\\Migration\\Namespace'
                ),
            ])
        );

        $container->add(
            \Whirlwind\MigrationCore\Config\Config::class,
            fn () => new \Whirlwind\MigrationCore\Config\Config(
                $container->get(\Whirlwind\MigrationCore\Config\MigrationPaths::class),
                '/path/to/your/template' // by default using template from core package
            )
        );   
    }
}
```
4. Add console commands routes.
```php
/**
* @var \Whirlwind\App\Console\Application $app
 */
$app->addCommand('migrate:create', \Whirlwind\MigrationCore\Command\Migration\CreateCommand::class);
$app->addCommand('migrate:install', \Whirlwind\MigrationCore\Command\Migration\InstallCommand::class);
$app->addCommand('migrate:rollback', \Whirlwind\MigrationCore\Command\Migration\RollbackCommand::class);
$app->addCommand('migrate:status', \Whirlwind\MigrationCore\Command\Migration\StatusCommand::class);
```