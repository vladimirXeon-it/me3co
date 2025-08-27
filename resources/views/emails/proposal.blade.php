<h3 style="font-style: italic">
    Greetings Mr. {{ $project->projectCustomer->name }}, <br />

    My name is {{ $user->name }} from {{ $user->company }}, and I've completed estimation of project
    {{ $project->name }}.
    I've attached Proposal letter as PDF with this email.
    <br />
    <br />
    {{ $user->name }}
    <br />
    <br />
    {{ $user->company }}
    <br />
    <br />
    {{ $user->email }}
</h3>
