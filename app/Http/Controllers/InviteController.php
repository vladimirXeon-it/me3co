<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\User;
use App\Mail\SendEmail;
use App\Models\Project;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\InviteProject;
use App\Models\UserInvitation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class InviteController extends Controller
{
    function invite(Request $request) {
        $email = $request->email;
        $project_id = $request->project_id;
        $invitedUser = InviteProject::where('invite_to', $email)->where('project_id', $project_id)->first();
        if (!$invitedUser) {
            $random = Str::random(40);
            $domain = env('APP_URL');
            $url = $domain . '/invite-user?token=' . $random;
            $toEmail = $request->email;
            $mailData = [
                'url' => $url,
                'email' => $toEmail,
                'title' => 'Project Invitation',
                'btnTitle' => 'Accept Invitation',
                'body' => 'Welcome to Me3co.com, Accept invitation by clicking the above button.',
            ];
            // dd($mailData);
            Mail::to($toEmail)->send(new SendEmail($mailData));
            $userInvited = InviteProject::where('project_id',$project_id)
                    ->where('invite_to', $toEmail)
                    ->first();
            if(!$userInvited) {
                $user = new UserInvitation();
                $user->project_id = $project_id;
                $user->invitation_token = $random;
                $user->to_invite = $toEmail;
                $user->save();
                
                $inviteProject = new InviteProject();
                $inviteProject->project_id = $request->project_id; 
                $inviteProject->invite_to = $toEmail;
                $inviteProject->status ='0'; 
                $inviteProject->save(); 
                return redirect()->back()->with('message', 'Invitation link has sent to your invited user. User will be added in this project after accepting invitation');
            }else{
                return redirect()->back()->with('message', 'This user is already invited');
            }
        }else{
            return redirect()->back()->with('message', 'This user is already invited');
        }       
    }
    function inviteUser(Request $request) {
        $token = $request->query('token');
        $check = UserInvitation::where('invitation_token', $token)->first();
        if($check){
            $projectId = $check->project_id;
            $email = $check->to_invite;
            $user = User::where('email', $email)->first();
            if($user){
                return view("user.auth.invite-login", compact('projectId','email'));
            }else{
                $projectId = $check->project_id;
                $email = $check->to_invite;
                return view('user.auth.invite-register', compact('projectId', 'email'));
            }
        }else{
            dd('Token has expired. Please, contact the sender to send again!');
        }
    }
    public function inviteLogin(Request $request) {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);
        $remember_me = $request->has('remember_me') ? true : false; 
        if(Auth::attempt($credentials, $remember_me)){
            $inviteProject =InviteProject::where('project_id', $request->project_id)
                ->where('invite_to', $request->email)->first();
            $inviteProject->status ='1'; 
            $inviteProject->update(); 
            if($inviteProject){
                $userInvite = UserInvitation::where('project_id', $request->project_id)
                    ->where('to_invite', $request->email)->first();
                $userInvite->delete();     
            }
            return redirect()->route('project');
        }
        return redirect()->back()->with("message", "Wrong Credentials!");
    }
     public function inviteRegister(Request $request) {
        // dd($request->all());
     $user = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'company' => 'required',
            'username' => 'required',
            'password' => 'required',
            'business_type' => 'required',
        ]);

        if(email_exists($request->post('email'))) {
            return back()->with("message", "⚠ Email Already Exists");
        }
        if(username_exists($request->post('username'))) {
            return back()->with("message", "⚠ Username Already Exists");
        }
        $plan = Plan::where('type', 0)->first();
        $user['password'] = Hash::make($request->post('password'));
        $user['plan_id'] =  $plan->id;
        $subsription_period = $plan->time_unit;

        if($subsription_period == 0) {
            $sub_end = \now();

            $sub_end->addMonths(1);
        } else {
            $sub_end = \now();

            $sub_end->addMonths(12);
        }
        $user['subscription_end'] =  $sub_end;
        $user['agree'] =  $request->agree ?? '0';
        $credentials = User::create($user);

        if($credentials) {
            $inviteProject =InviteProject::where('project_id', $request->project_id)
                ->where('invite_to', $request->email)->first();
            $inviteProject->status ='1'; 
            $inviteProject->update(); 
            if($inviteProject){
                $userInvite = UserInvitation::where('project_id', $request->project_id)
                ->where('to_invite', $request->email)->first();
                $userInvite->delete();     
            }
            Auth::login($credentials, $remember = true);
            create_notification("{$credentials->name} Registered To the Me3Co.com", "success");
            return \redirect()->route('subscription.purchase');
        }

        return back()->with("message", "⚠ Error while registering");

    }

    function inviteProjectUser($id) {
        $projectName = Project::find($id)->name;
        $projectUsers = InviteProject::where('project_id',$id)->get();
        $projectId = $id;
        return view("user.projects.invite-user", compact('projectUsers', 'projectId', 'projectName'));
    }
    public function deleteInviteUser($id) {
        $isSuccess = InviteProject::find($id)->delete();
        if($isSuccess) {
            return back()->with("message", "&check; Record Deleted Successfully!");
        }

        return back()->with("message", "⚠ Error While Deleting Record!");
    }
}