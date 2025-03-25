<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

class SiteSelector extends Model
{
    const SESSION_KEY = 'selected_site';

    public function setSite(Site $site)
    {
        Session::put(self::SESSION_KEY, $site->uuid);
    }

    public function getSite(): ?Site
    {
        return once(function () {
            $site_uuid = Session::get(self::SESSION_KEY);

            return $site_uuid ? Site::where('uuid',$site_uuid)->first() : null;
        });
    }

    public function clearSite()
    {
        Session::forget(self::SESSION_KEY);
    }

}
