<?php

namespace DocDigital\Bundle\FilterTypeGuesserBundle\Form;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\FormRegistryInterface;
use Symfony\Component\Form\FormTypeGuesserInterface;

/**
 * Description of FormAdapter
 *
 * @author Juan Manuel Fernandez <juanmf@gmail.com>
 */
class FormAdapter
{
    /**
     * FormTypeGuesserInterface
     * 
     * @var FormTypeGuesserInterface
     */
    private $formFiltertypeGuesser;
    
    /**
     * FormRegistry
     * 
     * @var FormRegistryInterface
     */
    private $formRegistry;
    
    public function __construct(
        FormRegistryInterface $formRegistry, FormTypeGuesserInterface $formFiltertypeGuesser,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->formRegistry = $formRegistry;
        $this->formFiltertypeGuesser = $formFiltertypeGuesser;
        $this->formFiltertypeGuesser->setEventDispatcher($eventDispatcher);
    }
    
    /**
     * Creates a Filter form to search for a Entities, by changing Doctrine GuessedTypes
     * for the lexikFormFilter best filter_type fit in the given form. Also removes
     * fields that you don't want for the resulting filterForm. 
     * 
     * @param string|AbstractType $formType          The FormType object or its FQCN.
     * @param string              $filterFormAction  Generated URL mapped to a Controller
     * that handles this form submission.
     * @param string[]            $removeFields      Field names to remove.
     * @param array               $typeSubmitOptions Options passed to submit button
     * 
     * @return \Symfony\Component\Form\Form The filterForm
     */
    public function adaptForm(
        $formType, $filterFormAction, array $removeFields = array(), array $typeSubmitOptions = array()
    ) {
        ($formType instanceof AbstractType) || (class_exists($formType) && $formType = new $formType());
        empty($typeSubmitOptions) 
            && $typeSubmitOptions = array(
                    'label' => 'search', 'attr' => array('class' => 'btn', 'formnovalidate' => true)
                );
        $extensions = $this->formRegistry->getExtensions();

        $formFactoryBuilder = Forms::createFormFactoryBuilder();
        $formFactoryBuilder->addTypeGuesser($this->formFiltertypeGuesser);
        $formFactoryBuilder->addExtensions($extensions);
        $formFactory = $formFactoryBuilder->getFormFactory();

        $form = $formFactory->create($formType, null, array(
            'validation_groups' => false,
            'required'          => false,
            'action'            => $filterFormAction,
            'method'                => 'POST',
        ));
        $form->add('submit', 'submit', $typeSubmitOptions);
        $this->removeFieldsForFilter($form, $removeFields);
        return $form;
    }
    
    /**
     * Removes fields that surelly wont add any value in user search form.
     * 
     * @param \Symfony\Component\Form\Form $form11
     */
    private function removeFieldsForFilter(Form $form, $fields)
    {
        foreach ($fields as $field) {
            $form->remove($field);
        }
    }
}
