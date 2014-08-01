<?php

namespace DocDigital\Bundle\FilterTypeGuesserBundle\Event;

/**
 * Describer the event used for altering the Filter Type Guess Map
 *
 * @author Juan Manuel Fernandez <juanmf@gmail.com>
 */
class FilterFormTypeGuesserEvents
{
    /**
     * used to allow listeners to edit the {@link FilterFormTypeGuesser::$typesMap} 
     * mappings array used to convert Doctrine Guessed types into filter types,
     * so that you can change the default filters.
     */
    const FILTER_TYPE_GUESSER_LOADED = 'ddfiltertypeguesser.type_guesser_loaded';
    
}
