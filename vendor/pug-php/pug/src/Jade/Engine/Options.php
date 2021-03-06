<?php

namespace Jade\Engine;

/**
 * Class Jade\Engine\Options.
 */
class Options extends Keywords
{
    /**
     * @var array
     */
    protected $options = array(
        'allowMixedIndent'   => true,
        'allowMixinOverride' => true,
        'basedir'            => null,
        'cache'              => null,
        'classAttribute'     => null,
        'customKeywords'     => array(),
        'expressionLanguage' => 'auto',
        'extension'          => array('.pug', '.jade'),
        'filterAutoLoad'     => true,
        'indentChar'         => ' ',
        'indentSize'         => 2,
        'jsLanguage'         => array(),
        'keepBaseName'       => false,
        'keepNullAttributes' => false,
        'nodePath'           => null,
        'phpSingleLine'      => false,
        'php5compatibility'  => false,
        'postRender'         => null,
        'preRender'          => null,
        'prettyprint'        => false,
        'pugjs'              => false,
        'restrictedScope'    => false,
        'singleQuote'        => false,
        'stream'             => null,
        'upToDateCheck'      => true,
        'localsJsonFile'     => false,
    );

    /**
     * Get standard or custom option, return the previously setted value or the default value else.
     *
     * Throw a invalid argument exception if the option does not exists.
     *
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    public function getOption($name)
    {
        switch ($name) {
            case 'sharedVariables':
            case 'shared_variables':
            case 'globals':
                return isset($this->sharedVariables) ? $this->sharedVariables : null;
        }

        if (!array_key_exists($name, $this->options)) {
            throw new \InvalidArgumentException("$name is not a valid option name.", 2);
        }

        return $this->options[$name];
    }

    /**
     * Set one standard option (listed in $this->options).
     *
     * @param string $name
     * @param mixed  $value
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function setOption($name, $value)
    {
        if (!array_key_exists($name, $this->options)) {
            throw new \InvalidArgumentException("$name is not a valid option name.", 3);
        }

        $this->options[$name] = $value;

        return $this;
    }

    /**
     * Set multiple standard options.
     *
     * @param array $options list of options
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function setOptions($options)
    {
        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }

        return $this;
    }

    /**
     * Set one custom option.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    public function setCustomOption($name, $value)
    {
        $this->options[$name] = $value;

        return $this;
    }

    /**
     * Set multiple custom options.
     *
     * @param array $options list of options
     *
     * @return $this
     */
    public function setCustomOptions(array $options)
    {
        $this->options = array_merge($this->options, $options);

        return $this;
    }
}
