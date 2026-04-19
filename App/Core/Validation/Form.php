<?php

namespace App\Core\Validation;

class Form
{
    /**
     * Contient le HTML généré du formulaire.
     */
    private string $formCode = '';

    /**
     * Retourne le code HTML final du formulaire.
     */
    public function render(): string
    {
        return $this->formCode;
    }

    /**
     * Ouvre un formulaire HTML.
     */
    public function startForm(string $action = '#', string $method = 'post', array $attributes = []): self
    {
        $action = htmlspecialchars($action, ENT_QUOTES, 'UTF-8');
        $method = strtolower(trim($method));

        /* Sécurité : autorise seulement GET ou POST */
        if (!in_array($method, ['get', 'post'], true))
        {
            $method = 'post';
        }

        $this->formCode .= "<form action=\"{$action}\" method=\"{$method}\"";
        $this->formCode .= $attributes ? $this->addAttributes($attributes) : '';
        $this->formCode .= '>';

        return $this;
    }

    /**
     * Ferme le formulaire.
     */
    public function endForm(): self
    {
        $this->formCode .= '</form>';
        return $this;
    }

    /**
     * Ouvre une div.
     */
    public function startDiv(array $attributes = []): self
    {
        $this->formCode .= '<div';
        $this->formCode .= $attributes ? $this->addAttributes($attributes) : '';
        $this->formCode .= '>';

        return $this;
    }

    /**
     * Ferme une div.
     */
    public function endDiv(): self
    {
        $this->formCode .= '</div>';
        return $this;
    }

    /**
     * Ajoute un label.
     */
    public function addLabelFor(string $for, string $text, array $attributes = []): self
    {
        $for = htmlspecialchars($for, ENT_QUOTES, 'UTF-8');
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

        $this->formCode .= "<label for=\"{$for}\"";
        $this->formCode .= $attributes ? $this->addAttributes($attributes) : '';
        $this->formCode .= ">{$text}</label>";

        return $this;
    }

    /**
     * Ajoute un champ input.
     */
    public function addInput(string $type, string $name, array $attributes = []): self
    {
        $type = htmlspecialchars($type, ENT_QUOTES, 'UTF-8');
        $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');

        $this->formCode .= "<input type=\"{$type}\" name=\"{$name}\"";
        $this->formCode .= $attributes ? $this->addAttributes($attributes) : '';
        $this->formCode .= '>';

        return $this;
    }

    /**
     * Ajoute un textarea.
     */
    public function addTextarea(string $name, string $value = '', array $attributes = []): self
    {
        $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

        $this->formCode .= "<textarea name=\"{$name}\"";
        $this->formCode .= $attributes ? $this->addAttributes($attributes) : '';
        $this->formCode .= ">{$value}</textarea>";

        return $this;
    }

    /**
     * Ajoute un select avec options.
     */
    public function addSelect(string $name, array $options, array $attributes = [], mixed $selected = null): self
    {
        $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');

        $this->formCode .= "<select name=\"{$name}\"";
        $this->formCode .= $attributes ? $this->addAttributes($attributes) : '';
        $this->formCode .= '>';

        foreach ($options as $value => $text)
        {
            $valueEscaped = htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
            $textEscaped = htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
            $isSelected = (string) $selected === (string) $value ? ' selected' : '';

            $this->formCode .= "<option value=\"{$valueEscaped}\"{$isSelected}>{$textEscaped}</option>";
        }

        $this->formCode .= '</select>';

        return $this;
    }

    /**
     * Ajoute un bouton.
     */
    public function addButton(string $text, array $attributes = []): self
    {
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

        $this->formCode .= '<button';
        $this->formCode .= $attributes ? $this->addAttributes($attributes) : '';
        $this->formCode .= ">{$text}</button>";

        return $this;
    }

    /**
     * Génère les attributs HTML.
     * Gère aussi les attributs courts :
     * required, checked, disabled, etc.
     */
    private function addAttributes(array $attributes): string
    {
        $string = '';

        $shorts = [
            'checked',
            'disabled',
            'readonly',
            'multiple',
            'required',
            'autofocus',
            'novalidate',
            'formnovalidate'
        ];

        foreach ($attributes as $attribute => $value)
        {
            $attribute = htmlspecialchars((string) $attribute, ENT_QUOTES, 'UTF-8');

            if (in_array($attribute, $shorts, true))
            {
                if ($value)
                {
                    $string .= " {$attribute}";
                }

                continue;
            }

            $value = htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
            $string .= " {$attribute}=\"{$value}\"";
        }

        return $string;
    }
}