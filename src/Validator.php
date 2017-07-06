<?php


namespace GeorgRinger\ExtemconfValidator;

use Assert\Assert;
use Assert\Assertion;
use Assert\LazyAssertion;
use function PHPSTORM_META\type;
use RuntimeException;

class Validator
{
    protected $data = [];

    /** @var LazyAssertion */
    protected $assertion;

    public function __construct()
    {
        $this->assertion = Assert::lazy();
    }

    public function validate(string $file)
    {
        $this->data = $this->getExtensionEmConf($file);

        $this->validateRequiredFields();
        $this->validateConstraints();
        $this->validateDeprecatedConfiguration();

        $this->assertion->verifyNow();
    }


    protected function validateRequiredFields()
    {
        $requiredFields = ['title', 'description', 'version', 'state', 'author'];
        foreach ($requiredFields as $field) {
            $this->assertion->that($this->data, $field)->keyExists($field);
        }

        // description
        $this->assertion->that($this->data['description'], 'description')->minLength(50);

        // categories
        $this->assertion->that($this->data['category'], 'category')->inArray(['be', 'module', 'fe', 'plugin', 'misc', 'services', 'templates', 'example', 'doc', 'distribution']);

        // author & co
        $this->assertion->that($this->data['author'], 'author')->string()->minLength(10);

        // state
        $this->assertion->that($this->data['state'], 'state')->inArray(['alpha', 'beta', 'stable', 'experimental', 'test', 'obsolute', 'excludeFromUpdates']);

        $booleanFields = ['uploadfolder', 'shy', 'clearCacheOnLoad'];
        foreach ($booleanFields as $booleanField) {
            if (isset($this->data[$booleanField])) {
                $errorMessage = sprintf('Field "%s" is not boolean but "%s" (%s).', $booleanField, gettype($booleanField), $this->data[$booleanField]);
                $this->assertion->that($this->data[$booleanField], $booleanField)->boolean($errorMessage);
            }
        }
    }

    protected function validateConstraints()
    {
        if (!isset($this->data['constraints'])) {
            return;
        }
        $constraints = $this->data['constraints'];
        $this->assertion->that($constraints, 'constraints')->isArray();

        $constraintVariants = ['depends', 'conflicts', 'suggests'];
        foreach ($constraintVariants as $constraintVariantName) {
            if (!isset($constraints[$constraintVariantName])) {
                continue;
            }
            $constraintVariant = $constraints[$constraintVariantName];
            $propertyPath = sprintf('%s -> %s', 'constraints', $constraintVariantName);
            $this->assertion->that($constraintVariant, $propertyPath)->isArray();

        }
    }

    protected function validateDeprecatedConfiguration()
    {
        $deprecatedConfigurationItems = ['dependencies', 'conflicts', 'suggests', 'docPath', 'CGLcompliance', 'CGLcompliance_note', 'private', 'download_password'];
        foreach ($deprecatedConfigurationItems as $deprecatedConfigurationItem) {
            $this->assertion->that($this->data, $deprecatedConfigurationItem)->keyNotExists($deprecatedConfigurationItem, '"%s" is deprecated!', 'Configuration');
        }
    }

    /**
     * Fetches MetaData information from ext_emconf.php
     *
     * @param string $packagePath
     * @return array
     */
    protected function getExtensionEmConf(string $path)
    {
        $info = pathinfo($path);

        $extensionKey = basename($info['dirname']);

        $EM_CONF = null;
        if (@file_exists($path)) {
            include $path;
            if (is_array($EM_CONF[$extensionKey])) {
                return $EM_CONF[$extensionKey];
            }
        }
        throw new RuntimeException('No valid ext_emconf.php file found for package "' . $packageKey . '".', 1499278202);
    }
}
