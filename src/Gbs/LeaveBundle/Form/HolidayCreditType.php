<?php

namespace Gbs\LeaveBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Gbs\LeaveBundle\Entity\EmployeeLeave;

class HolidayCreditType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('type', 'choice', array('choices' => EmployeeLeave::$typesCredit))
            ->add('days')
            ->add('comment')
            ->add('add', 'submit')
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Gbs\LeaveBundle\Entity\EmployeeLeave',
            'method' => 'PUT',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'holidaycredit';
    }
}
