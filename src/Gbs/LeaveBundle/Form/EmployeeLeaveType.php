<?php

namespace Gbs\LeaveBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Gbs\LeaveBundle\Entity\EmployeeLeave;
use Doctrine\ORM\EntityRepository;
class EmployeeLeaveType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $date = array(
              'widget' => 'single_text',
            );

        $user = array(
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->where('u.enabled = true')
                    ->orderBy('u.lastName', 'ASC');
            },
        );

        $builder
            ->add('type', 'choice', array('choices' => EmployeeLeave::$typesAllowed))
            ->add('start', null, $date)
            ->add('end', null, $date)
            ->add('partday', 'choice', array('choices' => EmployeeLeave::$partdays))
//            ->add('days')
            ->add('comment')
        ;

        if($options['roleAdmin']) {
            $builder
                ->add('user', null, $user)
            ;
        }
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Gbs\LeaveBundle\Entity\EmployeeLeave',
            'roleAdmin' => false,
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'leave';
    }
}
