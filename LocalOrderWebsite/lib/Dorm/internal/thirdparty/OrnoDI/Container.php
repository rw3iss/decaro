<?php
/**
 * The Orno Component Library
 *
 * @author  Phil Bennett @philipobenito
 * @license MIT (see the LICENSE file)
 */
namespace Orno\Di;

use Orno\Cache\Cache;
use Orno\Di\Definition\ClassDefinition;
use Orno\Di\Definition\ClosureDefinition;
use Orno\Di\Definition\DefinitionInterface;
use Orno\Di\Definition\Factory;

class Container implements ContainerInterface, \ArrayAccess
{
    /**
     * @var \Orno\Di\Definition\Factory
     */
    protected $factory;

    /**
     * @var \Orno\Cache\Cache
     */
    protected $cache;

    /**
     * @var array
     */
    protected $items = array();
    /**
     * @var array
     */
    protected $singletons = array();

    /**
     * @var boolean
     */
    protected $caching = true;

    /**
     * Constructor
     *
     * @param \Orno\Cache\Cache           $cache
     * @param array|ArrayAccess           $config
     * @param \Orno\Di\Definition\Factory $factory
     */
    public function __construct(
        Cache   $cache   = null,
        $config          = array(),
        Factory $factory = null
    ) {
        $this->factory = (is_null($factory)) ? new Factory : $factory;
        $this->cache   = $cache;

        $this->addItemsFromConfig($config);

        $this->add('Orno\Di\ContainerInterface', $this);
        $this->add('Orno\Di\Container', $this);
    }

    /**
     * {@inheritdoc}
     */
    public function add($alias, $concrete = null, $singleton = false)
    {
        if (is_null($concrete)) {
            $concrete = $alias;
        }

        // if the concrete is an already instantiated object, we just store it
        // as a singleton
        if (is_object($concrete) && ! $concrete instanceof \Closure) {
            $this->singletons[$alias] = $concrete;
            return null;
        }

        // get a definition of the item
        $this->items[$alias]['singleton'] = (boolean) $singleton;

        $factory = $this->getDefinitionFactory();
        $definition = $factory($alias, $concrete, $this);
        $this->items[$alias]['definition'] = $definition;

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function singleton($alias, $concrete = null)
    {
        return $this->add($alias, $concrete, true);
    }

    /**
     * {@inheritdoc}
     */
    public function extend($alias)
    {
        if (! $this->isRegistered($alias)) {
            throw new \InvalidArgumentException(sprintf('[%s] is not registered in the container.', $alias));
        }

        if (array_key_exists($alias, $this->singletons)) {
            throw new Exception\ServiceNotExtendableException(sprintf(
                '[%s] is being managed singleton and cannot be modified.',
                $alias
            ));
        }

        return $this->items[$alias]['definition'];
    }

    /**
     * {@inheritdoc}
     */
    public function get($alias, array $args = array())
    {
        // if we have a singleton just return it
        if (array_key_exists($alias, $this->singletons)) {
            return $this->singletons[$alias];
        }

        // invoke the correct definition
        if (array_key_exists($alias, $this->items)) {
            return $this->resolveDefinition($alias, $args);
        }

        // check for and invoke a definition that was reflected on then cached
        if ($this->isCaching() && $cached = $this->getCachedDefinition($alias)) {
            return $cached;
        }

        // if we've got this far, we can assume we need to reflect on a class
        // and automatically resolve it's dependencies, we also cache the
        // result if a caching adapter is available
        $definition = $this->reflect($alias);

        if ($this->isCaching()) {
            $this->cache->set('orno::container::' . $alias, serialize($definition));
        }

        $this->items[$alias]['definition'] = $definition;

        return $definition();
    }

    /**
     * Resolve a container definition
     *
     * @param  string $alias
     * @param  array  $args
     * @return mixed
     */
    protected function resolveDefinition($alias, array $args)
    {
        $definition = $this->items[$alias]['definition'];
        $return     = $definition;

        if ($definition instanceof ClosureDefinition || $definition instanceof ClassDefinition) {
            $return = $definition($args);
        }

        // store as a singleton if needed
        if (isset($this->items[$alias]['singleton']) && $this->items[$alias]['singleton'] === true) {
            $this->singletons[$alias] = $return;
        }

        return $return;
    }

    /**
     * Return a cached definition object
     *
     * @param  string $alias
     * @return \Orno\Di\Definition\DefinitionInterface|boolean
     */
    protected function getCachedDefinition($alias)
    {
        if ($cached = $this->cache->get('orno::container::' . $alias)) {
            $definition = unserialize($cached);

            return $definition();
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isRegistered($alias)
    {
        return array_key_exists($alias, $this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function isSingleton($alias)
    {
        return (
            array_key_exists($alias, $this->singletons) ||
            (array_key_exists($alias, $this->items) && $this->items[$alias]['singleton'] === true)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function enableCaching()
    {
        $this->caching = true;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function disableCaching()
    {
        $this->caching = false;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isCaching()
    {
        return (! is_null($this->cache) && $this->caching === true);
    }

    /**
     * Encapsulate the definition factory to allow for invokation
     *
     * @return \Orno\Di\Definition\Factory
     */
    protected function getDefinitionFactory()
    {
        return $this->factory;
    }

    /**
     * Populate the container with items from config
     *
     * @param $config array|ArrayAccess
     * @return void
     */
    protected function addItemsFromConfig($config)
    {
        if (! is_array($config) && ! $config instanceof \ArrayAccess) {
            throw new \InvalidArgumentException('You can only load definitions from an array or an object that implements ArrayAccess.');
        }

        if (empty($config)) {
            return null;
        }

        if (! isset($config['di']) || ! is_array($config['di'])) {
            throw new \RuntimeException('Key "di" is missing from the definition config, or is not an array.');
        }

        $definitions = $config['di'];

        array_walk($definitions, array($this, 'createDefinitionFromConfig'));
    }

    /**
     * Create a definition from a config entry
     *
     * @param  mixed  $options
     * @param  string $alias
     * @return void
     */
    protected function createDefinitionFromConfig($options, $alias)
    {
        $concrete  = $this->resolveConcreteClassFromConfig($options);
        $singleton = false;
        $arguments = array();
        $methods   = array();

        if (is_array($options)) {
            $singleton  = (! empty($options['singleton']));
            $arguments  = (array_key_exists('arguments', $options)) ? (array) $options['arguments'] : array();
            $methods    = (array_key_exists('methods', $options)) ? (array) $options['methods'] : array();
        }

        // Define in the container, with constructor arguments and method calls
        $this->add($alias, $concrete, $singleton)
             ->withArguments($arguments)
             ->withMethodCalls($methods);
    }

    /**
     * Resolves the concrete class
     *
     * @param mixed $concrete
     * @return mixed
     */
    protected function resolveConcreteClassFromConfig($concrete)
    {
        if (is_array($concrete)) {
            if (array_key_exists('definition', $concrete)) {
                $concrete = $concrete['definition'];
            } elseif (array_key_exists('class', $concrete)) {
                $concrete = $concrete['class'];
            }
        }

        // if the concrete doesn't have a class associated with it then it
        // must be either a Closure or arbitrary type so we just bind that
        return $concrete;
    }

    /**
     * Reflect on a class, establish it's dependencies and build a definition
     * from that information
     *
     * @param  string $class
     * @throws Exception\ReflectionException
     * @throws Exception\UnresolvableDependencyException
     * @return \Orno\Di\Definition\ClassDefinition
     */
    protected function reflect($class)
    {
        // try to reflect on the class so we can build a definition
        try {
            $reflection  = new \ReflectionClass($class);
            $constructor = $reflection->getConstructor();
        } catch (\ReflectionException $e) {
            throw new Exception\ReflectionException(
                sprintf('Unable to reflect on the class [%s], does the class exist and is it properly autoloaded?', $class)
            );
        }

        $factory = $this->getDefinitionFactory();
        $definition = $factory($class, $class, $this);

        if (is_null($constructor)) {
            return $definition;
        }

        // loop through dependencies and get aliases/values
        foreach ($constructor->getParameters() as $param) {
            $dependency = $param->getClass();

            // if the dependency is not a class we attempt to get a dafult value
            if (is_null($dependency)) {
                if ($param->isDefaultValueAvailable()) {
                    $definition->withArgument($param->getDefaultValue());
                    continue;
                }

                throw new Exception\UnresolvableDependencyException(
                    sprintf('Unable to resolve a non-class dependency of [%s] for [%s]', $param, $class)
                );
            }

            // if the dependency is a class, just register it's name as an
            // argument with the definition
            $definition->withArgument($dependency->getName());
        }

        return $definition;
    }

    /**
     * Array Access get
     *
     * @param  string $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Array Access set
     *
     * @param  string $key
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->singleton($key, $value);
    }

    /**
     * Array Access unset
     *
     * @param  string $key
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($this->items[$key]);
        unset($this->singletons[$key]);
    }

    /**
     * Array Access isset
     *
     * @param  string $key
     * @return boolean
     */
    public function offsetExists($key)
    {
        return $this->isRegistered($key);
    }
}
