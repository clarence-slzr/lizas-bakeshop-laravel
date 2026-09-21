<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();
        $activity = $this->getActivityStats($user);

        return view('profile.edit', [
            'user' => $user,
            'activity' => $activity,
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validated();

        if ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->fill($data);
        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return Redirect::route('profile.edit')->with('status', 'password-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    private function getActivityStats($user): array
    {
        if ($user->role === 'admin') {
            return [
                'type' => 'admin',
                'stats' => [
                    ['label' => 'Products Managed', 'value' => Product::count(), 'icon' => 'fa-bread-slice'],
                    ['label' => 'Total Users', 'value' => User::count(), 'icon' => 'fa-users'],
                    ['label' => 'Total Orders', 'value' => Order::count(), 'icon' => 'fa-receipt'],
                    ['label' => 'Completed', 'value' => Order::where('status', 'completed')->count(), 'icon' => 'fa-circle-check'],
                ],
            ];
        }

        $weekStart = now()->startOfWeek();

        $weekOrders = Order::where('processed_by', $user->id)
            ->where('order_date', '>=', $weekStart);

        $weekCount = $weekOrders->count();
        $weekTotal = (clone $weekOrders)->sum('total_amount');
        $avg = $weekCount > 0 ? $weekTotal / $weekCount : 0;

        $refunds = Order::where('processed_by', $user->id)
            ->where('status', 'refunded')
            ->count();

        return [
            'type' => 'cashier',
            'stats' => [
                ['label' => 'Orders Processed', 'value' => $weekCount, 'icon' => 'fa-receipt'],
                ['label' => 'Total Sales', 'value' => '₱' . number_format($weekTotal, 2), 'icon' => 'fa-peso-sign'],
                ['label' => 'Average/Order', 'value' => '₱' . number_format($avg, 2), 'icon' => 'fa-chart-simple'],
                ['label' => 'Refunds', 'value' => $refunds, 'icon' => 'fa-rotate-left'],
            ],
        ];
    }
}
