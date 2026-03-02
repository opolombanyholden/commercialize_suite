<?php

use App\Services\NumberToWordsService;

if (!function_exists('number_to_words')) {
    /**
     * Convertir un nombre en lettres (français)
     *
     * @param float $number Le nombre à convertir
     * @param string $currency La devise (défaut: FCFA)
     * @return string
     */
    function number_to_words(float $number, string $currency = 'FCFA'): string
    {
        return NumberToWordsService::toWords($number, $currency);
    }
}

if (!function_exists('format_currency')) {
    /**
     * Formater un montant en devise
     *
     * @param float $amount Le montant
     * @param string $currency La devise
     * @return string
     */
    function format_currency(float $amount, string $currency = 'FCFA'): string
    {
        return number_format($amount, 0, ',', ' ') . ' ' . $currency;
    }
}

if (!function_exists('user_has_feature')) {
    /**
     * Vérifier si l'utilisateur courant a accès à une feature
     *
     * @param string $feature Nom de la feature
     * @return bool
     */
    function user_has_feature(string $feature): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }

        return $user->hasFeature($feature);
    }
}

if (!function_exists('user_has_version')) {
    /**
     * Vérifier si l'utilisateur a au moins la version spécifiée
     *
     * @param string $version Version minimale requise
     * @return bool
     */
    function user_has_version(string $version): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }

        return $user->hasVersionOrHigher($version);
    }
}

if (!function_exists('current_company')) {
    /**
     * Obtenir l'entreprise de l'utilisateur courant
     *
     * @return \App\Models\Company|null
     */
    function current_company()
    {
        $user = auth()->user();
        
        return $user?->company;
    }
}

if (!function_exists('current_site')) {
    /**
     * Obtenir le site principal de l'utilisateur courant
     *
     * @return \App\Models\Site|null
     */
    function current_site()
    {
        $user = auth()->user();
        
        return $user?->getPrimarySite();
    }
}

if (!function_exists('generate_reference')) {
    /**
     * Générer une référence unique
     *
     * @param string $prefix Préfixe de la référence
     * @return string
     */
    function generate_reference(string $prefix = 'REF'): string
    {
        return strtoupper($prefix) . '-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }
}
