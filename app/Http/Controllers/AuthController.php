<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Models\Transaction;
use App\Models\Document;
use Illuminate\Support\Facades\Redirect;
use App\Models\Plan;
use Stripe;
use Hash;

use App\Models\User;

class AuthController extends Controller
{
    public function login() {

        return view("user.auth.login");
    }

    public function do_login(Request $request) {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        $remember_me = $request->has('remember_me') ? true : false; 
        if(Auth::attempt($credentials, $remember_me)){
            $userId = Auth::user()->id;
            $currentLocation = User::where('id', $userId)->first()->current_location_file;
            if($currentLocation){
                if($currentLocation == '/'){
                    $windowLocation = User::where('id', $userId)->first()->current_location;
                    if($windowLocation){
                        return Redirect::away($windowLocation);
                    }else{
                        return redirect()->route('project');
                    }
                }
                $parts = explode('/', $currentLocation);
                // Replace any non-alphanumeric characters in the second part with an empty string
                $projectDirectory = preg_replace('/[^a-zA-Z0-9]/', '', $parts[2]);
                $projectId = Document::where('directory', $projectDirectory)->first()->project_id;
                $appUrl = env('APP_URL');
                // Create the target URL
                $targetUrl = "{$appUrl}/{$projectId}/application{$currentLocation}";
                // Redirect to the target URL
                return Redirect::away($targetUrl);
            }else{
                return redirect()->route('project');
            }
        }

        return redirect()->back()->with("message", "Wrong Credentials!");
    }

    public function register() {
        return view('user.auth.register');
    }

    public function do_register(Request $request) {
        $user = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'company' => 'required',
            'username' => 'required',
            'password' => 'required',
            'business_type' => 'required',
            'agree' => 'required',
        ]);

        if(email_exists($request->post('email'))) {
            return back()->with("message", "⚠ Email Already Exists");
        }
        if(username_exists($request->post('username'))) {
            return back()->with("message", "⚠ Username Already Exists");
        }
        $user['password'] = Hash::make($request->post('password'));
        $user['plan_id'] =  Plan::where('type', 0)->first()->id;
        $user['agree'] =  $request->agree == 'on'? '1':'0';
        // dd($user);
        $credentials = User::create($user);

        if($credentials) {
            Auth::login($credentials, $remember = true);
            create_notification("{$credentials->name} Registered To the Me3Co.com", "success");
            return \redirect()->route('subscription.purchase');
        }

        return back()->with("message", "⚠ Error while registering");

    }
    public function add_number() {
        return view('user.auth.phone');
    }
    public function do_add_number(Request $request) {
        $isSuccess = User::find(Auth::id())->update([
            'phone' => $request->post('phone')
        ]);

        if($isSuccess) {
            return \redirect()->route('project')->with('message', '&check; Phone Number Updated');
        }

        return back()->with('message', '&times; Error while updating phone Number');
    }
    public function purchase_subscription(Request $request) {
        return view('user.auth.subscription');
    }

    public function create_intent(Request $request, $id) {
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

        $plan = Plan::find($id);

        if(!$plan) {
            abort(404);
        }

        
        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => $plan->price * 100,
            'currency' => env('CURRENCY'),
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
        ]);


    
        $output = [
            'clientSecret' => $paymentIntent->client_secret,
            'plan_id' => $plan->id
        ];
        Transaction::create([
            'plan_id' => $plan->id,
            'payment_intent' => $paymentIntent->client_secret,
            'status' => 0,
            'price' => $plan->price
        ]);

        return $output;
    }

    public function confirm_subscription(Request $request) {
        $payment_intent = $request->input('payment_intent_client_secret');
        $transaction = Transaction::where('payment_intent', $payment_intent)->first();

        $subsription_period = $transaction->plan->time_unit;

        if($subsription_period == 0) {
            $sub_end = \now();

            $sub_end->addMonths(1);
        } else {
            $sub_end = \now();

            $sub_end->addMonths(12);
        }
        User::find(Auth::id())->update([
            'plan_id' => $transaction->plan_id,
            'subscription_end' => $sub_end
        ]);
        return redirect()->route('project');
    }
    public function logout() {
        Auth::logout();

        return back();
    }

    public function passwordReset() {
        return view("user.auth.reset");
    }
    public function passwordResetConfirm() {
        return view('user.auth.confirmation');
    }

    public function changePassword(Request $request, $token) {
        return view('user.auth.change', ['token' => $token]);
    }

    public function doChangePassword(Request $request, $token) {
        $user = User::where([
            'remember_token' => $token
        ])->first();

        if($user) {
            $user->update([
                'password' => Hash::make($request->post('password'))
            ]);

            return redirect()->route('login')->with('message', '&check; Password Updated');
        }
    }
}
