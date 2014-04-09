<?php

namespace DocDigital\Bundle\FilterTypeGuesserBundle\Form;

use Symfony\Bridge\Doctrine\Form\DoctrineOrmTypeGuesser;
use Symfony\Component\Form\Guess\ValueGuess;
use Symfony\Component\Form\Guess\TypeGuess;
use Symfony\Component\Form\Guess\Guess;
use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;

/**
 * For Filter Forms, formTypes must be one of the LexikFormFilterBundle
 * 
 * @author Juan Manuel Fernandez <juanmf@gmail.com>
 */
class FilterFormTypeGuesser extends DoctrineOrmTypeGuesser 
{
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
                $type = 'number';
            case 'date':
            case 'datetime':
            case 'time':
                'time' === $type && $type = 'datetime';
                $options += $this->addRangeOptoins($type); 
                return new TypeGuess("filter_{$type}_range", $options, Guess::VERY_HIGH_CONFIDENCE);
            case 'textarea':
            case 'text':
                $type = 'text';
                $options += array('condition_pattern' => FilterOperands::STRING_BOTH);
            default:
                return new TypeGuess("filter_$type", $options, Guess::VERY_HIGH_CONFIDENCE);
        }
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
            "left_{$type}_options"  => array('attr' => array(
                'placeholder' => "Min",
                'class' => ('number' === $type ? '' : 'table'),
            )),
            "right_{$type}_options" => array('attr' => array('placeholder' => "Max")),
        );
    }
}
