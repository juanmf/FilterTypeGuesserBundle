Overview
========

This Symfony2 Bundle Integrates Lexik Filter types with FormTypes generated by Sencio
generator-bundle or any AbstractType that relies on Dcotrine ORM's Type Guessing 
(i.e. don't assign hardcoded type to its form fields.)

It works by changing Doctrine GuessedTypes for the lexikFormFilter best filter_type 
fit in the given form.

Instalation
===========

Update your composer.json
```json
    "require": {
        ...
        "docdigital/filter-type-guesser": "dev-master"
    },
    ...
```
Then run the composer update command in your project root dir
```
$ php composer.phar update docdigital/filter-type-guesser
```

After composer finish, register the Bundel in app/appKernel.php
```php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            ...
            new Lexik\Bundle\FormFilterBundle\LexikFormFilterBundle(),
            new DocDigital\Bundle\FilterTypeGuesserBundle\DdFilterTypeGuesserBundle(),
        );
        ...
        return $bundles;
    }
    ...
}
```

Usage
=====

In your Controller add a method like this, to create the FilterForm, out of the regular Entity's FormType.

```php
    /**
     * Creates a Filter form to search for Entities.
     *
     * @param AbstractType|string $formType The `generate:doctrine:form` generated Type or its FQCN.
     *
     * @return \Symfony\Component\Form\Form The filter Form
     */
    private function createFilterForm($formType)
    {
        $adapter = $this->get('dd_form.form_adapter');
        $form = $adapter->adaptForm(
            $formType,
            $this->generateUrl('document_search'),
            array('fieldToRemove1', 'fieldToRemove2')
        );
        return $form;
    }
```

Then, the query building process is [lexik's](https://github.com/lexik/LexikFormFilterBundle/blob/master/Resources/doc/index.md#simple-example)
```php
    /**
     * Collects filterForm filled params and builds a Query.
     * 
     * @Route("/find", name="document_search")
     * @Method("POST")
     * @template("DdDocumentBundle:Document:index.html.twig")
     */
    public function searchAction(Request $request)
    {
        // $docType = new FormType/FQCN() could do too.
        $docType = 'FormType/FQCN';
        $filterForm = $this->createFilterForm($docType);
        $filterForm->handleRequest($request);
        
        $filterBuilder = $this->getDocRepo($docType)
            ->createQueryBuilder('e');
        $this->get('lexik_form_filter.query_builder_updater')
            ->addFilterConditions($filterForm, $filterBuilder);
        
        $entities = $filterBuilder->getQuery()->execute();

        return array(
            'entities'   => $entities,
            'filterForm' => $filterForm->createView(),
        );
    }
```
