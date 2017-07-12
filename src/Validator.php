<?php


namespace GeorgRinger\ExtemconfValidator;

use Assert\Assert;
use Assert\LazyAssertion;
use RuntimeException;

class Validator
{
    protected $data = [];

    /** @var LazyAssertion */
    protected $assertion;

    protected $requiredFields = ['title', 'description', 'category', 'version', 'state', 'author', 'constraints'];
    protected $optionalFields = ['author_email', 'author_company', 'createDirs'];
    protected $booleanFields = ['uploadfolder', 'clearCacheOnLoad'];
    protected $deprecatedFields = ['dependencies', 'conflicts', 'suggests', 'docPath', 'CGLcompliance', 'CGLcompliance_note', 'private', 'download_password', 'shy', 'loadOrder', 'priority', 'internal', 'modify_tables', 'module', 'lockType', 'TYPO3_version', 'PHP_version'];

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
        $this->validateNonExistingFields();

        $this->assertion->verifyNow();
    }

    protected function validateRequiredFields()
    {
        foreach ($this->requiredFields as $field) {
            $this->assertion->that($this->data, $field)->keyExists($field);
        }

        // title
        $this->assertion->that($this->data['title'], 'title')->minLength(10);

        // description
        $this->assertion->that($this->data['description'], 'description')->minLength(30);

        // categories
        $this->assertion->that($this->data['category'], 'category')->inArray(['be', 'module', 'fe', 'plugin', 'misc', 'services', 'templates', 'example', 'doc', 'distribution']);

        // author & co
        $this->assertion->that($this->data['author'], 'author')->string()->minLength(10);

        // state
        $this->assertion->that($this->data['state'], 'state')->inArray(['alpha', 'beta', 'stable', 'experimental', 'test', 'obsolute', 'excludeFromUpdates']);

        foreach ($this->booleanFields as $booleanField) {
            if (isset($this->data[$booleanField])) {
                $errorMessage = sprintf('Field "%s" is not boolean but "%s" (%s).', $booleanField, gettype($booleanField), $this->data[$booleanField]);
                $this->assertion->that($this->data[$booleanField], $booleanField)->boolean($errorMessage);
            }
        }

        if (isset($this->data['clearcacheonload'])) {
            $this->assertion->that($this->data, 'clearcacheonload')->keyNotExists('clearcacheonload', 'The property "clearcacheonload" must be named "clearCacheOnLoad".');
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
        foreach ($this->deprecatedFields as $field) {
            $this->assertion->that($this->data, $field)->keyNotExists($field, '"%s" is deprecated!', 'Configuration');
        }
    }

    protected function validateNonExistingFields()
    {
        $differences = array_diff(
            array_keys($this->data),
            $this->requiredFields,
            $this->booleanFields,
            $this->optionalFields,
            $this->deprecatedFields,
            ['clearcacheonload']
        );
        if (!empty($differences)) {
            foreach ($differences as $fieldName) {
                $this->assertion->that($this->data, $fieldName)->keyNotExists($fieldName, '"%s" is unknown in TYPO3 world, remove it', 'Configuration');
            }
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

        $extensionKey = $_EXTKEY = basename($info['dirname']);

        $EM_CONF = null;
        if (file_exists($path)) {
            include $path;
            if (is_array($EM_CONF[$extensionKey])) {
                return $EM_CONF[$extensionKey];
            }
        }
        throw new RuntimeException('No valid ext_emconf.php file found for package "' . $path . '".', 1499278202);
    }
}
