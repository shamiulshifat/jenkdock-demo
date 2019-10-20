<?php

namespace Ballen\Plexity\Support;

use Ballen\Plexity\Plexity;
use Ballen\Plexity\Exceptions\ValidationException;

/**
 * Plexity
 *
 * Plexity (Password Complexity) is a password complexity library that
 * enables you to set "rules" for a password (or any other kind of string) that
 * you can then check against in your application.
 *
 * @author Bobby Allen <ballen@bobbyallen.me>
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/allebb/passplexity
 * @link https://bobbyallen.me
 *
 */
class Validator
{

    /**
     * The Plexity object (contains the validation configuration)
     * @var Plexity
     */
    private $configuration;

    /**
     * Numeric values list
     * @var array
     */
    protected $numbers = [
        1, 2, 3, 4, 5, 6, 7, 8, 9, 0
    ];

    /**
     * Special Character list
     * @see https://www.owasp.org/index.php/Password_special_characters
     * @var array
     */
    protected $specialCharacters = [
        ' ', '!', '"', '#', '$', '%', '&', '\'', '(', ')', '*', '+', ',', '.',
        '/', ':', ';', '<', '=', '>', '?', '@', '[', ']', '\\', '^', '_', '`',
        '{', '|', '}', '~',
    ];

    /**
     * Validates all the configured rules and responds as requested.
     * @return boolean
     * @throws ValidationException
     */
    public function validate(Plexity $configuration)
    {
        $this->configuration = $configuration;
        $this->checkMinimumLength();
        $this->checkMaximumLength();
        $this->checkLowerCase();
        $this->checkUpperCase();
        $this->checkNumericCharacters();
        $this->checkSpecialCharacters();
        $this->checkNotIn();
        return true;
    }

    /**
     * Checks the minimum length requirement.
     * @throws ValidationException
     */
    public function checkMinimumLength()
    {
        if ($this->configuration->rules()->get(Plexity::RULE_LENGTH_MIN) > 0) {
            if (!$this->validateLengthMin()) {
                throw new ValidationException('The length does not meet the minimum length requirements.');
            }
        }
    }

    /**
     * Checks the minimum maximum length requirement.
     * @throws ValidationException
     */
    public function checkMaximumLength()
    {
        if ($this->configuration->rules()->get(Plexity::RULE_LENGTH_MAX) > 0) {
            if (!$this->validateLengthMax()) {
                throw new ValidationException('The length exceeds the maximum length requirements.');
            }
        }
    }

    /**
     * Checks the lowercase character(s) requirement.
     * @throws ValidationException
     */
    public function checkLowerCase()
    {
        if ($this->configuration->rules()->get(Plexity::RULE_LOWER) > 0) {
            if (!$this->validateLowerCase()) {
                throw new ValidationException('The string failed to meet the lower case requirements.');
            }
        }
    }

    /**
     * Checks the upper case character(s) requirement.
     * @throws ValidationException
     */
    public function checkUpperCase()
    {
        if ($this->configuration->rules()->get(Plexity::RULE_UPPER) > 0) {
            if (!$this->validateUpperCase()) {
                throw new ValidationException('The string failed to meet the upper case requirements.');
            }
        }
    }

    /**
     * Checks the numeric character(s) requirement.
     * @throws ValidationException
     */
    public function checkNumericCharacters()
    {
        if ($this->configuration->rules()->get(Plexity::RULE_NUMERIC) > 0) {
            if (!$this->validateNumericCharacters()) {
                throw new ValidationException('The string failed to meet the numeric character requirements.');
            }
        }
    }

    /**
     * Checks the special character(s) requirement.
     * @throws ValidationException
     */
    public function checkSpecialCharacters()
    {
        if ($this->configuration->rules()->get(Plexity::RULE_SPECIAL) > 0) {
            if (!$this->validateSpecialCharacters()) {
                throw new ValidationException('The string failed to meet the special character requirements.');
            }
        }
    }

    /**
     * Validates if a string is not in a array (password history database).
     * @throws ValidationException
     */
    public function checkNotIn()
    {
        if (count($this->configuration->rules()->get(Plexity::RULE_NOT_IN)) > 0) {
            if (!$this->validateNotIn()) {
                throw new ValidationException('The string exists in the list of disallowed values requirements.');
            }
        }
    }

    /**
     * Validates the upper case requirements.
     * @return boolean
     */
    private function validateUpperCase()
    {
        $occurences = preg_match_all("/[A-Z]/", $this->configuration->checkString());

        if ($occurences >= $this->configuration->rules()->get(Plexity::RULE_UPPER)) {
            return true;
        }

        return false;
    }

    /**
     * Validates the lower case requirements.
     * @return boolean
     */
    private function validateLowerCase()
    {
        $occurences = preg_match_all("/[a-z]/", $this->configuration->checkString());

        if ($occurences >= $this->configuration->rules()->get(Plexity::RULE_LOWER)) {
            return true;
        }

        return false;
    }

    /**
     * Validates the special character requirements.
     * @return boolean
     */
    private function validateSpecialCharacters()
    {
        if ($this->countOccurrences($this->specialCharacters, $this->configuration->checkString()) >= $this->configuration->rules()->get(Plexity::RULE_SPECIAL)) {
            return true;
        }
        return false;
    }

    /**
     * Validates the numeric case requirements.
     * @return boolean
     */
    private function validateNumericCharacters()
    {
        if ($this->countOccurrences($this->numbers, $this->configuration->checkString()) >= $this->configuration->rules()->get(Plexity::RULE_NUMERIC)) {
            return true;
        }
        return false;
    }

    /**
     * Validates the minimum string length requirements.
     * @return boolean
     */
    private function validateLengthMin()
    {
        if (strlen($this->configuration->checkString()) >= $this->configuration->rules()->get(Plexity::RULE_LENGTH_MIN)) {
            return true;
        }
        return false;
    }

    /**
     * Validates the maximum string length requirements.
     * @return boolean
     */
    private function validateLengthMax()
    {
        if (strlen($this->configuration->checkString()) <= $this->configuration->rules()->get(Plexity::RULE_LENGTH_MAX)) {
            return true;
        }
        return false;
    }

    /**
     * Validates the not_in requirements.
     * @return boolean
     */
    private function validateNotIn()
    {
        if (in_array($this->configuration->checkString(), (array)$this->configuration->rules()->get(Plexity::RULE_NOT_IN))) {
            return false;
        }
        return true;
    }

    /**
     * Count the number of occurences of a character or string in a string.
     * @param array $needles The character/string to count occurrences of.
     * @param string $haystack The string to check against.
     * @return int The number of occurrences.
     */
    private function countOccurrences(array $needles, $haystack)
    {
        $count = 0;
        foreach ($needles as $char) {
            $count += substr_count($haystack, $char);
        }
        return $count;
    }
}