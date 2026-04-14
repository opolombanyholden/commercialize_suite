<?php

namespace App\Traits;

use App\Enums\UserVersion;

trait HasVersion
{
    /**
     * Édition effective utilisée pour le gating de features.
     *
     * Si COMMERCIALIZE_EDITION est définie → prime sur user.version.
     * Sinon fallback sur la version individuelle (mode SaaS).
     */
    public function effectiveEdition(): string
    {
        return config('commercialize.deployment_edition') ?: $this->version;
    }

    public function hasFeature(string $feature): bool
    {
        $overrides = config('commercialize.feature_overrides', []);
        if (array_key_exists($feature, $overrides)) {
            return filter_var($overrides[$feature], FILTER_VALIDATE_BOOLEAN);
        }

        return (bool) config('commercialize.features.' . $this->effectiveEdition() . '.' . $feature, false);
    }

    public function hasVersionOrHigher(string $minVersion): bool
    {
        $versions = ['light' => 1, 'standard' => 2, 'pro' => 3, 'enterprise' => 4];
        $userVersion = $versions[$this->effectiveEdition()] ?? 0;
        $requiredVersion = $versions[$minVersion] ?? 0;
        return $userVersion >= $requiredVersion;
    }

    public function getAvailableFeatures(): array
    {
        $features = config('commercialize.features.' . $this->effectiveEdition(), []);
        $overrides = config('commercialize.feature_overrides', []);

        foreach ($overrides as $feature => $value) {
            $features[$feature] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        return $features;
    }

    public function getVersionEnum(): UserVersion
    {
        return UserVersion::from($this->effectiveEdition());
    }

    public function getLimits(): array
    {
        return config('commercialize.limits.' . $this->effectiveEdition(), []);
    }

    public function hasReachedLimit(string $limitType, int $currentCount): bool
    {
        $limits = $this->getLimits();
        $limit = $limits[$limitType] ?? 999999;
        return $currentCount >= $limit;
    }

    public function getMonthlyPrice(): int
    {
        return config('commercialize.pricing.' . $this->effectiveEdition() . '.monthly_price', 0);
    }

    public function canUpgradeTo(string $targetVersion): bool
    {
        if (config('commercialize.deployment_edition')) {
            return false;
        }

        $versions = ['light' => 1, 'standard' => 2, 'pro' => 3, 'enterprise' => 4];
        $currentLevel = $versions[$this->version] ?? 0;
        $targetLevel = $versions[$targetVersion] ?? 0;
        return $targetLevel > $currentLevel;
    }

    public function getUpgradeOptions(): array
    {
        if (config('commercialize.deployment_edition')) {
            return [];
        }

        $versions = ['light', 'standard', 'pro', 'enterprise'];
        $currentIndex = array_search($this->version, $versions);
        if ($currentIndex === false) {
            return [];
        }
        return \array_slice($versions, $currentIndex + 1);
    }
}
