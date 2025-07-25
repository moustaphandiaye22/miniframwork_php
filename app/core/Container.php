<?php

namespace App\Core;

use ReflectionClass;
use ReflectionParameter;
use InvalidArgumentException;
use Exception;

class Container
{
    private static ?Container $instance = null;
    private array $bindings = [];
    private array $instances = [];
    private array $singletons = [];

    private function __construct() {}

    public static function getInstance(): Container
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Lier une interface ou classe à une implémentation
     */
    public function bind(string $abstract, string $concrete = null, bool $singleton = false): void
    {
        $concrete = $concrete ?? $abstract;
        
        $this->bindings[$abstract] = [
            'concrete' => $concrete,
            'singleton' => $singleton
        ];

        if ($singleton) {
            $this->singletons[] = $abstract;
        }
    }

    /**
     * Lier un singleton
     */
    public function singleton(string $abstract, string $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * Résoudre une dépendance
     */
    public function resolve(string $abstract)
    {
        // Si c'est un singleton déjà instancié, le retourner
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // Obtenir la classe concrète
        $concrete = $this->getConcrete($abstract);

        // Construire l'instance
        $instance = $this->build($concrete);

        // Si c'est un singleton, le stocker
        if ($this->isSingleton($abstract)) {
            $this->instances[$abstract] = $instance;
        }

        return $instance;
    }

    /**
     * Construire une instance avec injection de dépendances
     */
    private function build(string $concrete)
    {
        $reflectionClass = new ReflectionClass($concrete);

        if (!$reflectionClass->isInstantiable()) {
            throw new InvalidArgumentException("La classe $concrete n'est pas instanciable");
        }

        $constructor = $reflectionClass->getConstructor();

        // Si pas de constructeur, retourner une instance simple
        if ($constructor === null) {
            return $reflectionClass->newInstance();
        }

        // Résoudre les dépendances du constructeur
        $dependencies = $this->resolveDependencies($constructor->getParameters());

        return $reflectionClass->newInstanceArgs($dependencies);
    }

    /**
     * Résoudre les dépendances d'un constructeur
     */
    private function resolveDependencies(array $parameters): array
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $dependency = $this->resolveDependency($parameter);
            $dependencies[] = $dependency;
        }

        return $dependencies;
    }

    /**
     * Résoudre une dépendance spécifique
     */
    private function resolveDependency(ReflectionParameter $parameter)
    {
        // Si le paramètre a une valeur par défaut et pas de type
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        // Obtenir le type du paramètre
        $type = $parameter->getType();

        if ($type === null) {
            throw new InvalidArgumentException(
                "Impossible de résoudre la dépendance {$parameter->getName()}"
            );
        }

        // Si c'est un type natif PHP (string, int, etc.)
        if ($type->isBuiltin()) {
            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }
            
            throw new InvalidArgumentException(
                "Impossible de résoudre le type primitif {$parameter->getName()}"
            );
        }

        // Résoudre récursivement la classe
        return $this->resolve($type->getName());
    }

    /**
     * Obtenir la classe concrète pour un abstract
     */
    private function getConcrete(string $abstract): string
    {
        if (isset($this->bindings[$abstract])) {
            return $this->bindings[$abstract]['concrete'];
        }

        return $abstract;
    }

    /**
     * Vérifier si c'est un singleton
     */
    private function isSingleton(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) && 
               $this->bindings[$abstract]['singleton'] === true;
    }

    /**
     * Méthode de commodité pour créer des instances
     */
    public function make(string $abstract)
    {
        return $this->resolve($abstract);
    }

    /**
     * Enregistrer une instance existante
     */
    public function instance(string $abstract, $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    /**
     * Vérifier si un binding existe
     */
    public function bound(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]);
    }
}
