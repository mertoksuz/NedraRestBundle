# Plug & Play RestApiBundle

ApiBundle creating REST api based on your models. Just give your models and use your auto-generated endpoints.

## Installation

Step 1: Update composer.json

```
$ composer require mertoksuz/api-bundle "1.0"
```

Step 2: Register Classes to `AppKernel`

```php
[...]
new FOS\RestBundle\FOSRestBundle(),
new JMS\SerializerBundle\JMSSerializerBundle($this),
new Symfony\Cmf\Bundle\RoutingBundle\CmfRoutingBundle(),
new MertOksuz\ApiBundle\MertOksuzApiBundle(),
```

Step 3: Update your `config.yml` for `FOSRestBundle` and `MertOksuzApiBundle`

```yml
fos_rest:
    format_listener:
        rules:
            - { path: '^/', priorities: ['json', 'xml'], fallback_format: 'json', prefer_extension: false }
```

```yml
mert_oksuz_api:
    entities:
        app.model:
            classes:
                model: AppBundle\Entity\Model`
                form:  AppBundle\Form\ModelType
```