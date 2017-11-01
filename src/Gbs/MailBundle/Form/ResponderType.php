<?php

namespace Gbs\MailBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ResponderType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('text', 'textarea', array(
                'label' => 'The reponder message that will be sent when you receive something.',
                'attr' => array('rows' => 15),
                'required' => false,))
        ;

        if($options['active'] == TRUE) {
            $builder
                ->add('setup', 'submit', array('label' => 'Update !'))
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
            'data_class' => 'Gbs\MailBundle\Entity\Responder',
            'method' => 'POST',
            'active' => FALSE,
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'responder';
    }
}
