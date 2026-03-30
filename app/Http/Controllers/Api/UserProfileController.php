<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\ApiResponse;

class UserProfileController extends Controller
{
    /**
     * 🔹 LIST
     */
    public function index()
    {
        try {
            $users = UserProfile::orderBy('created_at', 'desc')->get();

            $data = $users->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'email' => $item->email,
                    'phone' => $item->phone,
                    'address' => $item->address,
                    'profile_image' => $item->profile_image ? asset('storage/' . $item->profile_image) : null,
                    'status' => $item->status, // Added status
                ];
            });

            return ApiResponse::success($data, 'Users fetched');

        } catch (\Exception $e) {
            return ApiResponse::error('Failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * 🔹 STORE
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:user_profiles,email',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'status' => 'required|in:Active,Inactive,Blocked', // Added validation
            ]);

            if ($request->hasFile('profile_image')) {
                $data['profile_image'] = $request->file('profile_image')->store('users', 'public');
            }

            $user = UserProfile::create($data);

            return ApiResponse::success($user, 'Created successfully');

        } catch (\Exception $e) {
            return ApiResponse::error('Create failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * 🔹 SHOW
     */
    public function show($id)
    {
        try {
            $user = UserProfile::find($id);

            if (!$user) {
                return ApiResponse::error('Not found', [], 404);
            }

            return ApiResponse::success($user);

        } catch (\Exception $e) {
            return ApiResponse::error('Error', ['error' => $e->getMessage()]);
        }
    }

    /**
     * 🔹 UPDATE
     */
    public function update(Request $request, $id)
    {
        try {
            $user = UserProfile::find($id);

            if (!$user) {
                return ApiResponse::error('Not found', [], 404);
            }

            $data = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:user_profiles,email,' . $id,
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'status' => 'required|in:Active,Inactive,Blocked', // Added validation
            ]);

            if ($request->hasFile('profile_image')) {
                if ($user->profile_image) {
                    Storage::disk('public')->delete($user->profile_image);
                }
                $data['profile_image'] = $request->file('profile_image')->store('users', 'public');
            }

            $user->update($data);

            return ApiResponse::success($user, 'Updated successfully');

        } catch (\Exception $e) {
            return ApiResponse::error('Update failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * 🔹 DELETE
     */
    public function destroy($id)
    {
        try {
            $user = UserProfile::find($id);

            if (!$user) {
                return ApiResponse::error('Not found', [], 404);
            }

            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }

            $user->delete();

            return ApiResponse::success([], 'Deleted successfully');

        } catch (\Exception $e) {
            return ApiResponse::error('Delete failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * 🔹 UPDATE USER STATUS
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $user = UserProfile::find($id);

            if (!$user) {
                return ApiResponse::error('User not found', [], 404);
            }

            $data = $request->validate([
                'status' => 'required|in:Active,Inactive,Blocked',
            ]);

            $user->update($data);

            return ApiResponse::success($user, 'User status updated successfully');

        } catch (\Exception $e) {
            return ApiResponse::error('Update failed', [
                'error' => $e->getMessage()
            ]);
        }
    }
}