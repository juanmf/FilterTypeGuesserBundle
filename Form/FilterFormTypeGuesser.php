<?php

namespace DocDigital\Bundle\FilterTypeGuesserBundle\Form;

use Symfony\Bridge\Doctrine\Form\DoctrineOrmTypeGuesser;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Guess\ValueGuess;
use Symfony\Component\Form\Guess\TypeGuess;
use Symfony\Component\Form\Guess\Guess;
use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;

use DocDigital\Bundle\FilterTypeGuesserBundle\Event\FilterFormTypeGuesserEvents;
use DocDigital\Bundle\FilterTypeGuesserBundle\Event\FilterFormTypeGuesserEvent;

/**
 * For Filter Forms, formTypes must be one of the LexikFormFilterBundle
 * 
 * @author Juan Manuel Fernandez <juanmf@gmail.com>
 */
class FilterFormTypeGuesser extends DoctrineOrmTypeGuesser 
{
    /**
     * The event dspatcher.
     * 
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
       
    /**
     * Maps possible parent guessed types with Lexik's closest filterTypes.
     * 
     * This Map can be altered at run time by Listeners of the 
     * {@link DocDigital\Bundle\FilterTypeGuesserBundle\Event\FilterFormTypeGuesserEvents}
     * Events.
     */
    private $typesMap = array(
        'checkbox' => 'filter_boolean',
        'integer'  => 'filter_number_range',
        'number'   => 'filter_number_range',
        'date'     => 'filter_date_range',
        'time'     => 'filter_datetime_range',
        'datetime' => 'filter_datetime_range',
        'text'     => 'filter_text',
        'textarea' => 'filter_text',
    );
    
    /**
     * Allows for changing the default guessed types by means of a filter event.
     * 
     * This gets called in {@link FormAdapter}, not injected by Container. 
     * 
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        $eventDispatcher->dispatch(
            FilterFormTypeGuesserEvents::FILTER_TYPE_GUESSER_LOADED, 
            $event = new FilterFormTypeGuesserEvent($this->typesMap)
        );
        $this->typesMap = $event->typesMap;
   }
    
    /**
     * {@inheritDoc}
     */
    public function guessPattern($class, $property)
    {
        return new ValueGuess(null, Guess::HIGH_CONFIDENCE);
    }

    /**
     * {@inheritDoc}
     */
    public function guessRequired($class, $property)
    {
        return new ValueGuess(false, Guess::HIGH_CONFIDENCE);
    }
        
    /**
     * {@inheritDoc}
     */
    public function guessType($class, $property)
    {
        $typeGuessed = parent::guessType($class, $property);
        $type = $typeGuessed->getType();
        $options = array('required' => false) + $typeGuessed->getOptions();
        $confidence = $typeGuessed->getConfidence();
        switch ($type) {
            case 'integer':
            case 'number':
                $options += $this->addRangeOptoins('number'); 
                break;
            case 'date':
            case 'datetime':
            case 'time':
                $options += $this->addRangeOptoins('time' === $type ? 'datetime' : $type); 
                break;
            case 'textarea':
            case 'text':
                $options += array('condition_pattern' => FilterOperands::STRING_BOTH);
                break;
        }
        return $this->getTypeGuess($type, $options);
    }

    private function getTypeGuess($typeMapKey, $options)
    {
        $typeName = isset($this->typesMap[$typeMapKey]) ? $this->typesMap[$typeMapKey] : "filter_$typeMapKey";
        return new TypeGuess($typeName, $options, Guess::VERY_HIGH_CONFIDENCE);
    }
    
    /**
     * Ranges are confussing without placeholders, also date[time] ranges appear
     * with no gap inbetween.
     * 
     * @param string $type The {@link DoctrineOrmTypeGuesser} guessed Field Type.
     * 
     * @return array The options.
     */
    private function addRangeOptoins($type)
    {
        return array(
            "left_{$type}_options"   => array(
                'label' => false,
                'attr'               => array(
                    'placeholder' => "Min $type",
                    'class'       => ('number' === $type ? '' : 'table'),
                ),
            ),
            "right_{$type}_options"  => array(
                'label' => false,
                'attr'               => array(
                    'placeholder' => "Max $type"
                ),
            ),
        );
    }
}
