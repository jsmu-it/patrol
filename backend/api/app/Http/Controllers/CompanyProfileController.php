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
use App\Models\Faq;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class CompanyProfileController extends Controller
{
    private function getContent($key)
    {
        return CmsContent::firstOrCreate(['key' => $key], ['title' => ucfirst(str_replace('_', ' ', $key))]);
    }

    public function home()
    {
        $heroSlides = CmsHeroSlide::orderBy('order')->get();
        
        $services = CmsService::orderBy('order')->take(3)->get();
        $clients = CmsClient::orderBy('order')->take(6)->get();
        $activities = CmsActivity::latest('date')->take(3)->get();
        $testimonials = Testimonial::approved()->orderByDesc('is_featured')->orderBy('order')->take(6)->get();
        
        return view('company_profile.home', compact('heroSlides', 'services', 'clients', 'activities', 'testimonials'));
    }

    public function faq()
    {
        $faqs = Faq::active()->orderBy('order')->get();
        $categories = $faqs->pluck('category')->filter()->unique();

        return view('company_profile.faq', compact('faqs', 'categories'));
    }

    public function testimonials()
    {
        $testimonials = Testimonial::approved()->orderByDesc('is_featured')->orderBy('order')->get();

        return view('company_profile.testimonials', compact('testimonials'));
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

    public function showApplyForm(CmsCareer $career)
    {
        return view('company_profile.apply_form', compact('career'));
    }

    public function contact()
    {
        return view('company_profile.contact');
    }

    public function privacy()
    {
        return view('company_profile.privacy');
    }

    public function sendApplication(Request $request)
    {
        $request->validate([
            'cms_career_id' => 'nullable|exists:cms_careers,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'cover_letter' => 'nullable|string',
            'resume' => 'required|file|mimes:pdf,doc,docx|max:2048',
            
            // Basic validation for other fields (can be made stricter if needed)
            'birth_city' => 'nullable|string',
            'birth_date' => 'nullable|date',
            'age' => 'nullable|integer',
            'gender' => 'nullable|string',
            'religion' => 'nullable|string',
            'blood_type' => 'nullable|string',
            'mother_name' => 'nullable|string',
            'marital_status' => 'nullable|string',
            'ktp_number' => 'nullable|string',
            'kk_number' => 'nullable|string',
            'height_cm' => 'nullable|integer',
            'weight_kg' => 'nullable|integer',
            'domicile_street' => 'nullable|string',
            'domicile_rt' => 'nullable|string',
            'domicile_rw' => 'nullable|string',
            'domicile_subdistrict' => 'nullable|string',
            'domicile_district' => 'nullable|string',
            'domicile_regency' => 'nullable|string',
            'domicile_province' => 'nullable|string',
            'education_level' => 'nullable|string',
            'education_school_name' => 'nullable|string',
            'education_major' => 'nullable|string',
            'education_graduation_year' => 'nullable|string',
            'education_city' => 'nullable|string',
            
            'satpam_qualification' => 'nullable|string',
            'satpam_kta_number' => 'nullable|string',
            'satpam_certificate_number' => 'nullable|string',
            'satpam_training_date' => 'nullable|date',
            'satpam_training_institution' => 'nullable|string',
            'satpam_training_location' => 'nullable|string',

            'uniform_shirt_size' => 'nullable|string',
            'uniform_pants_size' => 'nullable|string',
            'uniform_shoes_size' => 'nullable|string',

            'emergency_name' => 'nullable|string',
            'emergency_phone' => 'nullable|string',
            'emergency_relation' => 'nullable|string',

            'npwp' => 'nullable|string',
            'sim_a_number' => 'nullable|string',
            'sim_c_number' => 'nullable|string',
            'bpjs_tk_number' => 'nullable|string',
            'bpjs_kes_number' => 'nullable|string',

            'address_street' => 'nullable|string',
            'address_rt' => 'nullable|string',
            'address_rw' => 'nullable|string',
            'address_subdistrict' => 'nullable|string',
            'address_district' => 'nullable|string',
            'address_regency' => 'nullable|string',
            'address_province' => 'nullable|string',
            'address_postal_code' => 'nullable|string',

            'children_count' => 'nullable|integer',

            'exp1_year' => 'nullable|string',
            'exp1_position' => 'nullable|string',
            'exp1_company' => 'nullable|string',
            'exp1_city' => 'nullable|string',
            'exp2_year' => 'nullable|string',
            'exp2_position' => 'nullable|string',
            'exp2_company' => 'nullable|string',
            'exp2_city' => 'nullable|string',
            'exp3_year' => 'nullable|string',
            'exp3_position' => 'nullable|string',
            'exp3_company' => 'nullable|string',
            'exp3_city' => 'nullable|string',

            'cert1_date' => 'nullable|date',
            'cert1_training' => 'nullable|string',
            'cert1_organizer' => 'nullable|string',
            'cert1_city' => 'nullable|string',
            'cert2_date' => 'nullable|date',
            'cert2_training' => 'nullable|string',
            'cert2_organizer' => 'nullable|string',
            'cert2_city' => 'nullable|string',
            'cert3_date' => 'nullable|date',
            'cert3_training' => 'nullable|string',
            'cert3_organizer' => 'nullable|string',
            'cert3_city' => 'nullable|string',

            'instagram' => 'nullable|string',
            'facebook' => 'nullable|string',
            'twitter' => 'nullable|string',
            'tiktok' => 'nullable|string',
            'linkedin' => 'nullable|string',
            'youtube' => 'nullable|string',
        ]);

        $path = $request->file('resume')->store('resumes', 'public');

        \App\Models\JobApplication::create($request->except('resume') + ['resume_path' => $path, 'status' => 'pending']);

        return redirect()->route('career')->with('success', 'Application submitted successfully! We will review your profile.');
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
