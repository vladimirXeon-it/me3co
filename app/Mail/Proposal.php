<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Project;

class Proposal extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $project;

    public $user;

    public $file;

    public function __construct(User $user, Project $project, $file)
    {
        $this->user = $user;
        $this->project = $project;
        $this->file = $file;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.proposal', ['user' => $this->user, 'project' => $this->project])->attach($this->file, [
            'as' => 'Project-Proposal.pdf',
            'mime' => 'application.pdf'
        ])
        ->subject("Project Proposal for {$this->project->name}")
        ->from($address = $this->user->email, $name = $this->user->company);
    }
}