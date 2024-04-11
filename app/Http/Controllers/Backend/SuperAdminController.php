<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Backend;
use Illuminate\Http\JsonResponse;

class SuperAdminController extends Controller
{
    public function createSuperAdmin(Request $request):JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:8',
            ]);

            $user = new Backend();
            $user->name = $validatedData['name'];
            $user->email = $validatedData['email'];
            // $user->password = bcrypt($validatedData['password']);
            $user->password = password_hash($validatedData['password'], PASSWORD_DEFAULT);
            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Super admin created successfully!',
                'data' => $user
            ]);
        }catch(\Illuminate\Validation\ValidationException $e){
            return response()->json(['error'=>$e->validator->errors()->getMessages()]);
        }
         catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create super admin: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $superAdmin = Backend::find($id);
        if (!$superAdmin) {
            return response()->json(['message' => 'Super admin not found'], 404);
        }
        $superAdmin->delete();
        return response()->json(['message' => 'Super admin moved to trash successfully'], 200);
    }
}
