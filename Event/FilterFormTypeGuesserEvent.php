<?php

namespace DocDigital\Bundle\FilterTypeGuesserBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Allows Listeners to alter {@link FilterFormTypeGuesser#$typesMap},
 * You can use a kernel.request listener to hook to this event.
 * 
 * Example:<pre>
 *     public function handleRequest(GetResponseEvent $event)
 *     {
 *         /**
 *          * Adds {@link FilterFormTypeGuesser} overrider to change default filter dates. 
 *          * /
 *         $event->getDispatcher()->addListener(
 *             FilterFormTypeGuesserEvents::FILTER_TYPE_GUESSER_LOADED, 
 *             array(new TypeGuesserOverriderListener(), 'override')
 *         );
 *     }
 * 
 *
 *  class TypeGuesserOverriderListener
 *  {
 *      /**
 *       * Overrides the default filter types 
 *       * associated with some Doctrine Guessed types
 *       * 
 *       * @param FilterFormTypeGuesserEvent $event the event containing the TypesMap.
 *       * /
 *      public function override(FilterFormTypeGuesserEvent $event)
 *      {
 *          $event->typesMap['date'] = 'dd_filter_date_range';
 *          $event->typesMap['datetime'] = 'dd_filter_date_range';
 *          $event->typesMap['time'] = 'dd_filter_date_range';
 *      }
 *  }
 * </pre>
 * 
 * @author Juan Manuel Fernandez <juanmf@gmail.com>
 */
class FilterFormTypeGuesserEvent  extends Event 
{
    /**
     * Originally contains the {@link FilterFormTypeGuesser::$typesMap}. Allows listeners 
     * to change the mappings, for instance to replace default filter_date by a 
     * better date widget.
     * 
     * @var array 
     */
    public $typesMap = array();
    
    /**
     * Initializes self::$typesMap
     * 
     * @param array $originalTypesMap
     */
    public function __construct(array $originalTypesMap)
    {
        $this->typesMap = $originalTypesMap;
    }
}
