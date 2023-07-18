<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Intervention\Image\Facades\Image;


class ProfileController extends Controller
{
    public function profile(Request $request): View
    {
        return view('backend.admin.profile.index', [
            'user' => $request->user(),
        ]);
    }

    public function profileUpdate(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|max:255|unique:users,email,'.Auth::user()->id,
            'phone_number' => 'nullable|min:11|max:14',
            'profile_photo' => 'nullable|image|mimes:png,jpg,jpeg',
        ]);

        Auth::user()->update($request->except('profile_photo'));

        // Profile Photo Upload
        if($request->hasFile('profile_photo')){
            if(Auth::user()->profile_photo != 'default_profile_photo.png'){
                unlink(base_path("public/uploads/profile_photo/").Auth::user()->profile_photo);
            }
            $profile_photo_name = Auth::user()->id."-Profile-Photo".".". $request->file('profile_photo')->getClientOriginalExtension();
            $upload_link = base_path("public/uploads/profile_photo/").$profile_photo_name;
            Image::make($request->file('profile_photo'))->resize(120, 120)->save($upload_link);
            Auth::user()->update([
                'profile_photo' => $profile_photo_name
            ]);
        }

        $notification = array(
            'message' => 'Profile updated successfully.',
            'alert-type' => 'success'
        );

        return back()->with($notification);
    }

    public function passwordUpdate(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
            'password_confirmation' => ['required'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $notification = array(
            'message' => 'Profile updated successfully.',
            'alert-type' => 'success'
        );

        return back()->with($notification);
    }
}
