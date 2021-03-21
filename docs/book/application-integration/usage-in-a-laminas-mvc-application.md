# Usage in a laminas-mvc Application

The following example shows _one_ potential use case of laminas-inputfilter within
a laminas-mvc based application. The example uses a module, a controller and the
laminas-inputfilter plugin manager.

Before starting, make sure laminas-inputfilter is [installed and configured](../installation.md).

## Create Input Filter

Create an input filter as separate class, e.g.
`module/Album/src/InputFilter/QueryInputFilter.php`:

```php
namespace Album\InputFilter;

use Laminas\Filter\ToInt;
use Laminas\I18n\Validator\IsInt;
use Laminas\InputFilter\InputFilter;

class QueryInputFilter extends InputFilter
{
    public function init()
    {
        // Page
        $this->add(
            [
                'name'              => 'page',
                'allow_empty'       => true,
                'validators'        => [
                    [
                        'name' => IsInt::class,                        
                    ],                    
                ],
                'filters'           => [
                    [
                        'name' => ToInt::class,
                    ],
                ],
                'fallback_value'    => 1,
            ]
        );
    
        // …
    }
}
```

## Using Input Filter

### Create Controller

Using the input filter in a controller, e.g.
`module/Album/Controller/AlbumController.php`:

```php
namespace Album\Controller;

use Album\InputFilter\QueryInputFilter;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Mvc\Controller\AbstractActionController;

class AlbumController extends AbstractActionController
{
    /** @var InputFilterInterface */
    private $inputFilter;
    
    public function __construct(InputFilterInterface $inputFilter)
    {
        $this->inputFilter = $inputFilter;        
    }
    
    public function indexAction()
    {
        $this->inputFilter->setData($this->getRequest()->getQuery());
        $this->inputFilter->isValid();
        $filteredParams = $this->inputFilter->getValues();
        
        // …
    }
}
```

### Create Factory for Controller

Fetch the `QueryInputFilter` from the input filter plugin manager in a factory,
e.g. `src/Album/Handler/ListHandlerFactory.php`:

```php
namespace Album\Controller;

use Album\InputFilter\QueryInputFilter;
use Interop\Container\ContainerInterface;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\ServiceManager\Factory\FactoryInterface;

class AlbumControllerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null
    ) {
        /** @var InputFilterPluginManager $pluginManager */
        $pluginManager = $container->get(InputFilterPluginManager::class);
        $inputFilter   = $pluginManager->get(QueryInputFilter::class);

        return new AlbumController($inputFilter);
    }
}
```

> ### Instantiating the Input Filter
>
> The `InputFilterPluginManager` is used instead of directly instantiating the
> input filter to ensure to get the filter and validator plugin managers
> injected. This allows usage of any filters and validators registered with
> their respective plugin managers.
>
> Additionally the `InputFilterPluginManager` calls the `init` method _after_
> instantiating the input filter, ensuring all dependencies are fully injected
> first.

## Register Input Filter and Controller

Extend the configuration of the module to register the input filter and
controller in the application.  
Add the following lines to the module configuration file, e.g.
`module/Album/config/module.config.php`:

<pre class="language-php" data-line="8-9,12-17"><code>
namespace Album;

use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    'controllers' => [
        'factories' => [
            // Add this line
            Controller\AlbumController::class => Controller\AlbumControllerFactory::class,
        ],
    ],
    // Add the following array
    'input_filters' => [
        'factories => [
            InputFilter\QueryInputFilter::class => InvokableFactory::class,
        ],
    ],
    // …
];
</code></pre>
