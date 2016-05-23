<?php

namespace Ens\TostainGuillaumeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Ens\TostainGuillaumeBundle\Entity\Job;

class JobType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('category')
            ->add('company')
            ->add('url')
            ->add('position')
            ->add('location')
            ->add('description')
            ->add('email')
            ->add('type', 'choice', array('choices' => Job::getTypes(), 'expanded' => true))
            ->add('how_to_apply', null, array('label' => 'How to apply?'))
            ->add('is_public', null, array('label' => 'Public?'))
            ->add('file', 'file', array('label' => 'Company logo', 'required' => false))
        ;
    }

    public function getName()
    {
        return 'job';
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ens\TostainGuillaumeBundle\Entity\Job'
        ));
    }
}
