<?php

declare(strict_types=1);

namespace Mautic\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Mautic\CoreBundle\Doctrine\AbstractMauticMigration;

final class Version20201104215140 extends AbstractMauticMigration
{
    protected const TABLE_NAME = 'email_stat_replies';
    private const COLUMN       = 'stat_id';

    public function up(Schema $schema): void
    {
        $indexName = $this->getDefaultIndexName();
        $tableName = $this->getTableName();
        $this->skipIf(!$this->hasIndex(), sprintf('%s table doesn\'t contain the duplicated index (%s).', $tableName, $indexName));
        $this->addSql(sprintf('ALTER TABLE `%s` DROP INDEX `%s`;', $tableName, $indexName));
    }

    public function down(Schema $schema): void
    {
        $indexName = $this->getDefaultIndexName();
        $tableName = $this->getTableName();
        $this->skipIf($this->hasIndex(), sprintf('%s table already contains %s index.', $tableName, $indexName));
        $this->addSql(sprintf('ALTER TABLE `%s` ADD INDEX `%s` (`%s` ASC);', $tableName, $indexName, static::COLUMN));
    }

    private function getTableName(): string
    {
        return $this->prefix.static::TABLE_NAME;
    }

    private function getDefaultIndexName(): string
    {
        return $this->getIndex() ?? $this->generatePropertyName($this->getTableName(), 'idx', [static::COLUMN]);
    }

    private function hasIndex(): bool
    {
        return (bool) $this->getIndex();
    }

    private function getIndex(): ?string
    {
        $indexes = $this->connection->createSchemaManager()->listTableIndexes($this->getTableName());

        foreach ($indexes as $index) {
            if (!$index->spansColumns([static::COLUMN])) {
                continue;
            }

            return $index->getName();
        }

        return null;
    }
}
