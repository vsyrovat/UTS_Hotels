<?php

namespace App\Form;

use App\Form\Transformer\EntityToIdTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class DependentEntityType
 * @package App\Form
 */
class DependentEntityType extends AbstractType
{
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var array
     */
    private $config;

    /**
     * DependentEntityType constructor.
     * @param EntityManagerInterface $em
     * @param array $config
     */
    public function __construct(EntityManagerInterface $em, array $config)
    {
        $this->em = $em;
        $this->config = $config;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!isset($this->config[$options['alias']])) {
            throw new \InvalidArgumentException(sprintf('Alias %s must be configured', $options['alias']));
        }
        $class = $this->config[$options['alias']]['class'];
        $property = $this->config[$options['alias']]['property'];
        $options['data_class'] = $class;

        $builder->addViewTransformer(
                new EntityToIdTransformer(
                    $this->em,
                    $class
                ), true
            )
        ;
        $builder->setCompound(false);
        $builder->setAttribute('alias', $options['alias']);
        $builder->setAttribute('data_class', $class);
        $builder->setAttribute('parent', $options['depends_on']);
        $builder->setAttribute('binding', $property);
        $builder->setAttribute('placeholder', $options['placeholder']);
        $builder->setAttribute('no_result_message', $options['no_result_message']);
    }

    /**
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['parent'] = $form->getConfig()->getAttribute('parent');
        $view->vars['alias'] = $form->getConfig()->getAttribute('alias');
        $view->vars['no_result_msg'] = $form->getConfig()->getAttribute('no_result_msg');
        $view->vars['placeholder'] = $form->getConfig()->getAttribute('placeholder');
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'alias' => null,
            'depends_on' => null,
            'placeholder' => '',
            'no_result_message' => 'Data not found'
        ]);

        $resolver->setRequired(
            ['alias', 'depends_on']
        );
    }
}
