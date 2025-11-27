<?php

namespace App\Http\Controllers;

use App\Models\CmsAchievement;
use App\Models\CmsActivity;
use App\Models\CmsCareer;
use App\Models\CmsClient;
use App\Models\CmsContent;
use App\Models\CmsHeroSlide;
use App\Models\CmsService;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class CompanyProfileController extends Controller
{
    private function getContent($key)
    {
        return CmsContent::where('key', $key)->first();
    }

    public function home()
    {
        $heroSlides = CmsHeroSlide::orderBy('order')->get();
        
        $services = CmsService::orderBy('order')->take(3)->get();
        $clients = CmsClient::orderBy('order')->take(6)->get();
        $activities = CmsActivity::latest('date')->take(3)->get();
        
        return view('company_profile.home', compact('heroSlides', 'services', 'clients', 'activities'));
    }

    public function profile()
    {
        $about = $this->getContent('about_us');
        $visi = $this->getContent('visi');
        $misi = $this->getContent('misi');
        $hsse = $this->getContent('hsse');
        $archipelago = $this->getContent('archipelago');

        return view('company_profile.profile', compact('about', 'visi', 'misi', 'hsse', 'archipelago'));
    }

    public function services()
    {
        $services = CmsService::orderBy('order')->get();
        return view('company_profile.services', compact('services'));
    }

    public function achievements()
    {
        $achievements = CmsAchievement::orderBy('order')->get();
        return view('company_profile.achievements', compact('achievements'));
    }

    public function activities()
    {
        $activities = CmsActivity::latest('date')->get();
        return view('company_profile.activities', compact('activities'));
    }

    public function activityDetail(CmsActivity $activity)
    {
        return view('company_profile.activity_detail', compact('activity'));
    }

    public function clients()
    {
        $clients = CmsClient::orderBy('order')->get();
        return view('company_profile.clients', compact('clients'));
    }

    public function career()
    {
        $careers = CmsCareer::where('is_active', true)->latest()->get();
        return view('company_profile.career', compact('careers'));
    }

    public function contact()
    {
        return view('company_profile.contact');
    }

    public function sendContact(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        ContactMessage::create($data);

        return back()->with('success', 'Message sent successfully! We will get back to you soon.');
    }
}
