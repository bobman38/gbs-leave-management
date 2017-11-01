<?php

namespace Gbs\MailBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class ForwarderType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('to', 'entity', array(
                'label' => 'The user that will receive all your emails.',
                'class' => 'Gbs\LeaveBundle\Entity\User',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.enabled = true')
                        ->orderBy('u.lastName', 'ASC');
                    },
                'required' => false,));

        if($options['active'] == TRUE) {
            $builder
                ->add('setup', 'submit', array('label' => 'Setup !', 'disabled' => TRUE))
                ->add('remove', 'submit', array('label' => 'Remove'));
        }
        else {
            $builder
                ->add('setup', 'submit', array('label' => 'Setup !'))
                ->add('remove', 'submit', array('label' => 'Remove', 'disabled' => TRUE));
        }


    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Gbs\MailBundle\Entity\Forwarder',
            'method' => 'POST',
            'active' => FALSE,
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'forwarder';
    }
}
