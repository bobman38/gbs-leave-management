<?php

namespace Gbs\LeaveBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class UserAdmin extends Admin
{
    protected $datagridValues = array(
        '_page' => 1,            // display the first page (default = 1)
        '_sort_order' => 'ASC', // reverse order (default = 'ASC')
        '_sort_by' => 'lastName'  // name of the ordered field
                                 // (default = the model's id field, if any)
        // the '_sort_by' key can be of the form 'mySubModel.mySubSubModel.myField'.
    );
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('enabled')
            ->add('fullName')
            ->add('username')
            ->add('tr')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('fullName')
            ->add('department')
            ->add('enabled')
            ->add('lastMedicalVisit')
            ->add('tr')
            ->add('holidayCreditPerMonth')
            ->add('company')
            ->add('site')
            ->add('_action', 'actions', array(
                'actions' => array(
                    'edit' => array(),
                    'delete' => array(),
                )
            ))
        ;
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('username')
            ->add('fullName')
            ->add('lastName')
            ->add('email')
            ->add('dn')
            ->add('department')
            ->add('company', null)
            ->add('site', null)
            ->add('enabled', null, array('required' => false))
            ->add('lastMedicalVisit')
            ->add('tr', null, array('required' => false))
            ->add('holidayCreditPerMonth', null, array('required' => false))
        ;
    }
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('create')
//            ->remove('delete')
//            ->remove('edit')
            ;
    }
}
