<?php

namespace Gbs\LeaveBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Gbs\LeaveBundle\Entity\EmployeeLeave;

class PasswordForgetType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', 'email', array('label' => 'Email (enter your GBS email address)'))
            ->add('actualPw', 'password', array('label' => 'Actual password'))
            ->add('newPw1', 'password', array('label' => 'New password (password must contain between 8-12 characters, including 1 uppercase, 1 lowercase and 1 number.)'))
            ->add('newPw2', 'password', array('label' => 'New password (repeat)'))
            ->add('reset', 'submit')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Gbs\LeaveBundle\Entity\PasswordForget',
            'method' => 'POST',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'passwordforget';
    }
}
