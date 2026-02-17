<?php

namespace App\Services;

use App\Http\Requests\StoreActualiteRequest;
use Illuminate\Http\Request;

class ActualiteValidationService
{
    /**
     * Valide une requête d'actualité avec normalisation de date et support WebP.
     *
     * @param Request $request
     * @return array
     */
    public function validateRequest(Request $request): array
    {
        // Support both StoreActualiteRequest and plain Request in tests
        if (method_exists($request, 'validated')) {
            return $request->validated();
        }

        // Normalize slashed date format before validating
        $this->normalizeDateP($request);
        
        // Normalize archive checkbox value before validating
        $this->normalizeArchive($request);

        $formRequest = new StoreActualiteRequest();
        $rules = $this->addWebpSupportToImageRules($formRequest->rules());
        $messages = $this->getValidationMessages($formRequest);
        $attributes = $this->getValidationAttributes($formRequest);

        return $request->validate($rules, $messages, $attributes);
    }

    /**
     * Normalise le format de date d/m/Y vers Y-m-d si nécessaire.
     */
    private function normalizeDateP(Request $request): void
    {
        if ($request->has('dateP') && str_contains($request->dateP, '/')) {
            $d = \DateTime::createFromFormat('d/m/Y', $request->dateP);
            if ($d) {
                $request->merge(['dateP' => $d->format('Y-m-d')]);
            }
        }
    }

    /**
     * Normalise la valeur de la checkbox archive en booléen.
     * Une checkbox envoie "on" quand elle est cochée, rien quand elle n'est pas cochée.
     */
    private function normalizeArchive(Request $request): void
    {
        $archiveValue = false;
        if ($request->has('archive')) {
            $inputValue = $request->input('archive');
            // Convertir "on", true, "1", 1 en true, tout le reste en false
            $archiveValue = in_array($inputValue, ['on', true, '1', 1], true);
        }
        $request->merge(['archive' => (bool) $archiveValue]);
    }

    /**
     * Retourne les messages de validation pour les images.
     *
     * @param StoreActualiteRequest $formRequest
     * @return array
     */
    private function getValidationMessages(StoreActualiteRequest $formRequest): array
    {
        $messages = method_exists($formRequest, 'messages') ? $formRequest->messages() : [];
        return array_merge($messages, [
            'images.*.image' => __('actualite.validation.image_format'),
            'images.*.mimes' => __('actualite.validation.image_format'),
            'images.*.max'   => __('actualite.validation.image_max'),
        ]);
    }

    /**
     * Retourne les attributs de validation pour les images.
     *
     * @param StoreActualiteRequest $formRequest
     * @return array
     */
    private function getValidationAttributes(StoreActualiteRequest $formRequest): array
    {
        $attributes = method_exists($formRequest, 'attributes') ? $formRequest->attributes() : [];
        return array_merge($attributes, [
            'images.*' => __('actualite.validation.image_label'),
        ]);
    }

    /**
     * Ajoute WebP aux règles de validation des images d'actualité.
     *
     * @param array<string, mixed> $rules
     * @return array<string, mixed>
     */
    private function addWebpSupportToImageRules(array $rules): array
    {
        if (!array_key_exists('images.*', $rules)) {
            return $rules;
        }

        $rules['images.*'] = $this->ensureRuleAllowsWebp($rules['images.*']);
        return $rules;
    }

    /**
     * Ajoute WebP à une règle de validation (string ou array).
     *
     * @param mixed $rule
     * @return mixed
     */
    private function ensureRuleAllowsWebp($rule)
    {
        if (is_string($rule)) {
            return $this->addWebpToStringRule($rule);
        }

        if (is_array($rule)) {
            return $this->addWebpToArrayRule($rule);
        }

        return $rule;
    }

    /**
     * Ajoute WebP à une règle de validation sous forme de string.
     *
     * @param string $rule
     * @return string
     */
    private function addWebpToStringRule(string $rule): string
    {
        $parts = explode('|', $rule);
        $hasMimes = false;
        foreach ($parts as &$p) {
            if (str_starts_with($p, 'mimes:')) {
                $hasMimes = true;
                $p = $this->addWebpToMimesString($p);
            }
        }
        unset($p);
        if (! $hasMimes) {
            $parts[] = 'mimes:jpeg,png,jpg,gif,webp';
        }
        return implode('|', $parts);
    }

    /**
     * Ajoute WebP à une règle de validation sous forme de array.
     *
     * @param array $rule
     * @return array
     */
    private function addWebpToArrayRule(array $rule): array
    {
        $hasMimes = false;
        foreach ($rule as $i => $r) {
            if (!is_string($r)) {
                continue;
            }
            if (str_starts_with($r, 'mimes:')) {
                $hasMimes = true;
                $rule[$i] = $this->addWebpToMimesString($r);
            }
        }
        if (! $hasMimes) {
            $rule[] = 'mimes:jpeg,png,jpg,gif,webp';
        }
        return $rule;
    }

    /**
     * Ajoute WebP à une chaîne mimes: existante.
     *
     * @param string $mimesString
     * @return string
     */
    private function addWebpToMimesString(string $mimesString): string
    {
        $list = substr($mimesString, strlen('mimes:'));
        $mimes = array_filter(array_map('trim', explode(',', $list)));
        if (!in_array('webp', $mimes, true)) {
            $mimes[] = 'webp';
        }
        return 'mimes:' . implode(',', $mimes);
    }
}
