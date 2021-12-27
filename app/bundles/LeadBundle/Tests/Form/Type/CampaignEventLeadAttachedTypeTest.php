<?php

declare(strict_types=1);

/*
 * @copyright   2020 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\LeadBundle\Tests\Form\Type;

use Mautic\LeadBundle\Form\Type\CampaignEventLeadAttachedType;
use Mautic\LeadBundle\Model\FieldModel;
use Mautic\LeadBundle\Model\LeadModel;
use Mautic\LeadBundle\Model\ListModel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;

final class CampaignEventLeadAttachedTypeTest extends TestCase
{
    /**
     * @var CampaignEventLeadAttachedType
     */
    private $campaignEventLeadAttachedType;

    /**
     * @var FormBuilderInterface<FormBuilderInterface>
     */
    private $formBuilderInterface;

    protected function setUp(): void
    {
        parent::setUp();

        $translator = $this->createMock(TranslatorInterface::class);
        $leadModel  = $this->createMock(LeadModel::class);
        $fieldModel = $this->createMock(FieldModel::class);
        $listModel  = $this->createMock(ListModel::class);

        $listModel->method('getOperatorsForFieldType')->willReturn(
            ['Greater than' => 'gt', 'Lesser than' => 'lt']
        );

        $this->campaignEventLeadAttachedType    = new CampaignEventLeadAttachedType(
            $translator,
            $leadModel,
            $fieldModel,
            $listModel
        );
        $this->formBuilderInterface = $this->createMock(FormBuilderInterface::class);
    }

    public function testThatGetBlockPrefixReturnsAValue(): void
    {
        $blockPrefix = $this->campaignEventLeadAttachedType->getBlockPrefix();
        $this->assertNotEmpty($blockPrefix);
        $this->assertTrue(is_string($blockPrefix));
    }

    public function testThatBuildFormMethodAddsSegmentRebuildTimeWarningOption(): void
    {
        $parameters = $this->parameters();
        $this->formBuilderInterface->expects($this->exactly(4))
            ->method('add')
            ->withConsecutive(
                $parameters[0],
                $parameters[1],
                $parameters[2],
                $parameters[3]
            );

        $this->campaignEventLeadAttachedType->buildForm($this->formBuilderInterface, []);
    }

    /**
     * @return array[]
     */
    private function parameters(): array
    {
        return [
            [
                'timestamp',
                ChoiceType::class,
                [
                    'label'             => 'mautic.lead.lead.events.campaigns.timestamp',
                    'label_attr'        => ['class' => 'control-label'],
                    'multiple'          => false,
                    'choices'           => ['Campaign Start Date' => 1],
                    'attr'              => ['class' => 'form-control'],
                    'required'          => true,
                ],
            ], [
                'operator',
                ChoiceType::class,
                [
                    'label'             => 'mautic.lead.lead.events.campaigns.operator',
                    'multiple'          => false,
                    'choices'           => ['Greater than' => 'gt', 'Lesser than' => 'lt'],
                    'required'          => true,
                    'label_attr'        => ['class' => 'control-label'],
                    'attr'              => [
                        'class'        => 'form-control',
                    ],
                ],
            ], [
                'triggerInterval',
                NumberType::class,
                [
                    'label'     => 'mautic.lead.lead.events.campaigns.number',
                    'attr'      => [
                        'class'    => 'form-control',
                    ],
                    'data'      => 1,
                    'required'  => true,
                ],
            ], [
                'triggerIntervalUnit',
                ChoiceType::class,
                [
                    'label'       => 'mautic.lead.lead.events.campaigns.unit',
                    'choices'     => [
                        'mautic.campaign.event.intervalunit.choice.i' => 'i',
                        'mautic.campaign.event.intervalunit.choice.h' => 'h',
                        'mautic.campaign.event.intervalunit.choice.d' => 'd',
                        'mautic.campaign.event.intervalunit.choice.m' => 'm',
                        'mautic.campaign.event.intervalunit.choice.y' => 'y',
                    ],
                    'multiple'          => false,
                    'label_attr'        => ['class' => 'control-label'],
                    'attr'              => [
                        'class' => 'form-control',
                    ],
                    'placeholder' => false,
                    'required'    => true,
                    'data'        => 'd',
                ],
            ],
        ];
    }
}
