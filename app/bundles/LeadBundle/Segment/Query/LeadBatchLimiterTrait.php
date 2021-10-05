<?php

namespace Mautic\LeadBundle\Segment\Query;

trait LeadBatchLimiterTrait
{
    public function addMinMaxLimiters(QueryBuilder $queryBuilder, array $batchLimiters, string $tableName, string $columnName = 'lead_id'): void
    {
        $leadsTableAlias = $queryBuilder->getTableAlias(MAUTIC_TABLE_PREFIX.$tableName);

        if (!empty($batchLimiters['minId']) && !empty($batchLimiters['maxId'])) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->comparison($leadsTableAlias.'.'.$columnName, 'BETWEEN', "{$batchLimiters['minId']} and {$batchLimiters['maxId']}")
            );
        } elseif (!empty($batchLimiters['maxId'])) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->lte($leadsTableAlias.'.'.$columnName, $batchLimiters['maxId'])
            );
        } elseif (!empty($batchLimiters['minId'])) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->gte($leadsTableAlias.'.'.$columnName, $queryBuilder->expr()->literal((int) $batchLimiters['minId']))
            );
        }
    }

    public function addLeadLimiter(QueryBuilder $queryBuilder, array $batchLimiters, string $tableName, string $columnName = 'lead_id'): void
    {
        $leadsTableAlias = $queryBuilder->getTableAlias(MAUTIC_TABLE_PREFIX.$tableName);

        if (empty($batchLimiters['lead_id'])) {
            return;
        }

        $queryBuilder->andWhere($leadsTableAlias.'.'.$columnName.' = :leadId')
            ->setParameter('leadId', $batchLimiters['lead_id']);
    }
}
