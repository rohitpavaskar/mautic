<?php

declare(strict_types=1);

namespace Mautic\InstallBundle\InstallFixtures\ORM;

use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mautic\CoreBundle\Doctrine\Common\DataFixtures\Event\PreExecuteEvent;
use Mautic\CoreBundle\Doctrine\Helper\IndexSchemaHelper;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RemoveDuplicatedIndexOnStatIdColumnsFromEmailStatRepliesTable extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface, FixtureGroupInterface
{
    use ContainerAwareTrait;

    private const TABLE_NAME = 'email_stat_replies';
    private const COLUMN     = ['stat_id'];

    public function __construct(IndexSchemaHelper $indexSchemaHelper, EventDispatcherInterface $eventDispatcher)
    {
        $eventDispatcher->addListener(PreExecuteEvent::class, function (PreExecuteEvent $event) use ($indexSchemaHelper): void {
            $table   = $this->container->getParameter('mautic.db_table_prefix').self::TABLE_NAME;
            $indexes = $event->getEntityManager()->getConnection()->createSchemaManager()->listTableIndexes($table);
            $indexSchemaHelper->setName(self::TABLE_NAME);
            foreach ($indexes as $index) {
                if (!$index->spansColumns(self::COLUMN)) {
                    continue;
                }

                $indexSchemaHelper->dropIndex(self::COLUMN, $index->getName())->executeChanges();
                break;
            }
        });
    }

    public static function getGroups(): array
    {
        return ['group_install', 'group_mautic_install_data'];
    }

    public function load(ObjectManager $manager): void
    {
        // https://www.doctrine-project.org/projects/doctrine-migrations/en/stable/explanation/implicit-commits
    }

    public function getOrder(): int
    {
        return 7;
    }
}
