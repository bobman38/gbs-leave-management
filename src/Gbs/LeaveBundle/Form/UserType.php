<?php

namespace Gbs\LeaveBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
class UserType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $manager = array(
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->where('u.enabled = true')
                    ->orderBy('u.lastName', 'ASC');
            },
        );

        $builder
            ->add('dn', null, array('read_only' => true))
            ->add('manager', null, $manager)
            ->add('country')
            ->add('company')
            ->add('site')
            ->add('department')
            ->add('employeeStartDate', null, array('widget' => 'single_text', 'attr' => array('class' => 'datepicker')))
            ->add('lastMedicalVisit', null, array('widget' => 'single_text', 'attr' => array('class' => 'datepicker')))
            ->add('tr', 'checkbox', array('required' => false, 'label' => 'Ticket Restaurant for this user ?'))
            ->add('holidayCreditPerMonth', null, array('required' => false, 'label' => 'Set holiday credit per month (if different of country value)'))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Gbs\LeaveBundle\Entity\User'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'user';
    }
}
