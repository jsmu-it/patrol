<?php

namespace App\Http\Controllers\Pwa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PwaController extends Controller
{
    /**
     * Login page
     */
    public function login()
    {
        return view('pwa.login');
    }

    /**
     * Home page (requires auth via JS)
     */
    public function home()
    {
        return view('pwa.home');
    }

    /**
     * Attendance page
     */
    public function attendance()
    {
        return view('pwa.attendance');
    }

    /**
     * Patrol scan page
     */
    public function patrolScan()
    {
        return view('pwa.patrol.scan');
    }

    /**
     * Patrol form page
     */
    public function patrolForm()
    {
        return view('pwa.patrol.form');
    }

    /**
     * Leave list page
     */
    public function leaveList()
    {
        return view('pwa.leave.list');
    }

    /**
     * Leave form page
     */
    public function leaveForm()
    {
        return view('pwa.leave.form');
    }

    /**
     * Attendance history page
     */
    public function history()
    {
        return view('pwa.history');
    }

    /**
     * Profile page
     */
    public function profile()
    {
        return view('pwa.profile');
    }

    /**
     * Leave approval page (for admins)
     */
    public function approvalLeave()
    {
        return view('pwa.approval-leave');
    }
}
