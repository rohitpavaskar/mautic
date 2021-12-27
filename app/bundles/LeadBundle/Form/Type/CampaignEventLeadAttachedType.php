<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\LeadBundle\Form\Type;

use Mautic\LeadBundle\Helper\FormFieldHelper;
use Mautic\LeadBundle\Model\FieldModel;
use Mautic\LeadBundle\Model\LeadModel;
use Mautic\LeadBundle\Model\ListModel;
use Mautic\LeadBundle\Segment\OperatorOptions;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class CampaignEventLeadAttachedType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var LeadModel
     */
    protected $leadModel;

    /**
     * @var FieldModel
     */
    protected $fieldModel;

    /**
     * @var ListModel
     */
    protected $listModel;

    public function __construct(TranslatorInterface $translator, LeadModel $leadModel, FieldModel $fieldModel, ListModel $listModel)
    {
        $this->translator = $translator;
        $this->leadModel  = $leadModel;
        $this->fieldModel = $fieldModel;
        $this->listModel  = $listModel;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'timestamp',
            ChoiceType::class,
            [
                'label'             => 'mautic.lead.lead.events.campaigns.timestamp',
                'label_attr'        => ['class' => 'control-label'],
                'multiple'          => false,
                'choices'           => ['Campaign Start Date' => 1],
                'attr'              => ['class' => 'form-control'],
                'required'          => true,
            ]
        );

        $builder->add(
            'operator',
            ChoiceType::class,
            [
                'label'             => 'mautic.lead.lead.events.campaigns.operator',
                'multiple'          => false,
                'choices'           => $this->listModel->getOperatorsForFieldType([
                    'include' => [
                        'gt',
                        'lt',
                    ],
                ]),
                'required'   => true,
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'        => 'form-control',
                ],
            ]
        );

        $data = (!isset($options['data']['triggerInterval']) || '' === $options['data']['triggerInterval']
            || null === $options['data']['triggerInterval']) ? 1 : (int) $options['data']['triggerInterval'];
        $builder->add(
            'triggerInterval',
            NumberType::class,
            [
                'label'     => 'mautic.lead.lead.events.campaigns.number',
                'attr'      => [
                    'class'    => 'form-control',
                ],
                'data'      => $data,
                'required'  => true,
            ]
        );

        $data = (!empty($options['data']['triggerIntervalUnit'])) ? $options['data']['triggerIntervalUnit'] : 'd';
        $builder->add(
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
                'data'        => $data,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'campaignevent_lead_contact_added';
    }
}
