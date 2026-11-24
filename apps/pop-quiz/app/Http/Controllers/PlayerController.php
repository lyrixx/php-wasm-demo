<?php

namespace App\Http\Controllers;

use App\Http\Requests\JoinRequest;
use App\Models\Player;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PlayerController extends Controller
{
    public function create(): View
    {
        return view('players.create', [
            'avatars' => Player::AVATARS,
        ]);
    }

    public function store(JoinRequest $request): RedirectResponse
    {
        $player = Player::join($request->validated('nickname'), $request->validated('avatar'));

        Auth::login($player);
        $request->session()->regenerate();

        return redirect()->intended(route('home'))->with('status', "Let's go, {$player->nickname}!");
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
