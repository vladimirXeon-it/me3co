<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;

class Location
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $clientIp = $request->ip();
        $details = file_get_contents("http://www.geoplugin.net/json.gp?ip=$clientIp");
        $obj = json_decode($details);
        if(Setting::where('user_id', Auth::id())->get()->count() == 0) {
            get_user_settings(Auth::id());
            if($obj->geoplugin_countryCode == 'US') {
                Setting::where('user_id', Auth::id())->update([
                    'measurement_system' => 0
            ]);
            }
        else {
                Setting::where('user_id', Auth::id())->update([
                'measurement_system' => 1
            ]);
        }
        }
        return $next($request);
    }
}