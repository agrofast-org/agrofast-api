<?php

namespace App\Http\Controllers;

use App\Models\Hr\User;

class CashOutController extends Controller
{
    public function __construct() {}

    public function index()
    {
        $user = User::auth();

        return response()->json($user->cashOuts()->get());
    }

    public function show(string $uuid) {}
}
