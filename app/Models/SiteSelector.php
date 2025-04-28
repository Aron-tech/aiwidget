<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

class SiteSelector extends Model
{
    const SESSION_KEY = 'selected_site';

    public static function setSite(Site $site)
    {
        if (!auth()->user()->sites()->where('sites.id', $site->id)->exists()) {
            throw new \Exception('Unauthorized site selection attempt');
        }

        Session::put(self::SESSION_KEY, $site->uuid);
    }

    public static function getSite(): ?Site
    {
        return once(function () {
            $site_uuid = Session::get(self::SESSION_KEY);

            return $site_uuid ? Site::where('uuid',$site_uuid)->first() : null;
        });
    }

    public static function clearSite(): void
    {
        Session::forget(self::SESSION_KEY);
        Session::save();
    }

    public static function hasSite(): bool
    {
        return Session::has(self::SESSION_KEY);
    }

}
