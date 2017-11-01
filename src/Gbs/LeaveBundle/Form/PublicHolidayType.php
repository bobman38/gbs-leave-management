<?php

namespace Gbs\LeaveBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PublicHolidayType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date', 'date', array('widget' => 'single_text', 'attr' => array('class' => 'datepicker')))
            ->add('country')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Gbs\LeaveBundle\Entity\PublicHoliday'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'gbs_leavebundle_publicholiday';
    }
}
