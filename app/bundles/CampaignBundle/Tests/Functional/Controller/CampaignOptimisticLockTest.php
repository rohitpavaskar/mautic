<?php

declare(strict_types=1);

/*
 * @copyright   2021 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\CampaignBundle\Tests\Functional\Controller;

use Mautic\CampaignBundle\Entity\Campaign;
use Mautic\CampaignBundle\Entity\Event;
use Mautic\CoreBundle\Test\MauticMysqlTestCase;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Entity\LeadList;
use PHPUnit\Framework\Assert;

class CampaignOptimisticLockTest extends MauticMysqlTestCase
{
    use CampaignControllerTrait;

    /**
     * @var string
     */
    private const OPTIMISTIC_LOCK_ERROR = 'The record you are updating has been changed by someone else in the meantime. Please refresh the browser window and re-submit your changes.';

    public function testOptimisticLock(): void
    {
        $campaign = $this->setupCampaign();
        $version  = $campaign->getVersion();

        // version should be incremented as campaign's "modified by user" is updated
        $this->refreshAndSubmitForm($campaign, ++$version);

        // version should not be incremented as there are no changes
        $this->refreshAndSubmitForm($campaign, $version);

        // version should be incremented as there are changes
        $this->refreshAndSubmitForm($campaign, ++$version, [
            'campaign[allowRestart]' => '1',
            'campaign[isPublished]'  => '1',
        ]);

        // version should not be incremented as there are no changes
        $this->refreshAndSubmitForm($campaign, $version);

        // refresh the page
        $pageCrawler = $this->refreshPage($campaign);

        // we should not get an optimistic lock error as the page was refreshed, version should be incremented
        $crawler = $this->submitForm($pageCrawler, $campaign, ++$version, [
            'campaign[allowRestart]' => '0',
        ]);
        Assert::assertStringNotContainsString(self::OPTIMISTIC_LOCK_ERROR, $crawler->text());

        // we should get an optimistic lock error as the page wasn't refreshed
        $crawler = $this->submitForm($pageCrawler, $campaign, $version, [
            'campaign[isPublished]' => '1',
        ]);
        Assert::assertStringContainsString(self::OPTIMISTIC_LOCK_ERROR, $crawler->text());

        // we should get an optimistic lock error even if there is no change
        $crawler = $this->submitForm($pageCrawler, $campaign, $version);
        Assert::assertStringContainsString(self::OPTIMISTIC_LOCK_ERROR, $crawler->text());
    }

    private function setupCampaign(): Campaign
    {
        $leadList = new LeadList();
        $leadList->setName('Test list');
        $leadList->setAlias('test-list');
        $this->em->persist($leadList);

        $campaign = new Campaign();
        $campaign->setName('Test campaign');
        $campaign->setIsPublished(true);
        $campaign->setPublishUp(new \DateTime());
        $campaign->addList($leadList);
        $this->em->persist($campaign);

        $lead = new Lead();
        $lead->setFirstname('Test Lead');
        $this->em->persist($lead);

        $campaignEvent = new Event();
        $campaignEvent->setCampaign($campaign);
        $campaignEvent->setName('Send Email 1');
        $campaignEvent->setType('email.send');
        $campaignEvent->setEventType('action');
        $campaignEvent->setProperties([]);
        $this->em->persist($campaignEvent);

        $this->em->flush();
        $this->em->clear();

        return $campaign;
    }
}
