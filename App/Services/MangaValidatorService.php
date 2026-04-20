<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config\UploadConfig;
use App\Core\Validation\Validator;

class MangaValidatorService
{
    /**
     * Retourne le validator d’ajout.
     */
    public function makeCreateValidator(array $post, array $files): Validator
    {
        $validator = new Validator($post, $files);

        $validator
            ->required('livre', 'Le titre est obligatoire.')
            ->string('livre', 'Le titre doit être une chaîne.')
            ->maxLength('livre', 150, 'Le titre ne doit pas dépasser 150 caractères.')
            ->required('slug', 'Le slug est obligatoire.')
            ->string('slug', 'Le slug doit être une chaîne.')
            ->maxLength('slug', 150, 'Le slug ne doit pas dépasser 150 caractères.')
            ->required('numero', 'Le numéro est obligatoire.')
            ->integer('numero', 'Le numéro doit être un entier.')
            ->min('numero', 1, 'Le numéro doit être supérieur à 0.')
            ->max('numero', 999, 'Le numéro ne doit pas dépasser 999.')
            ->nullable('commentaire')
            ->string('commentaire', 'Le commentaire doit être un texte.')
            ->maxLength('commentaire', 1000, 'Le commentaire ne doit pas dépasser 1000 caractères.')
            ->fileRequired('image', 'Aucune image envoyée.')
            ->fileOk('image', 'Erreur lors de l’envoi du fichier.')
            ->imageExtension(
                'image',
                UploadConfig::allowedExtensions(),
                'Format image non autorisé.'
            )
            ->imageMime(
                'image',
                UploadConfig::allowedMimeTypes(),
                'Type MIME image non autorisé.'
            )
            ->maxFileSize(
                'image',
                UploadConfig::maxSize(),
                'L’image ne doit pas dépasser la taille autorisée.'
            );

        return $validator;
    }

    /**
     * Retourne le validator de modification.
     */
    public function makeUpdateValidator(array $post, array $files): Validator
    {
        $validator = new Validator($post, $files);

        $validator
            ->nullable('commentaire')
            ->string('commentaire', 'Le commentaire doit être un texte.')
            ->maxLength('commentaire', 1000, 'Le commentaire ne doit pas dépasser 1000 caractères.')
            ->nullable('jacquette')
            ->integer('jacquette', 'La note jacquette doit être un entier.')
            ->min('jacquette', 1, 'La note jacquette doit être supérieure ou égale à 1.')
            ->max('jacquette', 5, 'La note jacquette doit être inférieure ou égale à 5.')
            ->nullable('livre_note')
            ->integer('livre_note', 'La note du livre doit être un entier.')
            ->min('livre_note', 1, 'La note du livre doit être supérieure ou égale à 1.')
            ->max('livre_note', 5, 'La note du livre doit être inférieure ou égale à 5.');

        return $validator;
    }

    /**
     * Retourne le premier message d’erreur lisible.
     *
     * @param array<string, mixed> $errors
     */
    public function firstErrorMessage(
        array $errors,
        string $fallback = 'Le formulaire contient des erreurs.'
    ): string {
        foreach ($errors as $messages)
        {
            if (is_array($messages) && !empty($messages))
            {
                return (string) $messages[0];
            }

            if (is_string($messages) && trim($messages) !== '')
            {
                return $messages;
            }
        }

        return $fallback;
    }
}