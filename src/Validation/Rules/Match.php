<?php

declare(strict_types=1);

namespace IronFlow\Validation\Rules;

use IronFlow\Validation\AbstractRule;

/**
 * Règle de validation pour vérifier si une valeur correspond à une autre valeur
 */
class Match extends AbstractRule
{
    /**
     * Message d'erreur par défaut
     */
    protected string $defaultMessage = 'Le champ :field doit correspondre au champ :fieldToMatch';

    /**
     * Nom du champ à comparer
     */
    private string $fieldToMatch;

    /**
     * Constructeur
     * 
     * @param string $fieldToMatch Nom du champ à comparer
     */
    public function __construct(string $fieldToMatch)
    {
        $this->fieldToMatch = $fieldToMatch;
        $this->setAttribute('fieldToMatch', $fieldToMatch);
    }

    /**
     * Définit le champ à comparer
     * 
     * @param string $fieldToMatch
     * @return self
     */
    public function setFieldToMatch(string $fieldToMatch): self
    {
        $this->fieldToMatch = $fieldToMatch;
        $this->setAttribute('fieldToMatch', $fieldToMatch);
        return $this;
    }

    /**
     * Valide si une valeur correspond à une autre valeur
     * 
     * @param mixed $value
     * @param array $data
     * @return bool
     */
    public function validate($value, array $data = []): bool
    {
        // Si la valeur est vide, on ne vérifie pas la correspondance
        if ($value === null || $value === '') {
            return true; // Pas d'erreur si vide (utiliser Required pour vérifier la présence)
        }

        // Vérification que le champ à comparer existe
        if (!isset($data[$this->fieldToMatch])) {
            return false;
        }

        return $value === $data[$this->fieldToMatch];
    }
} 