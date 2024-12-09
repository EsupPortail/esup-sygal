<?php

namespace Feature\PluginManager;

use Feature\Adapter\DbFeatureAdapter;
use Feature\Adapter\DbFeatureAdapterInterface;
use Feature\Entity\Db\Feature as DbFeature;
use Feature\Entity\Db\Service\FeatureServiceAwareTrait;
use Feature\Entity\FeatureInterface;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\Stdlib\ArrayUtils;
use ReflectionClass;
use Webmozart\Assert\Assert;

/**
 * Plugin manager chargé de construire les "features" (fonctionalités).
 *
 * Deux types de feature :
 * - feature existante en base de données,
 * - feature simple.
 */
class FeaturePluginManager extends AbstractPluginManager
{
    use FeatureServiceAwareTrait;

    protected $instanceOf = FeatureInterface::class;

    protected array $config = [];
    protected string $configKey = 'config';

    protected array $instances = [];

    public function __construct($configInstanceOrParentLocator = null, array $config = [])
    {
        $this->config = $config[$this->configKey] ?? [];
        unset($config[$this->configKey]);

        parent::__construct($configInstanceOrParentLocator, $config);
    }

    public function get($name, ?array $options = null): FeatureInterface
    {
        if (array_key_exists($name, $this->instances)) {
            return $this->instances[$name];
        }

        $featureOptions = ArrayUtils::merge(
            $this->config[$name] ?? [],
            $options
        );

        // Recherche d'une éventuelle feature existante en bdd
        $feature = $this->getDbFeature($name, $featureOptions);

        // Si la feature n'existe pas en bdd, on la cherche en tant que simple service
        if ($feature === null) {
            try {
                $feature = parent::get($name, $featureOptions);
            } catch (ServiceNotFoundException $e) {
                throw new ServiceNotFoundException(
                    sprintf("Feature '%s' non trouvée par le plugin manager %s", $name, static::class)
                );
            }
        }

        $this->instances[$name] = $feature;

        return $feature;
    }

    /**
     * Recherche de la feature en base de données.
     * Si la config le spécifie, une feature existante en bdd peut être "enveloppée" dans un adapter,
     * auquel cas c'est une instance de cet adapter qui est retourné.
     */
    private function getDbFeature(string $name, ?array $options = null): DbFeature|DbFeatureAdapter|null
    {
        // Le name correspond-il à une classe d'adapter pour feature en bdd ?
        if (class_exists($name) && in_array(DbFeatureAdapterInterface::class, class_implements($name))) {
            // la classe doit déclarer une constante correspondant au code de la feature en bdd.
            $refl = new ReflectionClass($name);
            Assert::true(
                $refl->hasConstant($c = 'CODE'),
                "La classe d'adpater de feature suivante doit déclarer la constante '$c' : $name"
            );
            Assert::keyNotExists(
                $options,
                'adapter',
                "Interdit de spécifier un adapter pour un adapter de feature"
            );
            $code = $refl->getConstant($c);
            $feature = $this->featureService->getRepository()->findOneByCode($code);
            Assert::notNull(
                $feature,
                "Aucune feature trouvée en bdd avec le code '$code' déclaré dans la classe $name (constante $c)"
            );
            return new $name($feature);
        }

        // Recherche de la feature en bdd par son code/name
        $feature = $this->featureService->getRepository()->findOneByCode($name);
        if ($feature === null) {
            return null;
        }

        // Si une classe d'adapter est spécifiée dans les options de la feature, on retourne une instance
        // de cet adapter (qui "enveloppe" la feature) à la place de la feature elle-même.
        $adapter = $options['adapter'] ?? null;
        if ($adapter !== null) {
            Assert::classExists(
                $adapter,
                sprintf("La classe de l'adapter spécifié pour la feature %s n'existe pas : %s", $name, '%s')
            );
            Assert::implementsInterface(
                $adapter,
                DbFeatureAdapterInterface::class,
                sprintf("La classe d'adapter %s spécifiée pour la feature '%s' doit implémenter %s", '%s', $name, '%2$s')
            );
            $feature = new $adapter($feature);
        }

        return $feature;
    }

    public function has($name): bool
    {
        if (array_key_exists($name, $this->instances)) {
            return true;
        }

        // Recherche de la feature en bdd par son code/name :
        // si elle existe, bingo !
        $feature = $this->featureService->getRepository()->findOneByCode($name);
        if ($feature !== null) {
            // mise en "cache" de l'entité, ça peut servir
            $this->instances[$name] = $feature;
            return true;
        }
        // Sinon c'est un service
        else {
            return parent::has($name);
        }
    }
}