<?php

declare(strict_types=1);

/* @var string $className the new migration class name without namespace */
/* @var string $namespace the new migration class namespace */

echo "<?php\n";
if (!empty($namespace)) {
    echo "\nnamespace {$namespace};\n";
}
?>

use Whirlwind\MigrationCore\Migration;
use Whirlwind\MigrationCore\MigrationException;

/**
 * Class <?php echo $className . "\n" ?>
 */
class <?php echo $className ?> extends Migration
{
    public function up(): void
    {

    }

    public function down(): void
    {
        throw new MigrationException("<?php echo $className ?> cannot be reverted.");
    }
}
