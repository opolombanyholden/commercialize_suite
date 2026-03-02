<?php

namespace App\Traits;

use App\Enums\UserVersion;

trait HasVersion
{
    public function hasFeature(string $feature): bool
    {
        return config('commercialize.features.' . $this->version . '.' . $feature, false);
    }

    public function hasVersionOrHigher(string $minVersion): bool
    {
        $versions = ['light' => 1, 'standard' => 2, 'pro' => 3, 'enterprise' => 4];
        $userVersion = $versions[$this->version] ?? 0;
        $requiredVersion = $versions[$minVersion] ?? 0;
        return $userVersion >= $requiredVersion;
    }

    public function getAvailableFeatures(): array
    {
        return config('commercialize.features.' . $this->version, []);
    }

    public function getVersionEnum(): UserVersion
    {
        return UserVersion::from($this->version);
    }

    public function getLimits(): array
    {
        return config('commercialize.limits.' . $this->version, []);
    }

    public function hasReachedLimit(string $limitType, int $currentCount): bool
    {
        $limits = $this->getLimits();
        $limit = $limits[$limitType] ?? 999999;
        return $currentCount >= $limit;
    }

    public function getMonthlyPrice(): int
    {
        return config('commercialize.pricing.' . $this->version . '.monthly_price', 0);
    }

    public function canUpgradeTo(string $targetVersion): bool
    {
        $versions = ['light' => 1, 'standard' => 2, 'pro' => 3, 'enterprise' => 4];
        $currentLevel = $versions[$this->version] ?? 0;
        $targetLevel = $versions[$targetVersion] ?? 0;
        return $targetLevel > $currentLevel;
    }

    public function getUpgradeOptions(): array
    {
        $versions = ['light', 'standard', 'pro', 'enterprise'];
        $currentIndex = array_search($this->version, $versions);
        if ($currentIndex === false) {
            return [];
        }
        return array_slice($versions, $currentIndex + 1);
    }
}
