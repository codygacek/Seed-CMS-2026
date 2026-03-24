<?php

namespace App\Http\Controllers\Frontend;

use Hash;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ResourceAuthenticationController extends Controller
{
    /**
     * Check resource auth_token
     *
     * @return \Illuminate\Http\Response
     */
    public function check(Request $request)
    {
        $validated_data = $request->validate([
            'tokenable_id' => 'required',
            'tokenable_type' => 'required',
            'password' => 'required'
        ]);

        $model = $request->tokenable_type;

        $resource = $model::find($request->tokenable_id);

        if( Hash::check($request->password, $resource->token->token) ) {
            if(!$request->session()->has('authenticated.resources')) {
                $request->session()->put('authenticated.resources', []);
            }

            if (!in_array($resource->slug, $request->session()->get('authenticated.resources'))) {
                $request->session()->push('authenticated.resources', $resource->slug);
            }

            return redirect('/'.$resource->slug);
        }

        return redirect()->back()
                         ->with(['message' => 'Incorrect password', 'message_type' => 'error']);;

    }
}
