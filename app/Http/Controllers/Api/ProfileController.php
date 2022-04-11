<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Hash;
use File;

class ProfileController extends Controller
{
    public function change_password(Request $request){
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'password' => 'required|min:6|max:100',
            'confirm_password' => 'required|same:password'
        ]);

        if($validator->fails()){
            return response()->json([
                'message' => 'Validator fails',
                'errors' => $validator->errors()
            ], 422);
        }

        $user= $request->user();
        if(Hash::check($request->old_password, $user->password)){
            $user->update([
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'message' => 'Password successfully updated'
            ], 200);
        }else{
            return response()->json([
                'message' => 'Old password does not matched'
            ], 200);
        }
    }

    public function update_profile(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:2|max:100',
            'profession' => 'nullable|max:100',
            'profile_photo' => 'nullable|image|mimes:jpg,bmp,png'
        ]);

        if($validator->fails()){
            return response()->json([
                'message' => 'Validator fails',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        if($request->hasFile('profile_photo')){
            if($user->profile_photo){
                $old_path = public_path().'uploads/profile_images/'.$user->profile_photo;
                if(File::exists($old_path)){
                    File::delete($old_path);
                }
            }

            $image_name = 'profile-image-'.time().'.'.$request->profile_photo->extension();
            $request->profile_photo->move(public_path('/uploads/profile_images'), $image_name);
        }else{
            $image_name = $user->profile_photo;
        }

        $user->update([
            'name' => $request->name,
            'profession' => $request->profession,
            'profile_photo' => $image_name 
        ]);
    }
}
