<?php

namespace MauticPlugin\MauticEmailMarketingBundle\Form\Type;

use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\PluginBundle\Form\Type\FieldsType;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use Mautic\PluginBundle\Model\PluginModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<array<mixed>>
 */
class IcontactType extends AbstractType
{
    public function __construct(
        private IntegrationHelper $integrationHelper,
        private PluginModel $pluginModel,
        protected RequestStack $requestStack,
        protected CoreParametersHelper $coreParametersHelper,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var \MauticPlugin\MauticEmailMarketingBundle\Integration\IcontactIntegration $object */
        $object          = $this->integrationHelper->getIntegrationObject('Icontact');
        $integrationName = $object->getName();
        $session         = $this->requestStack->getSession();
        $limit           = $session->get(
            'mautic.plugin.'.$integrationName.'.lead.limit',
            $this->coreParametersHelper->get('default_pagelimit')
        );
        $page = $session->get('mautic.plugin.'.$integrationName.'.lead.page', 1);

        $api = $object->getApiHelper();
        try {
            $lists = $api->getLists();

            $choices = [];
            if (!empty($lists['lists'])) {
                foreach ($lists['lists'] as $list) {
                    $choices[$list['listId']] = $list['name'];
                }

                asort($choices);
            }
        } catch (\Exception $e) {
            $choices = [];
            $error   = $e->getMessage();
            $page    = 1;
        }

        $builder->add('list', ChoiceType::class, [
            'choices'           => array_flip($choices), // Choice type expects labels as keys
            'label'             => 'mautic.emailmarketing.list',
            'required'          => false,
            'attr'              => [
                'tooltip' => 'mautic.emailmarketing.list.tooltip',
            ],
        ]);

        if (!empty($error)) {
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($error): void {
                $form = $event->getForm();

                if ($error) {
                    $form['list']->addError(new FormError($error));
                }
            });
        }

        if (isset($options['form_area']) && 'integration' == $options['form_area']) {
            $leadFields = $this->pluginModel->getLeadFields();

            $fields = $object->getFormLeadFields();

            [$specialInstructions, $alertType] = $object->getFormNotes('leadfield_match');
            $builder->add('leadFields', FieldsType::class, [
                'label'                => 'mautic.integration.leadfield_matches',
                'required'             => true,
                'mautic_fields'        => $leadFields,
                'integration'          => $object->getName(),
                'integration_object'   => $object,
                'limit'                => $limit,
                'page'                 => $page,
                'data'                 => $options['data'] ?? [],
                'integration_fields'   => $fields,
                'special_instructions' => $specialInstructions,
                'mapped'               => true,
                'error_bubbling'       => false,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined(['form_area']);
    }

    public function getBlockPrefix(): string
    {
        return 'emailmarketing_icontact';
    }
}
