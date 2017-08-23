[![Build Status](https://travis-ci.org/mertoksuz/RestBundle.svg?branch=master)](https://travis-ci.org/mertoksuz/RestBundle)
[![Coverage Status](https://coveralls.io/repos/github/mertoksuz/RestBundle/badge.svg?branch=master)](https://coveralls.io/github/mertoksuz/RestBundle?branch=master)
 
# NedraRestBundle

NedraRestBundle creates automatically a REST API based on your models. Just give your models and use your auto-generated endpoints.

## Get Started

Bundle works on Doctrine ORM based models. Bundle purpose is generate a REST Api in fastest and easy way.

## Installation

Step 1: Update composer.json

```
composer require nedra/rest-bundle
```

Step 2: Register Classes to `AppKernel`

```php
[...]
new FOS\RestBundle\FOSRestBundle(),
new JMS\SerializerBundle\JMSSerializerBundle($this),
new Symfony\Cmf\Bundle\RoutingBundle\CmfRoutingBundle(),
new Nedra\RestBundle\NedraRestBundle(),
```

Step 3: Update your `config.yml` for `NedraRestBundle`

```yml
nedra_rest:
    active: true # this is default value, you can disable the bundle with set to false
    entities:
        app.model:
            classes:
                model: AppBundle\Entity\Model
```

#### Note
`app.model` is a pattern example. You must define your models with application prefix like `app`


## Check Routes

Run that command and debug your router
```
bin/console debug:router
```

You will see auto-generated routings for REST
```php 
app_model_index             GET         ANY      ANY    /models/                            
app_model_create            POST        ANY      ANY    /models/new                         
app_model_update            PUT|PATCH   ANY      ANY    /models/{id}                        
app_model_show              GET         ANY      ANY    /models/{id}                        
app_model_delete            DELETE      ANY      ANY    /models/{id} 
```


## Configuration Reference

```yml
nedra_rest:
    entities:
        app.model:
            identifier: id # you can change {id} to {slug}
            path: model # you can change /models/ to /cars/
            except: ['show']
            only: ['create', 'index']
            classes:
                model: AppBundle\Entity\Model
                form:  AppBundle\Form\ModelType
```

#### Note

You can use only one of them `except` or `only`
