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

Usage
=====
In your Controller add a method like this, to create the FilterForm, out of the regular Entity's FormType.

```php
    /**
     * Creates a Filter form to search for Entities.
     *
     * @param AbstractType|string The `generate:doctrine:form` generated Type of its FQCN.
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
