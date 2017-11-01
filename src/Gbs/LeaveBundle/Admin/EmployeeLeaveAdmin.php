<?php

namespace Gbs\LeaveBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Gbs\LeaveBundle\Entity\EmployeeLeave;
use Symfony\Component\Security\Core\SecurityContext;
use Gbs\LeaveBundle\Service\HolidayService;
use Doctrine\ORM\EntityRepository;

class EmployeeLeaveAdmin extends Admin
{
    private $securityContext;
    private $holidayService;

    public function setSecurityContext(SecurityContext $securityContext) {
        $this->securityContext = $securityContext;
    }

    public function setHolidayService(HolidayService $holidayService) {
        $this->holidayService = $holidayService;
    }

    protected $datagridValues = array(
        '_page' => 1,            // display the first page (default = 1)
        '_sort_order' => 'DESC', // reverse order (default = 'ASC')
        '_sort_by' => 'id'  // name of the ordered field
                                 // (default = the model's id field, if any)
        // the '_sort_by' key can be of the form 'mySubModel.mySubSubModel.myField'.
    );
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $userOptions = array(
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->where('u.enabled = true')
                    ->orderBy('u.lastName', 'ASC');
            },
        );

        $datagridMapper
            ->add('user', null, array(), null, $userOptions)
            ->add('type', null, array(), 'choice', array('choices' => EmployeeLeave::$types, 'multiple' => false, 'expanded' => false))
            ->add('state', null, array(), 'choice', array('choices' => EmployeeLeave::$states))
            ->add('start', 'doctrine_orm_date_range', array('field_type'=>'sonata_type_date_range_picker',))
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('user')
            ->add('stateName')
            ->add('start')
            ->add('end')
            ->add('days')
            ->add('typeName')
            ->add('comment')
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
        $userOptions = array(
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->where('u.enabled = true')
                    ->orderBy('u.lastName', 'ASC');
            },
        );

        $formMapper
            ->with('Global')
                ->add('type', 'choice', array('choices' => EmployeeLeave::$types))
                ->add('state', 'choice', array('choices' => EmployeeLeave::$states))
                ->add('start', 'date', array('widget' => 'single_text', 'attr' => array('class' => 'datepicker')))
                ->add('end', 'date', array('widget' => 'single_text', 'attr' => array('class' => 'datepicker')))
                ->add('partday', 'choice', array('choices' => EmployeeLeave::$partdays))
                ->add('days', null, array('required' => false, 'help' => 'If set to 0 or not provided then it will be computed automatically. If value is provided then no automatic calculation is done. Caution when you change the leave dates.'))
                ->add('comment')
                ->add('user', null, $userOptions)
            ->end()
            ->with('Read Only Info')
                ->add('created', 'datetime', array('disabled' => true, 'widget' => 'single_text'))
                ->add('creator', null, array('disabled' => true))
                ->add('validationDate', 'datetime', array('disabled' => true, 'widget' => 'single_text'))
                ->add('validationUser', null, array('disabled' => true))
            ->end()
        ;
    }

    public function getNewInstance() {
        $instance = parent::getNewInstance();
        $instance->setState(2);
        $instance->setCreator($this->securityContext->getToken()->getUser());
        return $instance;
    }

    public function prePersist($object) {
        $this->computeDays($object);
    }
    public function preUpdate($object) {
        $this->computeDays($object);
    }
    private function computeDays($object) {
        if($object->getDays()==0) {
            $object->setDays($this->holidayService->getLeaveDays($object));
        }
    }

    public function getExportFields() {
        return array('id', 'user', 'user.company', 'user.site', 'stateName', 'start', 'end', 'partDay', 'days', 'typeName', 'comment');
    }

    public function getDataSourceIterator() {
        $datasourceit = parent::getDataSourceIterator();
        $datasourceit->setDateTimeFormat('d/m/Y'); //change this to suit your needs
        return $datasourceit;
    }
}
