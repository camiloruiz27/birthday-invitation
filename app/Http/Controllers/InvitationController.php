<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class InvitationController extends Controller
{
    /**
     * Display the birthday invitation.
     */
    public function index(): View
    {
        return view('invitation');
    }
}
