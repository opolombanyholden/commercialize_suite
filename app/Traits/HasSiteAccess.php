<?php

namespace App\Traits;

use App\Models\Site;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasSiteAccess
{
    public function sites(): BelongsToMany
    {
        return $this->belongsToMany(Site::class, 'user_site_access')
            ->withTimestamps();
    }

    public function getSiteIds(): array
    {
        return $this->sites()->pluck('sites.id')->toArray();
    }

    public function hasAccessToSite(int $siteId): bool
    {
        if ($this->hasAnyRole(['super_admin', 'company_admin'])) {
            return true;
        }
        return $this->sites()->where('sites.id', $siteId)->exists();
    }

    public function hasAccessToAllSites(): bool
    {
        return $this->hasAnyRole(['super_admin', 'company_admin']);
    }

    public function assignSite(int $siteId): void
    {
        if (!$this->sites()->where('sites.id', $siteId)->exists()) {
            $this->sites()->attach($siteId);
        }
    }

    public function removeSite(int $siteId): void
    {
        $this->sites()->detach($siteId);
    }

    public function assignSites(array $siteIds): void
    {
        $this->sites()->sync($siteIds);
    }

    public function removeAllSites(): void
    {
        $this->sites()->detach();
    }

    public function getPrimarySite(): ?Site
    {
        return $this->sites()->wherePivot('is_primary', true)->first()
            ?? $this->sites()->first();
    }

    public function setPrimarySite(int $siteId): void
    {
        $this->sites()->updateExistingPivot($this->getSiteIds(), ['is_primary' => false]);
        if ($this->hasAccessToSite($siteId)) {
            $this->sites()->updateExistingPivot($siteId, ['is_primary' => true]);
        }
    }
}
