<?php

namespace App\Core;

class Form
{
    private string $formCode = '';

    public function create(): string
    {
        return $this->formCode;
    }

    public static function validate(?array $form = null, ?array $fields = null): bool
    {
        if (!is_array($form) || !is_array($fields))
        {
            return false;
        }

        foreach ($fields as $field)
        {
            if (!isset($form[$field]) || trim((string) $form[$field]) === '')
            {
                return false;
            }
        }

        return true;
    }

    public function startForm(string $action = '#', string $method = 'post', array $attributes = []): self
    {
        $action = htmlspecialchars($action, ENT_QUOTES, 'UTF-8');
        $method = strtolower(trim($method));

        $this->formCode .= "<form action=\"{$action}\" method=\"{$method}\"";
        $this->formCode .= $attributes ? $this->addAttributes($attributes) : '';
        $this->formCode .= '>';

        return $this;
    }

    public function endForm(): self
    {
        $this->formCode .= '</form>';
        return $this;
    }

    public function startDiv(array $attributes = []): self
    {
        $this->formCode .= '<div';
        $this->formCode .= $attributes ? $this->addAttributes($attributes) : '';
        $this->formCode .= '>';

        return $this;
    }

    public function endDiv(): self
    {
        $this->formCode .= '</div>';
        return $this;
    }

    public function addInput(string $type, string $name, array $attributes = []): self
    {
        $type = htmlspecialchars($type, ENT_QUOTES, 'UTF-8');
        $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');

        $this->formCode .= "<input type=\"{$type}\" name=\"{$name}\"";
        $this->formCode .= $attributes ? $this->addAttributes($attributes) : '';
        $this->formCode .= '>';

        return $this;
    }

    public function addLabelFor(string $for, string $text, array $attributes = []): self
    {
        $for = htmlspecialchars($for, ENT_QUOTES, 'UTF-8');
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

        $this->formCode .= "<label for=\"{$for}\"";
        $this->formCode .= $attributes ? $this->addAttributes($attributes) : '';
        $this->formCode .= ">{$text}</label>";

        return $this;
    }

    public function addTextarea(string $name, string $value = '', array $attributes = []): self
    {
        $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

        $this->formCode .= "<textarea name=\"{$name}\"";
        $this->formCode .= $attributes ? $this->addAttributes($attributes) : '';
        $this->formCode .= ">{$value}</textarea>";

        return $this;
    }

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
            $isSelected = ((string) $selected === (string) $value) ? ' selected' : '';

            $this->formCode .= "<option value=\"{$valueEscaped}\"{$isSelected}>{$textEscaped}</option>";
        }

        $this->formCode .= '</select>';

        return $this;
    }

    public function addButton(string $text, array $attributes = []): self
    {
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

        $this->formCode .= '<button';
        $this->formCode .= $attributes ? $this->addAttributes($attributes) : '';
        $this->formCode .= ">{$text}</button>";

        return $this;
    }

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
            'formnovalidate',
            'selected'
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